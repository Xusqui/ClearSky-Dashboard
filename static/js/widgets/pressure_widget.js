/* pressure_widget.js */
function fetchPressureData() {
    fetch("./static/modules/widgets/get_pressure_data.php")
        .then(response => response.json())
        .then(data => {
            const pressure = parseFloat(data.pressure);

            // Actualizar el valor absoluto
            document.getElementById("pressure-widget-main-display").textContent = pressure;

            // Calcular el ángulo de la aguja
            const minPres = 950;
            const maxPres = 1050;
            const minAnglePres = -134;
            const maxAnglePres = 134;

            const pressureAngle = (pressure - minPres) * (maxAnglePres - minAnglePres) / (maxPres - minPres) + minAnglePres;

            // Actualizar la aguja
            document.getElementById("pressure-widget-needle").style.transform =
                `translate(-50%, -100%) rotate(${pressureAngle}deg)`;

            // Actualizar data-pressure-angle en el widget
            document.querySelector("pressure-widget-view").setAttribute("data-pressure-angle", pressureAngle);
        })
        .catch(error => console.error("Error al obtener presión:", error));
}

// Actualización inmediata al cargar
fetchPressureData();

// Refresco automático cada minuto. Se actualiza desde update_status.js
//setInterval(fetchPressureData, 1 * 60 * 1000);
