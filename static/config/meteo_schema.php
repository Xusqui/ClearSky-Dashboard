<?php
// meteo_schema.php

// Definición de la estructura DESEADA de la tabla 'meteo', basada en la exportación SQL funcional.
$meteo_schema = [
    // CLAVE PRIMARIA AUTO INCREMENTAL
    // Nota: MariaDB 10.x incluye (11) por defecto, lo respetamos aquí.
    'id' => 'INT(11) PRIMARY KEY AUTO_INCREMENT',

    // DATOS DE TIEMPO
    'timestamp' => 'DATETIME NOT NULL',
    'timezone' => "VARCHAR(50) NOT NULL DEFAULT 'UTC'",

    // DATOS METEOROLÓGICOS (Valores DECIMALes)
    'temperatura' => 'DECIMAL(5,2) DEFAULT NULL',
    'humedad' => 'DECIMAL(5,2) DEFAULT NULL',
    'sensacion_termica' => 'DECIMAL(5,2) DEFAULT NULL',
    'presion_relativa' => 'DECIMAL(6,2) DEFAULT NULL',
    'presion_absoluta' => 'DECIMAL(6,2) DEFAULT NULL',
    'punto_rocio' => 'DECIMAL(5,2) DEFAULT NULL',
    'viento_velocidad' => 'DECIMAL(5,2) DEFAULT NULL',
    'viento_direccion' => 'SMALLINT(6) DEFAULT NULL', // ⬅️ VUELVE A SER smallint(6)
    'viento_racha' => 'DECIMAL(5,2) DEFAULT NULL',
    'lluvia_diaria' => 'DECIMAL(6,2) DEFAULT NULL',
    'indice_uv' => 'DECIMAL(4,2) DEFAULT NULL',
    'radiacion_solar' => 'DECIMAL(6,2) DEFAULT NULL',
    'temperatura_interior' => 'DECIMAL(5,2) DEFAULT NULL',
    'humedad_interior' => 'DECIMAL(5,2) DEFAULT NULL',
    'lluvia_rate' => 'DECIMAL(6,2) DEFAULT NULL',
    'lluvia_evento' => 'DECIMAL(6,2) DEFAULT NULL',
    'lluvia_hora' => 'DECIMAL(6,2) DEFAULT NULL',
    'lluvia_semana' => 'DECIMAL(7,2) DEFAULT NULL',
    'lluvia_mes' => 'DECIMAL(7,2) DEFAULT NULL',
    'lluvia_ano' => 'DECIMAL(7,2) DEFAULT NULL',
    'lluvia_total' => 'DECIMAL(8,2) DEFAULT NULL',
    'viento_racha_maxima' => 'DECIMAL(5,2) DEFAULT NULL',
    'vpd' => 'DECIMAL(5,3) DEFAULT NULL',

    // METADATOS DE LA ESTACIÓN
    'stationtype' => 'VARCHAR(50) DEFAULT NULL',
    'runtime' => 'INT(11) DEFAULT NULL', // ⬅️ VUELVE A SER int(11)
    'heap' => 'INT(11) DEFAULT NULL', // ⬅️ VUELVE A SER int(11)
    'wh65batt' => 'TINYINT(4) DEFAULT NULL', // ⬅️ VUELVE A SER tinyint(4)
    'freq' => 'VARCHAR(10) DEFAULT NULL',
    'model' => 'VARCHAR(50) DEFAULT NULL',
    'passkey' => 'CHAR(32) DEFAULT NULL',
    'interval_sec' => 'INT(11) DEFAULT NULL'
];

// Opcional: Definición de índices (requiere lógica adicional en setup.php)
$meteo_indexes = [
    'PRIMARY KEY' => ['id'],
    'KEY_TIMESTAMP' => ['timestamp']
];
?>
