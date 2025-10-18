<?php
header('Content-Type: application/json');
require_once '../config/config.php'; // tu configuración de Home Assistant

$year = date("Y");
$monthly_rain = [];

// Vamos a obtener datos de cada mes
for ($month = 1; $month <= 12; $month++) {
    $entity_id = "sensor.ws2900_v2_02_03_rain_" . str_pad($month, 2, "0", STR_PAD_LEFT);
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
        $monthly_rain[] = floatval($json["state"] ?? 0);
    } else {
        $monthly_rain[] = 0;
    }
}

echo json_encode($monthly_rain);
?>