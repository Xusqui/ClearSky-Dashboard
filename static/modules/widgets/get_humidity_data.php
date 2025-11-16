<?php
// get_humidity_data.php

// Incluir el archivo de configuración que debe contener las variables de conexión a MariaDB:
// $db_user, $db_pass, $db_url, $db_database
include '../../config/config.php';

/**
 * Función para devolver un error en formato JSON y terminar el script.
 * @param string $message Mensaje de error a devolver.
 */
function die_with_error($message) {
    header('Content-Type: application/json');
    die(json_encode([
        "error" => true,
        "message" => $message
    ]));
}

/**
 * Calcula el índice Humidex.
 * @param float $temp Temperatura en ºC.
 * @param float $dew Punto de Rocío en ºC.
 * @return float Humidex.
 */
function humidex ($temp, $dew){
    // Cálculo de la presión de vapor real a partir de Td (Magnus-Tetens, hPa)
    // $e = 6.112 * exp ((17.62 * $dew) / (243.12 + $dew));
    $e = 6.112 * exp ((17.62 * $dew) / (243.12 + $dew));

    // Humidex
    $humidex = $temp + 0.5555 * ($e - 10);

    return $humidex;
}

// ----------------------------------------------------
// 1. Conexión a la base de datos y obtención de datos
// ----------------------------------------------------

// Verificar si las variables de conexión están definidas después de la inclusión
if (!isset($db_url, $db_user, $db_pass, $db_database)) {
    die_with_error("Error: Las credenciales de la base de datos no están definidas en config.php.");
}

$mysqli = new mysqli($db_url, $db_user, $db_pass, $db_database);

if ($mysqli->connect_error) {
    // Error de conexión
    error_log("Error de conexión a la BD: " . $mysqli->connect_error);
    die_with_error("Error al conectar con la base de datos.");
}

// Consulta SQL para obtener los tres valores necesarios de la última fila
$sql = "SELECT humedad, temperatura, punto_rocio
        FROM meteo
        ORDER BY timestamp DESC
        LIMIT 1";

$result = $mysqli->query($sql);

if ($result === false) {
    // Error en la consulta
    error_log("Error en la consulta SQL: " . $mysqli->error);
    $mysqli->close();
    die_with_error("Error al ejecutar la consulta de datos de humedad.");
}

if ($result->num_rows === 0) {
    // No hay datos
    $mysqli->close();
    die_with_error("No se encontraron datos en la tabla 'meteo'.");
}

// 2. Obtener los valores y sanitizarlos
$row = $result->fetch_assoc();

// Aseguramos que los valores sean flotantes o 0 si son nulos/inválidos, manteniendo la lógica original.
$humidity = isset($row['humedad']) && is_numeric($row['humedad']) ? floatval($row['humedad']) : 0;
$temp = isset($row['temperatura']) && is_numeric($row['temperatura']) ? floatval($row['temperatura']) : 0;
$dew = isset($row['punto_rocio']) && is_numeric($row['punto_rocio']) ? floatval($row['punto_rocio']) : 0;

$result->free();
$mysqli->close();


// ----------------------------------------------------
// 3. Cálculos y Lógica de Estado
// ----------------------------------------------------

// Calcular Humidex
$hdex = round(humidex ($temp, $dew), 2);

// Calcular ángulo del gráfico (El 100 es el valor máximo de humedad)
$angle_humidity = 360 * ($humidity / 100);

// Determinar estado (El punto de rocío de 15ºC marca el umbral de confort)
if ($dew < 15) {
    $humid_state  = "dry";
    $humid_legend = "Seco";
// La condición original para 'humid' era $humidity >= 20.
// Asumiendo que esta es la lógica que quiere mantener:
} elseif ($humidity >= 20) {
    $humid_state  = "humid";
    $humid_legend = "Húmedo";
} else {
    $humid_state  = "comfortable";
    $humid_legend = "Confortable";
}

// Variable color (CSS)
$humidity_color = "--humidity-{$humid_state}-color";


// ----------------------------------------------------
// 4. Devolver JSON (Mismo formato anterior)
// ----------------------------------------------------
header('Content-Type: application/json');
echo json_encode([
    "humidity" => $humidity,
    //"temp" => $temp, // La temperatura se devuelve comentada en su script original, así que la mantengo fuera
    "dew" => $dew,
    "humidex" => $hdex,
    "angle" => $angle_humidity,
    "legend" => $humid_legend,
    "color" => $humidity_color,
    "state" => $humid_state
]);
?>
