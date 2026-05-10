// Variables used by Scriptable.
// icon-color: purple; icon-glyph: cloud
// Script: iPhone Weather Widget - Configuraciones Alternativas

/**
 * Este archivo contiene ejemplos de diferentes configuraciones y versiones
 * del widget de Scriptable para la estación meteorológica.
 * 
 * Selecciona la versión que mejor se adapte a tus necesidades.
 */

// ==========================================
// VERSIÓN COMPACTA - Solo datos esenciales
// ==========================================
async function versionCompacta() {
    try {
        const API_URL = 'https://xusqui.com/weather/static/config/iphone_widget_api.php';
        const data = await fetchData(API_URL);
        const isDark = Device.isUsingDarkAppearance();
        const colors = isDark ? getColorsDark() : getColorsLight();

        const widget = new ListWidget();
        widget.backgroundColor = colors.bg;
        widget.setPadding(10, 10, 10, 10);

        // Título
        let title = widget.addText('Estación Meteorológica');
        title.font = Font.boldSystemFont(12);
        title.textColor = colors.text;

        widget.addSpacer(6);

        // Temperatura grande
        let temp = widget.addText(parseFloat(data.temperatura || 0).toFixed(1) + '°C');
        temp.font = Font.boldSystemFont(28);
        temp.textColor = getTemperatureColor(data.temperatura, colors);

        widget.addSpacer(4);

        // Datos en una línea
        let conditions = widget.addText(
            '💧 ' + parseFloat(data.humedad || 0).toFixed(0) + '% | ' +
            '💨 ' + parseFloat(data.viento_velocidad || 0).toFixed(1) + 'km/h | ' +
            '🌧️ ' + parseFloat(data.lluvia_diaria || 0).toFixed(1) + 'mm'
        );
        conditions.font = Font.systemFont(10);
        conditions.textColor = colors.secondary;

        widget.addSpacer(4);

        // Hora actualización
        let time = widget.addText('Actualizado: ' + formatTime(data.timestamp));
        time.font = Font.systemFont(8);
        time.textColor = colors.secondary;

        return widget;
    } catch (err) {
        return createErrorWidget('Error: ' + err.message);
    }
}

// ==========================================
// VERSIÓN DETALLADA - Más información
// ==========================================
async function versionDetallada() {
    try {
        const API_URL = 'https://xusqui.com/weather/static/config/iphone_widget_api.php';
        const data = await fetchData(API_URL);
        const isDark = Device.isUsingDarkAppearance();
        const colors = isDark ? getColorsDark() : getColorsLight();

        const widget = new ListWidget();
        widget.backgroundColor = colors.bg;
        widget.setPadding(12, 12, 12, 12);

        // Header
        let header = widget.addStack();
        header.layoutHorizontally();

        let title = header.addText('Estación Meteorológica');
        title.font = Font.boldSystemFont(14);
        title.textColor = colors.text;

        header.addSpacer();

        let time = header.addText(formatTime(data.timestamp));
        time.font = Font.systemFont(9);
        time.textColor = colors.secondary;

        widget.addSpacer(8);

        // Sección 1: Temperatura y Humedad
        createSection(widget, 'Temperatura y Humedad', [
            { label: '🌡️ Actual', value: data.temperatura, unit: '°C' },
            { label: '🤔 Sensación', value: data.sensacion_termica, unit: '°C' },
            { label: '💧 Humedad', value: data.humedad, unit: '%' },
            { label: '❄️ Rocío', value: data.punto_rocio, unit: '°C' }
        ], colors);

        widget.addSpacer(6);

        // Sección 2: Viento
        createSection(widget, 'Viento', [
            { label: '💨 Velocidad', value: data.viento_velocidad, unit: 'km/h' },
            { label: '🌪️ Racha', value: data.viento_racha, unit: 'km/h' },
            { label: '⬆️ Máxima', value: data.viento_racha_maxima, unit: 'km/h' },
            { label: '🧭 Dirección', value: data.viento_direccion, unit: '°' }
        ], colors);

        widget.addSpacer(6);

        // Sección 3: Lluvia
        createSection(widget, 'Lluvia', [
            { label: '🌧️ Hoy', value: data.lluvia_diaria, unit: 'mm' },
            { label: '📊 Semana', value: data.lluvia_semana, unit: 'mm' },
            { label: '📅 Mes', value: data.lluvia_mes, unit: 'mm' },
            { label: '📈 Año', value: data.lluvia_ano, unit: 'mm' }
        ], colors);

        return widget;
    } catch (err) {
        return createErrorWidget('Error: ' + err.message);
    }
}

