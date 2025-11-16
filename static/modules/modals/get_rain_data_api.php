<?php
// get_rain_data_api.php
header("Content-Type: application/json");
require_once __DIR__ . "/../../config/config.php"; // Incluir config de la BD

// --- Funciones auxiliares ---
function die_with_error($mysqli, $message) {
    if ($mysqli) $mysqli->close();
    die(json_encode(["error" => true, "message" => $message]));
}

function get_target_timezone($mysqli) {
    $tz_query = "SELECT `tz` FROM `config` LIMIT 1";
    $tz_result = $mysqli->query($tz_query);
    $target_timezone = "UTC";
    if ($tz_result && $tz_result->num_rows > 0) {
        $tz_row = $tz_result->fetch_assoc();
        $target_timezone = str_replace('\\', '', $tz_row['tz']); // Limpiar TZ
        $tz_result->free();
    }
    return $target_timezone;
}

// ----------------------------------------------------
// 1. Conexión y Configuración
// ----------------------------------------------------
if (!isset($db_url, $db_user, $db_pass, $db_database)) {
    die_with_error(null, "Error: Las credenciales de la base de datos no están definidas en config.php.");
}

$mysqli = new mysqli($db_url, $db_user, $db_pass, $db_database);

if ($mysqli->connect_error) {
    error_log("Error de conexión a la BD: " . $mysqli->connect_error);
    die_with_error(null, "Error al conectar con la base de datos.");
}
$mysqli->set_charset("utf8mb4");

$mode = $_GET['mode'] ?? 'stats'; // Modo por defecto

// ----------------------------------------------------
// 2. Modo: Estadísticas Actuales (Cuadrícula)
// ----------------------------------------------------
if ($mode === 'stats') {
    // Consulta SQL para obtener todos los valores de lluvia de la última fila
    $sql = "SELECT
                lluvia_rate, lluvia_evento, lluvia_hora, lluvia_diaria,
                lluvia_semana, lluvia_mes, lluvia_ano, lluvia_total
            FROM meteo
            ORDER BY timestamp DESC
            LIMIT 1";

    $result = $mysqli->query($sql);

    if ($result === false || $result->num_rows === 0) {
        die_with_error($mysqli, "No se encontraron datos de lluvia.");
    }

    $row = $result->fetch_assoc();
    $result->free();
    $mysqli->close();

    $data = [
        "status"        => floatval($row['lluvia_rate'] ?? 0.0),
        "rate"          => floatval($row['lluvia_rate'] ?? 0.0),
        "event"         => floatval($row['lluvia_evento'] ?? 0.0),
        "hourly"        => floatval($row['lluvia_hora'] ?? 0.0),
        "monthly"       => floatval($row['lluvia_mes'] ?? 0.0),
        "total"         => floatval($row['lluvia_total'] ?? 0.0),
        "rain_daily"    => floatval($row['lluvia_diaria'] ?? 0.0),
        "rain_weekly"   => floatval($row['lluvia_semana'] ?? 0.0),
        "rain_yearly"   => floatval($row['lluvia_ano'] ?? 0.0),
    ];
    echo json_encode($data);
    exit();
}

// ----------------------------------------------------
// 3. Modo: Gráfico Mensual (lluvia_mes - Valor del último registro del mes)
// ----------------------------------------------------
if ($mode === 'monthly') {
    $start_date_str = $_GET['start'] ?? date('Y-01');
    $end_date_str = $_GET['end'] ?? date('Y-12');

    // Convertir 'YYYY-MM' en fechas SQL completas
    $start_date = date('Y-m-01 00:00:00', strtotime($start_date_str));
    $end_date = date('Y-m-t 23:59:59', strtotime($end_date_str));

    // Consulta para obtener el valor de lluvia_mes del ÚLTIMO registro de cada mes
    $query = "
        SELECT
            DATE_FORMAT(`timestamp`, '%Y-%m') AS anio_mes,
            SUBSTRING_INDEX(GROUP_CONCAT(lluvia_mes ORDER BY `timestamp` DESC), ',', 1) AS total_mes
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
            $labels[] = $row['anio_mes'];
            $data[] = round(floatval($row['total_mes'] ?? 0.0), 2);
        }
    }

    $mysqli->close();
    echo json_encode(['labels' => $labels, 'data' => $data]);
    exit();
}

// ----------------------------------------------------
// 4. Modo: Gráfico Detallado (Serie de tiempo)
// ----------------------------------------------------
if ($mode === 'detailed') {
    $start_date = $_GET['start'] ?? date('Y-m-d\TH:i', strtotime('-24 hours'));
    $end_date = $_GET['end'] ?? date('Y-m-d\TH:i');

    $target_timezone = get_target_timezone($mysqli);
    $tz_destination = new DateTimeZone($target_timezone);
    $date_format_php = (strtotime($end_date) - strtotime($start_date)) > (24 * 3600) ? 'Y-m-d H:i' : 'H:i';

    // Consulta SQL para obtener los datos de la serie de tiempo (lluvia_rate, lluvia_evento, etc.)
    $query = "
        SELECT `timestamp` AS hora, lluvia_rate, lluvia_evento, lluvia_hora
        FROM meteo
        WHERE `timestamp` BETWEEN ? AND ?
        ORDER BY `timestamp` ASC
    ";

    $stmt = $mysqli->prepare($query);
    $stmt->bind_param("ss", $start_date, $end_date);
    $stmt->execute();
    $result = $stmt->get_result();

    $labels = [];
    $rate_data = [];
    $evento_data = [];
    // $hora_data = []; // Podríamos incluir lluvia_hora si es necesario

    if ($result) {
        while ($row = $result->fetch_assoc()) {

            // CONVERSIÓN DE HORA EN PHP
            try {
                $dt = new DateTime($row['hora'], new DateTimeZone('UTC'));
                $dt->setTimezone($tz_destination);
                $labels[] = $dt->format($date_format_php);
            } catch (Exception $e) {
                $labels[] = $row['hora']; // Si falla, usa la hora cruda
            }

            $rate_data[] = floatval($row['lluvia_rate'] ?? 0.0);
            $evento_data[] = floatval($row['lluvia_evento'] ?? 0.0);
            // $hora_data[] = floatval($row['lluvia_hora'] ?? 0.0);
        }
    }

    $mysqli->close();

    $series = [
        ['name' => 'Lluvia Rate', 'data' => $rate_data],
        ['name' => 'Lluvia Evento', 'data' => $evento_data],
    ];

    echo json_encode(['labels' => $labels, 'series' => $series]);
    exit();
}

// Si no se especifica un modo válido
die_with_error($mysqli, "Modo de API no válido.");

?>
