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

$CACHE_FILE = __DIR__ . '/../../cache/cache_forecast.json';
$CACHE_TTL  = 30 * 60; // 30 minutos

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
// CONTROL DE CONCURRENCIA + CACHE
// ─────────────────────────────────────────────
$fp = fopen($CACHE_FILE, 'c+');
if (!$fp) {
    http_response_code(500);
    echo json_encode(["error" => "No se puede abrir la caché"]);
    exit;
}

flock($fp, LOCK_EX);

$cache = null;
$now = time();

// Leer caché existente
$contents = stream_get_contents($fp);
if ($contents) {
    $cache = json_decode($contents, true);
}

// Cache válida → devolver
if (
    $cache &&
    isset($cache['timestamp'], $cache['data']) &&
    ($now - $cache['timestamp']) < $CACHE_TTL
) {
    echo json_encode($cache['data']);
    flock($fp, LOCK_UN);
    fclose($fp);
    exit;
}

// ─────────────────────────────────────────────
// CONSULTA A OPEN-METEO (vía cURL)
// ─────────────────────────────────────────────
$response = curl_get_json($OPENMETEO_URL);

if ($response === null) {
    // Fallback: servir caché vieja si existe
    if ($cache && isset($cache['data'])) {
        echo json_encode($cache['data']);
    } else {
        http_response_code(502);
        echo json_encode(["error" => "Open-Meteo no responde"]);
    }
    flock($fp, LOCK_UN);
    fclose($fp);
    exit;
}

$data = json_decode($response, true);

// ─────────────────────────────────────────────
// GUARDAR NUEVA CACHE
// ─────────────────────────────────────────────
rewind($fp);
ftruncate($fp, 0);
fwrite($fp, json_encode([
    "timestamp" => $now,
    "data" => $data
], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));

flock($fp, LOCK_UN);
fclose($fp);

// ─────────────────────────────────────────────
// RESPUESTA
// ─────────────────────────────────────────────
echo json_encode($data);
