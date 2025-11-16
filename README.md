# CLEARSKY DASHBOARD
# 🌦️ Interfaz Web para datos Estación Meteorológica con protocolo EcoWitt
# Posibilidad de enviar datos a Home Assistant
# Posibilidad de enviar datos a Meteoclimatic
# Estimación de la calidad del cielo para observación astronómica (Seeing)

<p alight="center"><img width="1024" height="1024" alt="Gemini_Generated_Image_q710xlq710xlq710" src="https://github.com/user-attachments/assets/7d63b6cb-147a-41cb-b9cf-23e019e089f5" /></p>


<p align="center">
    <img alt="PHP" src="https://img.shields.io/badge/PHP-777BB4?logo=php&logoColor=white&style=flat">
    <img alt="MySQL" src="https://img.shields.io/badge/MySQL-4479A1?logo=mysql&logoColor=white&style=flat">
    <img alt="Home Assistant" src="https://img.shields.io/badge/Home%20Assistant-41BDF5?logo=homeassistant&logoColor=white&style=flat">
    <img alt="JavaScript" src="https://img.shields.io/badge/JavaScript-F7DF1E?logo=javascript&logoColor=black&style=flat">
    <img alt="CSS3" src="https://img.shields.io/badge/CSS3-1572B6?logo=css3&logoColor=white&style=flat">
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
  - [Paso 1: Cambiar el nombre de api_data_xxxxx.php"](#1️⃣-paso-1-cambiar-el-nombre-del-archivo-api_config_xxxxx.php)
  - [Paso 2: Configurar la Estación para enviar datos a tu servidor](#2️⃣-paso-2-configurar-la-estacion-para-enviar-datos-a-tu-servidor)
  - [Paso 3: Crear la Base de Datos](#3️⃣-paso-3-crear-la-base-de-datos-automáticamente)
  - [Paso 4: Acceder al Servidor](#4️⃣-paso-4-acceder-al-servidor)
- [ToDo](#-todo)
- [Feedback](#-feedback)

---

## 💡 Sobre el Proyecto

Este es un software desarrollado a partir de la interfaz nueva de Weather Underground.

> [!NOTE] No tengo mucha idea de programación, por lo que todo el código se ha creado con la ayuda de ChatGPT y Gemini.

### Componentes

El proyecto tiene dos partes principales:

1.  **`api_data_xxxxx.php`**: Un _endpoint_ que es utilizado por la Estación Meteorológica para enviar los datos
    a la página web. Este api_data tiene opción de guardar datos en la base de datos local (MySQL / MariaDB)
    , enviar datos a Home Assistant y enviar datos a MeteoClimatic.
2.  **La Web (index.php)**: La propia página web que lee y muestra los datos de la base de datos.

Inicialmente se asumína que el software se instalaba en el directorio `/weather/` de tu servidor web. Actualmente se han corregido las rutas estáticas tipo `/weather/`por `./`o `../`, etc... No obstante no está probado si funciona en un directorio raíz y estas instrucciones siguen asumiento que lo instalas en el directorio `/weather` de tu servidor. Cambia esto a tu conveniencia y bajo tu responsabilidad.

---

## ⚙️ Mi Configuración (Probada y Funcional)

Esta es la configuración de hardware y software con la que el proyecto ha sido probado:

- **Estación Meteorológica Personal**: Ambient Weather WS-2090
- **Software de la Estación**: EasyWeatherPro V5.2.2
- **Software de Domótica**: Home Assistant

---

## 🚀 Guía de Configuración

El flujo de datos es: **Estación ➡️ api_config_xxxxx.php ➡️ Envío de datos a Base de datos local +/- Home Assistant +/- Meteoclimatic

Sigue estos pasos para replicar la configuración:

### 1️⃣ Paso 1: Cambiar el nombre del archivo api_config_xxxxx.php

Busca el archivo api_config_xxxxx.php y cámbiale el nombre por algo único, esto añade una capa de seguridad, por ejemplo: api_config_123456.php

### 2️⃣ Paso 2: Configurar la Estacion para enviar datos a tu servidor.

Configura la aplicación WSView Plus:
- Abre la aplicación
- Haz click en la pestaña "My Devices"
- Pulsa en el nombre de tu estación.
- Ve a la pestaña Customized.
- Pulsa "Enable"
- Protocol Type Same AS: "Ecowitt"
- Server IP / Hostname: La IP de tu servidor (NAS, Raspberry...): Ej: 192.168.1.100
- Path: El sitio donde alojas api_config_xxxxx.php dentro de tu servidor, con el nombre que le has cambiado: ej: /weather/api_data123456.php?token=TOKEN_UNICO
- Tienes que crear un TOKEN_UNICO para que la estación se autentifique en el software, es cualquier combinación de letras y números, ej: 123456
- Port: El puerto que utilice tu servidor: Ej: 80
- Upload Interval: Recomendado 60

### 3️⃣ Paso 3: Crear la Base de Datos Automáticamente

Asumimos que ya tienes un servidor con una instancia de MySQL / MariaDB funcionando.
Sólo necesitas crear, con la línea de comandos o con phpMyAdmin un usuario único para este software
y una base de datos, por ejemplo:
- Usuario: weather_user
- Contraseña: weather_password
- Base de Datos: weather

Modifica el archivo dentro de la rute /static/config/config_db.php.example para que tenga los datos de conexión a tu base de datos:

```php
<?php
// Renombrar a config_db.php
// Datos de conexión a MariaDB

$db_user = "weather_user"; // DataBase User
$db_pass = "weather_password"; // DataBase Password
$db_url = "127.0.0.1"; // dadtabase url
$db_database = "weather"; // DataBase name
?>
```

Y renombra ese archivo a config_db.php

### 4️⃣ Paso 4: Acceder al servidor

Accede al software recién creado: http://ip_de_tu_servidor/weather/index.php (Cambia weather por la carpeta donde lo hayas instalado)

La primera vez que accedes, el software debe detectar que es la primera instalación y crear las carpetas config y meteo.

Una vez creada la estructura de la base de datos, te solicitará los datos imprescindibles para utilizarla, en primer lugar una contraseña para acceder a la zona de configuración y posteriormente los datos relativos a tu estación meteorológica, así como si deseas guardar los datos en local, si deseas enviarlos a Home Assistant y si los quieres enviar a Meteoclimatic.

La integración de las estaciones meteorológicas con Home Assistant, corre de vuestra cuenta.

---
### &check; ToDo

* Cualquier sugerencia es bienvenida.


### 💬 Feedback

Si alguien llegara a probar esta configuración, ¡me gustaría saber si le funciona!

---

<p align="center">
    Hecho con ❤️ por <strong>Xisco</strong> · <a href="https://xusqui.com/">xusqui.com</a>
</p>
