let secondsSinceUpdate = 0;
let updateTimer = null;

// Función que actualiza el contador en pantalla
function startUpdateTimer() {
    // Si ya hay un contador en marcha, lo reiniciamos
    if (updateTimer) {
        clearInterval(updateTimer);
    }
    secondsSinceUpdate = 0;
    document.getElementById("pws-status-time-ago").textContent = "Actualizado hace 0 sec";

    // Iniciamos un contador que suma cada segundo
    updateTimer = setInterval(() => {
        secondsSinceUpdate++;
        document.getElementById("pws-status-time-ago").textContent =
            "Actualizado hace " + secondsSinceUpdate + " sec";
    }, 1000);
}

function updateWindWidget() {
    fetch("./static/modules/widgets/get_wind_data.php")
        .then((response) => response.json())
        .then((data) => {
            if (data.error) {
                console.error("Error Home Assistant:", data.message);
                return;
            }

            // --- Categoría del viento ---
            let windCategory = "";
            let windSpeed = data.wind !== null ? parseFloat(data.wind) : 0;
            if (windSpeed == 0) windCategory = "";
            else if (windSpeed <= 10) windCategory = "gentle";
            else if (windSpeed <= 25) windCategory = "moderate";
            else windCategory = "strong";

            // Actualizar los datos dentro de "<wind-widget-view>"
            document.getElementById("wind-widget-view").setAttribute("data-wind-speed", data.wind);
            document.getElementById("wind-widget-view").setAttribute("data-wind-gust", data.gust);
            document.getElementById("wind-widget-view").setAttribute("data-wind-dir", data.wind_dir);
            document.getElementById("wind-widget-view").setAttribute("data-main-value", data.wind);
            document.getElementById("wind-widget-view").setAttribute("aria-valuenow", data.wind);
            document.getElementById("wind-widget-view").setAttribute("data-secondary-value", data.gust);
            document.getElementById("wind-widget-view").setAttribute("data-description", windCategory);
            // Actualizar valores principales
            document.getElementById("wind-widget-main-display").textContent = data.wind;
            document.getElementById("wind-widget-secondary-display").textContent = data.gust;

            // Dirección del viento
            let deg = data.wind_dir !== null ? parseFloat(data.wind_dir) : 0;
            let dirText = data.wind_dir !== null ? `${deg}° ${data.wind_direction}` : data.wind_direction || "N";
            document.getElementById("wind-widget-tertiary-value").textContent = dirText;

            document.getElementById("wind-arrow-pointer-wrapper").style.transform = `rotate(${deg}deg)`;
            document.getElementById("wind-widget-lines").style.transform = `rotate(${deg}deg)`;

            // Máximo del día
            let gustMaxText = data.gust_max !== null ? `Máx: ${data.gust_max}` : "Máx: --";
            document.getElementById("wind-widget-cuaternary-value").textContent = gustMaxText;

            // Reiniciar el contador de segundos desde última actualización exitosa
            startUpdateTimer();
        })
        .catch((err) => console.error("Error al actualizar viento:", err));
}

// Primera actualización inmediata
updateWindWidget();

// Actualizar cada 60 segundos
setInterval(updateWindWidget, 1 * 60 * 1000);