// ==========================================
// VERSIÓN ASTRONÓMICA - Radiación Solar y UV
// ==========================================
async function versionAstronomica() {
    try {
        const API_URL = 'https://xusqui.com/weather/static/config/iphone_widget_api.php';
        const data = await fetchData(API_URL);
        const isDark = Device.isUsingDarkAppearance();
        const colors = isDark ? getColorsDark() : getColorsLight();

        const widget = new ListWidget();
        widget.backgroundColor = colors.bg;
        widget.setPadding(12, 12, 12, 12);

        // Header
        let title = widget.addText('☀️ Observatorio - Condiciones Solares');
        title.font = Font.boldSystemFont(13);
        title.textColor = colors.text;

        widget.addSpacer(8);

        // Radiación Solar
        let radStack = widget.addStack();
        radStack.layoutHorizontally();
        radStack.spacing = 6;

        let radIcon = radStack.addText('☀️');
        radIcon.font = Font.systemFont(20);

        let radTextStack = radStack.addStack();
        radTextStack.layoutVertically();

        let radLabel = radTextStack.addText('Radiación Solar');
        radLabel.font = Font.boldSystemFont(11);
        radLabel.textColor = colors.text;

        let radValue = radTextStack.addText(parseFloat(data.radiacion_solar || 0).toFixed(0) + ' W/m²');
        radValue.font = Font.systemFont(12);
        radValue.textColor = getRadiationColor(data.radiacion_solar, colors);

        widget.addSpacer(8);

        // Índice UV
        let uvStack = widget.addStack();
        uvStack.layoutHorizontally();
        uvStack.spacing = 6;

        let uvIcon = uvStack.addText('🛡️');
        uvIcon.font = Font.systemFont(20);

        let uvTextStack = uvStack.addStack();
        uvTextStack.layoutVertically();

        let uvLabel = uvTextStack.addText('Índice UV');
        uvLabel.font = Font.boldSystemFont(11);
        uvLabel.textColor = colors.text;

        let uvValue = uvTextStack.addText(parseFloat(data.indice_uv || 0).toFixed(1) + ' (Riesgo: ' + getUVRisk(data.indice_uv) + ')');
        uvValue.font = Font.systemFont(12);
        uvValue.textColor = getUVColor(data.indice_uv, colors);

        widget.addSpacer(8);

        // Condiciones generales
        let condTitle = widget.addText('Condiciones Generales');
        condTitle.font = Font.boldSystemFont(11);
        condTitle.textColor = colors.text;

        let condGrid = widget.addStack();
        condGrid.layoutHorizontally();
        condGrid.spacing = 6;

        createSmallDataBox(condGrid, '🌡️', 'Temp', data.temperatura, '°C', colors);
        createSmallDataBox(condGrid, '💧', 'Hum', data.humedad, '%', colors);
        createSmallDataBox(condGrid, '💨', 'Viento', data.viento_velocidad, 'km/h', colors);
        createSmallDataBox(condGrid, '⬇️', 'Presión', data.presion_relativa, 'mb', colors);

        return widget;
    } catch (err) {
        return createErrorWidget('Error: ' + err.message);
    }
}

