// Variables used by Scriptable.
// icon-color: blue; icon-glyph: cloud
// Script: iPhone Weather Widget - Estación Meteorológica

// ===== CONFIGURACIÓN =====
const API_URL = 'https://xusqui.com/weather/static/config/iphone_widget_api.php';
const REFRESH_MINUTES = 5;
const WIDGET_SIZE = 'medium'; // small, medium, large

// Color Theme
const COLORS = {
    bg: new Color('#f5f5f7'),
    text: new Color('#000000'),
    secondary: new Color('#666666'),
    accent: new Color('#007AFF'),
    warning: new Color('#FF9500'),
    danger: new Color('#FF3B30'),
    success: new Color('#34C759'),
};

const COLORS_DARK = {
    bg: new Color('#1c1c1e'),
    text: new Color('#ffffff'),
    secondary: new Color('#999999'),
    accent: new Color('#0A84FF'),
    warning: new Color('#FF9500'),
    danger: new Color('#FF453A'),
    success: new Color('#30B558'),
};

// ===== MAIN ENTRY =====
(async () => {
    try {
        // Detectar tema del iPhone (claro u oscuro)
        const isDark = Device.isUsingDarkAppearance();
        const colors = isDark ? COLORS_DARK : COLORS;

        // Obtener datos meteorológicos
        const data = await fetchWeatherData();

        // Crear widget
        const widget = await createWeatherWidget(data, colors);

        // Presentar o configurar widget
        if (config.runsInWidget) {
            Script.setWidget(widget);
        } else {
            await widget.presentMedium();
            Script.setWidget(widget);
        }
        Script.complete();
    } catch (err) {
        const errorWidget = createErrorWidget(err.message || String(err));
        if (config.runsInWidget) {
            Script.setWidget(errorWidget);
        } else {
            await errorWidget.presentMedium();
        }
        Script.complete();
    }
})();

// ===== FETCH WEATHER DATA =====
async function fetchWeatherData() {
    try {
        const req = new Request(API_URL);
        req.timeoutInterval = 10;
        const data = await req.loadJSON();

        if (data.error) {
            throw new Error(data.error);
        }

        return data;
    } catch (err) {
        throw new Error('Error obteniendo datos: ' + err.message);
    }
}

