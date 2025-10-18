<?php
include '../config/config.php';
/*
 * Definido en config.php
 *
$lat = 36.566;
$lon = -4.604; */
$url = "https://api.open-meteo.com/v1/forecast?latitude=$lat&longitude=$lon&hourly=temperature_300hPa,temperature_500hPa,wind_speed_300hPa,wind_speed_500hPa";

// Inicializar cURL
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 10);
// Evita bloqueos por no tener User-Agent
curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/5.0 (PHP cURL)");

// Ejecutar la petición
$response = curl_exec($ch);

if ($response === false) {
    die("Error al conectar con Open-Meteo: " . curl_error($ch));
}

curl_close($ch);

// Decodificar JSON
$data = json_decode($response, true);

// Verificar que hay datos
if (isset($data['hourly']['time'])) {
    $lastIndex = count($data['hourly']['time']) - 1;

    $temp300 = $data['hourly']['temperature_300hPa'][$lastIndex];
    $temp500 = $data['hourly']['temperature_500hPa'][$lastIndex];
    $wind300 = $data['hourly']['wind_speed_300hPa'][$lastIndex];
    $wind500 = $data['hourly']['wind_speed_500hPa'][$lastIndex];

    echo "Última hora disponible:\n";
    echo "Temperatura 300hPa: $temp300 °C\n";
    echo "Temperatura 500hPa: $temp500 °C\n";
    echo "Viento 300hPa: $wind300 km/h\n";
    echo "Viento 500hPa: $wind500 km/h\n";
} else {
    echo "Datos no disponibles.\n";
}
?>
