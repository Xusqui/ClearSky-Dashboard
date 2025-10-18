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
  - [Paso 2: Configurar la App "WSView Pluss"](#paso-2-configurar-la-app-wsview-pluss)
  - [Paso 3: Añadir `rest_command`](#paso-3-añadir-rest_command-a-home-assistant)
  - [Paso 4: Crear Automatización](#paso-4-crear-automatización-en-home-assistant)
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

### Paso 2: Configurar la App "WSView Pluss"

En la app móvil (WSView Pluss), entra en tu estación meteorológica (en "My Devices") y ve a la pestaña "Customized":

- **Customized**: Enable
- **Protocol**: Ecowitt
- **Server IP / Hostname**: La IP de tu Home Assistant (ej: `192.168.1.100`)
- **Path**: `/api/webhook/API_DE_HOME_ASSISTAN` (Sustituye por tu API Key/Webhook ID)
- **Port**: El puerto de Home Assistant (ej: `8123`)
- **Upload Interval**: 60 seconds

### Paso 3: Añadir `rest_command` a Home Assistant

Añade lo siguiente a tu archivo `configuration.yaml` en Home Assistant. Esto define el `rest_command` (la acción de
enviar datos) y el `input_text` (para guardar tu token de forma segura).

```yaml
rest_command:
  send_all_meteo_data:
    url: "[http://192.168.1.100/weather/insert.php](http://192.168.1.100/weather/insert.php)" # Sustituye por la IP de tu servidor web
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

### Paso 4: Crear Automatización en Home Assistant

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

---

## 💬 Feedback

Si alguien llegara a probar esta configuración, ¡me gustaría saber si le funciona!
