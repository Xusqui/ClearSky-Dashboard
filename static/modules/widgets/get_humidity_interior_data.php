<?php
// get_humidity_interior_data.php

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
 * Calcula una aproximación del punto de rocío a partir de la temperatura del aire y la humedad relativa.
 * NOTA: Esta función utiliza una fórmula simplificada para determinar la sensación de confort.
 *
 * @param float $temperatura La temperatura del aire en grados Celsius.
 * @param float $humedadRelativa La humedad relativa en porcentaje (de 0 a 100).
 * @return float El valor del Punto de Rocío calculado.
 */
function obtenerSensacionAmbiente(float $temperatura, float $humedad)
{
    // Punto de rocío (fórmula de aproximación simple: Td = T - ((100 - RH)/5))
    $puntoRocio = $temperatura - ((100 - $humedad) / 5);

    // Redondeamos para mayor limpieza en el resultado, aunque no es estrictamente necesario para la lógica de estado.
    return round($puntoRocio, 2);
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

// Consulta SQL para obtener la última humedad y temperatura interior
$sql = "SELECT humedad_interior, temperatura_interior
        FROM meteo
        ORDER BY timestamp DESC
        LIMIT 1";

$result = $mysqli->query($sql);

if ($result === false) {
    // Error en la consulta
    error_log("Error en la consulta SQL: " . $mysqli->error);
    $mysqli->close();
    die_with_error("Error al ejecutar la consulta de datos de humedad interior.");
}

if ($result->num_rows === 0) {
    // No hay datos
    $mysqli->close();
    die_with_error("No se encontraron datos de humedad interior en la tabla 'meteo'.");
}

// 2. Obtener los valores y sanitizarlos
$row = $result->fetch_assoc();

// Humedad interior
$humidity = isset($row['humedad_interior']) && is_numeric($row['humedad_interior']) ? floatval($row['humedad_interior']) : 0.0;
// Temperatura interior
$temp_int = isset($row['temperatura_interior']) && is_numeric($row['temperatura_interior']) ? floatval($row['temperatura_interior']) : 0.0;

$result->free();
$mysqli->close();


// ----------------------------------------------------
// 3. Cálculos y Lógica de Estado
// ----------------------------------------------------

$sensacion = obtenerSensacionAmbiente($temp_int, $humidity); // Esto es el Punto de Rocío Interior

// Calcular ángulo del gráfico (El 100% de humedad es 360 grados)
$angle_humidity = 360 * ($humidity / 100);

// Determinar estado basado en el Punto de Rocío interior ($sensacion)
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

// ----------------------------------------------------
// 4. Devolver JSON
// ----------------------------------------------------
header('Content-Type: application/json');
echo json_encode([
    "humidity" => $humidity,
    "temp" => $temp_int,
    "angle" => $angle_humidity,
    "legend" => $humid_legend,
    "color" => $humidity_color,
    "state" => $humid_state
]);
?>
