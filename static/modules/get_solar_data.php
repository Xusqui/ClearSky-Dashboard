<?php
// get_solar_data.php
header('Content-Type: application/json');

// Cargar configuración
include '../config/config.php';

$entity   = "sensor.ws2900_v2_02_03_solar_radiation"; // Ajusta al nombre real de tu sensor

// Llamada a la API de Home Assistant
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
$solar = isset($data['state']) ? floatval($data['state']) : null;

// Normalizamos (0 → 1100 W/m2 = 0 → 100%)
$percentage = ($solar !== null) ? min(max(($solar / 1100) * 100, 0), 100) : 0;

// Respuesta
echo json_encode([
    "solar"      => $solar,
    "percentage" => $percentage
]);
?>