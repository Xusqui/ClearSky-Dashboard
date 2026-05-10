<?php

/**
 * test_iphone_widget_api.php
 * 
 * Archivo de prueba para verificar que la API funciona correctamente
 * Accede a: https://tudominio.com/static/config/test_iphone_widget_api.php
 */

header('Content-Type: text/html; charset=utf-8');

// Ruta al archivo de configuración
$config_path = __DIR__ . '/config_db.php';

echo '<html lang="es"><head>';
echo '<meta charset="UTF-8">';
echo '<meta name="viewport" content="width=device-width, initial-scale=1.0">';
echo '<title>Test iPhone Widget API</title>';
echo '<style>';
echo 'body { font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif; margin: 20px; background: #f5f5f5; }';
echo '.container { max-width: 800px; margin: 0 auto; background: white; padding: 20px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }';
echo 'h1 { color: #007AFF; border-bottom: 2px solid #007AFF; padding-bottom: 10px; }';
echo '.status { padding: 12px; margin: 10px 0; border-radius: 6px; border-left: 4px solid; }';
echo '.status.success { background: #E8F5E9; color: #2E7D32; border-color: #4CAF50; }';
echo '.status.error { background: #FFEBEE; color: #C62828; border-color: #F44336; }';
echo '.status.warning { background: #FFF3E0; color: #E65100; border-color: #FF9800; }';
echo '.data-box { background: #f9f9f9; padding: 15px; margin: 10px 0; border-radius: 6px; border: 1px solid #ddd; }';
echo '.data-row { display: flex; justify-content: space-between; padding: 8px 0; border-bottom: 1px solid #eee; }';
echo '.data-row:last-child { border-bottom: none; }';
echo '.label { font-weight: 500; color: #666; }';
echo '.value { font-weight: bold; color: #007AFF; }';
echo '.null { color: #999; font-style: italic; }';
echo 'code { background: #f4f4f4; padding: 2px 6px; border-radius: 3px; font-family: monospace; }';
echo 'pre { background: #f4f4f4; padding: 10px; border-radius: 4px; overflow-x: auto; border: 1px solid #ddd; }';
echo 'button { background: #007AFF; color: white; border: none; padding: 10px 20px; border-radius: 6px; cursor: pointer; font-size: 14px; }';
echo 'button:hover { background: #0051D5; }';
echo '.section { margin-top: 20px; }';
echo '</style>';
echo '</head><body>';
echo '<div class="container">';
echo '<h1>🔬 Test iPhone Weather Widget API</h1>';

// Test 1: Verificar archivo de configuración
echo '<div class="section">';
echo '<h2>1. Verificación de Configuración</h2>';

