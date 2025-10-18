// Abrir modal al hacer click en el widget

document.getElementById("temp_widget").addEventListener("click", openTempModal);
document.getElementById("dew_point").addEventListener("click", openTempModal);

function openTempModal() {
    var modal = document.getElementById("tempModal");
    modal.style.display = "block";
    loadTempChart();
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
function loadTempChart() {
    var chartDom = document.getElementById("tempChart");
    var myChart = echarts.init(chartDom);

    var rootStyle = getComputedStyle(document.documentElement);
    var fontColor = rootStyle.getPropertyValue("--font-color").trim();
    var bgColor = rootStyle.getPropertyValue("--bg-color").trim();
    var redColor = rootStyle.getPropertyValue("--wu-red").trim();
    var greenColor = rootStyle.getPropertyValue("--wu-green").trim();
    var lightBlue = rootStyle.getPropertyValue("--wu-lightblue").trim();
    var lightBlue80 = rootStyle.getPropertyValue("--wu-lightblue80").trim();

    fetch("/weather/static/modules/get_temp_last24h.php")
        .then((response) => response.json())
        .then((data) => {
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
                xAxis: {
                    type: "category",
                    data: labels,
                    axisLine: { lineStyle: { color: fontColor } },
                    axisLabel: { color: fontColor }
                },
                yAxis: {
                    type: "value",
                    name: "°C",
                    min: minY,
                    max: maxY,
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
        .catch((err) => console.error("Error al cargar datos 24h:", err));
}
