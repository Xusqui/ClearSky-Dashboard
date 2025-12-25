<?php
header('Content-Type: application/json');

if (!isset($_GET['lat'], $_GET['lon'])) {
    http_response_code(400);
    echo json_encode(["error" => "Faltan lat o lon"]);
    exit;
}

$lat = floatval($_GET['lat']);
$lon = floatval($_GET['lon']);

if ($lat < -90 || $lat > 90 || $lon < -180 || $lon > 180) {
    http_response_code(400);
    echo json_encode(["error" => "Coordenadas inválidas"]);
    exit;
}

$url = "https://api.open-meteo.com/v1/elevation?latitude={$lat}&longitude={$lon}";

$ch = curl_init($url);
curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_TIMEOUT => 5,
    CURLOPT_SSL_VERIFYPEER => true,
]);

$response = curl_exec($ch);

if ($response === false) {
    http_response_code(500);
    echo json_encode(["error" => curl_error($ch)]);
    exit;
}

$data = json_decode($response, true);

if (!isset($data['elevation'][0])) {
    http_response_code(500);
    echo json_encode(["error" => "Respuesta inválida"]);
    exit;
}

echo json_encode(["elevation" => $data['elevation'][0]]);
