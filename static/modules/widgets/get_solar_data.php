<?php
// get_solar_data.php

// Incluir el archivo de configuración que debe contener las variables de conexión a MariaDB:
// $db_user, $db_pass, $db_url, $db_database
include __DIR__ . '/../../config/config.php';

// Definición de variables
$SOLAR_MAX_REF = 1100; // Valor máximo de referencia para 100% de la barra

// CONSTANTES DE LA PLANTA SOLAR (En un futuro irán en en el setup.php)
$POTENCIA_PICO = 8000;          // 8,000 kWp
$COEF_TEMP = -0.0034;           // -0.34% / ºC
$NOCT = 44;                     // Temp. nominal de la célula
$PR_ESTIMADO = 0.97;            // Pérdidas por cableado/otros (Ajustado según rendimiento real)
$CORRECCION_INCLINACION = 1.38; // Corrección por la diferencia entre la inclinacion del sensor de radiacion y la inclinacion de las placas solares

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

// Consulta SQL para obtener el último valor de 'radiacion_solar'
$sql = "SELECT radiacion_solar, temperatura
        FROM meteo
        ORDER BY timestamp DESC
        LIMIT 1";

$result = $mysqli->query($sql);

if ($result === false) {
    // Error en la consulta
    error_log("Error en la consulta SQL: " . $mysqli->error);
    $mysqli->close();
    die_with_error("Error al ejecutar la consulta de datos de radiación solar.");
}

if ($result->num_rows === 0) {
    // No hay datos
    $mysqli->close();
    die_with_error("No se encontraron datos de radiación solar en la tabla 'meteo'.");
}

// 2. Obtener el valor y sanitizar
$row = $result->fetch_assoc();

// Valor de la radiación solar
$solar_hor = isset($row['radiacion_solar']) && is_numeric($row['radiacion_solar']) ? floatval($row['radiacion_solar']) : null;
// EJEMPLO: $solar_hor = 318.29;

$temp_amb = floatval($row['temperatura']);

// 2. CÁLCULOS TÉCNICOS
// A. Corrección por inclinación (Horizontal a 25º)
if ($solar_hor < 1) {
    $produccion = 0;
} else {
    // Ajustado factor de inclinación a 1.38 según datos reales de producción vs radiación horizontal
    $solar_poa = $solar_hor * $CORRECCION_INCLINACION;

    // B. Estimación Temp. Célula
    $temp_celula = $temp_amb + (($NOCT - 20) / 800) * $solar_poa;

    // C. Cálculo de Producción Estimada (kW)
    $factor_temp = 1 + ($COEF_TEMP * ($temp_celula - 25));
    $produccion = $POTENCIA_PICO * ($solar_poa / 1000) * $factor_temp * $PR_ESTIMADO;

    // Asegurar que no devuelva valores negativos de noche
    $produccion = max(0, round($produccion, 2));
}
$produccion = max(0, round($produccion, 2));

$result->free();
$mysqli->close();


// ----------------------------------------------------
// 3. Normalización y Cálculo del Porcentaje
// ----------------------------------------------------

$percentage = 0;
if ($solar_hor !== null) {
    // Normalizamos (0 → 1100 W/m2 = 0 → 100%)
    $percentage = ($solar_hor / $SOLAR_MAX_REF) * 100;

    // Limitar el porcentaje entre 0 y 100
    $percentage = min(max($percentage, 0), 100);
}


// ----------------------------------------------------
// 4. Respuesta
// ----------------------------------------------------
header('Content-Type: application/json');
echo json_encode([
    "solar"      => $solar_hor,
    "percentage" => $percentage,
    "estimacion" => $produccion
], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
?>
