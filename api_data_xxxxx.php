<?php
// api_data.php
// 1.- Cambia el nombre de api_data_xxxxxxxxxx.php a algo diferente, ej: api_data_219871554981.php (Una cadena aleatoria, para más seguridad)
// -------------------------------------------
// DEBUG (Definición temprana por si falla la DB)
// -------------------------------------------
$LOG_FILE = "weather_data.log";
$DEBUG_FILE = "debug.log";

function write_debug($file, $msg) {
    $time = date("Y-m-d H:i:s");
    file_put_contents($file, "[$time] $msg\n", FILE_APPEND);
}

// -------------------------------------------
// CONFIGURACIÓN: OBTENER DESDE BASE DE DATOS
// -------------------------------------------
// Incluir las credenciales de la DB
require_once __DIR__ . "/static/config/config_db.php";
//$db_database = "el_patio";

$conn = new mysqli($db_url, $db_user, $db_pass, $db_database);
if ($conn->connect_error) {
    $error_msg = "Error de conexión a la Base de Datos: " . $conn->connect_error;
    write_debug($DEBUG_FILE, $error_msg);
    // Respondemos OK a la estación para evitar reintentos, pero no procesamos.
    http_response_code(200);
    echo "DB Connection Error";
    exit;
}

// Cargar la configuración principal (id = 1)
$result = $conn->query("SELECT * FROM config WHERE id = 1");
$config = $result->fetch_assoc() ?? [];
$conn->close();

// --- DEFINICIÓN DE VARIABLES DINÁMICAS ---
// URL base del Webhook de Home Assistant (se asume fija)
// Si el token es 'ha_token' en la DB, la URL se construye: BASE + TOKEN
const HA_WEBHOOK_BASE = "http://192.168.1.100:8123/api/webhook/";

$TOKEN_SEGURO = $config['local_token'] ?? 'FAILSAFE_TOKEN';
$HOME_ASSISTANT_WEBHOOK = HA_WEBHOOK_BASE . ($config['ha_token'] ?? '');
$METEOCLIMATIC_API = "http://api.m11c.net/v2/ew/{station_code}/{api_key}"; // Template
$STATION_CODE = $config['meteoclimatic_code'] ?? '';
$API_KEY = $config['meteoclimatic_token'] ?? '';

// Flags de envío
$send_LOCAL = (int)($config['send_local'] ?? 0);
$send_HA = (int)($config['send_ha'] ?? 0);
$send_METEOCLIMATIC = (int)($config['send_meteoclimatic'] ?? 0);

// Zona horaria
$TIMEZONE = $config['tz'] ?? "UTC";
date_default_timezone_set($TIMEZONE);

// -------------------------------------------
// TOKEN
// -------------------------------------------
// Si el token cargado está vacío, denegamos el acceso inmediatamente.
if (empty($TOKEN_SEGURO) || !isset($_GET["token"]) || $_GET["token"] !== $TOKEN_SEGURO) {
    write_debug($DEBUG_FILE, 'Acceso denegado: token inválido o no configurado. Token: ' . ($_GET["token"] ?? 'NULO'));
    http_response_code(403);
    echo "Forbidden";
    exit;
}
unset($_GET["token"]);

// -------------------------------------------
// OBTENER DATOS (Ecowitt → $_POST o $_GET)
// -------------------------------------------
$data = $_POST;
if (empty($data)) {
    $data = $_GET;
}

// Guardar JSON crudo
// Una vez compruebes que todo funciona bien, puedes comentar la línea siguiente para que
// El archivo weather_data.log no se haga gigantesco
file_put_contents($LOG_FILE, json_encode($data) . "\n", FILE_APPEND);


// -------------------------------------------
// VALIDACIÓN DE DATOS CRÍTICOS
// -------------------------------------------
$required_fields = [
    'tempf' => 'Temperatura Exterior',
    'humidity' => 'Humedad Exterior',
    'windspeedmph' => 'Velocidad del Viento',
    'winddir' => 'Dirección del Viento',
    'baromrelin' => 'Presión Relativa',
    'dailyrainin' => 'Lluvia Diaria'
];

$missing_fields = [];
foreach ($required_fields as $key => $label) {
    // Comprueba si la clave no existe O si es null, vacía, o no es un valor numérico válido (que es lo que esperamos)
    if (!isset($data[$key]) || $data[$key] === null || trim($data[$key]) === '' || !is_numeric($data[$key])) {
        $missing_fields[] = $label . " ({$key})";
    }
}