// ==========================================
// VERSIÓN INTERIOR/EXTERIOR
// ==========================================
async function versionInteriorExterior() {
    try {
        const API_URL = 'https://xusqui.com/weather/static/config/iphone_widget_api.php';
        const data = await fetchData(API_URL);
        const isDark = Device.isUsingDarkAppearance();
        const colors = isDark ? getColorsDark() : getColorsLight();

        const widget = new ListWidget();
        widget.backgroundColor = colors.bg;
        widget.setPadding(12, 12, 12, 12);

        // EXTERIOR
        let extTitle = widget.addText('🌍 Exterior');
        extTitle.font = Font.boldSystemFont(12);
        extTitle.textColor = colors.accent;

        let extBox = widget.addStack();
        extBox.backgroundColor = new Color(isDark ? '#333333' : '#E8E8E8');
        extBox.cornerRadius = 8;
        extBox.setPadding(8, 10, 8, 10);
        extBox.layoutHorizontally();
        extBox.spacing = 8;

        createCompactBox(extBox, 'Temp\n' + parseFloat(data.temperatura || 0).toFixed(1) + '°C', colors);
        createCompactBox(extBox, 'Hum\n' + parseFloat(data.humedad || 0).toFixed(0) + '%', colors);
        createCompactBox(extBox, 'Lluvia\n' + parseFloat(data.lluvia_diaria || 0).toFixed(1) + 'mm', colors);

        widget.addSpacer(8);

        // INTERIOR
        let intTitle = widget.addText('🏠 Interior');
        intTitle.font = Font.boldSystemFont(12);
        intTitle.textColor = colors.accent;

        let intBox = widget.addStack();
        intBox.backgroundColor = new Color(isDark ? '#333333' : '#E8E8E8');
        intBox.cornerRadius = 8;
        intBox.setPadding(8, 10, 8, 10);
        intBox.layoutHorizontally();
        intBox.spacing = 8;

        createCompactBox(intBox, 'Temp\n' + parseFloat(data.temperatura_interior || 0).toFixed(1) + '°C', colors);
        createCompactBox(intBox, 'Hum\n' + parseFloat(data.humedad_interior || 0).toFixed(0) + '%', colors);
        createCompactBox(intBox, 'VPD\n' + parseFloat(data.vpd || 0).toFixed(2) + 'kPa', colors);

        widget.addSpacer(8);

        // Footer
        let footer = widget.addText('Actualizado: ' + formatFullTime(data.timestamp));
        footer.font = Font.systemFont(8);
        footer.textColor = colors.secondary;

        return widget;
    } catch (err) {
        return createErrorWidget('Error: ' + err.message);
    }
}

// ==========================================
// FUNCIONES AUXILIARES
// ==========================================

async function fetchData(url) {
    let req = new Request(url);
    req.timeoutInterval = 10;
    return await req.loadJSON();
}

function getColorsLight() {
    return {
        bg: new Color('#f5f5f7'),
        text: new Color('#000000'),
        secondary: new Color('#666666'),
        accent: new Color('#007AFF')
    };
}

function getColorsDark() {
    return {
        bg: new Color('#1c1c1e'),
        text: new Color('#ffffff'),
        secondary: new Color('#999999'),
        accent: new Color('#0A84FF')
    };
}

function getTemperatureColor(temp, colors) {
    let t = parseFloat(temp || 0);
    if (t < 0) return new Color('#4A90E2');
    if (t < 10) return new Color('#50C878');
    if (t < 20) return new Color('#FFD700');
    if (t < 30) return new Color('#FF8C00');
    return new Color('#FF3B30');
}

function getRadiationColor(rad, colors) {
    let r = parseFloat(rad || 0);
    if (r < 100) return new Color('#90EE90');
    if (r < 300) return new Color('#FFD700');
    if (r < 600) return new Color('#FF8C00');
    return new Color('#FF3B30');
}

function getUVColor(uv, colors) {
    let u = parseFloat(uv || 0);
    if (u < 3) return new Color('#34C759');
    if (u < 6) return new Color('#FFD700');
    if (u < 8) return new Color('#FF8C00');
    if (u < 11) return new Color('#FF3B30');
    return new Color('#9900CC');
}

function getUVRisk(uv) {
    let u = parseFloat(uv || 0);
    if (u < 3) return 'Bajo';
    if (u < 6) return 'Moderado';
    if (u < 8) return 'Alto';
    if (u < 11) return 'Muy Alto';
    return 'Extremo';
}

