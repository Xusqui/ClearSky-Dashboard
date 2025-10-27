// --- NUEVA FUNCIÓN UTILITARIA ---
// Formatea un objeto Date al formato 'YYYY-MM-DDTHH:MM' que usa datetime-local
function formatLocalDateTime(date) {
    const pad = (num) => (num < 10 ? '0' + num : num);
    const year = date.getFullYear();
    const month = pad(date.getMonth() + 1); // getMonth() es 0-indexado
    const day = pad(date.getDate());
    const hours = pad(date.getHours());
    const minutes = pad(date.getMinutes());
    return `${year}-${month}-${day}T${hours}:${minutes}`;
}
// ---------------------------------

// Abrir modal al hacer click en el widget
document.getElementById("tempint_widget").addEventListener("click", function () {
    var modal = document.getElementById("tempIntModal");
    modal.style.display = "block";

    // --- NUEVO: Establecer fechas por defecto (últimas 24h) ---
    var now = new Date();
    var yesterday = new Date(now.getTime() - 24 * 60 * 60 * 1000);

    document.getElementById("startDate").value = formatLocalDateTime(yesterday);
    document.getElementById("endDate").value = formatLocalDateTime(now);
    // -----------------------------------------------------

    // Cargar gráfico inicial (con las fechas por defecto o las últimas 24h)
    loadTempIntChart(formatLocalDateTime(yesterday), formatLocalDateTime(now));
});

// Cerrar modal al hacer click en el botón de cerrar
document.getElementById("closeTempIntModal").addEventListener("click", function () {
    closeTempIntModal();
});

// Cerrar modal al hacer click fuera del contenido
window.addEventListener("click", function (event) {
    var modal = document.getElementById("tempIntModal");
    if (event.target === modal) {
        closeTempIntModal();
    }
});

// --- NUEVO: Event Listener para el botón de actualizar ---
document.getElementById("updateChartBtn").addEventListener("click", function() {
    var startDate = document.getElementById("startDate").value;
    var endDate = document.getElementById("endDate").value;

    if (!startDate || !endDate) {
        alert("Por favor, selecciona un rango de fechas y horas válido.");
        return;
    }
    if (new Date(startDate) >= new Date(endDate)) {
        alert("La fecha de inicio debe ser anterior a la fecha de fin.");
        return;
    }

    // Volver a cargar el gráfico con el nuevo rango
    loadTempIntChart(startDate, endDate);
});
// ---------------------------------------------------

// Función para cerrar modal y destruir gráfico
function closeTempIntModal() {
    var modal = document.getElementById("tempIntModal");
    modal.style.display = "none";

    var chartDom = document.getElementById("tempIntChart");
    var myChart = echarts.getInstanceByDom(chartDom);
    if (myChart) {
        myChart.dispose(); // destruye la instancia de ECharts
    }
}

// Función para cargar datos y dibujar gráfico
// --- MODIFICADO: Acepta parámetros startDate y endDate ---
function loadTempIntChart(startDate, endDate) {
    var chartDom = document.getElementById("tempIntChart");

    // --- MODIFICADO: Destruir gráfico anterior si existe ANTES de inicializar uno nuevo ---
    // Esto es crucial para que el botón "Actualizar" funcione
    var myChart = echarts.getInstanceByDom(chartDom);
    if (myChart) {
        myChart.dispose();
    }
    myChart = echarts.init(chartDom);
    // ---------------------------------------------------------------------------------

    var rootStyle = getComputedStyle(document.documentElement);
    var fontColor = rootStyle.getPropertyValue("--font-color").trim();
    var bgColor = rootStyle.getPropertyValue("--bg-color").trim();
    var redColor = rootStyle.getPropertyValue("--wu-red").trim();
    // (Omitido el resto de colores por brevedad, ya los tienes)

    // --- MODIFICADO: Construir la URL de fetch dinámicamente ---
    var fetchUrl = "./static/modules/get_tempint_last24h.php";
    if (startDate && endDate) {
        // Añadir parámetros a la URL
        fetchUrl += `?start=${encodeURIComponent(startDate)}&end=${encodeURIComponent(endDate)}`;
    }
    // -----------------------------------------------------------

    // Mostrar un "cargando" mientras se obtienen los datos (opcional pero recomendado)
    myChart.showLoading({
        text: 'Cargando datos...',
        color: redColor,
        textColor: fontColor,
        maskColor: 'rgba(255, 255, 255, 0.1)'
    });

    fetch(fetchUrl)
        .then((response) => response.json())
        .then((data) => {
        // Ocultar el "cargando"
        myChart.hideLoading();

        if (data.error) {
            console.error(data.message);
            // Aquí podrías mostrar un error en el gráfico
            return;
        }

        if (data.length === 0) {
            // Mostrar mensaje si no hay datos en ese rango
            chartDom.innerHTML = `<p style="text-align:center; color:${fontColor}; padding-top: 50px;">No hay datos disponibles para el rango seleccionado.</p>`;
            return;
        }

        var labels = data.map((row) => row.hora);
        var temperaturas = data.map((row) => parseFloat(row.temperatura_interior));

        // Escala Y dinámica
        var minY = Math.min(...temperaturas) - 2;
        var maxY = Math.max(...temperaturas) + 2;

        var option = {
            backgroundColor: bgColor,
            tooltip: { trigger: "axis", backgroundColor: bgColor, textStyle: { color: fontColor } },
            legend: { data: ["Temperatura Interior"], textStyle: { color: fontColor } },

            // --- NUEVO: DataZoom para hacer zoom/scroll si hay muchos datos ---
            dataZoom: [
                {
                    type: 'inside', // Permite hacer zoom con la rueda del ratón
                    start: 0,
                    end: 100
                },
                {
                    type: 'slider', // Muestra una barra de scroll inferior
                    start: 0,
                    end: 100,
                    backgroundColor: 'rgba(0,0,0,0.1)',
                    borderColor: '#777',
                    fillerColor: 'rgba(255, 0, 0, 0.2)', // Color rojo de tu tema
                    handleStyle: {
                        color: redColor
                    },
                    textStyle: {
                        color: fontColor
                    }
                }
            ],
            // -----------------------------------------------------------------

            xAxis: {
                type: "category",
                data: labels,
                axisLine: { lineStyle: { color: fontColor } },
                axisLabel: { color: fontColor }
            },
            yAxis: {
                type: "value",
                name: "°C",
                min: minY.toFixed(1), // Asegurar que los límites sean fijos
                max: maxY.toFixed(1),
                axisLine: { lineStyle: { color: fontColor } },
                axisLabel: { color: fontColor }
            },
            series: [
                {
                    name: "Temperatura Interior",
                    data: temperaturas,
                    type: "line",
                    smooth: true,
                    lineStyle: { width: 2, color: redColor },
                    markPoint: {
                        data: [
                            { type: "max", name: "Máx", itemStyle: { color: "darkred" } },
                            { type: "min", name: "Mín", itemStyle: { color: "orange" } }
                        ]
                    }
                }
            ]
        };

        myChart.setOption(option);
    })
        .catch((err) => {
        myChart.hideLoading();
        console.error("Error al cargar datos:", err)
    });
}
