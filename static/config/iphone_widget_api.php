<?php

/**
 * iphone_widget_api.php
 * 
 * API endpoint para el widget de Scriptable del iPhone
 * Obtiene los datos meteorológicos más relevantes de la base de datos
 * y los retorna en JSON formateado para el widget
 */

// Header de respuesta JSON
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Cache-Control: no-cache, must-revalidate');

// Ruta al archivo de configuración
$config_path = __DIR__ . '/config_db.php';

if (!file_exists($config_path)) {
    http_response_code(500);
    echo json_encode([
        "error" => "Archivo de configuración no encontrado",
        "timestamp" => date('Y-m-d H:i:s')
    ]);
    exit;
}

// Incluir variables de configuración de la base de datos
include $config_path;

// Crear conexión
$mysqli = new mysqli($db_url, $db_user, $db_pass, $db_database);

// Verificar conexión
if ($mysqli->connect_errno) {
    http_response_code(500);
    echo json_encode([
        "error" => "Fallo al conectar a la base de datos: " . $mysqli->connect_error,
        "timestamp" => date('Y-m-d H:i:s')
    ]);
    exit;
}

// Establecer charset
$mysqli->set_charset("utf8mb4");

try {
    // Consultar el último registro de la tabla 'meteo'
    $query = "
        SELECT 
            id,
            timestamp,
            temperatura,
            humedad,
            sensacion_termica,
            presion_relativa,
            presion_absoluta,
            punto_rocio,
            viento_velocidad,
            viento_direccion,
            viento_racha,
            viento_racha_maxima,
            lluvia_diaria,
            lluvia_hora,
            lluvia_semana,
            lluvia_mes,
            lluvia_ano,
            lluvia_total,
            lluvia_rate,
            lluvia_evento,
            indice_uv,
            radiacion_solar,
            temperatura_interior,
            humedad_interior,
            vpd,
            stationtype,
            model,
            timezone
        FROM meteo
        ORDER BY timestamp DESC
        LIMIT 1
    ";

    $result = $mysqli->query($query);

    if (!$result) {
        http_response_code(500);
        echo json_encode([
            "error" => "Error en la consulta: " . $mysqli->error,
            "timestamp" => date('Y-m-d H:i:s')
        ]);
        $mysqli->close();
        exit;
    }

    // Obtener el resultado
    $row = $result->fetch_assoc();

    if (!$row) {
        http_response_code(404);
        echo json_encode([
            "error" => "No hay datos meteorológicos disponibles",
            "timestamp" => date('Y-m-d H:i:s')
        ]);
        $mysqli->close();
        exit;
    }

    // Procesar valores nulos
    $row = array_map(function ($value) {
        return $value === null ? 'NULL' : $value;
    }, $row);

    // Agregar información adicional útil
    $row['_metadata'] = [
        'api_version' => '1.0',
        'widget_type' => 'iPhone',
        'generated_at' => date('Y-m-d H:i:s'),
        'timezone' => $row['timezone'] ?? 'UTC'
    ];

    // Agregar estado de lluvia
    $lluvia_rate = floatval($row['lluvia_rate']);
    $row['_lluvia_estado'] = [
        'es_lluvia' => $lluvia_rate > 0,
        'intensidad' => $lluvia_rate > 0 ? ($lluvia_rate < 1 ? 'ligera' : ($lluvia_rate < 5 ? 'moderada' : 'fuerte')) : 'sin_lluvia'
    ];

    // Liberar resultado
    $result->free();

    // Retornar datos en JSON
    echo json_encode($row, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        "error" => "Error procesando solicitud: " . $e->getMessage(),
        "timestamp" => date('Y-m-d H:i:s')
    ]);
} finally {
    $mysqli->close();
}
