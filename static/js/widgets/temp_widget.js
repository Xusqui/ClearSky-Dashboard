/* /static/js/widgets/temp_widget.js */
function updateTempWidget() {
    fetch('./static/modules/widgets/get_temp_data.php')
        .then(response => response.json())
        .then(data => {
        if (data.error) {
            console.error("Error Home Assistant:", data.message);
            return;
        }
        // Modificar los datos del temp-widget-view:
        // Seleccionamos el widget
        const tempWidget = document.querySelector('temp-widget-view');
        const dewWidget = document.querySelector('dew-point-widget-view');

        tempWidget.setAttribute('data-temp', `${data.temp}`);
        tempWidget.setAttribute('data-main-value', `${data.temp}`);
        tempWidget.setAttribute('aria-valuenow', `${data.temp}`);
        tempWidget.setAttribute('data-temp-angle', `${data.angle}`);
        dewWidget.setAttribute('data-temp', `${data.temp}`);

        // Valores principales
        document.getElementById('temp-widget-main-display').textContent = data.temp;
        document.getElementById('temp-widget-feel-display').textContent = `Sensación: ${data.feels_like}`;

        // Rotar aguja
        document.getElementById('temp-widget-needle').style.transform = `translate(-50%, -100%) rotate(${data.angle}deg)`;
        const trendEl = document.getElementById('temp-widget-trend');
        if (trendEl && data.trend) {
            let text = '';
            let cls  = '';

            if (data.trend === 'up') {
                text = 'Subiendo';
                icon = '▲';
                cls  = 'trend-hot';
            } else if (data.trend === 'down') {
                text = 'Bajando';
                icon = '▼';
                cls  = 'trend-cold';
            } else {
                text = 'Estable';
                icon = '●';
                cls  = 'trend-stable';
            }

            trendEl.textContent = `${text} ${icon}`;
            trendEl.className = `temp-trend ${cls}`;
        }
    })
        .catch(err => console.error('Error al actualizar temperatura:', err));
}

// Primera actualización inmediata
updateTempWidget();
