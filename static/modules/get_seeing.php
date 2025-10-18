<?php
header("Content-Type: application/json");
include '../config/config.php';

$mysqli = new mysqli($db_url, $db_user, $db_pass, $db_database);
if ($mysqli->connect_errno) {
    echo json_encode(["error" => true, "message" => "Error de conexión a la BD"]);
    exit();
}

// --- 1. Leer datos de las últimas 8 horas ---
$query = "
    SELECT temperatura, humedad, presion_absoluta, viento_velocidad, viento_racha, radiacion_solar, timestamp
    FROM meteo
    WHERE `timestamp` >= NOW() - INTERVAL 8 HOUR
    ORDER BY `timestamp` ASC
";
$result = $mysqli->query($query);

$datos = [];
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $datos[] = [
            "temperatura" => (float) $row["temperatura"],
            "humedad" => (float) $row["humedad"],
            "presion_absoluta" => (float) $row["presion_absoluta"],
            "viento_velocidad" => (float) $row["viento_velocidad"],
            "viento_racha" => (float) $row["viento_racha"],
            "radiacion_solar" => (float) $row["radiacion_solar"],
        ];
    }
}

// --- 2. Calcular variaciones ---
function calcularVariacion($array, $campo) {
    $valores = array_column($array, $campo);
    return max($valores) - min($valores);
}

$variacionTemp = calcularVariacion($datos, "temperatura");
$variacionHum = calcularVariacion($datos, "humedad");
$variacionPres = calcularVariacion($datos, "presion_absoluta");
$vientoActual = end($datos)["viento_velocidad"];
$rachaActual = end($datos)["viento_racha"];
$luminosidadActual = end($datos)["radiacion_solar"];

$detalles = [
    'variacionTemp' => $variacionTemp,
    'variacionHum' => $variacionHum,
    'variacionPres' => $variacionPres,
    'vientoActual' => $vientoActual,
    'rachaActual' => $rachaActual,
    'luminosidadActual' => $luminosidadActual
];

// --- 3. Función para obtener datos 300/500 hPa ---
function fetch_pressure_levels($lat, $lon) {
    $url = "https://api.open-meteo.com/v1/forecast?latitude={$lat}&longitude={$lon}"
         . "&hourly=temperature_300hPa,temperature_500hPa,wind_speed_300hPa,wind_speed_500hPa"
         . "&forecast_days=1&timezone=UTC";

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/5.0 (PHP cURL)");
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);

    $resp = curl_exec($ch);
    if ($resp === false) {
        $err = curl_error($ch);
        curl_close($ch);
        return ["error" => true, "message" => $err];
    }
    curl_close($ch);

    $json = json_decode($resp, true);
    if (!isset($json['hourly']['time'])) {
        return ["error" => true, "message" => "No hourly data"];
    }

    $lastIndex = count($json['hourly']['time']) - 1;

    return [
        "error" => false,
        "temp300" => (float) $json['hourly']['temperature_300hPa'][$lastIndex],
        "temp500" => (float) $json['hourly']['temperature_500hPa'][$lastIndex],
        "wind300" => (float) $json['hourly']['wind_speed_300hPa'][$lastIndex],
        "wind500" => (float) $json['hourly']['wind_speed_500hPa'][$lastIndex]
    ];
}

// --- 4. Función para cobertura de nubes ---
function fetch_cloud_layers_openmeteo($lat, $lon, $tz) {
    $now = new DateTime("now", new DateTimeZone($tz));
    $today = $now->format("Y-m-d");

    $url = "https://api.open-meteo.com/v1/forecast?" .
           "latitude={$lat}&longitude={$lon}" .
           "&hourly=cloud_cover,cloud_cover_low,cloud_cover_mid,cloud_cover_high" .
           "&timezone={$tz}" .
           "&start_date={$today}&end_date={$today}";

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);

    $resp = curl_exec($ch);
    if ($resp === false) {
        $err = curl_error($ch);
        curl_close($ch);
        return ["error" => true, "message" => "Error al conectar con Open-Meteo: $err"];
    }
    curl_close($ch);

    $data = json_decode($resp, true);
    if (!$data || !isset($data["hourly"]["time"])) {
        return ["error" => true, "message" => "Datos de nubosidad no encontrados"];
    }

    $time_array = $data["hourly"]["time"];
    $closest_index = 0;
    $min_diff = PHP_INT_MAX;
    foreach ($time_array as $i => $time_str) {
        $t = new DateTime($time_str, new DateTimeZone($tz));
        $diff = abs($t->getTimestamp() - $now->getTimestamp());
        if ($diff < $min_diff) {
            $min_diff = $diff;
            $closest_index = $i;
        }
    }

    return [
        "error" => false,
        "cloud_low"  => $data["hourly"]["cloud_cover_low"][$closest_index]  ?? 0,
        "cloud_mid"  => $data["hourly"]["cloud_cover_mid"][$closest_index]  ?? 0,
        "cloud_high" => $data["hourly"]["cloud_cover_high"][$closest_index] ?? 0,
        "timestamp"  => $data["hourly"]["time"][$closest_index]
    ];
}

