<?php
// temp_interior_data.php
header('Content-Type: application/json');
header('Cache-Control: no-cache, no-store, must-revalidate'); // Evita caché
header('Pragma: no-cache');
header('Expires: 0');

// Cargar configuración
include '../config/config.php';

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

if ($data && isset($data['last_updated'])) {
    $utcTime = new DateTime($data['last_updated'], new DateTimeZone("UTC"));
    $localTime = clone $utcTime;
    $localTime->setTimezone(new DateTimeZone("Europe/Madrid"));
    $timestamp = $localTime->getTimestamp();
    
    // Devolver JSON
    echo json_encode([
        'last_update_timestamp' => $timestamp,
        'formatted_time' => $localTime->format('H:i:s'),
    ]);
} else {
    echo json_encode([
        'error' => 'No se pudo obtener la fecha de actualización'
    ]);
}





/*
$last_updated = isset($data['last_updated']) ? date($data['last_updated']) : null;
echo $last_updated;
echo json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);*/