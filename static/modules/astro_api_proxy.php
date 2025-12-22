<?php
// ./static/modules/astro_api_proxy.php

// 1. Obtener la ruta base
$path = $_GET['send'] ?? '';

if (empty($path)) {
    header('Content-Type: application/json');
    echo json_encode(["error" => "No path specified"]);
    exit;
}

// 2. Limpiar parámetros para la petición interna
$params = $_GET;
unset($params['send']);

// 3. Construir la URL local (al puerto 8888)
$local_url = "http://192.168.1.100:8888/" . ltrim($path, '/');
if (!empty($params)) {
    $local_url .= "?" . http_build_query($params);
}

// 4. Configurar cURL
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $local_url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 15);

$response = curl_exec($ch);
$info = curl_getinfo($ch);
$http_code = $info['http_code'];
$content_type = $info['content_type']; // Detecta si es image/jpeg, application/json, etc.

if (curl_errno($ch)) {
    header('Content-Type: application/json');
    http_response_code(502);
    echo json_encode(["error" => "Local API unreachable", "details" => curl_error($ch)]);
} else {
    // 5. REENVÍO DINÁMICO DE CABECERAS
    // Esto es lo que permite que las imágenes funcionen
    if ($content_type) {
        header("Content-Type: $content_type");
    }
    http_response_code($http_code);
    echo $response;
}

curl_close($ch);
