<?php
header('Content-Type: application/json');

// Cargar configuración de Home Assistant
require_once '../../config/config.php';

// Lista de sensores
$sensors = [
    "status" => "sensor.ws2900_v2_02_03_rain_rate",
    "rate"   => "sensor.ws2900_v2_02_03_rain_rate",
    "daily"  => "sensor.ws2900_v2_02_03_event_rain",
    "hourly" => "sensor.ws2900_v2_02_03_hourly_rain",
    "monthly"=> "sensor.ws2900_v2_02_03_monthly_rain",
    "total"  => "sensor.ws2900_v2_02_03_total_rain"
];

$data = [];

foreach ($sensors as $key => $entity_id) {
    $url = rtrim($ha_url, '/') . "/api/states/" . $entity_id;

    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        "Authorization: Bearer " . $token,
        "Content-Type: application/json"
    ]);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

    $response = curl_exec($ch);
    curl_close($ch);

    if ($response) {
        $json = json_decode($response, true);
        $data[$key] = $json["state"] ?? null;
    } else {
        $data[$key] = null;
    }
}

echo json_encode($data);
