function updateTempWidget() {
    fetch('/weather/static/modules/get_temp_data.php')
        .then(response => response.json())
        .then(data => {
            if (data.error) {
                console.error("Error Home Assistant:", data.message);
                return;
            }

            // Valores principales
            document.getElementById('temp-widget-main-display').textContent = data.temp;
            document.getElementById('temp-widget-feel-display').textContent = `Sensación: ${data.feels_like}`;

            // Rotar aguja
            document.getElementById('temp-widget-needle').style.transform = `translate(-50%, -100%) rotate(${data.angle}deg)`;
        })
        .catch(err => console.error('Error al actualizar temperatura:', err));
}

// Primera actualización inmediata
updateTempWidget();

// Actualizar cada minutos (60000 ms)
setInterval(updateTempWidget, 1 * 60 * 1000);