if (!empty($missing_fields)) {
    $error_msg = "VALIDACIÓN FALLIDA: Datos críticos faltantes o inválidos. NO se enviarán ni guardarán datos. Faltantes: " . implode(", ", $missing_fields);
    write_debug($DEBUG_FILE, $error_msg);

    // RESPUESTA DE ÉXITO AL EMISOR: Es una buena práctica responder 200 OK
    // a la estación meteorológica para que no siga reintentando.
    http_response_code(200);
    echo "OK";
    exit; // Detiene la ejecución aquí.
}

// -------------------------------------------
// ENVÍO A HOME ASSISTANT
// -------------------------------------------
if ($send_HA === 1){
    try {
        // Validación: asegurar que la URL se ha construido correctamente
        if (empty($config['ha_token'])) {
            write_debug($DEBUG_FILE, "Error enviando a HA: Token de Home Assistant vacío.");
        } else {
            $ch = curl_init($HOME_ASSISTANT_WEBHOOK);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_exec($ch);
            $http_status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);
            // La siguiente línea escribe el resultado devuelto por Home Assistant.
            // Comentarla para que el debug.log no se haga muy grande
            write_debug($DEBUG_FILE, "Enviado a HA. Estado: $http_status");
        }
    } catch (Exception $e) {
        write_debug($DEBUG_FILE, "Error enviando a HA: " . $e->getMessage());
    }
} else {
    write_debug($DEBUG_FILE, "Se ha omitido el envío de datos a Home Assistant");
}
// -------------------------------------------
// ENVÍO A METEOCLIMATIC
// -------------------------------------------
if ($send_METEOCLIMATIC === 1) {
    try {
        // Validación: asegurar que las claves no están vacías
        if (empty($STATION_CODE) || empty($API_KEY)) {
            write_debug($DEBUG_FILE, "Error enviando a Meteoclimatic: Código de estación o API Key vacío.");
        } else {
            $url = str_replace(
                ["{station_code}", "{api_key}"],
                [$STATION_CODE, $API_KEY],
                $METEOCLIMATIC_API
            );
            $ch = curl_init($url);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_exec($ch);
            $http_status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);
            // La siguiente linea escribe el resultado devuelto por Meteoclimatic
            // COmentarla para que el debug.log no se haga muy grande
            write_debug($DEBUG_FILE, "Enviado a Meteoclimatic. Estado: $http_status");
        }
    } catch (Exception $e) {
        write_debug($DEBUG_FILE, "Error enviando a Meteoclimatic: " . $e->getMessage());
    }
} else {
    write_debug($DEBUG_FILE, "Se ha omitido el envío de datos a Meteoclimatic.net");
}
// -------------------------------------------
// CONVERSIONES
// -------------------------------------------
if ($send_LOCAL === 1) {
    // Fahrenheit → Celsius
    function f_to_c($f) {
        return ($f - 32) * 5/9;
    }

    // Mph → Km/h
    function mph_to_kmh($mph) {
        return $mph * 1.60934;
    }

    // in (lluvia) → mm
    function in_to_mm($in) {
        return $in * 25.4;
    }

    // Pulgadas mercurio → hPa
    function inHg_to_hPa($inhg) {
        return $inhg * 33.8639;
    }

    // Sensación térmica (viento en km/h)
    function wind_chill($tempC, $wind_kmh) {
        if ($tempC > 10 || $wind_kmh < 4.8) {
            return $tempC;
        }
        return 13.12 + 0.6215*$tempC - 11.37*pow($wind_kmh,0.16) + 0.3965*$tempC*pow($wind_kmh,0.16);
    }

    // Punto de rocío (fórmula de Magnus, robusta)
    function dew_point($tempC, $humidity) {
        // Forzar tipo numérico
        $t = floatval($tempC);
        $h = floatval($humidity);

        // Protección: si falta humedad o es 0, no calculamos (evita log(0))
        if ($h <= 0 || $h > 100 || !is_finite($t)) {
            return null;
        }

        $a = 17.27;
        $b = 237.7;

        // gamma = (a * T)/(b + T) + ln(RH)
        $gamma = ($a * $t) / ($b + $t) + log($h / 100.0);

        // Td = (b * gamma) / (a - gamma)
        $td = ($b * $gamma) / ($a - $gamma);

        // Redondear a 2 decimales para almacenar
        return round($td, 2);
    }

    // Convertir dateutc a la zona horaria de la configuración ($TIMEZONE)
    $utc = new DateTime($data["dateutc"], new DateTimeZone("UTC"));
    $timestamp_utc = $utc->format("Y-m-d H:i:s");
    $timezone = "UTC"; // Se almacena como UTC para consistencia

    // -------------------------------------------
    // MAPEO Y CONVERSIONES FINALES
    // -------------------------------------------
    $temperatura = f_to_c($data["tempf"]);
    $temperatura_interior = f_to_c($data["tempinf"]);
    $humedad = $data["humidity"];
    $humedad_interior = $data["humidityin"];

    $viento_velocidad = mph_to_kmh($data["windspeedmph"]);
    $viento_racha = mph_to_kmh($data["windgustmph"]);
    $viento_racha_maxima = mph_to_kmh($data["maxdailygust"]);
    $viento_direccion = $data["winddir"];

    $presion_rel = inHg_to_hPa($data["baromrelin"]);
    $presion_abs = inHg_to_hPa($data["baromabsin"]);

    $lluvia_diaria = in_to_mm($data["dailyrainin"]);
    $lluvia_rate = in_to_mm($data["rainratein"]);
    $lluvia_evento = in_to_mm($data["eventrainin"]);
    $lluvia_hora = in_to_mm($data["hourlyrainin"]);
    $lluvia_semana = in_to_mm($data["weeklyrainin"]);
    $lluvia_mes = in_to_mm($data["monthlyrainin"]);
    $lluvia_ano = in_to_mm($data["yearlyrainin"]);
    $lluvia_total = in_to_mm($data["totalrainin"]);

    $sol = $data["solarradiation"];
    $uv = $data["uv"];

    $sensacion = wind_chill($temperatura, $viento_velocidad);
    $punto_rocio = dew_point($temperatura, $humedad);

    $vpd = isset($data["vpd"]) ? $data["vpd"] : null;

    // Metadatos Ecowitt
    $stationtype = $data["stationtype"];
    $runtime = $data["runtime"];
    $heap = $data["heap"];
    $wh65batt = $data["wh65batt"];
    $freq = $data["freq"];
    $model = $data["model"];
    $passkey = $data["PASSKEY"];

    // -------------------------------------------
    // INSERT EN BASE DE DATOS
    // -------------------------------------------
    // Las credenciales de la DB ya se han requerido al inicio.
    $mysqli = new mysqli($db_url, $db_user, $db_pass, $db_database);
    if ($mysqli->connect_errno) {
        write_debug($DEBUG_FILE, "Error conexión DB: " . $mysqli->connect_error);
    } else {
        // La siguiente línea escribe la conexión a la BD ha sido exitosa
        // Comentarla para que debug.log no sea muy grande
        write_debug($DEBUG_FILE, "Conexión OK a la DB");
        $stmt = $mysqli->prepare("
    INSERT INTO meteo (
        timestamp, timezone, temperatura, humedad, sensacion_termica,
        presion_relativa, presion_absoluta, punto_rocio,
        viento_velocidad, viento_direccion, viento_racha,
        lluvia_diaria, indice_uv, radiacion_solar,
        temperatura_interior, humedad_interior,
        lluvia_rate, lluvia_evento, lluvia_hora,
        lluvia_semana, lluvia_mes, lluvia_ano, lluvia_total,
        viento_racha_maxima, vpd,
        stationtype, runtime, heap, wh65batt,
        freq, model, passkey
    ) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)
");

        if (!$stmt) {
            write_debug($DEBUG_FILE, "Error en prepare(): " . $mysqli->error);
            exit;
        }

        $stmt->bind_param(
            "ssdddddddddddddddddddddddsiiisss",
            $timestamp_utc, $timezone, $temperatura, $humedad, $sensacion,
            $presion_rel, $presion_abs, $punto_rocio,
            $viento_velocidad, $viento_direccion, $viento_racha,
            $lluvia_diaria, $uv, $sol,
            $temperatura_interior, $humedad_interior,
            $lluvia_rate, $lluvia_evento, $lluvia_hora,
            $lluvia_semana, $lluvia_mes, $lluvia_ano, $lluvia_total,
            $viento_racha_maxima, $vpd,
            $stationtype, $runtime, $heap, $wh65batt,
            $freq, $model, $passkey
        );

        if ($stmt->errno) {
            write_debug($DEBUG_FILE, "Error en bind_param(): " . $stmt->error);
            exit;
        }

        if ($stmt->execute()) {
            // La siguiente línea escribe que los datos se insertaron correctamente en la BD
            // Comentarla para que debug.log no sea muy grande
            write_debug($DEBUG_FILE, "DB insert OK");
        } else {
            write_debug($DEBUG_FILE, "DB ERROR: " . $stmt->error);
        }

        $stmt->close();
        $mysqli->close();
    }
} else {
    write_debug($DEBUG_FILE, "Se ha omitido el envío a la Base de Datos local");
}
// -------------------------------------------
// RESPUESTA A ECOWITT
// -------------------------------------------
http_response_code(200);
echo "OK";
?>
