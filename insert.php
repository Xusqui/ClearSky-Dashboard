<?php
// insert.php
// Habilitar reporte de errores para logs internos
error_reporting(E_ALL);
ini_set("display_errors", 0); // No mostrar errores al usuario
ini_set("log_errors", 1);
ini_set("error_log", __DIR__ . '/error.log'); // Guardar errores de PHP en un log

// Establecer el tipo de contenido de la respuesta a JSON
header('Content-Type: application/json');

/**
 * Función para enviar una respuesta de error en JSON y terminar el script.
 * @param string $public_message Mensaje genérico y seguro para el cliente
 * @param string $log_message Mensaje detallado para el log (opcional)
 * @param int $code Código de estado HTTP
 */
function send_json_error($public_message, $code, $log_message = null) {
    // Escribir el error detallado en el log
    if ($log_message === null) {
        $log_message = $public_message; // Usar el mensaje público si no hay uno detallado
    }

    $log_file = __DIR__ . '/debug.log'; // Asegúrate de que este archivo esté protegido
    $error_log_message = "--- ERROR: " . date("Y-m-d H:i:s") . " ---\n";
    $error_log_message .= "Code: $code\n";
    $error_log_message .= "Message: $log_message\n";
    $error_log_message .= "--------------------------------------\n\n";
    file_put_contents($log_file, $error_log_message, FILE_APPEND);

    // Enviar respuesta genérica y segura al cliente
    http_response_code($code);
    echo json_encode(['error' => $public_message]);
    exit;
}

// Conexión a MariaDB
include __DIR__ . '/static/config/config.php';

$conn = new mysqli($db_url, $db_user, $db_pass, $db_database);
if ($conn->connect_error) {
    // Detalle para el log, genérico para el usuario
    send_json_error("Error interno del servidor", 500, "Error de conexión a la base de datos: " . $conn->connect_error);
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
    send_json_error("Error interno del servidor", 500, "Error al consultar el token: " . $e->getMessage());
}

if (empty($expected_token)) {
    $conn->close();
    send_json_error("Error de configuración del servidor", 500, "No se encontró 'ha_token' en la tabla 'config'.");
}

// 2. Obtener el token enviado por Home Assistant desde los encabezados
$auth_header = $_SERVER['HTTP_AUTHORIZATION'] ?? null;
if (!$auth_header) {
    $conn->close();
    send_json_error("No autorizado", 401, "No se proporcionó token de autorización.");
}

// 3. Extraer el token del formato "Bearer <token>"
$received_token = null;
if (sscanf($auth_header, "Bearer %s", $received_token) !== 1) {
    $conn->close();
    send_json_error("No autorizado", 401, "Formato de token inválido.");
}

// 4. Comparar los tokens de forma segura
// ¡¡ESTA ES LA CORRECCIÓN DE SEGURIDAD!!
if (!hash_equals($expected_token, $received_token)) {
    $conn->close();
    // NO filtres los tokens en el mensaje.
    send_json_error("Token inválido", 403, "Discrepancia de token.");
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
            $values["temperatura"], $values["sensacion_termica"], $values["humedad"],
            $values["presion_relativa"], $values["presion_absoluta"], $values["punto_rocio"],
            $values["viento_velocidad"], $values["viento_direccion"], $values["viento_racha"],
            $values["lluvia_diaria"], $values["indice_uv"], $values["radiacion_solar"],
            $values["temperatura_interior"], $values["humedad_interior"]
        );

        if ($stmt->execute()) {
            echo json_encode(['status' => 'success', 'message' => 'Datos insertados correctamente.']);
        } else {
            // Envía un error genérico al cliente, pero el detalle al log
            send_json_error("Error al procesar la solicitud", 500, "Error al ejecutar la consulta: " . $stmt->error);
        }
        // --- FIN DE LA CORRECCIÓN ---

        $stmt->close();
    } catch (Exception $e) {
        send_json_error("Error en el procesamiento de datos", 400, $e->getMessage());
    }
} else {
    send_json_error("Datos de entrada inválidos", 400, "Datos de entrada inválidos o 'timestamp' faltante.");
}

$conn->close();
?>
