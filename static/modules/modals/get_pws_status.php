<?php
// get_pws_status.php
/********************************************************
 * NOTA: El archivo /static/modals/modal_info.php ya    *
 * recoge muchos de los datos de las variables globales *
 * definidas en el script de configuracion:             *
 * /static/config/config.php                            *
 *******************************************************/

header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . "/../../config/config.php";

$mysqli = new mysqli($db_url, $db_user, $db_pass, $db_database);

if ($mysqli->connect_errno) {
    echo json_encode([
        "error" => true,
        "message" => "Error de conexión: " . $mysqli->connect_error
    ], JSON_UNESCAPED_UNICODE);
    exit();
}

$mysqli->set_charset("utf8mb4");

// Consulta para obtener el último registro de la tabla meteo
$query = "SELECT interval_sec, wh65batt FROM meteo ORDER BY id DESC LIMIT 1";
$result = $mysqli->query($query);

if ($result && $row = $result->fetch_assoc()) {
    echo json_encode([
        "error" => false,
        "interval_sec" => (int)$row['interval_sec'],
        "wh65batt" => (int)$row['wh65batt']
    ]);
} else {
    echo json_encode([
        "error" => true,
        "message" => "No se encontraron datos meteorológicos"
    ]);
}

$mysqli->close();
