<?php
// get_uv_data.php

// Incluir el archivo de configuración que debe contener las variables de conexión a MariaDB:
// $db_user, $db_pass, $db_url, $db_database
include '../../config/config.php';

/**
 * Función para devolver un error en formato JSON y terminar el script.
 * @param string $message Mensaje de error a devolver.
 */
function die_with_error($message) {
    header('Content-Type: application/json');
    die(json_encode([
        "error" => true,
        "message" => $message
    ]));
}

/**
 * Función para categorizar el índice UV según la escala estándar.
 * @param float|null $uv Índice UV.
 * @return string Categoría de exposición.
 */
function uvIndexToCategory($uv) {
    if ($uv === null || !is_numeric($uv)) {
        return "Valor inválido";
    }
    $uv = floatval($uv);

    if ($uv >= 0 && $uv <= 2) {
        return "Muy bajo";
    } elseif ($uv > 2 && $uv <= 5) {
        return "Moderado";
    } elseif ($uv > 5 && $uv <= 7) {
        return "Alto";
    } elseif ($uv > 7 && $uv <= 10) {
        return "Muy alto";
    } elseif ($uv >= 11) {
        return "Extremo";
    }
    return "Valor inválido";
}

// ----------------------------------------------------
// 1. Conexión a la base de datos y obtención de datos
// ----------------------------------------------------

// Verificar si las variables de conexión están definidas después de la inclusión
if (!isset($db_url, $db_user, $db_pass, $db_database)) {
    die_with_error("Error: Las credenciales de la base de datos no están definidas en config.php.");
}

$mysqli = new mysqli($db_url, $db_user, $db_pass, $db_database);

if ($mysqli->connect_error) {
    // Error de conexión
    error_log("Error de conexión a la BD: " . $mysqli->connect_error);
    die_with_error("Error al conectar con la base de datos.");
}

// Consulta SQL para obtener el último valor de 'indice_uv'
$sql = "SELECT indice_uv
        FROM meteo
        ORDER BY timestamp DESC
        LIMIT 1";

$result = $mysqli->query($sql);

if ($result === false) {
    // Error en la consulta
    error_log("Error en la consulta SQL: " . $mysqli->error);
    $mysqli->close();
    die_with_error("Error al ejecutar la consulta de datos UV.");
}

if ($result->num_rows === 0) {
    // No hay datos
    $mysqli->close();
    die_with_error("No se encontraron datos UV en la tabla 'meteo'.");
}

// 2. Obtener el valor y sanitizar
$row = $result->fetch_assoc();

// Valor del Índice UV
$uv = isset($row['indice_uv']) && is_numeric($row['indice_uv']) ? floatval($row['indice_uv']) : null;

$result->free();
$mysqli->close();


// ----------------------------------------------------
// 3. Categorización
// ----------------------------------------------------
$category = uvIndexToCategory($uv);

// ----------------------------------------------------
// 4. Preparar respuesta
// ----------------------------------------------------
header('Content-Type: application/json');
echo json_encode([
    "uv"       => $uv,
    "category" => $category
]);
?>
