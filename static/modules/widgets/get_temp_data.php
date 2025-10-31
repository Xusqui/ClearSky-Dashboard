<?php
include '../../config/config.php';

// Entidades Home Assistant para temperatura
$entities = [
    "temp"  => "sensor.ws2900_v2_02_03_outdoor_temperature",
    "feel"  => "sensor.ws2900_v2_02_03_feels_like_temperature"
];

function get_sensor($entity) {
    global $ha_url, $token;

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
            "message" => "Error JSON: " . json_last_error_msg(),
            "raw" => $response
        ]));
    }

    return $data['state'] ?? null;
}

// Obtener datos
$temp = get_sensor($entities['temp']);
$feels_like = get_sensor($entities['feel']);

// Calcular ángulo de la aguja
$minTemp = -20;
$maxTemp = 50;
$minAngle = -145;
$maxAngle = 145;

$temp_angle = ($temp - $minTemp) * ($maxAngle - $minAngle) / ($maxTemp - $minTemp) + $minAngle;
if ($temp_angle < $minAngle) $temp_angle = $minAngle;
if ($temp_angle > $maxAngle) $temp_angle = $maxAngle;

// Devolver JSON
header('Content-Type: application/json');
echo json_encode([
    "temp" => $temp,
    "feels_like" => $feels_like,
    "angle" => $temp_angle
]);
