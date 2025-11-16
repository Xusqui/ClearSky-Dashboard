<?php
// temp_interior_data.php

// Cargar configuración
include '../../config/config.php';
/*
//Entidad de temperatura interior en Home Assistant
$entity   = "sensor.ws2900_v2_02_03_indoor_temperature"; // Ajusta al nombre real de tu sensor

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, "$ha_url/api/states/$entity");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    "Authorization: Bearer $token",
    "Content-Type: application/json"
]);
$response = curl_exec($ch);

if ($response === false) {
    die(json_encode([
        "error" => true,
        "message" => "Error cURL: " . curl_error($ch)
    ]));
}

$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($http_code != 200) {
    die(json_encode([
        "error" => true,
        "message" => "Error HTTP: código $http_code al consultar $entity"
    ]));
}

$data = json_decode($response, true);
if (json_last_error() !== JSON_ERROR_NONE) {
    die(json_encode([
        "error" => true,
        "message" => "Error JSON: " . json_last_error_msg()
    ]));
}

$in_temp = isset($data['state']) ? floatval($data['state']) : null;

*/

// Configuración de tu termómetro
$minTemp = -20;      // Temperatura mínima de la escala
$maxTemp = 50;     // Temperatura máxima de la escala
$minAngle = -145;   // Ángulo mínimo de la aguja
$maxAngle = 145;    // Ángulo máximo de la aguja

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

// Consulta SQL para obtener la última temperatura interior
$sql = "SELECT temperatura_interior
        FROM meteo
        ORDER BY timestamp DESC
        LIMIT 1";

$result = $mysqli->query($sql);

if ($result === false) {
    // Error en la consulta
    error_log("Error en la consulta SQL: " . $mysqli->error);
    $mysqli->close();
    die_with_error("Error al ejecutar la consulta de datos de temperatura interior.");
}

if ($result->num_rows === 0) {
    // No hay datos
    $mysqli->close();
    die_with_error("No se encontraron datos de temperatura interior en la tabla 'meteo'.");
}

// 2. Obtener el valor y sanitizar
$row = $result->fetch_assoc();

// Valor de la temperatura interior. Si es nulo, usamos null.
$in_temp = isset($row['temperatura_interior']) && is_numeric($row['temperatura_interior']) ? floatval($row['temperatura_interior']) : null;

$result->free();
$mysqli->close();

// ----------------------------------------------------
// 3. Cálculo del ángulo (Misma lógica anterior)
// ----------------------------------------------------

// Asegurar que tenemos un valor numérico para el cálculo
if ($in_temp === null) {
    // Podríamos devolver un error, pero el script original devuelve null, lo que hará que el ángulo sea NaN.
    // Para evitar problemas de cálculo, si es null, forzamos un valor por defecto (0) para la representación,
    // aunque 'in_temp' seguirá siendo null en el JSON si se desea.
    $in_temp_for_calc = 0;
} else {
    $in_temp_for_calc = $in_temp;
}

$in_temp_angle = ($in_temp_for_calc - $minTemp) * ($maxAngle - $minAngle) / ($maxTemp - $minTemp) + $minAngle;

// Limitar extremos
if ($in_temp_angle < $minAngle) $in_temp_angle = $minAngle;
if ($in_temp_angle > $maxAngle) $in_temp_angle = $maxAngle;

$in_temp = round($in_temp, 1);

// Respuesta
header('Content-Type: application/json');
echo json_encode([
    "temp" => $in_temp,
    "angle" => $in_temp_angle
]);
?>
