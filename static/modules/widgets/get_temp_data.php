<?php
// get_temp_data.php
include __DIR__ . '/../../config/config.php';
include __DIR__ . '/math_utils.php';
$TEMP_TREND_CACHE = __DIR__ . '/../../cache/temp_trend.json';
$TEMP_TREND_TTL   = 10 * 60; // 10 minutos


// Parámetros para el cálculo del ángulo
$minTemp = -20;
$maxTemp = 50;
$minAngle = -145;
$maxAngle = 145;

/**
 * Función para devolver un error en formato JSON y terminar el script.
 * @param string $message Mensaje de error a devolver.
 */
function die_with_error($message)
{
    header('Content-Type: application/json');
    die(json_encode([
        "error" => true,
        "message" => $message
    ]));
}

// ----------------------------------------------------
// 1. Conexión a la base de datos y obtención de datos
// ----------------------------------------------------
$mysqli = new mysqli($db_url, $db_user, $db_pass, $db_database);

if ($mysqli->connect_error) {
    // Error de conexión
    error_log("Error de conexión a la BD: " . $mysqli->connect_error);
    die_with_error("Error al conectar con la base de datos.");
}

// FUNCIÓN PARA CALCULAR LA TENDENCIA TÉRMICA EXTERIOR:
function computeTempTrend(mysqli $db)
{
    $stmt = $db->prepare("
        SELECT UNIX_TIMESTAMP(timestamp) AS t, temperatura
        FROM meteo
        WHERE timestamp >= NOW() - INTERVAL 6 HOUR
          AND temperatura IS NOT NULL
        ORDER BY timestamp ASC
    ");
    $stmt->execute();
    $res = $stmt->get_result();

    $times = [];
    $values = [];

    while ($r = $res->fetch_assoc()) {
        $times[]  = intval($r['t']);
        $values[] = floatval($r['temperatura']);
    }

    if (count($values) < 50) return null;

    $emaVals = ema($values, 0.05);
    $slope   = linearTrend($times, $emaVals); // °C por segundo

    // Normalizamos a °C / hora
    $slopeHour = $slope * 3600;

    if ($slopeHour > 0.2)      $trend = 'up';
    elseif ($slopeHour < -0.2) $trend = 'down';
    else                       $trend = 'stable';

    return [
        'trend' => $trend,
        'slope_hour' => round($slopeHour, 3),
        'computed_at' => date('Y-m-d H:i:s')
    ];
}


// Consulta SQL para obtener la última temperatura y la sensación térmica (feels_like)
// Asumo que la columna de sensación térmica se llama 'feels_like' o similar.
// Usaremos 'temperatura' (temperatura exterior) y 'sensacion_termica' (un nombre lógico para la BD)

$sql = "SELECT temperatura, sensacion_termica, formula_sensacion_termica
    FROM meteo
    ORDER BY timestamp DESC
    LIMIT 1";

$result = $mysqli->query($sql);

if ($result === false) {
    // Error en la consulta
    error_log("Error en la consulta SQL: " . $mysqli->error);
    $mysqli->close();
    die_with_error("Error al ejecutar la consulta de datos de temperatura.");
}

if ($result->num_rows === 0) {
    // No hay datos
    $mysqli->close();
    die_with_error("No se encontraron datos en la tabla 'meteo'.");
}

// CÁLCULO DE TENDENCIAS:
$trendData = null;
$now = time();

if (file_exists($TEMP_TREND_CACHE)) {
    $cache = json_decode(file_get_contents($TEMP_TREND_CACHE), true);
    if ($cache && isset($cache['computed_at'])) {
        if ($now - strtotime($cache['computed_at']) < $TEMP_TREND_TTL) {
            $trendData = $cache;
        }
    }
}

if ($trendData === null) {
    $newTrend = computeTempTrend($mysqli);
    if ($newTrend !== null) {
        file_put_contents($TEMP_TREND_CACHE, json_encode($newTrend, JSON_PRETTY_PRINT));
        $trendData = $newTrend;
    }
}


// 2. Obtener los valores
$row = $result->fetch_assoc();

$temp = $row['temperatura'];
$feels_like = $row['sensacion_termica'];
$formula = isset($row['formula_sensacion_termica']) ? $row['formula_sensacion_termica'] : '';

$result->free();
$mysqli->close();


// ----------------------------------------------------
// 3. Cálculo del ángulo de la aguja (Misma lógica anterior)
// ----------------------------------------------------
// Asegurar que las variables son numéricas antes del cálculo
$temp = is_numeric($temp) ? (float)$temp : null;

if ($temp === null) {
    die_with_error("El valor de temperatura obtenido no es numérico.");
}

$temp = round($temp, 1);

// La lógica de mapeo de rangos se mantiene
$temp_angle = ($temp - $minTemp) * ($maxAngle - $minAngle) / ($maxTemp - $minTemp) + $minAngle;

// Asegurar que el ángulo esté dentro de los límites
if ($temp_angle < $minAngle) $temp_angle = $minAngle;
if ($temp_angle > $maxAngle) $temp_angle = $maxAngle;


// ----------------------------------------------------
// 4. Devolver JSON (Mismo formato anterior)
// ----------------------------------------------------
header('Content-Type: application/json');
echo json_encode([
    "temp" => $temp,
    "feels_like" => $feels_like,
    "formula" => $formula,
    "angle" => $temp_angle,
    "trend" => $trendData['trend'] ?? null
], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
