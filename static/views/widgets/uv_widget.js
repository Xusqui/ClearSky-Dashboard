// uv_widget.js

async function updateUVWidget() {
    try {
        const response = await fetch("./static/modules/get_uv_data.php");
        const data = await response.json();

        if (data.error) {
            console.error("Error obteniendo datos UV:", data.error);
            return;
        }

        const uvValue = data.uv;
        const uvCategory = data.category;

        // Actualizar valores
        const uvMain = document.getElementById("uv-widget-main-display");
        const uvSecondary = document.getElementById("uv-widget-secondary-display");
        const uvWidget = document.querySelector("uv-widget-view");

        if (uvMain) uvMain.textContent = uvValue;
        if (uvSecondary) uvSecondary.textContent = uvCategory;

        if (uvWidget) {
            uvWidget.setAttribute("data-uv", uvValue);
            uvWidget.setAttribute("data-main-value", uvValue);
            uvWidget.setAttribute("aria-valuenow", uvValue);
            uvWidget.setAttribute("data-secondary-value", uvCategory);
        }

        // Actualizar barras del triángulo
        for (let i = 1; i <= 13; i++) {
            const bar = document.getElementById("Fill-" + i);
            if (bar) {
                if (i <= uvValue) {
                    bar.classList.remove("empty");
                } else {
                    bar.classList.add("empty");
                }
            }
        }
    } catch (e) {
        console.error("Error en updateUVWidget:", e);
    }
}

// Primera carga
updateUVWidget();

// Actualizar cada minuto (60000 ms)
setInterval(updateUVWidget, 1 * 60 * 1000);
