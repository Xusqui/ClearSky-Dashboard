<?php
header("Content-Type: application/json");
require_once __DIR__ . "/../../config/config.php";

$mysqli = new mysqli($db_url, $db_user, $db_pass, $db_database);

if ($mysqli->connect_errno) {
    echo json_encode(["error" => true, "message" => "Error de conexión a la BD"]);
    exit();
}

$mysqli->set_charset("utf8mb4");

// Comprobar si se reciben parámetros de fecha
if (isset($_GET['start']) && isset($_GET['end'])) {
    $start_date = $_GET['start'];
    $end_date = $_GET['end'];

    // Validar formato
    $start_dt = DateTime::createFromFormat('Y-m-d\TH:i', $start_date);
    $end_dt = DateTime::createFromFormat('Y-m-d\TH:i', $end_date);

    if ($start_dt && $end_dt) {
        // Formato de fecha dinámico para el eje X
        $date_format = ($start_dt->diff($end_dt)->d > 0) ? '%Y-%m-%d %H:%i' : '%H:%i';

        $query = "
            SELECT DATE_FORMAT(`timestamp`, ?) AS hora, indice_uv, radiacion_solar
            FROM meteo
            WHERE `timestamp` BETWEEN ? AND ?
            ORDER BY `timestamp` ASC
        ";

        $stmt = $mysqli->prepare($query);
        $stmt->bind_param("sss", $date_format, $start_date, $end_date);

    } else {
        // Formato inválido, usar por defecto 24h
        $query = "
            SELECT DATE_FORMAT(`timestamp`, '%H:%i') AS hora, indice_uv, radiacion_solar
            FROM meteo
            WHERE `timestamp` >= NOW() - INTERVAL 24 HOUR
            ORDER BY `timestamp` ASC
        ";
        $stmt = $mysqli->prepare($query);
    }

} else {
    // Comportamiento por defecto: últimas 24 horas
    $query = "
        SELECT DATE_FORMAT(`timestamp`, '%H:%i') AS hora, indice_uv, radiacion_solar
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
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $data[] = $row;
    }
}

echo json_encode($data, JSON_UNESCAPED_UNICODE);
$stmt->close();
$mysqli->close();
?>
