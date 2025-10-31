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

// Abrir modal de humedad al hacer click en el widget correspondiente
document.getElementById("humint_widget").addEventListener("click", function () {
    var modal = document.getElementById("humIntModal");
    modal.style.display = "block";

    // --- NUEVO: Establecer fechas por defecto (últimas 24h) ---
    var now = new Date();
    var yesterday = new Date(now.getTime() - 24 * 60 * 60 * 1000);

    // Usamos los IDs específicos de este modal
    var startInput = document.getElementById("humInt_startDate");
    var endInput = document.getElementById("humInt_endDate");

    startInput.value = formatLocalDateTime(yesterday);
    endInput.value = formatLocalDateTime(now);
    // -----------------------------------------------------

    // Cargar gráfico inicial con las fechas por defecto
    loadHumIntChart(startInput.value, endInput.value);
});

// Cerrar modal al hacer click en el botón de cerrar
document.getElementById("closeHumIntModal").addEventListener("click", function () {
    closeHumIntModal();
});

// Cerrar modal al hacer click fuera del contenido
window.addEventListener("click", function (event) {
    var modal = document.getElementById("humIntModal");
    if (event.target === modal) {
        closeHumIntModal();
    }
});

// --- NUEVO: Event Listener para el botón de actualizar ---
document.getElementById("humInt_updateChartBtn").addEventListener("click", function() {
    var startDate = document.getElementById("humInt_startDate").value;
    var endDate = document.getElementById("humInt_endDate").value;

    if (!startDate || !endDate) {
        alert("Por favor, selecciona un rango de fechas y horas válido.");
        return;
    }
    if (new Date(startDate) >= new Date(endDate)) {
        alert("La fecha de inicio debe ser anterior a la fecha de fin.");
        return;
    }

    // Volver a cargar el gráfico con el nuevo rango
    loadHumIntChart(startDate, endDate);
});
// ---------------------------------------------------

// Función para cerrar modal y destruir gráfico
function closeHumIntModal() {
    var modal = document.getElementById("humIntModal");
    modal.style.display = "none";

    var chartDom = document.getElementById("humIntChart");
    var myChart = echarts.getInstanceByDom(chartDom);
    if (myChart) {
        myChart.dispose(); // destruye la instancia de ECharts
    }
}

// Función para cargar datos y dibujar gráfico de humedad
// --- MODIFICADO: Acepta parámetros startDate y endDate ---
function loadHumIntChart(startDate, endDate) {
    var chartDom = document.getElementById("humIntChart");

    // --- MODIFICADO: Destruir gráfico anterior si existe ---
    var myChart = echarts.getInstanceByDom(chartDom);
    if (myChart) {
        myChart.dispose();
    }
    myChart = echarts.init(chartDom);
    // ---------------------------------------------------

    // Obtener colores del CSS
    var rootStyle = getComputedStyle(document.documentElement);
    var fontColor = rootStyle.getPropertyValue("--font-color").trim();
    var bgColor = rootStyle.getPropertyValue("--bg-color").trim();
    var blueColor = rootStyle.getPropertyValue("--wu-purple").trim();
    var blueLight = rootStyle.getPropertyValue("--wu-lightblue").trim();
    var darkBlue = rootStyle.getPropertyValue("--wu-darkblue").trim();

    // --- MODIFICADO: Construir la URL de fetch dinámicamente ---
    var fetchUrl = "./static/modules/modals/get_humint_historic.php";
    if (startDate && endDate) {
        fetchUrl += `?start=${encodeURIComponent(startDate)}&end=${encodeURIComponent(endDate)}`;
    }
    // -----------------------------------------------------------

    // Mostrar "cargando"
    myChart.showLoading({
        text: 'Cargando datos...',
        color: blueColor, // Color del tema
        textColor: fontColor,
        maskColor: 'rgba(255, 255, 255, 0.1)'
    });

    fetch(fetchUrl)
        .then((response) => response.json())
        .then((data) => {
        // Ocultar "cargando"
        myChart.hideLoading();

        if (data.error) {
            console.error(data.message);
            return;
        }

        if (data.length === 0) {
            chartDom.innerHTML = `<p style="text-align:center; color:${fontColor}; padding-top: 50px;">No hay datos disponibles para el rango seleccionado.</p>`;
            return;
        }

        var labels = data.map((row) => row.hora);
        var humedad_interior = data.map((row) => parseFloat(row.humedad_interior));

        // Escala Y dinámica, acotada a 0-100%
        var minY = Math.min(...humedad_interior) - 5;
        var maxY = Math.max(...humedad_interior) + 5;
        if (minY < 0) minY = 0;
        if (maxY > 100) maxY = 100;

        var option = {
            backgroundColor: bgColor,
            tooltip: {
                trigger: "axis",
                backgroundColor: bgColor,
                textStyle: { color: fontColor }
            },
            legend: {
                data: ["Humedad Interior"],
                textStyle: { color: fontColor }
            },

            // --- NUEVO: DataZoom para hacer zoom/scroll ---
            dataZoom: [
                {
                    type: 'inside',
                    start: 0,
                    end: 100
                },
                {
                    type: 'slider',
                    start: 0,
                    end: 100,
                    backgroundColor: 'rgba(0,0,0,0.1)',
                    borderColor: '#777',
                    fillerColor: 'rgba(128, 0, 128, 0.2)', // Color púrpura/azul del tema
                    handleStyle: {
                        color: blueColor
                    },
                    textStyle: {
                        color: fontColor
                    }
                }
            ],
            // ------------------------------------------------

            xAxis: {
                type: "category",
                data: labels,
                axisLine: { lineStyle: { color: fontColor } },
                axisLabel: { color: fontColor }
            },
            yAxis: {
                type: "value",
                name: "%",
                min: minY.toFixed(1),
                max: maxY.toFixed(1),
                axisLine: { lineStyle: { color: fontColor } },
                axisLabel: { color: fontColor }
            },
            series: [
                {
                    name: "Humedad Interior",
                    data: humedad_interior,
                    type: "line",
                    smooth: true,
                    lineStyle: { width: 2, color: blueColor },
                    markPoint: {
                        data: [
                            { type: "max", name: "Máx", itemStyle: { color: blueLight } },
                            { type: "min", name: "Mín", itemStyle: { color: darkBlue } }
                        ]
                    }
                }
            ]
        };

        myChart.setOption(option);
    })
        .catch((err) => {
        myChart.hideLoading();
        console.error("Error al cargar datos de humedad:", err)
    });
}
