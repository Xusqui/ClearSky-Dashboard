// temp_interior_widget.js

async function updateTempInteriorWidget() {
    try {
        const response = await fetch("./static/modules/widgets/get_temp_interior_data.php");
        const data = await response.json();

        if (data.error) {
            console.error("Error obteniendo temperatura interior:", data.error);
            return;
        }

        const temp = data.temp;
        const angle = data.angle;

        // Actualizar valor absoluto
        const tempMain = document.getElementById("temp-int-widget-main-display");
        if (tempMain) tempMain.textContent = temp;

        // Actualizar aguja
        const needle = document.getElementById("temp-int-widget-needle");
        if (needle) needle.style.transform = `translate(-50%, -100%) rotate(${angle}deg)`;

        // Actualizar atributos del widget
        const widget = document.querySelector("temp-widget-view");
        if (widget) {
            widget.setAttribute("data-temp", temp);
            widget.setAttribute("data-temp-angle", angle);
            widget.setAttribute("data-main-value", temp);
            widget.setAttribute("aria-valuenow", temp);
        }

    } catch (e) {
        console.error("Error en updateTempInteriorWidget:", e);
    }
}

// Primera carga
updateTempInteriorWidget();

// Actualizar cada minuto
setInterval(updateTempInteriorWidget, 1 * 60 * 1000);
