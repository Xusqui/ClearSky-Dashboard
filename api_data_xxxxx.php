<?php
// api_data.php
// 
// -------------------------------------------
// DEBUG (Definición temprana por si falla la DB)
// -------------------------------------------
$LOG_FILE = "weather_data.log";
$DEBUG_FILE = "debug.log";

function write_debug($file, $msg)
{
    $time = date("Y-m-d H:i:s");
    file_put_contents($file, "[$time] $msg\n", FILE_APPEND);
}

// -------------------------------------------
// CONFIGURACIÓN: OBTENER DESDE BASE DE DATOS
// -------------------------------------------
// Incluir las credenciales de la DB
require_once __DIR__ . "/static/config/config_db.php";

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
// Construir URL base del Webhook de Home Assistant dinámicamente
$ha_ip = $config['ha_ip'] ?? '192.168.1.100';
$ha_port = $config['ha_port'] ?? 8123;

// Validar IP y puerto antes de construir la URL
if (filter_var($ha_ip, FILTER_VALIDATE_IP) && is_numeric($ha_port) && $ha_port > 0 && $ha_port <= 65535) {
    $HA_WEBHOOK_BASE = "http://" . $ha_ip . ":" . $ha_port . "/api/webhook/";
} else {
    // Fallback si los datos no son válidos
    $HA_WEBHOOK_BASE = "http://192.168.1.100:8123/api/webhook/";
    write_debug($DEBUG_FILE, "Advertencia: IP o puerto de HA no válidos. Usando configuración por defecto.");
}

// Si el token es 'ha_token' en la DB, la URL se construye: BASE + TOKEN
$TOKEN_SEGURO = trim((string)($config['local_token'] ?? ''));
$HOME_ASSISTANT_WEBHOOK = $HA_WEBHOOK_BASE . ($config['ha_token'] ?? '');
$METEOCLIMATIC_API = "http://api.m11c.net/v2/ew/{station_code}/{api_key}"; // Template
$STATION_CODE = $config['meteoclimatic_code'] ?? '';
$API_KEY = $config['meteoclimatic_token'] ?? '';

// Flags de envío
$send_LOCAL = (int)($config['send_local'] ?? 0);
$send_HA = (int)($config['send_ha'] ?? 0);
$send_METEOCLIMATIC = (int)($config['send_meteoclimatic'] ?? 0);

$send_LOG = (int)($config['log_weather'] ?? 0);

// Zona horaria
$TIMEZONE = $config['tz'] ?? "UTC";
date_default_timezone_set($TIMEZONE);

// Seguridad LAN: lista blanca de IPs/CIDR permitidos.
require_once __DIR__ . "/static/modules/security/ip_allowlist.php";

$REQUEST_IP = $_SERVER['REMOTE_ADDR'] ?? '';
$SERVER_IP = $_SERVER['SERVER_ADDR'] ?? '';
$ALLOWED_CIDRS = trim((string)(($config['local_allowlist_cidrs'] ?? '') ?: getenv('CLEARSKY_ALLOWED_CIDRS')));

if (!clearsky_is_request_ip_allowed($REQUEST_IP, $ALLOWED_CIDRS, $SERVER_IP)) {
    write_debug($DEBUG_FILE, 'Acceso denegado: IP no autorizada (' . ($REQUEST_IP ?: 'NULO') . ').');
    http_response_code(403);
    echo "Forbidden";
    exit;
}

// -------------------------------------------
// TOKEN
// -------------------------------------------
// Fail-closed: si local_token no está configurado, bloqueamos la ingesta.
if ($TOKEN_SEGURO === '') {
    write_debug($DEBUG_FILE, 'Acceso denegado: local_token no configurado en DB.');
    http_response_code(403);
    echo "Forbidden";
    exit;
}