// ===== CREATE MAIN WIDGET =====
async function createWeatherWidget(data, colors) {
    const widget = new ListWidget();
    widget.backgroundColor = colors.bg;
    widget.setPadding(12, 12, 12, 12);

    // HEADER - Última actualización y ubicación
    const headerStack = widget.addStack();
    headerStack.layoutHorizontally();

    const titleText = headerStack.addText('Estación Meteorológica');
    titleText.font = Font.boldSystemFont(14);
    titleText.textColor = colors.text;

    headerStack.addSpacer();

    const timeText = headerStack.addText(formatUpdateTime(data.timestamp));
    timeText.font = Font.systemFont(10);
    timeText.textColor = colors.secondary;

    widget.addSpacer(6);

    // TEMPERATURA - Destacado
    const tempStack = widget.addStack();
    tempStack.layoutHorizontally();
    tempStack.spacing = 8;

    const tempValue = tempStack.addText(formatValue(data.temperatura, '°C'));
    tempValue.font = Font.boldSystemFont(32);
    tempValue.textColor = getTemperatureColor(data.temperatura, colors);

    const tempLabel = tempStack.addStack();
    tempLabel.layoutVertically();
    tempLabel.spacing = 2;

    const sensationText = tempLabel.addText('Sensación: ' + formatValue(data.sensacion_termica, '°C'));
    sensationText.font = Font.systemFont(10);
    sensationText.textColor = colors.secondary;

    const dewText = tempLabel.addText('Rocío: ' + formatValue(data.punto_rocio, '°C'));
    dewText.font = Font.systemFont(10);
    dewText.textColor = colors.secondary;

    widget.addSpacer(8);

    // GRID DE DATOS
    const gridStack = widget.addStack();
    gridStack.layoutHorizontally();
    gridStack.spacing = 8;

    // Columna 1: Humedad y Presión
    const col1 = gridStack.addStack();
    col1.layoutVertically();
    col1.spacing = 6;

    addDataRow(col1, '💧 Humedad', formatValue(data.humedad, '%'), colors);
    addDataRow(col1, '⬇️ Presión', formatValue(data.presion_relativa, 'mb'), colors);

    gridStack.addSpacer();

    // Columna 2: Viento y Lluvia
    const col2 = gridStack.addStack();
    col2.layoutVertically();
    col2.spacing = 6;

    addDataRow(col2, '💨 Viento', formatValue(data.viento_velocidad, 'km/h'), colors);
    addDataRow(col2, '🌪️ Racha', formatValue(data.viento_racha, 'km/h'), colors);

    widget.addSpacer(8);

    // SECCIÓN DE LLUVIA - EXPANDIDA
    const rainStack = widget.addStack();
    rainStack.backgroundColor = getRainColor(data.lluvia_diaria, colors);
    rainStack.cornerRadius = 8;
    rainStack.setPadding(10, 12, 10, 12);
    rainStack.layoutVertically();
    rainStack.spacing = 6;

    // Header de lluvia
    const rainHeaderStack = rainStack.addStack();
    rainHeaderStack.layoutHorizontally();
    rainHeaderStack.spacing = 8;

    const rainIcon = rainHeaderStack.addText(data.lluvia_rate > 0 ? '🌧️' : '☀️');
    rainIcon.font = Font.systemFont(18);

    const rainStatusText = rainHeaderStack.addText(
        data.lluvia_rate > 0 ? 'Está lloviendo' : 'Sin lluvia'
    );
    rainStatusText.font = Font.boldSystemFont(12);
    rainStatusText.textColor = new Color('#FFFFFF');

    // Grid de lluvia (24h, mes, año)
    const rainDetailStack = rainStack.addStack();
    rainDetailStack.layoutHorizontally();
    rainDetailStack.spacing = 8;

    // 24 horas
    const rain24hBox = rainDetailStack.addStack();
    rain24hBox.layoutVertically();
    rain24hBox.spacing = 2;
    const rain24hLabel = rain24hBox.addText('Últimas 24h');
    rain24hLabel.font = Font.systemFont(8);
    rain24hLabel.textColor = new Color('#F0F0F0');
    const rain24hValue = rain24hBox.addText(formatValue(data.lluvia_diaria, 'mm'));
    rain24hValue.font = Font.boldSystemFont(11);
    rain24hValue.textColor = new Color('#FFFFFF');

    // Mes
    const rainMonthBox = rainDetailStack.addStack();
    rainMonthBox.layoutVertically();
    rainMonthBox.spacing = 2;
    const rainMonthLabel = rainMonthBox.addText('Este mes');
    rainMonthLabel.font = Font.systemFont(8);
    rainMonthLabel.textColor = new Color('#F0F0F0');
    const rainMonthValue = rainMonthBox.addText(formatValue(data.lluvia_mes, 'mm'));
    rainMonthValue.font = Font.boldSystemFont(11);
    rainMonthValue.textColor = new Color('#FFFFFF');

    // Año
    const rainYearBox = rainDetailStack.addStack();
    rainYearBox.layoutVertically();
    rainYearBox.spacing = 2;
    const rainYearLabel = rainYearBox.addText('Este año');
    rainYearLabel.font = Font.systemFont(8);
    rainYearLabel.textColor = new Color('#F0F0F0');
    const rainYearValue = rainYearBox.addText(formatValue(data.lluvia_ano, 'mm'));
    rainYearValue.font = Font.boldSystemFont(11);
    rainYearValue.textColor = new Color('#FFFFFF');

    widget.addSpacer(8);

    // DATOS ADICIONALES - FILA 2
    const gridStack2 = widget.addStack();
    gridStack2.layoutHorizontally();
    gridStack2.spacing = 8;

    const col3 = gridStack2.addStack();
    col3.layoutVertically();
    col3.spacing = 6;
    addDataRow(col3, '☀️ Radiación', formatValue(data.radiacion_solar, 'W/m²'), colors);

    gridStack2.addSpacer();

    const col4 = gridStack2.addStack();
    col4.layoutVertically();
    col4.spacing = 6;

    addDataRow(col4, '🏠 Temp Int', formatValue(data.temperatura_interior, '°C'), colors);
    addDataRow(col4, '🏠 Hum Int', formatValue(data.humedad_interior, '%'), colors);

    widget.addSpacer(8);

    // DATOS ADICIONALES - FILA 3: Lluvia detallada
    const gridStack3 = widget.addStack();
    gridStack3.layoutHorizontally();
    gridStack3.spacing = 8;

    const col5 = gridStack3.addStack();
    col5.layoutVertically();
    col5.spacing = 6;

    addDataRow(col5, '💧 Lluvia Hora', formatValue(data.lluvia_hora, 'mm'), colors);
    addDataRow(col5, '💧 Lluvia Semana', formatValue(data.lluvia_semana, 'mm'), colors);

    gridStack3.addSpacer();

    const col6 = gridStack3.addStack();
    col6.layoutVertically();
    col6.spacing = 6;

    addDataRow(col6, '🌧️ Tasa Lluvia', formatValue(data.lluvia_rate, 'mm/h'), colors);
    addDataRow(col6, '💧 Lluvia Total', formatValue(data.lluvia_total, 'mm'), colors);

    widget.addSpacer(8);

    // FOOTER
    const footerText = widget.addText('Última actualización: ' + formatFullTime(data.timestamp));
    footerText.font = Font.systemFont(8);
    footerText.textColor = colors.secondary;

    return widget;
}

