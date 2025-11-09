<?php
// get_rain_monthly.php
header("Content-Type: application/json");
// Ruta a tu config de BASE DE DATOS
require_once __DIR__ . "/../../config/config.php";

$mysqli = new mysqli($db_url, $db_user, $db_pass, $db_database);

if ($mysqli->connect_errno) {
    echo json_encode(["error" => true, "message" => "Error de conexión a la BD"]);
    exit();
}
$mysqli->set_charset("utf8mb4");

// --- Lógica de Fechas ---
// 'YYYY-MM'. Por defecto, el año actual.
$start_date_str = $_GET['start'] ?? date('Y-01');
$end_date_str = $_GET['end'] ?? date('Y-12');

// Convertir 'YYYY-MM' en fechas SQL completas
// Inicio: primer día del mes de inicio
$start_date = date('Y-m-01 00:00:00', strtotime($start_date_str));
// Fin: último segundo del mes de fin
$end_date = date('Y-m-t 23:59:59', strtotime($end_date_str));


// --- Consulta SQL ---
// Agrupamos por mes/año y sumamos la precipitación
//
// ⚠️⚠️⚠️ ATENCIÓN: CAMBIA 'lluvia_diaria'
// por el nombre de tu columna de lluvia (ej: lluvia_intervalo)
// ⚠️⚠️⚠️
$query = "
    SELECT
        DATE_FORMAT(`timestamp`, '%Y-%m') AS anio_mes,
        SUM(lluvia_diaria) AS total_mes
    FROM
        meteo
    WHERE
        `timestamp` BETWEEN ? AND ?
    GROUP BY
        anio_mes
    ORDER BY
        anio_mes ASC
";

$stmt = $mysqli->prepare($query);
$stmt->bind_param("ss", $start_date, $end_date);
$stmt->execute();
$result = $stmt->get_result();

$labels = [];
$data = [];

if ($result) {
    while ($row = $result->fetch_assoc()) {
        $labels[] = $row['anio_mes']; // '2023-01', '2023-02', ...
        $data[] = round(floatval($row['total_mes']), 2); // Redondeamos a 2 decimales
    }
}

// Devolvemos un objeto JSON estructurado para ECharts
echo json_encode([
    'labels' => $labels,
    'data' => $data
], JSON_UNESCAPED_UNICODE);

$stmt->close();
$mysqli->close();
?>
