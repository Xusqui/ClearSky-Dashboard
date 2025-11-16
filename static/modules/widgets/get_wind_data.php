<?php
// get_wind_data.php

// Incluir el archivo de configuración que debe contener las variables de conexión a MariaDB:
// $db_user, $db_pass, $db_url, $db_database
include '../../config/config.php';

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

/**
 * Convierte los grados de dirección del viento a un punto cardinal.
 * @param float|null $degrees Dirección del viento en grados.
 * @return string Punto cardinal (N, NE, E, etc.).
 */
function windDirection($degrees) {
    if ($degrees === null) return "N"; // Por defecto
    $dirs = ["N","NNE","NE","ENE","E","ESE","SE","SSE","S","SSO","SO","OSO","O","ONO","NO","NNO"];
    // Redondear a la dirección más cercana (cada 22.5 grados) y usar el módulo 16
    $index = round($degrees / 22.5) % 16;
    return $dirs[$index];
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

// Consulta SQL para obtener los cuatro valores de viento de la última fila
// Columnas esperadas: viento_velocidad, viento_direccion, viento_racha, viento_racha_maxima
$sql = "SELECT viento_velocidad, viento_direccion, viento_racha, viento_racha_maxima
        FROM meteo
        ORDER BY timestamp DESC
        LIMIT 1";

$result = $mysqli->query($sql);

if ($result === false) {
    // Error en la consulta
    error_log("Error en la consulta SQL: " . $mysqli->error);
    $mysqli->close();
    die_with_error("Error al ejecutar la consulta de datos de viento.");
}

if ($result->num_rows === 0) {
    // No hay datos
    $mysqli->close();
    die_with_error("No se encontraron datos de viento en la tabla 'meteo'.");
}

// 2. Obtener los valores y sanitizarlos
$row = $result->fetch_assoc();

// Asignamos los valores, asegurando que sean flotantes o null (para manejar la dirección)
$wind      = isset($row['viento_velocidad']) && is_numeric($row['viento_velocidad']) ? (float)$row['viento_velocidad'] : null;
$wind      = round ($wind, 1);
$gust      = isset($row['viento_racha']) && is_numeric($row['viento_racha']) ? (float)$row['viento_racha'] : null;
$gust      = round ($gust, 1);
$gust_max  = isset($row['viento_racha_maxima']) && is_numeric($row['viento_racha_maxima']) ? (float)$row['viento_racha_maxima'] : null;
$gust_max  = round ($gust_max, 1);
$wind_dir  = isset($row['viento_direccion']) && is_numeric($row['viento_direccion']) ? (float)$row['viento_direccion'] : null;

$result->free();
$mysqli->close();


// ----------------------------------------------------
// 3. Procesamiento de la dirección del viento
// ----------------------------------------------------
$wind_direction = windDirection($wind_dir);

// ----------------------------------------------------
// 4. Devolver JSON (Mismo formato anterior)
// ----------------------------------------------------
header('Content-Type: application/json');
echo json_encode([
    "wind" => $wind,
    "gust" => $gust,
    "gust_max" => $gust_max,
    "wind_dir" => $wind_dir,
    "wind_direction" => $wind_direction
]);
?>