// --- 5. Obtener datos de altura ---
$levels = fetch_pressure_levels($lat, $lon);
if ($levels['error']) {
    $detalles['pressure_levels_error'] = $levels['message'];
    $temp300 = $temp500 = $wind300 = $wind500 = $shear = $deltaT = 0;
} else {
    $temp300 = $levels['temp300'];
    $temp500 = $levels['temp500'];
    $wind300 = $levels['wind300'];
    $wind500 = $levels['wind500'];

    $shear = abs($wind300 - $wind500);
    $deltaT = abs($temp500 - $temp300);

    $detalles['temp300'] = $temp300;
    $detalles['temp500'] = $temp500;
    $detalles['wind300'] = $wind300;
    $detalles['wind500'] = $wind500;
    $detalles['shear'] = $shear;
    $detalles['deltaT'] = $deltaT;
}

// --- 6. Obtener nubosidad ---
$clouds = fetch_cloud_layers_openmeteo($lat, $lon, $tz);
if ($clouds['error']) {
    $detalles['cloud_error'] = $clouds['message'];
    $low = $mid = $high = 0;
} else {
    $low  = $clouds['cloud_low'];
    $mid  = $clouds['cloud_mid'];
    $high = $clouds['cloud_high'];
    $hora_nubes = $clouds['timestamp'];

    $detalles['hora_nubes'] = $hora_nubes;
    $detalles['nubes_low'] = $low;
    $detalles['nubes_mid'] = $mid;
    $detalles['nubes_high'] = $high;
}

// --- 7. Calcular puntos base (sin ajuste de nubes) ---
$puntos_base = 0;

// Superficie: temperatura, humedad, presión, viento, racha, radiación
$puntos_base += ($variacionTemp < 2) ? 5 : (($variacionTemp < 4) ? 3 : 1);
$puntos_base += ($variacionHum < 10) ? 5 : (($variacionHum < 20) ? 3 : 1);
$puntos_base += ($variacionPres < 3) ? 5 : (($variacionPres < 6) ? 3 : 1);
$puntos_base += ($vientoActual < 10) ? 5 : (($vientoActual < 20) ? 3 : 1);
$puntos_base += ($rachaActual < 15) ? 5 : (($rachaActual < 25) ? 3 : 1);
$puntos_base += ($luminosidadActual > 800) ? 1 : (($luminosidadActual > 400) ? 3 : 5);

// Altura: viento300, shear, deltaT
$puntos_base += ($wind300 < 40) ? 5 : (($wind300 < 80) ? 3 : 1);
$puntos_base += ($shear < 20) ? 5 : (($shear < 40) ? 3 : 1);
$puntos_base += ($deltaT < 15) ? 5 : (($deltaT < 30) ? 3 : 1);

// --- 8. Ajuste de puntos según nubosidad (factor multiplicativo) ---
$factor_nubes = 1 - (($low * 0.5 + $mid * 0.7 + $high * 1.0) / 100);
$factor_nubes = max(0, min(1, $factor_nubes)); // límite entre 0 y 1

$puntos_final = $puntos_base * $factor_nubes;
$detalles['cloud_index'] = round(($low*0.5 + $mid*0.7 + $high*1.0), 1);
$detalles['factor_nubes'] = round($factor_nubes, 2);
$detalles['puntos_base'] = $puntos_base;
$detalles['puntos_final'] = round($puntos_final, 1);

// --- 9. Clasificación final ---
if ($puntos_final < 5) {
    $seeing = "Nulo";
    $point = 0;
} elseif ($puntos_final < 15) {
    $seeing = "Pobre";
    $point = 0.5;
} elseif ($puntos_final < 25) {
    $seeing = "Regular";
    $point = 1;
} elseif ($puntos_final < 32) {
    $seeing = "Bueno";
    $point = 2;
} elseif ($puntos_final < 40) {
    $seeing = "Muy bueno";
    $point = 2.5;
} else {
    $seeing = "Excelente";
    $point = 3;
}

// --- 10. Salida JSON ---
echo json_encode([
    "IEAL" => round($puntos_final, 1),
    "estrellas" => $point,
    "seeing" => $seeing,
    "detalles" => $detalles
], JSON_UNESCAPED_UNICODE);

$mysqli->close();
?>
