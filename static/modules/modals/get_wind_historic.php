<?php
// get_wind_historic.php
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

    // Validar formato
    $start_dt = DateTime::createFromFormat('Y-m-d\TH:i', $start_date);
    $end_dt = DateTime::createFromFormat('Y-m-d\TH:i', $end_date);

    if ($start_dt && $end_dt) {
        // Formato de fecha dinámico para el eje X (Formato PHP)
        $date_format_php = ($start_dt->diff($end_dt)->d > 0) ? 'Y-m-d H:i' : 'H:i';

        // Consulta SQL: Seleccionamos el timestamp crudo
        $query = "
            SELECT `timestamp` AS hora, viento_velocidad, viento_racha, viento_direccion
            FROM meteo
            WHERE `timestamp` BETWEEN ? AND ?
            ORDER BY `timestamp` ASC
        ";

        $stmt = $mysqli->prepare($query);
        $stmt->bind_param("ss", $start_date, $end_date);

    } else {
        // Formato inválido, usar por defecto 24h
        $date_format_php = 'H:i';

        $query = "
            SELECT `timestamp` AS hora, viento_velocidad, viento_racha, viento_direccion
            FROM meteo
            WHERE `timestamp` >= NOW() - INTERVAL 24 HOUR
            ORDER BY `timestamp` ASC
        ";
        $stmt = $mysqli->prepare($query);
    }

} else {
    // Comportamiento por defecto: últimas 24 horas
    $date_format_php = 'H:i';

    $query = "
        SELECT `timestamp` AS hora, viento_velocidad, viento_racha, viento_direccion
        FROM meteo
        WHERE `timestamp` >= NOW() - INTERVAL 24 HOUR
        ORDER BY `timestamp` ASC
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

echo json_encode($data, JSON_UNESCAPED_UNICODE);
$stmt->close();
$mysqli->close();
?>
