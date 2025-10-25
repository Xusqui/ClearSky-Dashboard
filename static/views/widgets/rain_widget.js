let rainInterval = null;

function updateRainWidget() {
    fetch('./static/modules/get_rain_data.php')
        .then(response => response.json())
        .then(data => {
            if (data.error) {
                console.error("Error Home Assistant:", data.message);
                scheduleNextUpdate(300000); // reintentar en 5 min si falla
                return;
            }
            // Actualizar valores numéricos
            document.getElementById('rain-widget-main-display').textContent = data.daily_rain;
            document.getElementById('rain-widget-secondary-display').textContent = data.rain_rate;
            document.getElementById('widget_de_lluvia').setAttribute("data-precip-rate", data.rain_rate);

            // Actualizar pluviómetro
            const fill = document.getElementById('precip-bucket-fill');
            const top  = document.getElementById('precip-bucket-top');
            const bottom = document.getElementById('precip-bucket-bottom');

            if (fill) fill.setAttribute('y', data.water_start);
            if (fill) fill.setAttribute('height', data.heigh);
            if (top) top.setAttribute('cy', data.water_start);
            if (top) {
                top.setAttribute('stroke', data.stroke_bucket_top);
                top.setAttribute('fill', data.fill_bucket_top);
            }
            if (bottom) bottom.setAttribute('fill', data.fill_bucket_bottom);

            // Determinar intervalo dinámico
            if (data.daily_rain > 0 || data.rain_rate > 0) {
                scheduleNextUpdate(20000); // 20 segundos si llueve
            } else {
                scheduleNextUpdate(300000); // 5 minutos si no llueve
            }
        })
        .catch(err => {
            console.error('Error al actualizar precipitación:', err);
            scheduleNextUpdate(300000); // reintentar en 5 min si hay error
        });
}

// Función para programar la siguiente actualización
function scheduleNextUpdate(ms) {
    if (rainInterval) clearTimeout(rainInterval);
    rainInterval = setTimeout(updateRainWidget, ms);
}

// Primera actualización inmediata
updateRainWidget();
