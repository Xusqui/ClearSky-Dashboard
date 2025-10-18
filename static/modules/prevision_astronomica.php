<?php
header("Content-Type: application/json");
require_once __DIR__ . "/../config/config.php";

$mysqli = new mysqli($db_url, $db_user, $db_pass, $db_database);
if ($mysqli->connect_errno) {
    echo json_encode(["error" => true, "message" => "Error de conexión a la BD"]);
    exit();
}

// 1. Leer datos de las últimas 8 horas
$query = "
    SELECT temperatura, humedad, presion_absoluta, viento_velocidad, radiacion_solar, timestamp
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
            "radiacion_solar" => (float) $row["radiacion_solar"],
        ];
    }
}

// 2. Calcular variaciones
function calcularVariacion($array, $campo) {
    $valores = array_column($array, $campo);
    return max($valores) - min($valores);
}

$variacionTemp = calcularVariacion($datos, "temperatura");
$variacionHum = calcularVariacion($datos, "humedad");
$variacionPres = calcularVariacion($datos, "presion_absoluta");
$maxViento = max(array_column($datos, "viento_velocidad"));
$luminosidadFinal = end($datos)["radiacion_solar"];

// 3. Asignar puntuaciones
$puntos = 0;
$puntos += ($variacionTemp < 2) ? 5 : (($variacionTemp < 4) ? 3 : 1);
$puntos += ($variacionHum < 10) ? 5 : (($variacionHum < 20) ? 3 : 1);
$puntos += ($variacionPres < 3) ? 5 : (($variacionPres < 6) ? 3 : 1);
$puntos += ($maxViento < 10) ? 5 : (($maxViento < 20) ? 3 : 1);
$puntos += ($luminosidadFinal > 800) ? 1 : (($luminosidadFinal > 400) ? 3 : 5);

// 4. Clasificación
if ($puntos >= 30) {
    $seeing = "Excelente";
} elseif ($puntos >= 25) {
    $seeing = "Bueno";
} elseif ($puntos >= 18) {
    $seeing = "Regular";
} else {
    $seeing = "Pobre";
}

echo json_encode([
    "IEAL" => $puntos,
    "seeing" => $seeing,
    "detalles" => [
        "variacionTemp" => $variacionTemp,
        "variacionHum" => $variacionHum,
        "variacionPres" => $variacionPres,
        "maxViento" => $maxViento,
        "luminosidadFinal" => $luminosidadFinal
    ]
], JSON_UNESCAPED_UNICODE);

$mysqli->close();
?>