if (!isset($_GET["token"]) || $_GET["token"] !== $TOKEN_SEGURO) {
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
// Una vez compruebes que todo funciona bien, puedes desactivar el log en el setup de la web para que
// el archivo weather_data.log no se haga gigantesco
if ($send_LOG === 1) {
    file_put_contents($LOG_FILE, json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) . "\n", FILE_APPEND);
    write_debug($DEBUG_FILE, "SE ESTÁN ESCRIBIENDO LOS DATOS CRUDOS EN weather_data.log");
}

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
if ($send_HA === 1) {
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
            //curl_close($ch);
            // Para ver los datos enviados a HA, descomentar la siguiente línea
            // write_debug($DEBUG_FILE, "Enviado a HA. Estado: $http_status");
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
            //curl_close($ch);
            // Para ver el resultado de los datos enviados a meteoclimatic, descomentar la siguiente línea
            //write_debug($DEBUG_FILE, "Enviado a Meteoclimatic. Estado: $http_status");
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
    function f_to_c($f)
    {
        return ($f - 32) * 5 / 9;
    }

    // Mph → Km/h
    function mph_to_kmh($mph)
    {
        return $mph * 1.60934;
    }

    // in (lluvia) → mm
    function in_to_mm($in)
    {
        return $in * 25.4;
    }

    // Pulgadas mercurio → hPa
    function inHg_to_hPa($inhg)
    {
        return $inhg * 33.8639;
    }

    // Sensación térmica (viento en km/h)
    function wind_chill($tempC, $wind_kmh)
    {
        if ($tempC > 10 || $wind_kmh < 4.8) {
            return $tempC;
        }
        return 13.12 + 0.6215 * $tempC - 11.37 * pow($wind_kmh, 0.16) + 0.3965 * $tempC * pow($wind_kmh, 0.16);
    }

    // Punto de rocío (fórmula de Magnus, robusta)
    function dew_point($tempC, $humidity)
    {
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

    // Heat Index (NOAA / Rothfusz regression) - devuelve °C
    function heat_index($tempC, $humidity)
    {
        // Formula válida típicamente para temperaturas altas (≈27°C+) y humedad moderada/alta
        $Tf = $tempC * 9.0 / 5.0 + 32.0; // pasar a Fahrenheit
        $R = floatval($humidity);

        // Rothfusz regression
        $HI = -42.379 + 2.04901523 * $Tf + 10.14333127 * $R - 0.22475541 * $Tf * $R
            - 0.00683783 * ($Tf * $Tf) - 0.05481717 * ($R * $R)
            + 0.00122874 * ($Tf * $Tf) * $R + 0.00085282 * $Tf * ($R * $R)
            - 0.00000199 * ($Tf * $Tf) * ($R * $R);

        // Ajustes sencillos según NOAA (cuando aplican)
        if ($R < 13 && $Tf >= 80 && $Tf <= 112) {
            $HI -= ((13 - $R) / 4) * sqrt((17 - abs($Tf - 95.0)) / 17);
        } elseif ($R > 85 && $Tf >= 80 && $Tf <= 87) {
            $HI += (($R - 85) / 10) * ((87 - $Tf) / 5);
        }

        // Volver a Celsius
        $hic = ($HI - 32.0) * 5.0 / 9.0;
        return round($hic, 2);
    }

    // Apparent Temperature (fórmula australiana simple)
    // AT = T + 0.33*e - 0.70*ws - 4.00  where e = vapour pressure (hPa), ws in m/s
    function apparent_temperature($tempC, $humidity, $wind_kmh, $vpd = null)
    {
        $t = floatval($tempC);
        $rh = floatval($humidity);
        $ws = floatval($wind_kmh) / 3.6; // km/h -> m/s

        // vapour pressure (Magnus approximation)
        $e = ($rh / 100.0) * 6.105 * exp((17.27 * $t) / ($t + 237.7));

        // Si se proporciona VPD, usarlo en la fórmula variante
        if ($vpd !== null) {
            $AT = $t - 0.33 * floatval($vpd) - 0.70 * $ws - 4.00;
        } else {
            $AT = $t + 0.33 * $e - 0.70 * $ws - 4.00;
        }
        return round($AT, 2);
    }

    // Sensación térmica mejorada (regresión polinómica + humedad + viento)
    // Más precisa para rango -50 a 50°C
    function feeling_temperature($tempC, $humidity, $wind_kmh, $vpd = null)
    {
        return apparent_temperature($tempC, $humidity, $wind_kmh, $vpd);
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

    // Calcular varias sensaciones y elegir la más adecuada
    $wind_chill_val = wind_chill($temperatura, $viento_velocidad);
    $heat_index_val = heat_index($temperatura, $humedad);
    $vpd_val = isset($data["vpd"]) ? floatval($data["vpd"]) : null;
    $apparent_val = apparent_temperature($temperatura, $humedad, $viento_velocidad, $vpd_val);
    $feeling_val = feeling_temperature($temperatura, $humedad, $viento_velocidad, $vpd_val);

    // Lógica de selección simplificada:
    // Usar siempre la fórmula de Steadman (apparent_temperature)
    if ($vpd_val !== null) {
        $tipo_sensacion = 'steadman_vpd';
    } else {
        $tipo_sensacion = 'steadman';
    }
    $sensacion = round($feeling_val, 2);

    // Añadir al log los valores calculados si está activado el logging
    if ($send_LOG === 1) {
        $calc = [
            'sensacion_termica' => $sensacion,
            'formula_sensacion_termica' => $tipo_sensacion,
            'wind_chill' => $wind_chill_val,
            'heat_index' => $heat_index_val,
            'feeling_temperature' => $feeling_val,
            'apparent_temperature' => $apparent_val,
        ];
        //file_put_contents($LOG_FILE, json_encode($calc, JSON_PARTIAL_OUTPUT_ON_ERROR | JSON_UNESCAPED_UNICODE) . "\n", FILE_APPEND);
        file_put_contents($LOG_FILE, json_encode($calc, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) . "\n", FILE_APPEND);
    }
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
    $interval = $data["interval"];

    // -------------------------------------------
    // INSERT EN BASE DE DATOS
    // -------------------------------------------
    // Las credenciales de la DB ya se han requerido al inicio.
    $mysqli = new mysqli($db_url, $db_user, $db_pass, $db_database);
    if ($mysqli->connect_errno) {
        write_debug($DEBUG_FILE, "Error conexión DB: " . $mysqli->connect_error);
    } else {
        //write_debug($DEBUG_FILE, "Conexión OK a la DB");
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
        freq, model, passkey, interval_sec,
        formula_sensacion_termica
    ) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)
");

        if (!$stmt) {
            write_debug($DEBUG_FILE, "Error en prepare(): " . $mysqli->error);
            exit;
        }

        $stmt->bind_param(
            "ssdddddddddddddddddddddddsiiisssis",
            $timestamp_utc,
            $timezone,
            $temperatura,
            $humedad,
            $sensacion,
            $presion_rel,
            $presion_abs,
            $punto_rocio,
            $viento_velocidad,
            $viento_direccion,
            $viento_racha,
            $lluvia_diaria,
            $uv,
            $sol,
            $temperatura_interior,
            $humedad_interior,
            $lluvia_rate,
            $lluvia_evento,
            $lluvia_hora,
            $lluvia_semana,
            $lluvia_mes,
            $lluvia_ano,
            $lluvia_total,
            $viento_racha_maxima,
            $vpd,
            $stationtype,
            $runtime,
            $heap,
            $wh65batt,
            $freq,
            $model,
            $passkey,
            $interval,
            $tipo_sensacion
        );

        if ($stmt->errno) {
            write_debug($DEBUG_FILE, "Error en bind_param(): " . $stmt->error);
            exit;
        }

        if ($stmt->execute()) {
            //write_debug($DEBUG_FILE, "DB insert OK");
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
