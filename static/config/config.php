<?php
// config.php
// Script principal para cargar la configuración de la estación.

include __DIR__ . '/config_db.php';

// === INCLUIR ESQUEMA CENTRAL ===
// Asumo que config_schema.php está en la misma carpeta que config_db.php
require_once __DIR__ . '/config_schema.php';
require_once __DIR__ . '/meteo_schema.php'; // Se incluye el esquema de 'meteo'

// === CONECTAR CON LA BASE DE DATOS ===
$conn = new mysqli($db_url, $db_user, $db_pass, $db_database);
if ($conn->connect_error) {
    die("Error de conexión a la base de datos: " . $conn->connect_error);
}

// =========================================================
// === COMPROBACIONES DE ESQUEMA (config + meteo), con caché ===
// =========================================================
// Estas comprobaciones (SHOW TABLES / SHOW COLUMNS) solo cambian tras un
// despliegue o una visita a setup.php, así que se cachean con un TTL para
// no repetirlas en cada request de cada widget. setup.php invalida esta
// caché en cuanto migra el esquema.
$requiredFields = array_keys($config_schema);
$requiredMeteoFields = array_keys($meteo_schema);

$schemaCacheFile = __DIR__ . '/../cache/schema_check.json';
$schemaCacheTTL = 3600; // 1 hora
$schemaValidatedRecently = false;

if (is_file($schemaCacheFile)) {
    $cached = json_decode(file_get_contents($schemaCacheFile), true);
    if (is_array($cached) && isset($cached['checked_at']) && (time() - $cached['checked_at']) < $schemaCacheTTL) {
        $schemaValidatedRecently = true;
    }
}

if (!$schemaValidatedRecently) {
    // === COMPROBAR SI EXISTE LA TABLA 'config' ===
    $tableExists = $conn->query("SHOW TABLES LIKE 'config'");
    if ($tableExists->num_rows === 0) {
        // Si la tabla no existe, redirige al setup
        header("Location: ./static/config/setup.php");
        exit;
    }

    // === COMPROBAR SI EXISTEN LAS COLUMNAS REQUERIDAS (config) ===
    $result = $conn->query("SHOW COLUMNS FROM config");
    if (!$result) {
        // Si falla el SHOW COLUMNS, redirige al setup
        header("Location: ./static/config/setup.php");
        exit;
    }

    $columns = [];
    while ($row = $result->fetch_assoc()) {
        $columns[] = $row['Field'];
    }
    foreach ($requiredFields as $field) {
        if (!in_array($field, $columns)) {
            // Si falta alguna columna (porque se añadió al esquema después), redirige al setup
            header("Location: ./static/config/setup.php");
            exit;
        }
    }

    // === COMPROBAR SI EXISTE LA TABLA 'meteo' ===
    $meteoTableExists = $conn->query("SHOW TABLES LIKE 'meteo'");
    if ($meteoTableExists->num_rows === 0) {
        // Si la tabla 'meteo' no existe, redirige al setup
        header("Location: ./static/config/setup.php");
        exit;
    }

    // === COMPROBAR SI EXISTEN LAS COLUMNAS REQUERIDAS (meteo) ===
    $meteoResult = $conn->query("SHOW COLUMNS FROM meteo");
    if (!$meteoResult) {
        // Si falla el SHOW COLUMNS para 'meteo', redirige al setup
        header("Location: ./static/config/setup.php");
        exit;
    }

    $meteoColumns = [];
    while ($row = $meteoResult->fetch_assoc()) {
        $meteoColumns[] = $row['Field'];
    }
    foreach ($requiredMeteoFields as $field) {
        if (!in_array($field, $meteoColumns)) {
            // Si falta alguna columna en 'meteo', redirige al setup
            header("Location: ./static/config/setup.php");
            exit;
        }
    }

    // Esquema validado correctamente: refrescar la caché.
    @file_put_contents($schemaCacheFile, json_encode(['checked_at' => time()]));
}

// =========================================================
// === FIN DE COMPROBACIONES DE ESQUEMA ===
// =========================================================


// === COMPROBAR SI LOS CAMPOS TIENEN VALORES (Mínima Configuración) ===
// Excluimos 'id' y 'password' de la comprobación de valores vacíos
$fields_to_check_values = array_diff($requiredFields, [
    'id', 'password', 'send_local', 'send_ha', 'send_meteoclimatic', 'ha_token', 'meteoclimatic_code', 'meteoclimatic_token'
]);

$query = "SELECT " . implode(",", $fields_to_check_values) . " FROM config LIMIT 1";
$res = $conn->query($query);

if (!$res || $res->num_rows == 0) {
    header("Location: ./static/config/setup.php");
    exit;
}

$row = $res->fetch_assoc();

foreach ($fields_to_check_values as $field) {
    if (!array_key_exists($field, $row) || $row[$field] === null) {
        header("Location: ./static/config/setup.php");
        exit;
    }
}

// === TODO CORRECTO ===
// Se pueden asignar las variables PHP directamente (ajusta esta lista a las variables que necesites en tu script principal):
$lat = $row['latitud'];
$lon = $row['longitud'];
$elev = $row['elevacion'];
$hardware = $row['hardware'];
$software = $row['software'];
$observatorio = $row['observatorio'];
$city = $row['city'];
$country = $row['country'];
$tz = $row['tz'];
$log_weather = $row['log_weather'];
$show_in_temp = $row['show_in_temp'];
$show_in_hum = $row['show_in_hum'];
$show_uv =$row['show_UV'];
$show_solar = $row['show_solar'];
$show_dew = $row['show_dew'];
$show_sky = $row['show_sky'];
$rain_offset = $row['rain_offset'];

// Conversión de formato de Latitud/Longitud para visualización
if ($lat > 0) {
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

// Nota: Las banderas de envío y tokens específicos (ha_token, meteoclimatic_code, etc.)
// no se cargan aquí, ya que el script principal (index.php) no los requiere directamente.
// Si son necesarios, agrégalos. Por ejemplo:
// $send_ha = $row['send_ha'];
// $ha_token = $row['ha_token'];

$conn->close();
?>
