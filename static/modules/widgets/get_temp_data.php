<?php
// get_temp_data.php
include '../../config/config.php';

// Parámetros para el cálculo del ángulo
$minTemp = -20;
$maxTemp = 50;
$minAngle = -145;
$maxAngle = 145;

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
$mysqli = new mysqli($db_url, $db_user, $db_pass, $db_database);

if ($mysqli->connect_error) {
    // Error de conexión
    error_log("Error de conexión a la BD: " . $mysqli->connect_error);
    die_with_error("Error al conectar con la base de datos.");
}

// Consulta SQL para obtener la última temperatura y la sensación térmica (feels_like)
// Asumo que la columna de sensación térmica se llama 'feels_like' o similar.
// Usaremos 'temperatura' (temperatura exterior) y 'sensacion_termica' (un nombre lógico para la BD)
$sql = "SELECT temperatura, sensacion_termica
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

// 2. Obtener los valores
$row = $result->fetch_assoc();
$temp = $row['temperatura'];
$feels_like = $row['sensacion_termica']; // Asumiendo que esta es la columna para 'feels_like'

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
    "angle" => $temp_angle
]);
?>
