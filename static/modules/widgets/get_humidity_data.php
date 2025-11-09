<?php
// get_humidity_data.php
include '../../config/config.php';

// Entidad de humedad en Home Assistant
$entity_humidity = "sensor.ws2900_v2_02_03_humidity";
$entity_temp = "sensor.ws2900_v2_02_03_outdoor_temperature";
$entity_dew = "sensor.ws2900_v2_02_03_dewpoint";

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

function humidex ($temp, $dew){
    // $T = temperatura en ºC
    // $Td = Punto de ioro´c

    // Preon dsíe vapor real a partir de Td (Magnus-Tetens, hPa)
    $e = 6.112 * exp ((17.62 * $dew) / (243.12 + $dew));

    // Humidex
    $humidex = $temp + 0.5555 * ($e -10);

    return $humidex;
}

// Obtener valor de humedad
$humidity = get_sensor($entity_humidity);
$humidity = $humidity !== null ? floatval($humidity) : 0;

// Obtener valor de Temperatura
$temp = get_sensor ($entity_temp);
$temp = $temp !== null ? floatval($temp) : 0;

// Obtener valor de Punto de Rocío
$dew = get_sensor($entity_dew);
$dew = $dew !== null ? floatval($dew) : 0;

// Calcular Humidex
$hdex = round(humidex ($temp, $dew), 2);

// Calcular ángulo del gráfico
$angle_humidity = 360 * ($humidity / 100);

// Determinar estado
if ($dew < 15) {
    $humid_state  = "dry";
    $humid_legend = "Seco";
} elseif ($humidity >= 20) {
    $humid_state  = "humid";
    $humid_legend = "Húmedo";
} else {
    $humid_state  = "comfortable";
    $humid_legend = "Confortable";
}

// Variable color (CSS)
$humidity_color = "--humidity-{$humid_state}-color";

// Devolver JSON
header('Content-Type: application/json');
echo json_encode([
    "humidity" => $humidity,
    //"temp" => $temp,
    "dew" => $dew,
    "humidex" => $hdex,
    "angle" => $angle_humidity,
    "legend" => $humid_legend,
    "color" => $humidity_color,
    "state" => $humid_state
]);
?>
