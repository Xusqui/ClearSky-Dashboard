# Interfaz Web para Estación Meteorológica (con Home Assistant)

<p align="center">
  <img alt="PHP" src="https://img.shields.io/badge/PHP-777BB4?style=for-the-badge&logo=php&logoColor=white">
  <img alt="MySQL" src="https://img.shields.io/badge/MySQL-4479A1?style=for-the-badge&logo=mysql&logoColor=white">
  <img alt="Home Assistant" src="https://img.shields.io/badge/Home%20Assistant-41BDF5?style=for-the-badge&logo=home-assistant&logoColor=white">
</p>

<p align="center">
  <strong>Versión totalmente funcional en: <a href="https://xusqui.com/weather/">https://xusqui.com/weather/</a></strong>
</p>

<p align="center">
  <img width="800" alt="Captura de pantalla de la web" src="https://github.com/user-attachments/assets/50994aba-f6c7-4ff5-9baf-ca1f2c293047" />
</p>

## 📖 Índice

- [Sobre el Proyecto](#-sobre-el-proyecto)
  - [Componentes](#componentes)
- [Mi Configuración](#%EF%B8%8F-mi-configuración-probada-y-funcional)
- [Guía de Configuración](#-guía-de-configuración)
  - [Paso 1: Añadir Integración "Ecowitt"](#paso-1-añadir-integración-ecowitt-a-home-assistant)
  - [Paso 2: Configurar la Estación para enviar datos a Home Assistant](#paso-2-configurar-la-estacion-para-enviar-datos-a-home-assistant)
  - [Paso 3: Crear la Base de Datos](#paso-3-crear-la-base-de-datos)
  - [Paso 4: Añadir `rest_command`](#paso-4-añadir-rest_command-a-home-assistant)
  - [Paso 5: Crear el Token para que Home Assistant se autentique en la web](#paso-5-crear-el-token-para-que-home-assistant-se-autentique-en-la-web)
  - [Paso 6: Crear Automatización en Home Assistant](#paso-6-crear-automatización-en-home-assistant)
  - [Paso 7: Configurar la web](#paso-7-configurar-la-web)
- [Feedback](#-feedback)

---

## 💡 Sobre el Proyecto

Este es un software desarrollado a partir de la interfaz clásica de Weather Underground.

> [!NOTE] No tengo mucha idea de programación, por lo que todo el código se ha creado con la ayuda de ChatGPT y Gemini.

### Componentes

El proyecto tiene dos partes principales:

1.  **`insert.php`**: Un _endpoint_ que es utilizado por Home Assistant para escribir los datos que va recibiendo de la
    estación meteorológica en la base de datos (MySQL/MariaDB).
2.  **La Web (index.php)**: La propia página web que lee y muestra los datos de la base de datos.

Se asume que el software se instala en el directorio `/weather/` de tu servidor web.

---

## ⚙️ Mi Configuración (Probada y Funcional)

Esta es la configuración de hardware y software con la que el proyecto ha sido probado:

- **Estación Meteorológica Personal**: Ambient Weather WS-2090
- **Software de la Estación**: EasyWeatherPro V5.2.2
- **Software de Domótica**: Home Assistant
- **Integración de Home Assistant**: "Ecowitt"

---

## 🚀 Guía de Configuración

El flujo de datos es: **Estación ➡️ Home Assistant ➡️ Esta Web**.

Sigue estos pasos para replicar la configuración:

### Paso 1: Añadir Integración "Ecowitt" a Home Assistant

Añade la integración "Ecowitt" a tu instancia de Home Assistant de la manera habitual.

### Paso 2: Configurar la Estacion para enviar datos a Home Assistant

Sigue las instrucciones detalladas <a href="https://www.home-assistant.io/integrations/ecowitt/">aquí</a> para que tu estación Ecowitt / Ambient Weather envíe los datos a Home Assistant.
Yo utilizo un intervalo de 60 segundos.

### Paso 3: Crear la Base de Datos

Asumimos que ya tienes un servidor con una instancia de MySQL / MariaDB funcionando.
Accede a la base de datos, por ejemplo, con phpMyAdmin y crea la base de datos:

```sql
-- Servidor: localhost
-- Versión de PHP: 8.2.28

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";

--
-- Base de datos: `weather`
--
CREATE DATABASE IF NOT EXISTS `weather` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
USE `weather`;

--
-- Estructura de tabla para la tabla `config`
--

CREATE TABLE `config` (
  `id` int(11) NOT NULL,
  `latitud` decimal(10,6) DEFAULT NULL,
  `longitud` decimal(10,6) DEFAULT NULL,
  `elevacion` int(11) DEFAULT NULL,
  `hardware` varchar(255) DEFAULT NULL,
  `software` varchar(255) DEFAULT NULL,
  `observatorio` varchar(255) DEFAULT NULL,
  `city` varchar(255) DEFAULT NULL,
  `country` varchar(255) DEFAULT NULL,
  `tz` varchar(255) DEFAULT NULL,
  `password` varchar(255) DEFAULT NULL,
  `ha_token` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Estructura de tabla para la tabla `meteo`
--

CREATE TABLE `meteo` (
  `id` int(11) NOT NULL,
  `timestamp` datetime NOT NULL,
  `temperatura` decimal(5,2) DEFAULT NULL,
  `humedad` decimal(5,2) DEFAULT NULL,
  `sensacion_termica` decimal(5,2) DEFAULT NULL,
  `presion_relativa` decimal(6,2) DEFAULT NULL,
  `presion_absoluta` decimal(6,2) DEFAULT NULL,
  `punto_rocio` decimal(5,2) DEFAULT NULL,
  `viento_velocidad` decimal(5,2) DEFAULT NULL,
  `viento_direccion` smallint(6) DEFAULT NULL,
  `viento_racha` decimal(5,2) DEFAULT NULL,
  `lluvia_diaria` decimal(6,2) DEFAULT NULL,
  `indice_uv` decimal(4,2) DEFAULT NULL,
  `radiacion_solar` decimal(6,2) DEFAULT NULL,
  `temperatura_interior` decimal(5,2) DEFAULT NULL,
  `humedad_interior` decimal(5,2) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;


--
-- Indices de la tabla `config`
--
ALTER TABLE `config`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `meteo`
--
ALTER TABLE `meteo`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT de la tabla `config`
--
ALTER TABLE `config`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `meteo`
--
ALTER TABLE `meteo`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
COMMIT;
```

Ahora añade un nuevo usuario y contraseña en phpMyAdmin y otórgale todos los permisos de la base de datos recién creada "weather"

### Paso 4: Añadir `rest_command` a Home Assistant

Añade lo siguiente a tu archivo `configuration.yaml` en Home Assistant. Esto define el `rest_command` (la acción de
enviar datos) y el `input_text` (para guardar tu token de forma segura).

```yaml
rest_command:
  send_all_meteo_data:
    url: "http://localhost/weather/insert.php" # Sustituye por la IP de tu servidor web
    method: POST
    content_type: "application/json"
    headers:
      Authorization: "Bearer {{ states('input_text.meteo_token_holder') }}"
    payload: >
      {
        "timestamp": "{{ now().isoformat() }}",
        "temperatura": "{{ states('sensor.ws2900_v2_02_03_outdoor_temperature') }}",
        "sensacion_termica": "{{ states('sensor.ws2900_v2_02_03_feels_like_temperature') }}",
        "humedad": "{{ states('sensor.ws2900_v2_02_03_humidity') }}",
        "presion_relativa": "{{ states('sensor.ws2900_v2_02_03_relative_pressure') }}",
        "presion_absoluta": "{{ states('sensor.ws2900_v2_02_03_absolute_pressure') }}",
        "punto_rocio": "{{ states('sensor.ws2900_v2_02_03_dewpoint') }}",
        "viento_velocidad": "{{ states('sensor.ws2900_v2_02_03_wind_speed') }}",
        "viento_direccion": "{{ states('sensor.ws2900_v2_02_03_wind_direction') }}",
        "viento_racha": "{{ states('sensor.ws2900_v2_02_03_wind_gust') }}",
        "lluvia_diaria": "{{ states('sensor.ws2900_v2_02_03_daily_rain') }}",
        "indice_uv": "{{ states('sensor.ws2900_v2_02_03_uv_index') }}",
        "radiacion_solar": "{{ states('sensor.ws2900_v2_02_03_solar_radiation') }}",
        "temperatura_interior": "{{ states('sensor.ws2900_v2_02_03_indoor_temperature') }}",
        "humedad_interior": "{{ states('sensor.ws2900_v2_02_03_indoor_humidity') }}"
      }

input_text:
  meteo_token_holder:
    name: "Portador del Token Meteo"
    initial: !secret meteo_api_token
    mode: password # Oculta el token en la interfaz
```

> [!IMPORTANT]
> **¡Sustituye los nombres de los sensores!**
> Los nombres (`sensor.ws2900_v2_02_03...`) son específicos de mi estación. Debes buscarlos en tu propia instancia de Home Assistant y reemplazarlos en el `payload` anterior.
> Para encontrar los nombres de los sensores, en Home Assistant, abre la integración EcoWitt:
> Haz clic en el nombre que has dado a tu estación meteorológica
> Haz clic en uno de los sensores (p. ej: Absolute Pressure), y se abre el sensor actual y una gráfica temporal con los valores registrados.
> Haz clic en la rueda dentada dentro de este panel y en el panel nuevo que se abre, dentro de la casilla "Entity ID" Está el nombre del sensor.
> Cambia los nombres del Payload para que coincidan con los nombres de tus sensores.

### Paso 5: Crear el Token para que Home Assistant se autentique en la web.
La primera vez que abres la web, p. ej: http://localhost/weather, se carga el script de configuración.
En primer lugar se solicita una contraseña para que el script esté protegido.
En segundo lugar se abre la configuración:

<img width="570" height="1058" alt="Captura de pantalla 2025-10-18 a las 21 49 35" src="https://github.com/user-attachments/assets/e0cbd6dd-4f01-45c7-9997-bc2878e141ac" />

Dentro de ésta, en la casilla "Token", se guarda el token generado para que Home Assistant pueda autenticarse. 
Rellena todos los datos y haz clic en el botón "Copiar" para copiar el Token.

Abre el archivo secrets.yaml de Home Assistanta e introduce lo siguiente:

```yaml
meteo_api_token: "Aquí-El-Toke-Copiado"
```

Guarda secrets.yaml

### Paso 6: Crear Automatización en Home Assistant

Finalmente, crea una automatización para llamar al `rest_command` periódicamente.

1.  Ve a **Settings / Automations & scenes / Create automation / Create new automation**.
2.  **Trigger (Disparador):**
    * **+ Add trigger** / **Time and location** / **Time pattern**
    * **Trigger ID (Optional):** `Cada 5 minutos`
    * **Minutes:** `/5`
3.  **Actions (Acciones):**
    * **+ Add Action**
    * Busca y selecciona **RESTful Command: `send_all_meteo_data`**
4.  **Guardar:**
    * Ponle un nombre (ej: `Enviar datos meteorológicos a la base de datos`) y guarda.

¡Y listo! Home Assistant recibirá los datos de la estación cada minuto y escribirá el último valor en tu base de datos cada 5 minutos.

### Paso 7: Configurar la web

Vamos a modificar los archivos de configuración de la web.

Abre el archivo config_db.php.example y modifícalo según los datos que utilizaste en el paso 3:

```php
<?php
// Renombrar a config_db.php
// Datos de conexión a MariaDB

$db_user = ""; // DataBase User
$db_pass = ""; // DataBase Password
$db_url = "127.0.0.1"; // dadtabase url
$db_database = "weather"; // DataBase name
?>
```

Guardalo en la misma ruta con el nombre config_db.php (/weather/static/config/config_db.php

Ahora tenemos que crear el long-lived access token en Home Assistant:

Abre Home Assistant y haz click en tu nombre (abajo a la izquierda), o haz clic <a href="https://my.home-assistant.io/redirect/profile/">aquí</a>

Selecciona la pestaña "Security" y ve abajo del todo.

En la sección Long-lived access tokens, haz clic en el botón "Create Token".

Da un nombre al token, por ejemplo "weather" o "meteo" y haz clic en "OK"

Se abre la ventana con el token y un botón de copiar. Copia el token y ya lo puedes cerrar.

Abre el archivo /weather/static/config/config.php.example y modifica las dos líneas siguientes según tus necesidades:

```php
// Renombrar a config.php
// CONFIGURACIÓN

// Datos de Conexión a Home Assistant
$ha_url = "127.0.0.1:8123"; // IP de tu Home Assistant. I.e: http://127.0.0.1:8123
$token = "Pega-Aquí-El-Long-Lived-Access-Token"; // Long-lived access Token de Home Assistant que acabas de copiar
```
Guarda el archivo como /weather/static/config/config.php

---

## 💬 Feedback

Si alguien llegara a probar esta configuración, ¡me gustaría saber si le funciona!
