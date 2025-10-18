Versión totalmente funcional en https://xusqui.com/weather/

<img width="1273" height="1264" alt="Captura de pantalla 2025-10-18 a las 9 42 04" src="https://github.com/user-attachments/assets/50994aba-f6c7-4ff5-9baf-ca1f2c293047" />

Este es un software desarrollado a partir de Weather Underground, por lo que su interfaz es similar.

No tengo mucha idea de programación, por lo que todo el código se ha creado con ChatGPT y Gemini

Se asume que se instala en el directorio /weather/

Tiene dos partes, un archivo insert.php que es utilizado por Home Assistant para escribir los datos que va recibiendo de la estación meteorológica en la base de datos

La segunda parte es la propia web en sí.

Mi configuración, que es la que he probado aquí y ya funcional es la siguiente:

* Estación Meteorológica Personal: Ambient Weather WS-2090
* Software de la estación meteorológica: EasyWeatherPro V5.2.2

El software envía los datos a una instancia funcional de home assistant: 
* En primer lugar añadir la integración "Ecowitt" a Home Assistant de la manera habitual
* En la app WSView Pluss entrar en la estación meteorológica dentro de la pestaña "My Devices" y dentro de ésta, en la pestaña "Customized":
* Customized: Enable
* Protocol: Ecowitt
* Server IP / Hostname: IP de home assistant, en mi caso: 192.168.1.100
* Path: /api/webhook/API_DE_HOME_ASSISTAN
* Port: Puerto de Home assistant, en mi caso: 8123
* Upload Interval: 60 seconds

En el archivo configuration.yaml de Home assistant incluir el siguiente rest_command:

rest_command:\
  send_all_meteo_data:\
    url: "http://192.168.1.100/weather/insert.php" // Sustituir por la dirección local de esta web\
    method: POST\
    content_type: "application/json"\
    payload: >\
      {\
        "timestamp": "{{ now().isoformat() }}",\
        "temperatura": "{{ states('sensor.ws2900_v2_02_03_outdoor_temperature') }}",\
        "sensacion_termica": "{{ states('sensor.ws2900_v2_02_03_feels_like_temperature') }}",\
        "humedad": "{{ states('sensor.ws2900_v2_02_03_humidity') }}",\
        "presion_relativa": "{{ states('sensor.ws2900_v2_02_03_relative_pressure') }}",\
        "presion_absoluta": "{{ states('sensor.ws2900_v2_02_03_absolute_pressure') }}",\
        "punto_rocio": "{{ states('sensor.ws2900_v2_02_03_dewpoint') }}",\
        "viento_velocidad": "{{ states('sensor.ws2900_v2_02_03_wind_speed') }}",\
        "viento_direccion": "{{ states('sensor.ws2900_v2_02_03_wind_direction') }}",\
        "viento_racha": "{{ states('sensor.ws2900_v2_02_03_wind_gust') }}",\
        "lluvia_diaria": "{{ states('sensor.ws2900_v2_02_03_daily_rain') }}",\
        "indice_uv": "{{ states('sensor.ws2900_v2_02_03_uv_index') }}",\
        "radiacion_solar": "{{ states('sensor.ws2900_v2_02_03_solar_radiation') }}",\
        "temperatura_interior": "{{ states('sensor.ws2900_v2_02_03_indoor_temperature') }}",\
        "humedad_interior": "{{ states('sensor.ws2900_v2_02_03_indoor_humidity') }}"\
      }\
      
// Sustituir los sensores por los de tu estación (Los nombres hay que buscarlos dentro de home assistant)

Dentro de Home Assistant ir a Settings / Automations & scenes / Create automation / Create new automation:
*  When: + Add trigger / Time and location / Time pattern
*    Trigger ID (Optional): Lo que sea, p ej: Cada 5 minutos
*    Hours: En blanco
*    Minutes: /5
*    Seconds: en blanco
* Click: + Add Action:
*   RESTful Command: send_all_meteo_data
* Click: Save

Name: Enviar datos meteorológicos a la base de datos
Click: Save

Y listo.

De esta manera, home assistant recibe los datos desde la estación meteorológica cada minuto, y escribe el último valor en la base de datos cada 5 minutos.

Si alguien llegara a probarlo, me gustaría saber si le funciona
