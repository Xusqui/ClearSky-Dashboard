<?php
// get_pressure_data.php

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

// Consulta SQL para obtener la última presión relativa
$sql = "SELECT presion_relativa
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

// Valor de la presión (usamos 1013 como valor por defecto si es nulo, como en el original)
$pressure = isset($row['presion_relativa']) && is_numeric($row['presion_relativa']) ? floatval($row['presion_relativa']) : 1013.0;
$pressure = round($pressure, 1);

$result->free();
$mysqli->close();


// ----------------------------------------------------
// 3. Cálculo del ángulo del manómetro (Misma lógica anterior)
// ----------------------------------------------------
$minPres = 950;
$maxPres = 1050;
$minAnglePres = -134;
$maxAnglePres = 134;

// Mapeo lineal de la presión al ángulo
$pres_angle = ($pressure - $minPres) * ($maxAnglePres - $minAnglePres) / ($maxPres - $minPres) + $minAnglePres;

// Limitar ángulo a extremos
if ($pres_angle < $minAnglePres) $pres_angle = $minAnglePres;
if ($pres_angle > $maxAnglePres) $pres_angle = $maxAnglePres;


// ----------------------------------------------------
// 4. Devolver JSON (Mismo formato anterior)
// ----------------------------------------------------
header('Content-Type: application/json');
echo json_encode([
    "pressure"   => $pressure,
    "pres_angle" => $pres_angle
]);
?>
