<?php
error_reporting(E_ALL);
ini_set("display_errors", 1);

// Conexión a MariaDB
include __DIR__ . '/static/config/config.php';
//$host = "127.0.0.1";
//$db = "weather";
//$user = "weather";
//$pass = "Pe5ut9tb#M3kps7yt";

$conn = new mysqli($db_url, $db_user, $db_pass, $db_database);
if ($conn->connect_error) {
    die("Error de conexión: " . $conn->connect_error);
}

// Leer datos de HA
$data = json_decode(file_get_contents("php://input"), true);

// Guardar log
// file_put_contents("/volume1/web/weather/log.json", json_encode($data) . "\n", FILE_APPEND);

if ($data && isset($data["timestamp"])) {
    try {
        $dt = new DateTime($data["timestamp"]);
        $mysql_timestamp = $dt->format("Y-m-d H:i:s");

        // Convertir a float o NULL si no son numéricos
        $fields = [
            "temperatura",
            "sensacion_termica",
            "humedad",
            "presion_relativa",
            "presion_absoluta",
            "punto_rocio",
            "viento_velocidad",
            "viento_direccion",
            "viento_racha",
            "lluvia_diaria",
            "indice_uv",
            "radiacion_solar",
            "temperatura_interior",
            "humedad_interior",
        ];

        $values = [];
        foreach ($fields as $f) {
            $values[$f] = isset($data[$f]) && is_numeric($data[$f]) ? $data[$f] : null;
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
            echo "OK";
        } else {
            echo "Error: " . $stmt->error;
        }

        $stmt->close();
    } catch (Exception $e) {
        echo "Error en fecha: " . $e->getMessage();
    }
}

$conn->close();
?>
