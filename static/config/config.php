<?php
// CONFIGURACIÓN
// Datos de Conexión a Home Assistant
// añadido a .gitignore
$ha_url = "127.0.0.1:8123"; // IP de tu Home Assistant. I.e: http://127.0.0.1:8123
$token = ""; // Token de Home Assistant

include __DIR__ . '/config_db.php';

// === CONECTAR CON LA BASE DE DATOS ===
$conn = new mysqli($db_url, $db_user, $db_pass, $db_database);
if ($conn->connect_error) {
    die("Error de conexión a la base de datos: " . $conn->connect_error);
}

// === COMPROBAR SI EXISTE LA TABLA 'config' ===
$tableExists = $conn->query("SHOW TABLES LIKE 'config'");
if ($tableExists->num_rows === 0) {
    header("Location: /weather/static/config/setup.php");
    exit;
}

// === COMPROBAR SI EXISTEN LAS COLUMNAS REQUERIDAS ===
$requiredFields = ['latitud', 'longitud', 'elevacion', 'hardware', 'software', 'observatorio', 'city', 'country', 'tz', 'password', 'ha_token'];
$result = $conn->query("SHOW COLUMNS FROM config");
if (!$result) {
    header("Location: /weather/static/config/setup.php");
    exit;
}

// === COMPROBAR SI EXISTEN LOS CAMPOS REQUERIDOS ===
$columns = [];
while ($row = $result->fetch_assoc()) {
    $columns[] = $row['Field'];
}
foreach ($requiredFields as $field) {
    if (!in_array($field, $columns)) {
        header("Location: /weather/static/config/setup.php");
        exit;
    }
}

// === COMPROBAR SI LOS CAMPOS TIENEN VALORES ===
$query = "SELECT " . implode(",", $requiredFields) . " FROM config LIMIT 1";
$res = $conn->query($query);

if (!$res || $res->num_rows == 0) {
    header("Location: /weather/static/config/setup.php");
    exit;
}

$row = $res->fetch_assoc();
foreach ($requiredFields as $field) {
    if (empty($row[$field])) {
        //   Esto no sé exactamente qué comprueba
        header("Location: /weather/static/config/setup.php");
        exit;
    }
}

// === TODO CORRECTO ===
// Se pueden asignar las variables PHP directamente:
$lat = $row['latitud'];
$lon = $row['longitud'];
$elev = $row['elevacion'];
$hardware = $row['hardware'];
$software = $row['software'];
$observatorio = $row['observatorio'];
$city = $row['city'];
$country = $row['country'];
$tz = $row['tz'];

if ($lat >0) {
    $latitud = $lat .'º N';
} elseif ($lat < 0) {
    $latitud = (-1 * $lat) . 'º S';
} else {
    $latitud = $lat . 'º';
}

if ($lon > 0) {
    $longitud = $lon . 'º E';
} elseif ($lon < 0) {
    $longitud = (-1 * $lon) . 'º W';
} else {
    $longitud = $lon . 'º';
}
?>
