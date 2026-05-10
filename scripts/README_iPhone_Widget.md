# Widget de Estación Meteorológica para iPhone

Script de Scriptable que se conecta a la base de datos de la estación meteorológica y muestra los datos más relevantes en un widget del iPhone.

## 📋 Requisitos

- **iPhone/iPad** con iOS 14 o superior
- **App Scriptable** (disponible en App Store)
- **Acceso a internet** para conectarse a la API
- **URL del servidor** donde está hospedado el proyecto

## 🚀 Instalación

### 1. Copiar el código del script

1. Abre la aplicación **Scriptable** en tu iPhone
2. Crea un nuevo script
3. Copia el contenido completo de `iPhoneWeatherWidget.js`
4. Pega el código en el script nuevo
5. Guarda el script con el nombre: **"Estación Meteorológica"** (o el que prefieras)

### 2. Configurar la URL de la API

En el script, localiza la línea:

```javascript
const API_URL = 'https://xusqui.com/weather/static/config/iphone_widget_api.php';
```

Y reemplaza la URL con la dirección completa de tu servidor:

```javascript
const API_URL = 'https://tudominio.com/static/config/iphone_widget_api.php';
```

### 3. Agregar el Widget a la pantalla de inicio

1. Ve a tu **Pantalla de inicio** del iPhone
2. Mantén presionado en un área vacía hasta que aparezca el menú
3. Toca **"Agregar widget"**
4. Busca y selecciona **Scriptable**
5. Elige el tamaño **Mediano** (recomendado)
6. Toca **"Agregar widget"**
7. **Mantén presionado** el nuevo widget
8. Toca **"Editar widget"**
9. En el campo **"Selecciona un script"**, elige **"Estación Meteorológica"**
10. Listo, el widget se actualizará automáticamente

## 📊 Datos que muestra el widget

El widget muestra los siguientes datos en tiempo real:

- 🌡️ **Temperatura actual** con cambio de color según la temperatura
- 🤔 **Sensación térmica**
- 💧 **Punto de rocío**
- 💧 **Humedad relativa**
- ⬇️ **Presión barométrica**
- 💨 **Velocidad del viento**
- 🌪️ **Racha máxima de viento**
- 🌧️ **Lluvia diaria, semanal, mensual y anual**
- ☀️ **Índice UV**
- ☀️ **Radiación solar**
- 🏠 **Temperatura interior**
- 🏠 **Humedad interior**
- 🕐 **Hora de última actualización**

## 🔄 Actualización automática

El widget se actualiza automáticamente según la configuración de Scriptable:

- **Recomendado**: Cada 5 minutos
- Para cambiar el intervalo, edita esta línea:

```javascript
const REFRESH_MINUTES = 5;
```

## 🎨 Temas

El widget detecta automáticamente el tema del iPhone:

- **Tema claro**: Colores claros y texto oscuro
- **Tema oscuro**: Colores oscuros y texto blanco

Los colores se adaptan también según los valores:

- **Temperatura**: Colores progresivos de azul (frío) a rojo (calor)
- **Lluvia**: Amarillo (sin lluvia) a azul oscuro (lluvia abundante)

## ⚙️ Configuración avanzada

### Cambiar tamaño del widget

Para usar el widget en tamaño **pequeño** o **grande**, edita:

```javascript
const WIDGET_SIZE = 'large'; // small, medium, large
```

Nota: El script está optimizado para tamaño **mediano**.

### Cambiar colores personalizados

Edita las constantes `COLORS` y `COLORS_DARK` al principio del script:

```javascript
const COLORS = {
    bg: new Color('#f5f5f7'),          // Fondo claro
    text: new Color('#000000'),        // Texto claro
    secondary: new Color('#666666'),   // Texto secundario
    accent: new Color('#007AFF'),      // Color de acento
    warning: new Color('#FF9500'),     // Color de alerta
    danger: new Color('#FF3B30'),      // Color de peligro
    success: new Color('#34C759'),     // Color de éxito
};
```

## 🔐 Seguridad

- La API está configurada para permitir acceso desde cualquier origen (`*`)
- Si necesitas restringir acceso, edita la línea en `iphone_widget_api.php`:

```php
header('Access-Control-Allow-Origin: *');
```

Por ejemplo, para restricción:

```php
header('Access-Control-Allow-Origin: https://tudominio.com');
```

## 🐛 Solución de problemas

### El widget muestra "Error obteniendo datos"

1. Verifica que la URL de la API sea correcta
2. Asegúrate de que el servidor está en línea
3. Comprueba la conexión de internet del iPhone
4. Revisa los logs del servidor

### El widget no se actualiza

1. Abre el script en Scriptable y presiona **"Ejecutar"** (►)
2. Verifica que el intervalo de actualización sea menor a lo permitido
3. Reinicia la aplicación Scriptable
4. Reinicia el iPhone

### Los datos no coinciden con la web

1. Verifica que ambas fuentes usen la misma base de datos
2. Comprueba la zona horaria configurada en el servidor
3. Asegúrate de que los sensores están funcionando correctamente

## 📝 Estructura de datos de la API

La API retorna un objeto JSON con la siguiente estructura:

```json
{
  "id": 123,
  "timestamp": "2026-01-28T15:30:45",
  "temperatura": 18.5,
  "humedad": 65.2,
  "sensacion_termica": 17.3,
  "presion_relativa": 1013.25,
  "punto_rocio": 10.5,
  "viento_velocidad": 12.3,
  "viento_racha": 25.5,
  "lluvia_diaria": 0.5,
  "lluvia_semana": 12.3,
  "indice_uv": 5.2,
  "radiacion_solar": 450.2,
  "temperatura_interior": 22.1,
  "humedad_interior": 55.0,
  "_metadata": {
    "api_version": "1.0",
    "widget_type": "iPhone",
    "generated_at": "2026-01-28T15:30:45"
  }
}
```

## 🔧 Archivos del proyecto

- **`scripts/iPhoneWeatherWidget.js`** - Script principal del widget
- **`static/config/iphone_widget_api.php`** - API que obtiene los datos de la BD

## 📄 Licencia

Este widget forma parte del proyecto de la Estación Meteorológica.

## ✉️ Soporte

Para reportar problemas o sugerencias, contacta al administrador del servidor.

---

**Última actualización**: 28 de enero de 2026
