<?php
// get_rain_data.php

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

// Consulta SQL para obtener los valores de lluvia necesarios de la última fila
$sql = "SELECT lluvia_diaria, lluvia_rate, lluvia_evento
        FROM meteo
        ORDER BY timestamp DESC
        LIMIT 1";

$result = $mysqli->query($sql);

if ($result === false) {
    // Error en la consulta
    error_log("Error en la consulta SQL: " . $mysqli->error);
    $mysqli->close();
    die_with_error("Error al ejecutar la consulta de datos de lluvia.");
}

if ($result->num_rows === 0) {
    // No hay datos
    $mysqli->close();
    die_with_error("No se encontraron datos de lluvia en la tabla 'meteo'.");
}

// 2. Obtener los valores y sanitizarlos
$row = $result->fetch_assoc();

// Aseguramos que los valores sean flotantes o 0 si son nulos/inválidos, manteniendo la lógica original.
$daily_rain  = isset($row['lluvia_diaria']) && is_numeric($row['lluvia_diaria']) ? floatval($row['lluvia_diaria']) : 0.0;
$rain_rate   = isset($row['lluvia_rate']) && is_numeric($row['lluvia_rate']) ? floatval($row['lluvia_rate']) : 0.0;
// Nueva variable para la lluvia del evento
$rain_event  = isset($row['lluvia_evento']) && is_numeric($row['lluvia_evento']) ? floatval($row['lluvia_evento']) : 0.0;

$result->free();
$mysqli->close();


// ----------------------------------------------------
// 3. Cálculos para el pluviómetro (Misma lógica anterior)
// ----------------------------------------------------

// Altura del pluviómetro
$max_rain = 200;
$h_min = 40;
$h_max = 440;

// Calcular la altura relativa
$heigh = $daily_rain / $max_rain * ($h_max - $h_min);
if ($heigh > ($h_max - $h_min)) {
    $heigh = $h_max - $h_min;
}

// Calcular el punto de inicio del agua (inverso a la altura)
$water_start = $h_max - $heigh;

// Colores del pluviómetro (Misma lógica anterior)
if ($daily_rain != 0) {
    $stroke_bucket_top = "var(--wu-lightblue20)";
    $fill_bucket_top = "var(--wu-lightblue)";
    $fill_bucket_bottom = "var(--wu-lightblue20)";
} else {
    $stroke_bucket_top = "transparent";
    $fill_bucket_top = "transparent";
    $fill_bucket_bottom = "var(--widget-empty)";
}

// ----------------------------------------------------
// 4. Devolver JSON (Incluyendo 'rain_event')
// ----------------------------------------------------
header('Content-Type: application/json');
echo json_encode([
    "daily_rain" => $daily_rain,
    "rain_rate"  => $rain_rate,
    "rain_event" => $rain_event, // Nuevo dato añadido
    "heigh"      => $heigh,
    "water_start"=> $water_start,
    "stroke_bucket_top" => $stroke_bucket_top,
    "fill_bucket_top"   => $fill_bucket_top,
    "fill_bucket_bottom"=> $fill_bucket_bottom
]);
?>
