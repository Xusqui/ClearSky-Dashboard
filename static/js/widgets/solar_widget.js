// solar_widget.js

async function updateSolarWidget() {
    try {
        const response = await fetch("./static/modules/widgets/get_solar_data.php");
        const data = await response.json();

        if (data.error) {
            console.error("Error obteniendo radiación solar:", data.error);
            return;
        }

        const solarValue = data.solar;
        const percentage = data.percentage;

        // Actualizar valor absoluto
        const solarMain = document.getElementById("solar-radiation-widget-main-display");
        if (solarMain) solarMain.textContent = solarValue;

        // Actualizar círculo interior y anillo
        const innerCircle = document.getElementById("solar-radiation-widget-inner-circle");
        const ring = document.getElementById("solar-radiation-widget-ring");

        if (innerCircle) innerCircle.style.width = percentage + "%";
        if (ring) ring.style.width = percentage + "%";

        // Actualizar atributos del widget
        const widget = document.querySelector("solar-radiation-widget-view");
        if (widget) {
            widget.setAttribute("data-solar-radiation", solarValue);
            widget.setAttribute("data-main-value", solarValue);
            widget.setAttribute("aria-valuenow", solarValue);
            widget.setAttribute("data-secondary-value", solarValue);
        }

    } catch (e) {
        console.error("Error en updateSolarWidget:", e);
    }
}

// Primera carga
updateSolarWidget();

// Cada 60 segundos.
setInterval(updateSolarWidget, 1 * 60 * 1000);
