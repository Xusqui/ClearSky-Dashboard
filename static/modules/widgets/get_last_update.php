<?php
// get_last_update.php
// Establecer la cabecera para indicar que la respuesta será un JSON
header('Content-Type: application/json');

// 1. Incluir la configuración y establecer la conexión a la base de datos
// La conexión $mysqli debe estar disponible después de este include
require_once "../../config/config.php";

// CREACIÓN DE LA CONEXIÓN A LA BASE DE DATOS
$mysqli = new mysqli($db_url, $db_user, $db_pass, $db_database);

// Si la conexión falla, devolver un error JSON
if ($mysqli->connect_errno) {
    http_response_code(500); // Internal Server Error
    echo json_encode([
        'error' => 'Fallo al conectar a la base de datos: ' . $mysqli->connect_error
    ]);
    exit();
}

// 2. Obtener la zona horaria local (tz)
$tz_query = "SELECT tz FROM config LIMIT 1";
$tz_result = $mysqli->query($tz_query);
$local_timezone_id = 'UTC'; // Valor por defecto

if ($tz_result && $tz_result->num_rows > 0) {
    $tz_row = $tz_result->fetch_assoc();
    $local_timezone_id = $tz_row['tz'];
    $tz_result->free();
}

// 3. Obtener el último timestamp de la tabla 'meteo'
$meteo_query = "SELECT timestamp, interval_sec FROM meteo ORDER BY timestamp DESC LIMIT 1";
$meteo_result = $mysqli->query($meteo_query);

if (!$meteo_result || $meteo_result->num_rows === 0) {
    // Manejar el caso de que no haya datos
    http_response_code(404); // Not Found
    echo json_encode([
        'error' => 'No se encontraron datos en la tabla meteo.'
    ]);
    $mysqli->close();
    exit();
}

$meteo_row = $meteo_result->fetch_assoc();
$last_update_utc_string = $meteo_row['timestamp'];
$station_interval_seconds = (int) $meteo_row['interval_sec'];
$meteo_result->free();

$mysqli->close();

// 4. Realizar los cálculos y el formateo de la fecha

// Crear objetos DateTime para manipular las fechas
// 4. Realizar los cálculos y el formateo de la fecha

try {
    // La marca de tiempo de la BD se considera en UTC
    $utc_datetime = new DateTime($last_update_utc_string, new DateTimeZone('UTC'));

    // Convertir la fecha UTC a la zona horaria local
    $local_timezone = new DateTimeZone($local_timezone_id);
    $local_datetime = clone $utc_datetime;
    $local_datetime->setTimezone($local_timezone);

    // Obtener la hora actual para el cálculo de la diferencia
    $now_local = new DateTime('now', $local_timezone);
    $diff_seconds = $now_local->getTimestamp() - $local_datetime->getTimestamp();

    // --- NUEVA FORMA DE FORMATEAR SIN STRFTIME ---

    // Creamos el formateador: (Idioma, Estilo Fecha, Estilo Hora, Zona Horaria, Tipo Calendario, Patrón)
    $formatter = new IntlDateFormatter(
        'es_ES',
        IntlDateFormatter::LONG,
        IntlDateFormatter::SHORT,
        $local_timezone_id,
        IntlDateFormatter::GREGORIAN,
        "'A LAS' HH:mm 'DEL' dd 'DE' MMMM 'DE' yyyy"
    );

    // Convertimos a mayúsculas para mantener tu estilo actual
    $ts_formatted = mb_strtoupper($formatter->format($local_datetime));

    // Construir la cadena de estado final
    $status_message = sprintf(
        '%s. ACTUALIZADO HACE %d SEG.',
        $ts_formatted,
        max(0, $diff_seconds)
    );

    // 5. Devolver la respuesta JSON
    echo json_encode([
        'ts_formatted' => $ts_formatted,
        'diff_seconds' => max(0, $diff_seconds),
        'status_message' => $status_message,
        'local_timestamp' => $local_datetime->getTimestamp(),
        'station_interval_sec' => $station_interval_seconds
    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
} catch (Exception $e) {
    http_response_code(500); // Internal Server Error
    echo json_encode([
        'error' => 'Error de procesamiento de fecha: ' . $e->getMessage()
    ]);
    exit();
}
