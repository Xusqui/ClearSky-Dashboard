function updateDewWidget() {
    fetch('./static/modules/get_dew_data.php')
        .then(response => response.json())
        .then(data => {
            if (data.error) {
                console.error("Error Home Assistant:", data.message);
                return;
            }

            // Actualizar valor del punto de rocío
            document.getElementById('dewpoint-widget-main-display').textContent = data.dew;

            // Actualizar tamaño de la gota
            const dewWidget = document.querySelector('dew-point-widget-view');
            if (dewWidget) {
                dewWidget.style.setProperty('--dewpoint-droplet-width', `${data.percent}%`);
            }
        })
        .catch(err => console.error('Error al actualizar punto de rocío:', err));
}

// Primera actualización inmediata
updateDewWidget();

// Actualizar cada 1 minuto (60000 ms)
setInterval(updateDewWidget, 60000);
