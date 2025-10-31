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

// Abrir modal al hacer click en los widgets
document.getElementById("temp_widget").addEventListener("click", openTempModal);
document.getElementById("dew_point").addEventListener("click", openTempModal);

function openTempModal() {
    var modal = document.getElementById("tempModal");
    modal.style.display = "block";

    // --- NUEVO: Establecer fechas por defecto (últimas 24h) ---
    var now = new Date();
    var yesterday = new Date(now.getTime() - 24 * 60 * 60 * 1000);

    // Usamos los IDs específicos de este modal
    var startInput = document.getElementById("temp_startDate");
    var endInput = document.getElementById("temp_endDate");

    startInput.value = formatLocalDateTime(yesterday);
    endInput.value = formatLocalDateTime(now);
    // -----------------------------------------------------

    // Cargar gráfico inicial con las fechas por defecto
    loadTempChart(startInput.value, endInput.value);
}

// Cerrar modal al hacer click en el botón de cerrar
document.getElementById("closeModal").addEventListener("click", function () {
    closeTempModal();
});

// Cerrar modal al hacer click fuera del contenido
window.addEventListener("click", function (event) {
    var modal = document.getElementById("tempModal");
    if (event.target === modal) {
        closeTempModal();
    }
});

// --- NUEVO: Event Listener para el botón de actualizar ---
document.getElementById("temp_updateChartBtn").addEventListener("click", function() {
    var startDate = document.getElementById("temp_startDate").value;
    var endDate = document.getElementById("temp_endDate").value;

    if (!startDate || !endDate) {
        alert("Por favor, selecciona un rango de fechas y horas válido.");
        return;
    }
    if (new Date(startDate) >= new Date(endDate)) {
        alert("La fecha de inicio debe ser anterior a la fecha de fin.");
        return;
    }

    // Volver a cargar el gráfico con el nuevo rango
    loadTempChart(startDate, endDate);
});
// ---------------------------------------------------

// Función para cerrar modal y destruir gráfico
function closeTempModal() {
    var modal = document.getElementById("tempModal");
    modal.style.display = "none";

    var chartDom = document.getElementById("tempChart");
    var myChart = echarts.getInstanceByDom(chartDom);
    if (myChart) {
        myChart.dispose(); // destruye la instancia de ECharts
    }
}

// Función para cargar datos y dibujar gráfico
// --- MODIFICADO: Acepta parámetros startDate y endDate ---
function loadTempChart(startDate, endDate) {
    var chartDom = document.getElementById("tempChart");

    // --- MODIFICADO: Destruir gráfico anterior si existe ---
    var myChart = echarts.getInstanceByDom(chartDom);
    if (myChart) {
        myChart.dispose();
    }
    myChart = echarts.init(chartDom);
    // ---------------------------------------------------

    var rootStyle = getComputedStyle(document.documentElement);
    var fontColor = rootStyle.getPropertyValue("--font-color").trim();
    var bgColor = rootStyle.getPropertyValue("--bg-color").trim();
    var redColor = rootStyle.getPropertyValue("--wu-red").trim();
    var greenColor = rootStyle.getPropertyValue("--wu-green").trim();
    var lightBlue = rootStyle.getPropertyValue("--wu-lightblue").trim();
    var lightBlue80 = rootStyle.getPropertyValue("--wu-lightblue80").trim();

    // --- MODIFICADO: Construir la URL de fetch dinámicamente ---
    var fetchUrl = "./static/modules/modals/get_temp_historic.php";
    if (startDate && endDate) {
        fetchUrl += `?start=${encodeURIComponent(startDate)}&end=${encodeURIComponent(endDate)}`;
    }
    // -----------------------------------------------------------

    // Mostrar "cargando"
    myChart.showLoading({
        text: 'Cargando datos...',
        color: redColor, // Usa uno de tus colores
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
            var temperaturas = data.map((row) => parseFloat(row.temperatura));
            var sensaciones = data.map((row) => parseFloat(row.sensacion_termica));
            var puntosRocio = data.map((row) => parseFloat(row.punto_rocio));

            // Escala Y dinámica
            var todos = temperaturas.concat(sensaciones, puntosRocio);
            var minY = Math.min(...todos) - 2;
            var maxY = Math.max(...todos) + 2;

            var option = {
                backgroundColor: bgColor,
                tooltip: { trigger: "axis", backgroundColor: bgColor, textStyle: { color: fontColor } },
                legend: { data: ["Temperatura", "Sensación térmica", "Punto de rocío"], textStyle: { color: fontColor } },

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
                        fillerColor: 'rgba(255, 0, 0, 0.2)', // Color rojo de tu tema
                        handleStyle: {
                            color: redColor
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
                    name: "°C",
                    min: minY.toFixed(1),
                    max: maxY.toFixed(1),
                    axisLine: { lineStyle: { color: fontColor } },
                    axisLabel: { color: fontColor }
                },
                series: [
                    {
                        name: "Temperatura",
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
                    },
                    {
                        name: "Sensación térmica",
                        data: sensaciones,
                        type: "line",
                        smooth: true,
                        lineStyle: { width: 2, color: greenColor },
                        markPoint: {
                            data: [
                                { type: "max", name: "Máx", itemStyle: { color: "darkgreen" } },
                                { type: "min", name: "Mín", itemStyle: { color: "lightgreen" } }
                            ]
                        }
                    },
                    {
                        name: "Punto de rocío",
                        data: puntosRocio,
                        type: "line",
                        smooth: true,
                        lineStyle: { width: 2, color: lightBlue80 },
                        areaStyle: { color: lightBlue }, // relleno debajo de la línea
                        markPoint: {
                            data: [
                                { type: "max", name: "Máx", itemStyle: { color: lightBlue80 } },
                                { type: "min", name: "Mín", itemStyle: { color: lightBlue80 } }
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
