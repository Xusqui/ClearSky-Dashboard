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
try {
    // La marca de tiempo de la BD se considera en UTC
    $utc_datetime = new DateTime($last_update_utc_string, new DateTimeZone('UTC'));

    // Convertir la fecha UTC a la zona horaria local
    $local_timezone = new DateTimeZone($local_timezone_id);
    $local_datetime = $utc_datetime->setTimezone($local_timezone);

    // Obtener la hora actual en la misma zona horaria local para el cálculo de la diferencia
    $now_local = new DateTime('now', $local_timezone);

    // Calcular la diferencia en segundos
    $interval = $now_local->diff($local_datetime);
    // Calcular los segundos totales
    // Nota: diff->s no es suficiente; debemos convertir la diferencia total a segundos.
    $diff_seconds = $now_local->getTimestamp() - $local_datetime->getTimestamp();

    // Formato de la fecha y hora local
    // 'H' (00-23), 'i' (00-59), 'd' (01-31), 'M' (Abrev. Mes), 'Y' (Año 4 dig.)
    // La 'M' se convierte a español si usas setlocale, pero para consistencia
    // y simplificación usaremos strftime si es posible, o una función de traducción.
    // Por simplicidad, usaremos el formato en inglés y luego lo ajustaremos

    // Configurar idioma español para la fecha (si se desea 'DE' en lugar de 'OF')
    setlocale(LC_TIME, 'es_ES.utf8', 'es_ES', 'es');

    $ts_formatted = strftime(
        'A LAS %H:%M DEL %d DE %B DE %Y',
        $local_datetime->getTimestamp()
    );

    // Reemplazar la primera letra del mes a minúscula
    $ts_formatted = str_replace(
        $local_datetime->format('F'),
        mb_strtolower($local_datetime->format('F')),
        $ts_formatted
    );

    // Construir la cadena de estado final
    $status_message = sprintf(
        '%s. ACTUALIZADO HACE %d SEG.',
        $ts_formatted,
        max(0, $diff_seconds) // Asegura que no sea negativo (aunque no debería)
    );

    // 5. Devolver la respuesta JSON
    echo json_encode([
        'ts_formatted' => $ts_formatted,
        'diff_seconds' => max(0, $diff_seconds),
        'status_message' => $status_message,
        'local_timestamp' => $local_datetime->getTimestamp(),
        'station_interval_sec' => $station_interval_seconds
    ]);

} catch (Exception $e) {
    http_response_code(500); // Internal Server Error
    echo json_encode([
        'error' => 'Error de procesamiento de fecha: ' . $e->getMessage()
    ]);
    exit();
}
?>
