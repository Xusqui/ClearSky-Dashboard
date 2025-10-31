<?php
// get_uv_data.php

header('Content-Type: application/json');

// Cargar configuración
include '../../config/config.php';

$entity   = "sensor.ws2900_v2_02_03_uv_index";

// Llamada a la API de Home Assistant

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, "$ha_url/api/states/$entity");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    "Authorization: Bearer $token",
    "Content-Type: application/json"
]);
$response = curl_exec($ch);


if ($response === false) {
    die(json_encode([
        "error" => true,
        "message" => "Error cURL: " . curl_error($ch)
    ]));
}
curl_close($ch);

$data = json_decode($response, true);
$uv = isset($data['state']) ? floatval($data['state']) : null;

// Función para categorizar UV
function uvIndexToCategory($uv) {
    if ($uv >= 0 && $uv <= 2) {
        return "Muy bajo";
    } elseif ($uv >= 3 && $uv <= 5) {
        return "Moderado";
    } elseif ($uv >= 6 && $uv <= 7) {
        return "Alto";
    } elseif ($uv >= 8 && $uv <= 10) {
        return "Muy alto";
    } elseif ($uv >= 11) {
        return "Extremo";
    }
    return "Valor inválido";
}

$category = uvIndexToCategory($uv);

// Preparar respuesta
echo json_encode([
    "uv"        => $uv,
    "category"  => $category
]);
