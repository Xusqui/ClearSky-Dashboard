<?php
// get_solar_data.php

// Incluir el archivo de configuración que debe contener las variables de conexión a MariaDB:
// $db_user, $db_pass, $db_url, $db_database
include '../../config/config.php';

// Definición de variables
$SOLAR_MAX_REF = 1100; // Valor máximo de referencia para 100% de la barra

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
$sql = "SELECT radiacion_solar
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
$solar = isset($row['radiacion_solar']) && is_numeric($row['radiacion_solar']) ? floatval($row['radiacion_solar']) : null;

$result->free();
$mysqli->close();


// ----------------------------------------------------
// 3. Normalización y Cálculo del Porcentaje
// ----------------------------------------------------

$percentage = 0;
if ($solar !== null) {
    // Normalizamos (0 → 1100 W/m2 = 0 → 100%)
    $percentage = ($solar / $SOLAR_MAX_REF) * 100;

    // Limitar el porcentaje entre 0 y 100
    $percentage = min(max($percentage, 0), 100);
}


// ----------------------------------------------------
// 4. Respuesta
// ----------------------------------------------------
header('Content-Type: application/json');
echo json_encode([
    "solar"      => $solar,
    "percentage" => $percentage
]);
?>