function createSection(widget, title, items, colors) {
    let sectionTitle = widget.addText(title);
    sectionTitle.font = Font.boldSystemFont(11);
    sectionTitle.textColor = colors.accent;

    let grid = widget.addStack();
    grid.layoutHorizontally();
    grid.spacing = 8;

    let col1 = grid.addStack();
    col1.layoutVertically();
    col1.spacing = 4;

    let col2 = grid.addStack();
    col2.layoutVertically();
    col2.spacing = 4;

    items.forEach((item, index) => {
        let col = index < 2 ? col1 : col2;
        let row = col.addStack();
        row.layoutHorizontally();
        row.spacing = 4;

        let label = row.addText(item.label);
        label.font = Font.systemFont(10);
        label.textColor = colors.secondary;

        row.addSpacer();

        let value = row.addText((parseFloat(item.value || 0).toFixed(1)) + item.unit);
        value.font = Font.boldSystemFont(10);
        value.textColor = colors.text;
    });
}

function createSmallDataBox(stack, emoji, label, value, unit, colors) {
    let box = stack.addStack();
    box.layoutVertically();
    box.spacing = 2;
    box.size = new Size(60, 50);

    let icon = box.addText(emoji);
    icon.font = Font.systemFont(16);

    let lbl = box.addText(label);
    lbl.font = Font.systemFont(8);
    lbl.textColor = colors.secondary;

    let val = box.addText(parseFloat(value || 0).toFixed(1) + unit);
    val.font = Font.boldSystemFont(9);
    val.textColor = colors.text;
}

function createCompactBox(stack, text, colors) {
    let box = stack.addStack();
    box.layoutVertically();
    box.backgroundColor = colors.bg;
    box.cornerRadius = 6;
    box.setPadding(8, 6, 8, 6);
    box.size = new Size(80, 0);

    let txt = box.addText(text);
    txt.font = Font.boldSystemFont(10);
    txt.textColor = colors.text;
    txt.lineLimit = 2;
    txt.centerAlignText();
}

function formatTime(timestamp) {
    try {
        let date = new Date(timestamp);
        return date.toLocaleTimeString('es-ES', { hour: '2-digit', minute: '2-digit' });
    } catch {
        return '--:--';
    }
}

function formatFullTime(timestamp) {
    try {
        let date = new Date(timestamp);
        return date.toLocaleString('es-ES', {
            day: '2-digit',
            month: '2-digit',
            hour: '2-digit',
            minute: '2-digit'
        });
    } catch {
        return '--';
    }
}

function createErrorWidget(message) {
    let widget = new ListWidget();
    widget.backgroundColor = new Color('#FF3B30');
    widget.setPadding(12, 12, 12, 12);

    let text = widget.addText('⚠️ ' + message);
    text.font = Font.systemFont(12);
    text.textColor = new Color('#FFFFFF');
    text.lineLimit = 0;

    return widget;
}

// ==========================================
// INSTRUCCIONES DE USO
// ==========================================

/**
 * CÓMO USAR ESTAS VERSIONES ALTERNATIVAS:
 * 
 * 1. Selecciona una de las funciones anteriores (versionCompacta, versionDetallada, etc)
 * 2. Reemplaza el contenido principal del script iPhoneWeatherWidget.js
 * 3. Cambia el bloque (async () => { ... }) al final para llamar la función deseada:
 * 
 * Ejemplo para versión compacta:
 * 
 * (async () => {
 *     try {
 *         const widget = await versionCompacta();
 *         if (config.runsInWidget) {
 *             Script.setWidget(widget);
 *         } else {
 *             await widget.presentMedium();
 *         }
 *         Script.complete();
 *     } catch (err) {
 *         // manejo de error
 *     }
 * })();
 * 
 * Cada versión tiene diferentes características:
 * - versionCompacta(): Mínima información, perfecta para pantalla pequeña
 * - versionDetallada(): Información completa, requiere scroll
 * - versionAstronomica(): Enfocada en radiación solar y UV
 * - versionInteriorExterior(): Compara datos interiores vs exteriores
 */
