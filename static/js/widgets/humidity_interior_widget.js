/* humidity_interior_widget.js */
function updateHumidityIntWidget() {
    fetch('./static/modules/widgets/get_humidity_interior_data.php')
        .then(response => response.json())
        .then(data => {
            if (data.error) {
                console.error("Error Consulta de Humedad INterior:", data.message);
                return;
            }

            // Actualizar valor numérico y leyenda
            document.getElementById('humidity-int-widget-main-display').textContent = data.humidity_int;
            document.getElementById('humidity-int-widget-text-display').textContent = data.legend_int;

            // Actualizar ángulo y color del gráfico
            const gauge = document.getElementById('humidity-int-gauge-bg');
            if (gauge) {
                gauge.style.setProperty('--humidity-int-gauge-bg',
                    `conic-gradient(from 270deg, rgba(var(${data.color_int}),0.8) 0deg, rgba(var(${data.color_int}),0.8) ${data.angle_int}deg, rgba(var(--black-or-white),0.1) ${data.angle_int}deg, rgba(var(--black-or-white),0.1) 360deg)`
                );
            }

            // Actualizar clase del widget según estado
            const widgetView = document.querySelector('humidity-int-widget-view');
            if (widgetView) {
                widgetView.setAttribute('data-humidity', data.humidity_int);
                widgetView.setAttribute('data-humidity-string', data.legend_int);
                widgetView.setAttribute('data-main-value', data.humidity_int);
                widgetView.setAttribute('aria-valuenow', data.humidity_int);
                widgetView.setAttribute('data-secondary-value', data.legend_int);
                widgetView.className = `widget-view ${data.state_int} loaded`;
            }
        })
        .catch(err => console.error('Error al actualizar humedad:', err));
}

// Primera actualización inmediata
updateHumidityIntWidget();

// Actualizar cada minuto (60000 ms). Se actualiza desde update_status.js
//setInterval(updateHumidityIntWidget, 60000);
