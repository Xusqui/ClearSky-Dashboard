<?php
// get_wind_data.php
include '../../config/config.php';

// Entidades del viento en Home Assistant
$entities = [
    "speed"    => "sensor.ws2900_v2_02_03_wind_speed",
    "gust"     => "sensor.ws2900_v2_02_03_wind_gust",
    "gust_max" => "sensor.ws2900_v2_02_03_max_daily_gust",
    "dir"      => "sensor.ws2900_v2_02_03_wind_direction"
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

// Obtener datos reales
$wind     = get_sensor($entities['speed']);
$gust     = get_sensor($entities['gust']);
$gust_max = get_sensor($entities['gust_max']);
$wind_dir = get_sensor($entities['dir']);

function windDirection($degrees) {
    if ($degrees === null) return "N"; // Por defecto
    $dirs = ["N","NNE","NE","ENE","E","ESE","SE","SSE","S","SSO","SO","OSO","O","ONO","NO","NNO"];
    $index = round($degrees / 22.5) % 16;
    return $dirs[$index];
}

$wind_direction = windDirection($wind_dir);

// Devolver JSON
header('Content-Type: application/json');
echo json_encode([
    "wind" => $wind,
    "gust" => $gust,
    "gust_max" => $gust_max,
    "wind_dir" => $wind_dir,
    "wind_direction" => $wind_direction
]);
?>
