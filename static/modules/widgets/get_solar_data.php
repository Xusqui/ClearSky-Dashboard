<?php
// get_solar_data.php

// Incluir el archivo de configuración que contiene $db_user, $db_pass, $db_url, $db_database, $lat, $lon
include __DIR__ . '/../../config/config.php';

// Definición de variables de visualización
$SOLAR_MAX_REF = 1100;

// CONSTANTES DE LA PLANTA SOLAR
$POTENCIA_PICO = 8000;          // 8,000 kWp
$INCLINACION_PLACAS = 25;       // Grados
$COEF_TEMP = -0.0038;           // Ajustado para mayor realismo térmico (-0.38% / ºC)
$NOCT = 45;                     // Temp. nominal de la célula para paneles de 600W
$PR_ESTIMADO = 0.735;            // Performance Ratio ajustado (pérdidas por suciedad, cables, inversores)

/**
 * Calcula la elevación solar actual en grados usando trigonometría esférica
 */
function get_solar_elevation_dynamic($lat, $lon, $test_ts = null) {
    $timestamp = $test_ts ?? time();
    $sun_info = date_sun_info($timestamp, $lat, $lon);

    // Si es de noche según la ubicación, la elevación es 0
    if ($timestamp < $sun_info['sunrise'] || $timestamp > $sun_info['sunset']) {
        return 0;
    }

    $dayOfYear = date('z', $timestamp);
    $hour = date('G', $timestamp) + (date('i', $timestamp) / 60);

    // Declinación solar (ángulo del sol respecto al ecuador)
    $declination = 23.45 * sin(deg2rad((360 / 365) * ($dayOfYear - 81)));

    // Ángulo horario (posición del sol respecto al mediodía local)
    $hourAngle = ($hour - 12) * 15;

    // Fórmula de elevación solar (resultado en radianes)
    $elevation_rad = asin(
        sin(deg2rad($lat)) * sin(deg2rad($declination)) +
        cos(deg2rad($lat)) * cos(deg2rad($declination)) * cos(deg2rad($hourAngle))
    );

    return rad2deg($elevation_rad);
}

function die_with_error($message) {
    header('Content-Type: application/json');
    die(json_encode(["error" => true, "message" => $message]));
}

// 1. Conexión a la base de datos
if (!isset($db_url, $db_user, $db_pass, $db_database)) {
    die_with_error("Error: Configuración incompleta.");
}

$mysqli = new mysqli($db_url, $db_user, $db_pass, $db_database);
if ($mysqli->connect_error) {
    die_with_error("Error al conectar con la base de datos.");
}

// Obtención de datos actuales
$sql = "SELECT radiacion_solar, temperatura FROM meteo ORDER BY timestamp DESC LIMIT 1";
$result = $mysqli->query($sql);

if (!$result || $result->num_rows === 0) {
    $mysqli->close();
    die_with_error("No hay datos disponibles.");
}

$row = $result->fetch_assoc();

// --- VARIABLES DE ENTRADA ---
$solar_hor = isset($row['radiacion_solar']) ? floatval($row['radiacion_solar']) : 0;
$temp_amb = isset($row['temperatura']) ? floatval($row['temperatura']) : 20;

/* PARA PRUEBAS: Descomentar estas líneas para forzar el escenario de Septiembre
   $timestamp_test = strtotime("2025-09-05 14:29:00");
   $solar_hor = 989.59;
   $temp_amb = 29;
*/

// 2. CÁLCULOS TÉCNICOS DINÁMICOS
if ($solar_hor < 1) {
    $produccion_kw = 0;
} else {
    // A. Factor de Corrección Geométrico (K)
    $elev_deg = get_solar_elevation_dynamic($lat, $lon, $timestamp_test ?? null);

    if ($elev_deg < 5) {
        $factor_k = 1.0;
    } else {
        // Cálculo de la irradiancia en el plano inclinado respecto a la horizontal
        // K = sin(Elevación + Inclinación) / sin(Elevación)
        $factor_k = sin(deg2rad($elev_deg + $INCLINACION_PLACAS)) / sin(deg2rad($elev_deg));

        // Limitadores lógicos para el factor K
        $factor_k = max(0.9, min($factor_k, 2.2));
    }

    $solar_poa = $solar_hor * $factor_k;

    // B. Estimación Temp. Célula (Impacto térmico real)
    $temp_celula = $temp_amb + (($NOCT - 20) / 800) * $solar_poa;

    // C. Cálculo de Producción Estimada
    // Penalización por temperatura sobre los 25ºC STC
    $factor_temp = 1 + ($COEF_TEMP * ($temp_celula - 25));

    $produccion_teorica = $POTENCIA_PICO * ($solar_poa / 1000) * $factor_temp * $PR_ESTIMADO;

    // LIMITADOR DE SEGURIDAD: No puede producir más de la potencia nominal instalada
    $produccion_kw = min($produccion_teorica, $POTENCIA_PICO);
}

// Formateo final
$produccion_kw = max(0, round($produccion_kw, 2));
$estimacion_display = round(($produccion_kw / 1000), 2); // En Megavatios para el widget

$result->free();
$mysqli->close();

// 3. Normalización Porcentaje Gráfico
$percentage = min(max(($solar_hor / $SOLAR_MAX_REF) * 100, 0), 100);

// 4. Respuesta JSON
header('Content-Type: application/json');
echo json_encode([
    "solar"      => $solar_hor,
    "percentage" => $percentage,
    "estimacion" => $estimacion_display,
    "debug"      => [ "elevacion" => round($elev_deg, 2), "factor_k" => round($factor_k, 2) ]
], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
?>
