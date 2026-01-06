<?php
// get_pressure_data.php

// Incluir el archivo de configuración que debe contener las variables de conexión a MariaDB:
// $db_user, $db_pass, $db_url, $db_database
include __DIR__ . '/../../config/config.php';
include __DIR__ . '/math_utils.php';
$PRESS_TREND_CACHE = __DIR__ . '/../../cache/pressure_trend.json';
$PRESS_TREND_TTL   = 15 * 60; // 15 minutos

/**
 * Función para devolver un error en formato JSON y terminar el script.
 * @param string $message Mensaje de error a devolver.
 */
function die_with_error($message) {
    header('Content-Type: application/json');
    die(json_encode([
        "error" => true,
        "message" => $message
    ]));
}

// CÁLCULO DE TENDENCIA DE LA PRESIÓN:
function computePressureTrend(mysqli $db) {
    $stmt = $db->prepare("
        SELECT UNIX_TIMESTAMP(timestamp) AS t, presion_relativa
        FROM meteo
        WHERE timestamp >= NOW() - INTERVAL 3 HOUR
          AND presion_relativa IS NOT NULL
        ORDER BY timestamp ASC
    ");
    $stmt->execute();
    $res = $stmt->get_result();

    $times = [];
    $values = [];

    while ($r = $res->fetch_assoc()) {
        $times[]  = intval($r['t']);
        $values[] = floatval($r['presion_relativa']);
    }

    if (count($values) < 50) return null;

    $emaVals = ema($values, 0.03); // más suave que temperatura
    $slope   = linearTrend($times, $emaVals); // hPa / segundo

    $slopeHour = $slope * 3600; // hPa / hora

    $threshold_stable = 0.5;
    $threshold_fast   = 2.0;

    if ($slopeHour > $threshold_fast) {
        $trend = 'fast_up';      // Subida muy rápida (> 2 hPa/h)
    } elseif ($slopeHour > $threshold_stable) {
        $trend = 'up';           // Subida moderada (0.5 a 2 hPa/h)
    } elseif ($slopeHour < -$threshold_fast) {
        $trend = 'fast_down';    // Caída muy rápida (< -2 hPa/h) - ¡Alerta de tormenta!
    } elseif ($slopeHour < -$threshold_stable) {
        $trend = 'down';         // Caída moderada (-0.5 a -2 hPa/h)
    } else {
        $trend = 'stable';       // Estable (-0.5 a 0.5 hPa/h)
    }

    return [
        'trend' => $trend,
        'slope' => number_format($slope, 7, '.', ''), // hPa/s
        'slope_hour' => round($slopeHour, 3), // hPa/h
        'computed_at' => date('Y-m-d H:i:s')
    ];
}


// ----------------------------------------------------
// 1. Conexión a la base de datos y obtención de datos
// ----------------------------------------------------

// Verificar si las variables de conexión están definidas después de la inclusión
if (!isset($db_url, $db_user, $db_pass, $db_database)) {
    die_with_error("Error: Las credenciales de la base de datos no están definidas en config.php.");
}

$mysqli = new mysqli($db_url, $db_user, $db_pass, $db_database);

if ($mysqli->connect_error) {
    // Error de conexión
    error_log("Error de conexión a la BD: " . $mysqli->connect_error);
    die_with_error("Error al conectar con la base de datos.");
}

// Consulta SQL para obtener la última presión relativa
$sql = "SELECT presion_relativa, presion_absoluta
        FROM meteo
        ORDER BY timestamp DESC
        LIMIT 1";

$result = $mysqli->query($sql);

if ($result === false) {
    // Error en la consulta
    error_log("Error en la consulta SQL: " . $mysqli->error);
    $mysqli->close();
    die_with_error("Error al ejecutar la consulta de datos de presión.");
}

if ($result->num_rows === 0) {
    // No hay datos
    $mysqli->close();
    die_with_error("No se encontraron datos de presión en la tabla 'meteo'.");
}

// 2. Obtener el valor y sanitizar
$row = $result->fetch_assoc();

// Valor de la presión
$pressure_rel = isset($row['presion_relativa']) && is_numeric($row['presion_relativa']) ? floatval($row['presion_relativa']) : null;
$pressure_rel = round($pressure_rel, 1);

// Valor de presión absoluta
$pressure_abs = isset($row['presion_absoluta']) && is_numeric($row['presion_absoluta']) ? floatval($row['presion_absoluta']) : null;
$pressure_abs = round($pressure_abs, 1);


$trendData = null;
$now = time();

if (file_exists($PRESS_TREND_CACHE)) {
    $cache = json_decode(file_get_contents($PRESS_TREND_CACHE), true);
    if ($cache && isset($cache['computed_at'])) {
        if ($now - strtotime($cache['computed_at']) < $PRESS_TREND_TTL) {
            $trendData = $cache;
        }
    }
}

if ($trendData === null) {
    $newTrend = computePressureTrend($mysqli);
    if ($newTrend !== null) {
        file_put_contents($PRESS_TREND_CACHE, json_encode($newTrend, JSON_PRETTY_PRINT));
        $trendData = $newTrend;
    }
}


$result->free();
$mysqli->close();


// ----------------------------------------------------
// 3. Cálculo del ángulo del manómetro
// ----------------------------------------------------
$minPres = 950;
$maxPres = 1050;
$minAnglePres = -134;
$maxAnglePres = 134;

// Mapeo lineal de la presión al ángulo
$pres_angle = ($pressure_rel - $minPres) * ($maxAnglePres - $minAnglePres) / ($maxPres - $minPres) + $minAnglePres;

// Limitar ángulo a extremos
if ($pres_angle < $minAnglePres) $pres_angle = $minAnglePres;
if ($pres_angle > $maxAnglePres) $pres_angle = $maxAnglePres;


// ----------------------------------------------------
// 4. Devolver JSON (Mismo formato anterior)
// ----------------------------------------------------
header('Content-Type: application/json');
echo json_encode([
    "pressure"   => $pressure_rel,
    "pressure_abs" => $pressure_abs,
    "pres_angle" => $pres_angle,
    "trend"      => $trendData['trend'] ?? null,
    "slope"        => $trendData['slope'] ?? null,
    "slope_hour"   => $trendData['slope_hour'] ?? null
], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_PRESERVE_ZERO_FRACTION);
?>
