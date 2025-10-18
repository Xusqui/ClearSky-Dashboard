<?php
/*
// --- INICIO DEBUG ---
// Define un archivo de log
$log_file = __DIR__ . '/debug.log';

// Obtiene todos los encabezados
$headers = getallheaders();

// Obtiene el cuerpo (payload)
$body = file_get_contents("php://input");

// Prepara el mensaje de log
$log_message = "--- INICIO PETICIÓN: " . date("Y-m-d H:i:s") . " ---\n";
$log_message .= "HEADERS:\n" . print_r($headers, true) . "\n";
$log_message .= "BODY (RAW):\n" . $body . "\n";

// Escribe en el archivo de log (añadiendo al final)
file_put_contents($log_file, $log_message, FILE_APPEND);
// --- FIN DEBUG ---
*/

// Habilitar reporte de errores
error_reporting(E_ALL);
ini_set("display_errors", 1);

// Establecer el tipo de contenido de la respuesta a JSON
header('Content-Type: application/json');

/**
 * Función para enviar una respuesta de error en JSON y terminar el script.
 * @param string $message Mensaje de error
 * @param int $code Código de estado HTTP (ej. 401, 403, 500)
 */
function send_json_error($message, $code) {
    // --- MODIFICACIÓN: Escribir error en el log ---
    $log_file = __DIR__ . '/debug.log';
    $error_log_message = "--- ERROR: " . date("Y-m-d H:i:s") . " ---\n";
    $error_log_message .= "Code: $code\n";
    $error_log_message .= "Message: $message\n";
    $error_log_message .= "--------------------------------------\n\n";
    file_put_contents($log_file, $error_log_message, FILE_APPEND);
    // --- FIN MODIFICACIÓN ---

    http_response_code($code);
    echo json_encode(['error' => $message]);
    exit;
}

// Conexión a MariaDB
include __DIR__ . '/static/config/config.php';

$conn = new mysqli($db_url, $db_user, $db_pass, $db_database);
if ($conn->connect_error) {
    send_json_error("Error de conexión a la base de datos: " . $conn->connect_error, 500);
}

// --- INICIO BLOQUE DE VALIDACIÓN DE TOKEN ---

// 1. Obtener el token esperado de la base de datos
$expected_token = null;
try {
    $result = $conn->query("SELECT ha_token FROM config LIMIT 1");
    if ($result && $row = $result->fetch_assoc()) {
        $expected_token = $row['ha_token'];
    }
} catch (Exception $e) {
    $conn->close();
    send_json_error("Error interno al consultar el token: " . $e->getMessage(), 500);
}

if (empty($expected_token)) {
    $conn->close();
    send_json_error("Error de configuración: No se encontró 'ha_token' en la tabla 'config'.", 500);
}

// 2. Obtener el token enviado por Home Assistant desde los encabezados
$auth_header = $_SERVER['HTTP_AUTHORIZATION'] ?? null;
if (!$auth_header) {
    $conn->close();
    send_json_error("No se proporcionó token de autorización.", 401);
}

// 3. Extraer el token del formato "Bearer <token>"
$received_token = null;
if (sscanf($auth_header, "Bearer %s", $received_token) !== 1) {
    $conn->close();
    send_json_error("Formato de token inválido. Se esperaba 'Bearer <token>'.", 401);
}

// 4. Comparar los tokens de forma segura
if (!hash_equals($expected_token, $received_token)) {
    $conn->close();
    send_json_error("Token inválido. (Recibido: $received_token / Esperado: $expected_token)", 403);
}

// --- FIN BLOQUE DE VALIDACIÓN DE TOKEN ---

// Leer datos de HA
$data = json_decode(file_get_contents("php://input"), true);

if ($data && isset($data["timestamp"])) {
    try {
        $dt = new DateTime($data["timestamp"]);
        $mysql_timestamp = $dt->format("Y-m-d H:i:s");

        $fields = [
            "temperatura", "sensacion_termica", "humedad", "presion_relativa",
            "presion_absoluta", "punto_rocio", "viento_velocidad", "viento_direccion",
            "viento_racha", "lluvia_diaria", "indice_uv", "radiacion_solar",
            "temperatura_interior", "humedad_interior",
        ];

        $values = [];
        foreach ($fields as $f) {
            $values[$f] = isset($data[$f]) && is_numeric($data[$f]) ? (float)$data[$f] : null;
        }

        $stmt = $conn->prepare("INSERT INTO meteo (timestamp, temperatura, sensacion_termica, humedad,
            presion_relativa, presion_absoluta, punto_rocio, viento_velocidad, viento_direccion, viento_racha,
            lluvia_diaria, indice_uv, radiacion_solar, temperatura_interior, humedad_interior)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        
        $stmt->bind_param(
            "sdddddddddddddd",
            $mysql_timestamp,
            $values["temperatura"],
            $values["sensacion_termica"],
            $values["humedad"],
            $values["presion_relativa"],
            $values["presion_absoluta"],
            $values["punto_rocio"],
            $values["viento_velocidad"],
            $values["viento_direccion"],
            $values["viento_racha"],
            $values["lluvia_diaria"],
            $values["indice_uv"],
            $values["radiacion_solar"],
            $values["temperatura_interior"],
            $values["humedad_interior"]
        );

        if ($stmt->execute()) {
            // --- MODIFICACIÓN: Escribir éxito en el log ---
            file_put_contents($log_file, "--- SUCCESS: " . date("Y-m-d H:i:s") . " - Datos insertados.\n\n", FILE_APPEND);
            // --- FIN MODIFICACIÓN ---
            echo json_encode(['status' => 'success', 'message' => 'Datos insertados correctamente.']);
        } else {
            send_json_error("Error al ejecutar la consulta: " . $stmt->error, 500);
        }

        $stmt->close();
    } catch (Exception $e) {
        send_json_error("Error en el procesamiento de datos: " . $e->getMessage(), 400);
    }
} else {
    send_json_error("Datos de entrada inválidos o 'timestamp' faltante.", 400);
}

$conn->close();
?>