// ===== HELPER FUNCTIONS =====
function addDataRow(stack, label, value, colors) {
    const row = stack.addStack();
    row.layoutHorizontally();
    row.spacing = 4;

    const labelText = row.addText(label);
    labelText.font = Font.systemFont(10);
    labelText.textColor = colors.secondary;
    labelText.lineLimit = 1;

    row.addSpacer();

    const valueText = row.addText(value);
    valueText.font = Font.boldSystemFont(11);
    valueText.textColor = colors.text;
    valueText.lineLimit = 1;

    return row;
}

function formatValue(value, unit) {
    if (value === null || value === undefined || value === 'NULL') {
        return '--' + unit;
    }
    const numValue = parseFloat(value);
    if (isNaN(numValue)) {
        return '--' + unit;
    }
    return numValue.toFixed(1) + unit;
}

function formatUpdateTime(timestamp) {
    if (!timestamp) return '--:--';
    try {
        const date = new Date(timestamp);
        return date.toLocaleTimeString('es-ES', {
            hour: '2-digit',
            minute: '2-digit',
        });
    } catch {
        return '--:--';
    }
}

function formatFullTime(timestamp) {
    if (!timestamp) return '--';
    try {
        const date = new Date(timestamp);
        return date.toLocaleString('es-ES', {
            year: 'numeric',
            month: '2-digit',
            day: '2-digit',
            hour: '2-digit',
            minute: '2-digit',
            second: '2-digit',
        });
    } catch {
        return '--';
    }
}

function getTemperatureColor(temp, colors) {
    if (temp === null || temp === undefined) return colors.text;
    const t = parseFloat(temp);
    if (isNaN(t)) return colors.text;

    if (t < 0) return new Color('#4A90E2'); // Azul frío
    if (t < 10) return new Color('#50C878'); // Verde
    if (t < 20) return new Color('#FFD700'); // Amarillo
    if (t < 30) return new Color('#FF8C00'); // Naranja
    return new Color('#FF3B30'); // Rojo calor
}

function getRainColor(rainAmount, colors) {
    if (rainAmount === null || rainAmount === undefined) {
        return colors.accent;
    }
    const rain = parseFloat(rainAmount);
    if (isNaN(rain)) {
        return colors.accent;
    }

    if (rain > 0) {
        // Escala de azul según cantidad de lluvia
        if (rain < 1) return new Color('#B3D9FF'); // Azul claro
        if (rain < 5) return new Color('#66B3FF'); // Azul medio
        return new Color('#0066FF'); // Azul oscuro
    }

    // Sin lluvia - amarillo
    return new Color('#FFEB99');
}

// ===== ERROR WIDGET =====
function createErrorWidget(errorMessage) {
    const widget = new ListWidget();
    widget.backgroundColor = new Color('#FF3B30');
    widget.setPadding(16, 16, 16, 16);

    const errorText = widget.addText('⚠️ Error');
    errorText.font = Font.boldSystemFont(16);
    errorText.textColor = new Color('#FFFFFF');

    widget.addSpacer(8);

    const msgText = widget.addText(errorMessage);
    msgText.font = Font.systemFont(12);
    msgText.textColor = new Color('#FFFFFF');
    msgText.lineLimit = 0;

    widget.addSpacer();

    const retryText = widget.addText('Reintentando en ' + REFRESH_MINUTES + ' min...');
    retryText.font = Font.systemFont(10);
    retryText.textColor = new Color('#E8E8E8');

    return widget;
}
