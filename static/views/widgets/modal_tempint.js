// Abrir modal al hacer click en el widget
document.getElementById("tempint_widget").addEventListener("click", function () {
    var modal = document.getElementById("tempIntModal");
    modal.style.display = "block";
    loadTempIntChart();
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
function loadTempIntChart() {
    var chartDom = document.getElementById("tempIntChart");
    var myChart = echarts.init(chartDom);

    var rootStyle = getComputedStyle(document.documentElement);
    var fontColor = rootStyle.getPropertyValue("--font-color").trim();
    var bgColor = rootStyle.getPropertyValue("--bg-color").trim();
    var redColor = rootStyle.getPropertyValue("--wu-red").trim();
    var greenColor = rootStyle.getPropertyValue("--wu-green").trim();
    var lightBlue = rootStyle.getPropertyValue("--wu-lightblue").trim();
    var lightBlue80 = rootStyle.getPropertyValue("--wu-lightblue80").trim();

    fetch("/weather/static/modules/get_tempint_last24h.php")
        .then((response) => response.json())
        .then((data) => {
            var labels = data.map((row) => row.hora);
            var temperaturas = data.map((row) => parseFloat(row.temperatura_interior));

            // Escala Y dinámica
            var minY = Math.min(...temperaturas) - 2;
            var maxY = Math.max(...temperaturas) + 2;

            var option = {
                backgroundColor: bgColor,
                tooltip: { trigger: "axis", backgroundColor: bgColor, textStyle: { color: fontColor } },
                legend: { data: ["Temperatura Interior"], textStyle: { color: fontColor } },
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
        .catch((err) => console.error("Error al cargar datos 24h:", err));
}
