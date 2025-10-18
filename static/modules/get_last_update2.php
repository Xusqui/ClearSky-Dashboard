<?php
header('Content-Type: application/json');
header('Cache-Control: no-cache, no-store, must-revalidate');
header('Pragma: no-cache');
header('Expires: 0');

include '../config/config.php';
echo $ha_url ." " . $token;

function get_sensor_data($ha_url, $token, $entity_id) {
    $url = $ha_url . "/api/states/" . $entity_id;
    $options = [
        "http" => [
            "header" => "Authorization: Bearer " . $token . "\r\n" .
                        "Content-Type: application/json\r\n",
            "method" => "GET"
        ]
    ];
    $context = stream_context_create($options);
    $result = file_get_contents($url, false, $context);
    if ($result === FALSE) return null;
    return json_decode($result, true);
}

$entity_id = "sensor.ws2900_v2_02_03_wind_speed";
$data = get_sensor_data($ha_url, $ha_token, $entity_id);

echo $data;

if ($data && isset($data['last_updated'])) {
    $utcTime = new DateTime($data['last_updated'], new DateTimeZone("UTC"));
    $localTime = clone $utcTime;
    $localTime->setTimezone(new DateTimeZone("Europe/Madrid"));
    echo json_encode([
        'last_update_timestamp' => $localTime->getTimestamp()
    ]);
} else {
    http_response_code(500);
    echo json_encode(['error' => true]);
}
