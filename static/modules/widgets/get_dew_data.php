<?php
// /static/modules/widgets/get_dew_data.php

include __DIR__ . '/../../config/config.php';

$windowMinutes = 30;
$epsilon = 0.0005;
$cache_file = __DIR__ . '/../../cache/dew_cache.json';
$cache_ttl = 180; // Segundos: 3 minutos

/**
 * Devuelve error JSON y termina
 */
function die_with_error($msg) {
    header('Content-Type: application/json');
    die(json_encode(["error" => true, "message" => $msg]));
}

/**
 * Calcula pendiente por regresión lineal (y respecto al tiempo real)
 */
function linearTrend(array $points) {
    $n = count($points);
    if ($n < 2) return 0.0;

    $sumX = $sumY = $sumXY = $sumXX = 0.0;

    foreach ($points as [$x, $y]) {
        $sumX  += $x;
        $sumY  += $y;
        $sumXY += $x * $y;
        $sumXX += $x * $x;
    }

    $den = $n * $sumXX - $sumX * $sumX;
    if (abs($den) < 1e-9) return 0.0;

    return ($n * $sumXY - $sumX * $sumY) / $den;
}

/**
 * Clasifica la pendiente y devuelve símbolo y variable CSS
 */
function classifyTrend(float $slope, float $epsilon) {
    if ($slope > $epsilon)  return ['up', '▲', '--red'];
    if ($slope < -$epsilon) return ['down', '▼', '--lightblue'];
    return ['stable', '■', '--green'];
}

// ----------------------------------------------------
// Conexión a DB
// ----------------------------------------------------
if (!isset($db_url, $db_user, $db_pass, $db_database)) {
    die_with_error("Credenciales no definidas.");
}

$mysqli = new mysqli($db_url, $db_user, $db_pass, $db_database);
if ($mysqli->connect_error) die_with_error("Error de conexión.");

// ----------------------------------------------------
// Último valor actual
// ----------------------------------------------------
$sqlCurrent = "
SELECT punto_rocio, temperatura, viento_velocidad
FROM meteo
ORDER BY timestamp DESC
LIMIT 1
";

$res = $mysqli->query($sqlCurrent);
if (!$res || $res->num_rows === 0) die_with_error("Sin datos actuales.");

$cur = $res->fetch_assoc();
$dew   = is_numeric($cur['punto_rocio']) ? (float)$cur['punto_rocio'] : null;
$temp  = is_numeric($cur['temperatura']) ? (float)$cur['temperatura'] : null;
$wind  = is_numeric($cur['viento_velocidad']) ? (float)$cur['viento_velocidad'] : 0.0;
$res->free();

// ----------------------------------------------------
// Leer cache si existe y no caducada
// ----------------------------------------------------
$cachedTrend = null;
if (file_exists($cache_file)) {
    $cacheData = json_decode(file_get_contents($cache_file), true);
    if ($cacheData && isset($cacheData['timestamp']) && time() - $cacheData['timestamp'] < $cache_ttl) {
        $cachedTrend = $cacheData;
    }
}

// ----------------------------------------------------
// Calcular tendencia solo si no hay cache válida
// ----------------------------------------------------
if ($cachedTrend) {
    [$trendState, $trendSymbol, $trendColor] = [$cachedTrend['trend_state'], $cachedTrend['trend_symbol'], $cachedTrend['trend_color']];
    $dewProb = $cachedTrend['dew_probability'];
    $fogProb = $cachedTrend['fog_probability'];
} else {
    // Historial últimos 30 minutos
    $sqlHist = "
    SELECT UNIX_TIMESTAMP(timestamp) AS t, punto_rocio
    FROM meteo
    WHERE timestamp >= NOW() - INTERVAL $windowMinutes MINUTE
      AND punto_rocio IS NOT NULL
    ORDER BY timestamp ASC
    ";

    $res = $mysqli->query($sqlHist);
    $points = [];
    if ($res) {
        while ($r = $res->fetch_assoc()) {
            $points[] = [(float)$r['t'], (float)$r['punto_rocio']];
        }
        $res->free();
    }

    $slope = linearTrend($points);
    [$trendState, $trendSymbol, $trendColor] = classifyTrend($slope, $epsilon);

    // Probabilidad de rocío
    $dewProb = 0;
    if ($temp !== null && $dew !== null) {
        $spread = $temp - $dew;
        if ($spread < 1)      $dewProb = 90;
        elseif ($spread < 2) $dewProb = 70;
        elseif ($spread < 3) $dewProb = 50;
        elseif ($spread < 5) $dewProb = 25;
        else                 $dewProb = 5;
    }

    // Probabilidad de niebla
    $fogProb = 0;
    if ($dewProb > 50 && $wind < 2)      $fogProb = 80;
    elseif ($dewProb > 40 && $wind < 3)  $fogProb = 60;
    elseif ($dewProb > 30 && $wind < 4)  $fogProb = 40;
    else                                  $fogProb = 10;

    // Guardar en cache
    $cacheData = [
        'timestamp' => time(),
        'trend_state' => $trendState,
        'trend_symbol' => $trendSymbol,
        'trend_color' => $trendColor,
        'dew_probability' => $dewProb,
        'fog_probability' => $fogProb
    ];
    file_put_contents($cache_file, json_encode($cacheData, JSON_PRETTY_PRINT));
}

$mysqli->close();

// ----------------------------------------------------
// Porcentaje gota
// ----------------------------------------------------
if ($dew !== null && $temp !== null && $temp > 0) {
    $ratio = min(max($dew / $temp, 0), 1);
    $percent = 60 * $ratio;
} else {
    $percent = 0;
}

// ----------------------------------------------------
// Devolver JSON
// ----------------------------------------------------
header('Content-Type: application/json');
echo json_encode([
    "dew" => $dew !== null ? round($dew, 1) : null,
    "percent" => round($percent, 1),
    "trend" => $trendSymbol,
    "trend_state" => $trendState,
    "trend_color" => $trendColor,
    "dew_probability" => $dewProb,
    "fog_probability" => $fogProb
], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
