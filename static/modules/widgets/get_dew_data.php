<?php
// get_dew_data.php

// Incluir el archivo de configuración que debe contener las variables de conexión a MariaDB:
// $db_user, $db_pass, $db_url, $db_database
include '../../config/config.php';

// Parámetros para el cálculo del porcentaje de la gota
// $inner_percent = min(max(100 * $dew / 49, 0), 100);
$dew_max_value = 49;

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

// Consulta SQL para obtener el último valor de 'punto_rocio'
$sql = "SELECT punto_rocio
        FROM meteo
        ORDER BY timestamp DESC
        LIMIT 1";

$result = $mysqli->query($sql);

if ($result === false) {
    // Error en la consulta
    error_log("Error en la consulta SQL: " . $mysqli->error);
    $mysqli->close();
    die_with_error("Error al ejecutar la consulta de punto de rocío.");
}

if ($result->num_rows === 0) {
    // No hay datos
    $mysqli->close();
    die_with_error("No se encontraron datos en la tabla 'meteo'.");
}

// 2. Obtener el valor
$row = $result->fetch_assoc();
$dew = $row['punto_rocio'];

$result->free();
$mysqli->close();


// ----------------------------------------------------
// 3. Cálculo del porcentaje (Misma lógica anterior)
// ----------------------------------------------------

// Asegurar que el valor es numérico y manejar null/errores
$dew = is_numeric($dew) ? (float)$dew : null;
$dew = round ($dew, 1);

if ($dew === null) {
    // Usar 0 para el porcentaje si el valor no es válido o está ausente
    $inner_percent = 0;
} else {
    // Calcular porcentaje de la gota: $dew / 49, limitado entre 0 y 100
    $inner_percent = 100 * $dew / $dew_max_value;
    $inner_percent = min(max($inner_percent, 0), 100);
}

// ----------------------------------------------------
// 4. Devolver JSON (Mismo formato anterior)
// ----------------------------------------------------
header('Content-Type: application/json');
echo json_encode([
    "dew" => $dew,
    "percent" => $inner_percent
]);
?>