if (!file_exists($config_path)) {
    echo '<div class="status error">';
    echo '❌ Archivo de configuración no encontrado: ' . $config_path;
    echo '</div>';
} else {
    echo '<div class="status success">';
    echo '✅ Archivo de configuración encontrado';
    echo '</div>';

    // Test 2: Verificar conexión a BD
    echo '<div class="section">';
    echo '<h2>2. Verificación de Conexión a BD</h2>';

    include $config_path;

    $mysqli = new mysqli($db_url, $db_user, $db_pass, $db_database);

    if ($mysqli->connect_errno) {
        echo '<div class="status error">';
        echo '❌ Error de conexión: ' . $mysqli->connect_error;
        echo '</div>';
    } else {
        echo '<div class="status success">';
        echo '✅ Conexión a BD exitosa';
        echo '</div>';

        // Test 3: Verificar tabla meteo
        echo '<div class="section">';
        echo '<h2>3. Verificación de Tabla "meteo"</h2>';

        $result = $mysqli->query("SHOW TABLES LIKE 'meteo'");

        if ($result->num_rows == 0) {
            echo '<div class="status error">';
            echo '❌ Tabla "meteo" no encontrada';
            echo '</div>';
        } else {
            echo '<div class="status success">';
            echo '✅ Tabla "meteo" encontrada';
            echo '</div>';

            // Test 4: Obtener últimos datos
            echo '<div class="section">';
            echo '<h2>4. Últimos Datos Meteorológicos</h2>';

            $query = "SELECT * FROM meteo ORDER BY timestamp DESC LIMIT 1";
            $result = $mysqli->query($query);

            if ($result->num_rows == 0) {
                echo '<div class="status warning">';
                echo '⚠️ La tabla está vacía - no hay datos para mostrar';
                echo '</div>';
            } else {
                $row = $result->fetch_assoc();

                echo '<div class="status success">';
                echo '✅ Datos encontrados';
                echo '</div>';

                echo '<div class="data-box">';
                echo '<h3>Datos Básicos:</h3>';

                $basic_fields = ['id', 'timestamp', 'timezone'];
                foreach ($basic_fields as $field) {
                    echo '<div class="data-row">';
                    echo '<span class="label">' . $field . ':</span>';
                    echo '<span class="value">' . ($row[$field] ?? 'N/A') . '</span>';
                    echo '</div>';
                }

                echo '<h3>Datos Meteorológicos:</h3>';

                $meteo_fields = [
                    'temperatura' => '°C',
                    'humedad' => '%',
                    'sensacion_termica' => '°C',
                    'presion_relativa' => 'mb',
                    'punto_rocio' => '°C',
                    'viento_velocidad' => 'km/h',
                    'viento_racha' => 'km/h',
                    'viento_racha_maxima' => 'km/h',
                    'lluvia_diaria' => 'mm',
                    'lluvia_semana' => 'mm',
                    'lluvia_mes' => 'mm',
                    'lluvia_ano' => 'mm',
                    'indice_uv' => '',
                    'radiacion_solar' => 'W/m²',
                    'temperatura_interior' => '°C',
                    'humedad_interior' => '%'
                ];

                foreach ($meteo_fields as $field => $unit) {
                    $value = $row[$field];
                    echo '<div class="data-row">';
                    echo '<span class="label">' . $field . ':</span>';

                    if ($value === null || $value === 'NULL') {
                        echo '<span class="null">-- ' . $unit . '</span>';
                    } else {
                        echo '<span class="value">' . number_format($value, 2, ',', '.') . ' ' . $unit . '</span>';
                    }

                    echo '</div>';
                }

                echo '</div>';

                // Test 5: JSON output
                echo '<div class="section">';
                echo '<h2>5. Salida JSON (para el widget)</h2>';
                echo '<pre>' . json_encode($row, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . '</pre>';
                echo '</div>';
            }

            // Test 6: Estadísticas
            echo '<div class="section">';
            echo '<h2>6. Estadísticas de la Tabla</h2>';

            $stats = $mysqli->query("SELECT COUNT(*) as total, MIN(timestamp) as first, MAX(timestamp) as last FROM meteo");
            $stat_row = $stats->fetch_assoc();

            echo '<div class="data-box">';
            echo '<div class="data-row">';
            echo '<span class="label">Total de registros:</span>';
            echo '<span class="value">' . $stat_row['total'] . '</span>';
            echo '</div>';
            echo '<div class="data-row">';
            echo '<span class="label">Primer registro:</span>';
            echo '<span class="value">' . ($stat_row['first'] ?? 'N/A') . '</span>';
            echo '</div>';
            echo '<div class="data-row">';
            echo '<span class="label">Último registro:</span>';
            echo '<span class="value">' . ($stat_row['last'] ?? 'N/A') . '</span>';
            echo '</div>';
            echo '</div>';
            echo '</div>';
        }

        $mysqli->close();
    }
}

echo '</div>';

// Test 7: Información de la API
echo '<div class="section">';
echo '<h2>7. Información del Endpoint API</h2>';
echo '<div class="data-box">';
echo '<strong>URL del endpoint:</strong><br>';
echo '<code>https://xusqui.com/weather/static/config/iphone_widget_api.php</code>';
echo '<br><br>';
echo '<strong>Método:</strong> GET<br>';
echo '<strong>Content-Type:</strong> application/json<br>';
echo '<strong>Timeout:</strong> 10 segundos<br>';
echo '<br>';
echo '<button onclick="window.location.href=\'iphone_widget_api.php\'">Ver JSON en vivo</button>';
echo '</div>';
echo '</div>';

echo '</div>';
echo '</body></html>';
