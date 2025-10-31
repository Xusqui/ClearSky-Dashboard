<?php
include '../../config/config.php';

// Entidad de punto de rocío en Home Assistant
$entity = "sensor.ws2900_v2_02_03_dewpoint";

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

// Obtener valor del punto de rocío
$dew = get_sensor($entity);

// Calcular porcentaje de la gota
$inner_percent = $dew !== null ? min(max(100 * $dew / 49, 0), 100) : 0;

// Devolver JSON
header('Content-Type: application/json');
echo json_encode([
    "dew" => $dew,
    "percent" => $inner_percent
]);
