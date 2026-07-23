<?php
/* get_forecast.php */
// ─────────────────────────────────────────────
// CONFIG
// ─────────────────────────────────────────────
require_once __DIR__ . '/../../config/config.php';

header('Content-Type: application/json');

// Ajusta esto si usas variables en vez de constantes
$LAT = defined('LAT') ? LAT : ($lat ?? null);
$LON = defined('LON') ? LON : ($lon ?? null);

if ($LAT === null || $LON === null) {
    http_response_code(500);
    echo json_encode(["error" => "LAT/LON no definidos"]);
    exit;
}

// Caché en APCu (memoria compartida entre los procesos PHP-FPM) en vez de un
// archivo en disco: misma semántica (TTL manual comparado contra
// 'timestamp', fallback a la última caché conocida si Open-Meteo falla) pero
// sin I/O de filesystem en cada request. Si APCu no estuviera disponible, se
// degrada a "calcular siempre sin cachear" en vez de fallar.
$CACHE_KEY = 'clearsky_forecast';
$CACHE_TTL = 30 * 60; // 30 minutos
$apcuAvailable = function_exists('apcu_fetch');

$OPENMETEO_URL =
    "https://api.open-meteo.com/v1/forecast?" .
    "latitude={$LAT}" .
    "&longitude={$LON}" .
    "&hourly=temperature_2m,temperature_500hPa,temperature_300hPa," .
    "wind_speed_2m,wind_speed_500hPa,wind_speed_300hPa," .
    "relative_humidity_2m,pressure_msl,cloud_cover_low," .
    "cloud_cover_mid,cloud_cover_high,weathercode" .
    "&timezone=auto" .
    "&forecast_hours=9";

// ─────────────────────────────────────────────
// FUNCIÓN cURL
// ─────────────────────────────────────────────
function curl_get_json(string $url): ?string
{
    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_CONNECTTIMEOUT => 5,
        CURLOPT_TIMEOUT => 10,
        CURLOPT_SSL_VERIFYPEER => true,
        CURLOPT_USERAGENT => 'ForecastWidget/1.0'
    ]);

    $response = curl_exec($ch);

    if ($response === false) {
        curl_close($ch);
        return null;
    }

    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    return ($httpCode >= 200 && $httpCode < 300) ? $response : null;
}

// ─────────────────────────────────────────────
// LEER CACHÉ (APCu)
// ─────────────────────────────────────────────
$now = time();
$cache = null;
$found = false;

if ($apcuAvailable) {
    $cache = apcu_fetch($CACHE_KEY, $found);
}

// Cache válida → devolver
if (
    $found &&
    isset($cache['timestamp'], $cache['data']) &&
    ($now - $cache['timestamp']) < $CACHE_TTL
) {
    echo json_encode($cache['data']);
    exit;
}

if (!$apcuAvailable) {
    // Sin APCu disponible: calcular siempre, sin cachear.
    $response = curl_get_json($OPENMETEO_URL);
    if ($response === null) {
        http_response_code(502);
        echo json_encode(["error" => "Open-Meteo no responde"]);
        exit;
    }
    echo json_encode(json_decode($response, true));
    exit;
}

// ─────────────────────────────────────────────
// CONTROL DE CONCURRENCIA: solo un proceso recalcula a la vez. apcu_add()
// falla atómicamente si la clave de lock ya existe, evitando que varias
// peticiones simultáneas golpeen Open-Meteo a la vez cuando expira el TTL.
// ─────────────────────────────────────────────
$lockKey = $CACHE_KEY . '_lock';

if (!apcu_add($lockKey, true, 10)) {
    // Otro proceso ya está recalculando: servir la caché existente aunque
    // esté caducada, antes que hacer esperar al usuario.
    if ($found && isset($cache['data'])) {
        echo json_encode($cache['data']);
    } else {
        http_response_code(503);
        echo json_encode(["error" => "Caché en proceso de actualización, reintenta en unos segundos"]);
    }
    exit;
}

// ─────────────────────────────────────────────
// CONSULTA A OPEN-METEO (vía cURL)
// ─────────────────────────────────────────────
$response = curl_get_json($OPENMETEO_URL);

if ($response === null) {
    apcu_delete($lockKey);
    // Fallback: servir caché vieja si existe
    if ($found && isset($cache['data'])) {
        echo json_encode($cache['data']);
    } else {
        http_response_code(502);
        echo json_encode(["error" => "Open-Meteo no responde"]);
    }
    exit;
}

$data = json_decode($response, true);

// ─────────────────────────────────────────────
// GUARDAR NUEVA CACHE
// ─────────────────────────────────────────────
apcu_store($CACHE_KEY, [
    "timestamp" => $now,
    "data" => $data
]);
apcu_delete($lockKey);

// ─────────────────────────────────────────────
// RESPUESTA
// ─────────────────────────────────────────────
echo json_encode($data);
