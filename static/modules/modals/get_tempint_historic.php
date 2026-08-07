<?php
// get_tempint_historic.php
header("Content-Type: application/json");
require_once __DIR__ . "/../../config/config.php";

$mysqli = new mysqli($db_url, $db_user, $db_pass, $db_database);

if ($mysqli->connect_errno) {
    echo json_encode(["error" => true, "message" => "Error de conexión a la BD"]);
    exit();
}

$mysqli->set_charset("utf8mb4");

// --- 1. Obtener y Limpiar la Zona Horaria (tz) de la tabla config ---
$tz_query = "SELECT `tz` FROM `config` LIMIT 1";
$tz_result = $mysqli->query($tz_query);
$target_timezone = "UTC"; // Zona horaria por defecto
$date_format_php = 'H:i'; // Formato de fecha PHP por defecto

if ($tz_result && $tz_result->num_rows > 0) {
    $tz_row = $tz_result->fetch_assoc();
    // Limpieza: Aseguramos el formato IANA correcto (eliminando '\' si existe)
    $target_timezone = str_replace('\\', '', $tz_row['tz']);
    $tz_result->free();
}
// --------------------------------------------------------------------

// Comprobar si se reciben parámetros de fecha
if (isset($_GET['start']) && isset($_GET['end'])) {
    $start_date = $_GET['start'];
    $end_date = $_GET['end'];

    // Validar formato (datetime-local envía 'YYYY-MM-DDTHH:MM')
    $start_dt = DateTime::createFromFormat('Y-m-d\TH:i', $start_date);
    $end_dt = DateTime::createFromFormat('Y-m-d\TH:i', $end_date);

    if ($start_dt && $end_dt) {
        // Downsampling según la amplitud del rango: por encima de 7/30 días,
        // devolver todas las muestras crudas puede significar decenas de miles
        // de filas. Se agrega por hora o por día para mantener el payload y la
        // consulta acotados.
        $diff_days = $start_dt->diff($end_dt)->days;

        if ($diff_days > 30) {
            // Rango largo (> 30 días): un punto por día. Además del promedio
            // (para la línea), se incluyen MIN/MAX reales de cada día: el
            // frontend los usa para el eje Y y las etiquetas Máx/Mín en vez
            // de derivarlas de la serie ya promediada.
            $date_format_php = 'Y-m-d';
            $query = "
                SELECT DATE(`timestamp`) AS hora,
                       AVG(temperatura_interior) AS temperatura_interior,
                       MIN(temperatura_interior) AS temperatura_interior_min,
                       MAX(temperatura_interior) AS temperatura_interior_max
                FROM meteo
                WHERE `timestamp` BETWEEN ? AND ?
                GROUP BY DATE(`timestamp`)
                ORDER BY hora ASC
            ";
        } elseif ($diff_days > 7) {
            // Rango medio (7-30 días): un punto por hora. Mismo motivo que
            // arriba para las columnas MIN/MAX.
            $date_format_php = 'Y-m-d H:i';
            $query = "
                SELECT DATE_FORMAT(`timestamp`, '%Y-%m-%d %H:00:00') AS hora,
                       AVG(temperatura_interior) AS temperatura_interior,
                       MIN(temperatura_interior) AS temperatura_interior_min,
                       MAX(temperatura_interior) AS temperatura_interior_max
                FROM meteo
                WHERE `timestamp` BETWEEN ? AND ?
                GROUP BY DATE_FORMAT(`timestamp`, '%Y-%m-%d %H:00:00')
                ORDER BY hora ASC
            ";
        } else {
            // Rango corto (<= 7 días): muestras sin agregar, como antes.
            // LIMIT de seguridad: cota defensiva ante un rango/frecuencia de
            // reporte inusualmente altos, sin afectar el uso normal.
            $date_format_php = ($start_dt->diff($end_dt)->d > 0) ? 'Y-m-d H:i' : 'H:i';
            $query = "
                SELECT `timestamp` AS hora, temperatura_interior
                FROM meteo
                WHERE `timestamp` BETWEEN ? AND ?
                ORDER BY `timestamp` ASC
                LIMIT 50000
            ";
        }

        $stmt = $mysqli->prepare($query);
        $stmt->bind_param("ss", $start_date, $end_date);

    } else {
        // Formato inválido, usar por defecto 24h
        $date_format_php = 'H:i';

        $query = "
            SELECT `timestamp` AS hora, temperatura_interior
            FROM meteo
            WHERE `timestamp` >= NOW() - INTERVAL 24 HOUR
            ORDER BY `timestamp` ASC
            LIMIT 50000
        ";
        $stmt = $mysqli->prepare($query);
    }

} else {
    // Comportamiento por defecto: últimas 24 horas
    $date_format_php = 'H:i';

    $query = "
        SELECT `timestamp` AS hora, temperatura_interior
        FROM meteo
        WHERE `timestamp` >= NOW() - INTERVAL 24 HOUR
        ORDER BY `timestamp` ASC
        LIMIT 50000
    ";
    $stmt = $mysqli->prepare($query);
}

// Ejecutar la consulta preparada
$stmt->execute();
$result = $stmt->get_result();

$data = [];
$tz_destination = new DateTimeZone($target_timezone); // Objeto TZ para la conversión

if ($result) {
    while ($row = $result->fetch_assoc()) {

        // --- CONVERSIÓN DE HORA EN PHP ---
        try {
            // 1. Crear objeto DateTime asumiendo que el dato es UTC
            $dt = new DateTime($row['hora'], new DateTimeZone('UTC'));

            // 2. Aplicar la zona horaria de destino
            $dt->setTimezone($tz_destination);

            // 3. Formatear la hora usando el formato PHP previamente determinado
            $row["hora"] = $dt->format($date_format_php);

        } catch (Exception $e) {
            // Mantenemos la hora UTC si la conversión falla
        }
        // ----------------------------------------------

        $data[] = $row;
    }
}

echo json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
$stmt->close();
$mysqli->close();
?>
