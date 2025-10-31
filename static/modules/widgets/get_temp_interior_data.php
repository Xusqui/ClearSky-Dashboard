<?php
// temp_interior_data.php
header('Content-Type: application/json');

// Cargar configuración
include '../../config/config.php';

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

// Configuración de tu termómetro
$minTemp = -20;      // Temperatura mínima de la escala
$maxTemp = 50;     // Temperatura máxima de la escala
$minAngle = -145;   // Ángulo mínimo de la aguja
$maxAngle = 145;    // Ángulo máximo de la aguja

$in_temp_angle = ($in_temp - $minTemp) * ($maxAngle - $minAngle) / ($maxTemp - $minTemp) + $minAngle;
// Limitar extremos
if ($in_temp_angle < $minAngle) $in_temp_angle = $minAngle;
if ($in_temp_angle > $maxAngle) $in_temp_angle = $maxAngle;

// Respuesta
echo json_encode([
    "temp"       => $in_temp,
    "angle"      => $in_temp_angle
]);
