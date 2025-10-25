function updateHumidityWidget() {
    fetch('./static/modules/get_humidity_interior_data.php')
        .then(response => response.json())
        .then(data => {
            if (data.error) {
                console.error("Error Home Assistant:", data.message);
                return;
            }

            // Actualizar valor numérico y leyenda
            document.getElementById('humidity-int-widget-main-display').textContent = data.humidity;
            document.getElementById('humidity-int-widget-text-display').textContent = data.legend;

            // Actualizar ángulo y color del gráfico
            const gauge = document.getElementById('humidity-int-gauge-bg');
            if (gauge) {
                gauge.style.setProperty('--humidity-int-gauge-bg', 
                    `conic-gradient(from 270deg, rgba(var(${data.color}),0.8) 0deg, rgba(var(${data.color}),0.8) ${data.angle}deg, rgba(var(--black-or-white),0.1) ${data.angle}deg, rgba(var(--black-or-white),0.1) 360deg)`
                );
            }

            // Actualizar clase del widget según estado
            const widgetView = document.querySelector('humidity-int-widget-view');
            if (widgetView) {
                widgetView.className = `widget-view ${data.state} loaded`;
            }
        })
        .catch(err => console.error('Error al actualizar humedad:', err));
}

// Primera actualización inmediata
updateHumidityWidget();

// Actualizar cada minuto (60000 ms)
setInterval(updateHumidityWidget, 60000);
