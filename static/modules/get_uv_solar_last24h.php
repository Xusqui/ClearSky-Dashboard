<?php
header("Content-Type: application/json");
require_once __DIR__ . "/../config/config.php";

$mysqli = new mysqli($db_url, $db_user, $db_pass, $db_database);

if ($mysqli->connect_errno) {
    echo json_encode(["error" => true, "message" => "Error de conexión a la BD"]);
    exit();
}

$query = "
    SELECT DATE_FORMAT(`timestamp`, '%H:%i') AS hora, indice_uv, radiacion_solar
    FROM meteo
    WHERE `timestamp` >= NOW() - INTERVAL 24 HOUR
    ORDER BY `timestamp` ASC
";

$result = $mysqli->query($query);

$data = [];
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $data[] = $row;
    }
}

echo json_encode($data, JSON_UNESCAPED_UNICODE);
$mysqli->close();
?>
