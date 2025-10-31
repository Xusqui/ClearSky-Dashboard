<?php
include '../../config/config.php';

// Entidad de presión en Home Assistant
$entity = "sensor.ws2900_v2_02_03_relative_pressure";

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

// Valor de la presión
$pressure = $data['state'] !== null ? floatval($data['state']) : 1013;

// Calcular ángulo del manómetro
$minPres = 950;
$maxPres = 1050;
$minAnglePres = -134;
$maxAnglePres = 134;
$pres_angle = ($pressure - $minPres) * ($maxAnglePres - $minAnglePres) / ($maxPres - $minPres) + $minAnglePres;

// Limitar ángulo a extremos
if ($pres_angle < $minAnglePres) $pres_angle = $minAnglePres;
if ($pres_angle > $maxAnglePres) $pres_angle = $maxAnglePres;

header('Content-Type: application/json');
echo json_encode([
    "pressure"   => $pressure,
    "pres_angle" => $pres_angle
]);
?>
