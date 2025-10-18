// Abrir modal de humedad al hacer click en el widget correspondiente
document.getElementById("humint_widget").addEventListener("click", function () {
    var modal = document.getElementById("humIntModal");
    modal.style.display = "block";
    loadHumIntChart();
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
function loadHumIntChart() {
    var chartDom = document.getElementById("humIntChart");
    var myChart = echarts.init(chartDom);

    // Obtener colores del CSS
    var rootStyle = getComputedStyle(document.documentElement);
    var fontColor = rootStyle.getPropertyValue("--font-color").trim();
    var bgColor = rootStyle.getPropertyValue("--bg-color").trim();
    var blueColor = rootStyle.getPropertyValue("--wu-purple").trim();
    var blueLight = rootStyle.getPropertyValue("--wu-lightblue").trim();
    var darkBlue = rootStyle.getPropertyValue("--wu-darkblue").trim();

    fetch("/weather/static/modules/get_humint_last24h.php")
        .then((response) => response.json())
        .then((data) => {
            var labels = data.map((row) => row.hora);
            var humedad_interior = data.map((row) => parseFloat(row.humedad_interior));

            var minY = Math.min(...humedad_interior) - 5;
            var maxY = Math.max(...humedad_interior) + 5;

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
                xAxis: {
                    type: "category",
                    data: labels,
                    axisLine: { lineStyle: { color: fontColor } },
                    axisLabel: { color: fontColor }
                },
                yAxis: {
                    type: "value",
                    name: "%",
                    min: minY,
                    max: maxY,
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
        .catch((err) => console.error("Error al cargar datos de humedad 24h:", err));
}
