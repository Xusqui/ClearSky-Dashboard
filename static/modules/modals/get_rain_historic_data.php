<?php
// get_rain_historic_data.php

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

// Consulta SQL para obtener todos los valores de lluvia de la última fila
$sql = "SELECT
            lluvia_rate,        -- Intensidad actual
            lluvia_evento,      -- Lluvia del último evento
            lluvia_hora,        -- Lluvia por hora
            lluvia_diaria,      -- Lluvia diaria (Añadido)
            lluvia_semana,      -- Lluvia semanal (Añadido)
            lluvia_mes,         -- Lluvia mensual
            lluvia_ano,         -- Lluvia anual (Añadido)
            lluvia_total        -- Lluvia total acumulada
        FROM meteo
        ORDER BY timestamp DESC
        LIMIT 1";

$result = $mysqli->query($sql);

if ($result === false) {
    error_log("Error en la consulta SQL: " . $mysqli->error);
    $mysqli->close();
    die_with_error("Error al ejecutar la consulta de datos históricos de lluvia.");
}

if ($result->num_rows === 0) {
    $mysqli->close();
    die_with_error("No se encontraron datos históricos de lluvia en la tabla 'meteo'.");
}

// 2. Obtener los valores y sanitizarlos
$row = $result->fetch_assoc();

$result->free();
$mysqli->close();


// ----------------------------------------------------
// 3. Mapeo a las claves JSON originales
// ----------------------------------------------------

// La salida JSON debe coincidir con las claves que espera su frontend:
$data = [
    // El 'status' y 'rate' original apuntaban al mismo sensor de HA (rain_rate)
    "status"    => isset($row['lluvia_rate']) && is_numeric($row['lluvia_rate']) ? floatval($row['lluvia_rate']) : 0.0,
    "rate"      => isset($row['lluvia_rate']) && is_numeric($row['lluvia_rate']) ? floatval($row['lluvia_rate']) : 0.0,

    // 'daily' del script original apuntaba a 'event_rain'. Usamos 'lluvia_evento'.
    "event"     => isset($row['lluvia_evento']) && is_numeric($row['lluvia_evento']) ? floatval($row['lluvia_evento']) : 0.0,

    "hourly"    => isset($row['lluvia_hora']) && is_numeric($row['lluvia_hora']) ? floatval($row['lluvia_hora']) : 0.0,
    "monthly"   => isset($row['lluvia_mes']) && is_numeric($row['lluvia_mes']) ? floatval($row['lluvia_mes']) : 0.0,
    "total"     => isset($row['lluvia_total']) && is_numeric($row['lluvia_total']) ? floatval($row['lluvia_total']) : 0.0,

    // Añadimos datos adicionales de la BD para tener una visión completa:
    "rain_daily"   => isset($row['lluvia_diaria']) && is_numeric($row['lluvia_diaria']) ? floatval($row['lluvia_diaria']) : 0.0,
    "rain_weekly"  => isset($row['lluvia_semana']) && is_numeric($row['lluvia_semana']) ? floatval($row['lluvia_semana']) : 0.0,
    "rain_yearly"  => isset($row['lluvia_ano']) && is_numeric($row['lluvia_ano']) ? floatval($row['lluvia_ano']) : 0.0,
];

// ----------------------------------------------------
// 4. Devolver JSON
// ----------------------------------------------------
header('Content-Type: application/json');
echo json_encode($data);
?>
