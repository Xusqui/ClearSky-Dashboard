<?php
include '../../config/config.php';

// Entidades de lluvia en Home Assistant
$entities = [
    "daily" => "sensor.ws2900_v2_02_03_daily_rain",
    "rate"  => "sensor.ws2900_v2_02_03_rain_rate"
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

// Obtener valores
$daily_rain = get_sensor($entities['daily']);
$rain_rate  = get_sensor($entities['rate']);

$daily_rain = $daily_rain !== null ? floatval($daily_rain) : 0;
$rain_rate  = $rain_rate  !== null ? floatval($rain_rate) : 0;

// Altura del pluviómetro
$max_rain = 200;
$h_min = 40;
$h_max = 440;
$heigh = $daily_rain / $max_rain * ($h_max - $h_min);
if ($heigh > ($h_max - $h_min)) $heigh = $h_max - $h_min;
$water_start = $h_max - $heigh;

// Colores del pluviómetro
if ($daily_rain != 0) {
    $stroke_bucket_top = "var(--wu-lightblue20)";
    $fill_bucket_top = "var(--wu-lightblue)";
    $fill_bucket_bottom = "var(--wu-lightblue20)";
} else {
    $stroke_bucket_top = "transparent";
    $fill_bucket_top = "transparent";
    $fill_bucket_bottom = "var(--widget-empty)";
}

// Devolver JSON
header('Content-Type: application/json');
echo json_encode([
    "daily_rain" => $daily_rain,
    "rain_rate"  => $rain_rate,
    "heigh"      => $heigh,
    "water_start"=> $water_start,
    "stroke_bucket_top" => $stroke_bucket_top,
    "fill_bucket_top"   => $fill_bucket_top,
    "fill_bucket_bottom"=> $fill_bucket_bottom
]);
