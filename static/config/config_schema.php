<?php
// config_schema.php

// Definición de la estructura DESEADA de la tabla 'config'.
// Usado por setup.php para crear/actualizar la tabla.
// La clave es el nombre de la columna, el valor es la definición SQL.
$config_schema = [
    'id' => 'INT PRIMARY KEY AUTO_INCREMENT',
    'latitud' => 'DECIMAL(10,6)',
    'longitud' => 'DECIMAL(10,6)',
    'elevacion' => 'INT',
    'hardware' => 'VARCHAR(255)',
    'software' => 'VARCHAR(255)',
    'observatorio' => 'VARCHAR(255)',
    'city' => 'VARCHAR(255)',
    'country' => 'VARCHAR(255)',
    'tz' => 'VARCHAR(255)',
    'password' => 'VARCHAR(255)',

    'send_local' => 'BOOLEAN NOT NULL DEFAULT TRUE',
    'local_token' => 'VARCHAR(255)',

    'send_ha' => 'BOOLEAN NOT NULL DEFAULT TRUE',
    'ha_token' => 'VARCHAR(255)',

    'send_meteoclimatic' => 'BOOLEAN NOT NULL DEFAULT FALSE',
    'meteoclimatic_code' => 'VARCHAR(50)',
    'meteoclimatic_token' => 'VARCHAR(255)'
];
?>
