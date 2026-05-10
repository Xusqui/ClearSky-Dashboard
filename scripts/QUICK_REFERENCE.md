# Guía de Referencia Rápida - Widget iPhone

## 📁 Archivos Creados

### Scripts (en `/scripts/`)
1. **iPhoneWeatherWidget.js** - Script principal y recomendado
   - Versión estándar con interfaz moderna
   - Muestra todos los datos meteorológicos relevantes
   - Compatible con tema claro y oscuro

2. **iPhoneWeatherWidget_Alternativas.js** - Versiones alternativas
   - `versionCompacta()` - Datos esenciales únicamente
   - `versionDetallada()` - Información expandida
   - `versionAstronomica()` - Enfocada en radiación solar y UV
   - `versionInteriorExterior()` - Compara interior vs exterior

3. **README_iPhone_Widget.md** - Documentación completa de instalación

### API (en `/static/config/`)
**iphone_widget_api.php** - Endpoint de datos para el widget
- Retorna JSON con últimos datos meteorológicos
- Conecta directamente a la base de datos
- Incluye metadatos de estado de lluvia

---

## 🚀 Instalación Rápida

### En el iPhone:
1. Instala Scriptable (App Store)
2. Copia el código de `iPhoneWeatherWidget.js`
3. Crea nuevo script en Scriptable
4. **IMPORTANTE**: Actualiza la URL en la línea 5:
   ```javascript
   const API_URL = 'https://tudominio.com/static/config/iphone_widget_api.php';
   ```
5. Guarda con nombre "Estación Meteorológica"
6. Agrega widget a pantalla de inicio (tamaño Mediano)
7. Selecciona el script en ajustes del widget

---

## 📊 Datos que Muestra

| Dato | Icono | Campo BD | Unidad |
|------|-------|----------|--------|
| Temperatura | 🌡️ | temperatura | °C |
| Sensación térmica | 🤔 | sensacion_termica | °C |
| Punto de rocío | ❄️ | punto_rocio | °C |
| Humedad | 💧 | humedad | % |
| Velocidad viento | 💨 | viento_velocidad | km/h |
| Racha máxima | 🌪️ | viento_racha | km/h |
| Presión | ⬇️ | presion_relativa | mb |
| Lluvia diaria | 🌧️ | lluvia_diaria | mm |
| Lluvia semanal | 📊 | lluvia_semana | mm |
| Lluvia mensual | 📅 | lluvia_mes | mm |
| Índice UV | ☀️ | indice_uv | - |
| Radiación solar | ☀️ | radiacion_solar | W/m² |
| Temp interior | 🏠 | temperatura_interior | °C |
| Hum interior | 🏠 | humedad_interior | % |

---

## 🔧 Configuración Principal

```javascript
// URL de la API (CAMBIAR ESTA)
const API_URL = 'https://tudominio.com/static/config/iphone_widget_api.php';

// Intervalo de actualización
const REFRESH_MINUTES = 5;

// Tamaño del widget
const WIDGET_SIZE = 'large'; // small, medium, large
```

---

## 🎨 Personalización de Colores

### Tema Claro
```javascript
const COLORS = {
    bg: new Color('#f5f5f7'),           // Fondo
    text: new Color('#000000'),         // Texto principal
    secondary: new Color('#666666'),    // Texto secundario
    accent: new Color('#007AFF'),       // Color de acento
    warning: new Color('#FF9500'),      // Alerta
    danger: new Color('#FF3B30'),       // Peligro
    success: new Color('#34C759'),      // Éxito
};
```

### Tema Oscuro
```javascript
const COLORS_DARK = {
    bg: new Color('#1c1c1e'),
    text: new Color('#ffffff'),
    secondary: new Color('#999999'),
    accent: new Color('#0A84FF'),
    warning: new Color('#FF9500'),
    danger: new Color('#FF453A'),
    success: new Color('#30B558'),
};
```

---

## 📱 Cambiar Versión del Widget

En `iPhoneWeatherWidget.js`, reemplaza la función en el bloque principal:

```javascript
// ACTUAL
const widget = await createWeatherWidget(data, colors);

// CAMBIAR A:
const widget = await versionCompacta();     // Mínima
const widget = await versionDetallada();    // Expandida
const widget = await versionAstronomica();  // Solar/UV
const widget = await versionInteriorExterior(); // Interior/Exterior
```

Nota: Necesitarás copiar también las funciones de `iPhoneWeatherWidget_Alternativas.js`

---

## 🔄 Ciclo de Datos

```
Sensores → Base de datos → iphone_widget_api.php → iPhone Widget
              ↓
          tabla 'meteo'
```

---

## ✅ Checklist de Configuración

- [ ] URL de API actualizada en script
- [ ] Base de datos y tabla 'meteo' accesible
- [ ] Archivo `iphone_widget_api.php` subido a servidor
- [ ] Scriptable instalado en iPhone
- [ ] Script creado en Scriptable
- [ ] Widget agregado a pantalla de inicio
- [ ] Widget configurado con el script correcto
- [ ] Conexión de internet en el iPhone activa
- [ ] Se muestra la hora de actualización más reciente

---

## 🐛 Debugging

### Ver errores en Scriptable
1. Abre el script en Scriptable
2. Presiona ▶️ (Play)
3. Mira la consola de salida

### Probar API desde navegador
Visita: `https://tudominio.com/static/config/iphone_widget_api.php`

Deberías ver un JSON con los últimos datos meteorológicos.

### Logs útiles
- Verifica `/Volumes/web/weather/weather_data.log`
- Revisa errores del servidor web

---

## 📈 Mejoras Futuras

- [ ] Widget tipo "Lock Screen" (iOS 16+)
- [ ] Notificaciones de lluvia inmediata
- [ ] Predicción de 24 horas
- [ ] Histórico en gráficas
- [ ] Compartir datos con otras apps
- [ ] Soporte multi-idioma

---

## 🔐 Seguridad API

El endpoint actual permite acceso desde cualquier origen:
```php
header('Access-Control-Allow-Origin: *');
```

Para producción, restringir a:
```php
header('Access-Control-Allow-Origin: https://tudominio.com');
```

---

## 📞 Solución de Problemas

| Problema | Solución |
|----------|----------|
| "Error obteniendo datos" | Verifica URL y conexión internet |
| Widget no se actualiza | Reinicia Scriptable o el iPhone |
| Datos desactualizados | Verifica que los sensores funcionen |
| API devuelve error | Verifica credenciales DB en config_db.php |
| Colores invertidos | Activa/desactiva tema oscuro en iPhone |

---

## 📚 Archivos Relacionados

- Base de datos: `/static/config/config_db.php`
- Schema: `/static/config/meteo_schema.php`
- Otras APIs: `/hoy.php`
- Configuración general: `/static/config/config.php`

---

## 🎯 Resumen de Ventajas

✅ Datos en tiempo real desde la BD
✅ Diseño adaptable (claro/oscuro)
✅ Múltiples versiones disponibles
✅ Fácil de instalar y personalizar
✅ Bajo consumo de recursos
✅ Actualización automática configurable
✅ Colores inteligentes según valores
✅ Información meteorológica completa

---

**Versión**: 1.0
**Fecha**: 28 de enero de 2026
**Compatible**: iOS 14+
**Requiere**: Scriptable App
