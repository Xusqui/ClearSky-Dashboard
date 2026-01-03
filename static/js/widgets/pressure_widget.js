/* /static/js/widgets/pressure_widget.js */
function fetchPressureData() {
    fetch("./static/modules/widgets/get_pressure_data.php")
        .then(response => response.json())
        .then(data => {
        const pressure = parseFloat(data.pressure);
        const press_abs = parseFloat(data.pressure_abs);

        // Actualizar el valor absoluto
        document.getElementById("pressure-widget-main-display").textContent = pressure;

        const pressureAngle = data.pres_angle;

        // Actualizar la aguja
        document.getElementById("pressure-widget-needle").style.transform =
            `translate(-50%, -100%) rotate(${pressureAngle}deg)`;

        // Actualizar data-pressure-angle en el widget
        document.querySelector("pressure-widget-view").setAttribute("data-pressure-angle", pressureAngle);

        // Actualizar presión absoluta
        document.getElementById("pressure-widget-secondary-display").textContent = `Abs: ${press_abs}`;

        const trendEl = document.getElementById("pressure-widget-trend");

        if (trendEl && data.trend) {
            let text = '';
            let cls  = '';

            if (data.trend === 'up') {
                text = 'Subiendo';
                icon = '▲';
                cls  = 'trend-high';
            } else if (data.trend === 'down') {
                text = 'Bajando';
                icon = '▼';
                cls  = 'trend-low';
            } else {
                text = 'Estable';
                icon = '●';
                cls  = 'trend-stable';
            }

            trendEl.textContent = `${text} ${icon}`;
            trendEl.className = `pressure-trend ${cls}`;
        }
    })
        .catch(error => console.error("Error al obtener presión:", error));
}

// Actualización inmediata al cargar
fetchPressureData();
