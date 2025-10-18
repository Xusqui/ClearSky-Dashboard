<?php
include '../config/config.php';

// Entidad de humedad en Home Assistant
$entity = "sensor.ws2900_v2_02_03_indoor_humidity";
$entity_temp_int = "sensor.ws2900_v2_02_03_indoor_temperature";

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
/**
 * Calcula el índice Humidex a partir de la temperatura del aire y la humedad relativa.
 *
 * @param float $temperatura La temperatura del aire en grados Celsius.
 * @param float $humedadRelativa La humedad relativa en porcentaje (de 0 a 100).
 * @return float El valor del Humidex calculado.
 */
function obtenerSensacionAmbiente(float $temperatura, float $humedad)
{
    // Paso 1: Calcular el punto de rocío (una medida más precisa de la humedad real).
    // Usamos una fórmula de aproximación simple pero efectiva.
    $puntoRocio = $temperatura - ((100 - $humedad) / 5);

    // Devolvemos una cadena de texto formateada con el resultado.
    return $puntoRocio;
}

// Obtener valor de humedad
$humidity = get_sensor($entity);
$humidity = $humidity !== null ? floatval($humidity) : 0;

$temp_int = get_sensor($entity_temp_int);
$temp_int = $temp_int !== null ? floatval($temp_int) : 0;

$sensacion = obtenerSensacionAmbiente($temp_int, $humidity);

// Calcular ángulo del gráfico
$angle_humidity = 360 * ($humidity / 100);

// Determinar estado
if ($sensacion < 10) {
    $humid_state  = "dry";
    $humid_legend = "Seco";
} elseif ($sensacion >= 20) {
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
    "temp" => $temp_int,
    "angle" => $angle_humidity,
    "legend" => $humid_legend,
    "color" => $humidity_color,
    "state" => $humid_state
]);
