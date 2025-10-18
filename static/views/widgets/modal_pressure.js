document.addEventListener("DOMContentLoaded", function () {
    const widget = document.getElementById("pressure_widget"); // id del widget que abrirá el modal
    const modal = document.getElementById("pressureModal");
    const closeBtn = document.getElementById("closePressureModal");
    let pressureChart = null; // variable global para destruir la gráfica al cerrar

    // Abrir modal al hacer click en el widget
    widget.addEventListener("click", function () {
        modal.style.display = "block";

        // Destruir gráfico previo si existía
        if (pressureChart) {
            pressureChart.dispose();
        }

        const chartDom = document.getElementById("pressureChart");
        pressureChart = echarts.init(chartDom);

        fetch("/weather/static/modules/get_pressure_last24h.php")
            .then(response => response.json())
            .then(data => {
                const labels = data.map(row => row.hora);
                const presiones = data.map(row => parseFloat(row.presion_relativa));

                // Escala Y dinámica
                const minY = Math.min(...presiones) - 2;
                const maxY = Math.max(...presiones) + 2;

                const rootStyle = getComputedStyle(document.documentElement);
                const fontColor = rootStyle.getPropertyValue('--font-color').trim();
                const bgColor = rootStyle.getPropertyValue('--bg-color').trim();

                const option = {
                    backgroundColor: bgColor,
                    tooltip: { trigger: 'axis', backgroundColor : bgColor, textStyle: { color: fontColor } },
                    xAxis: {
                        type: 'category',
                        data: labels,
                        axisLine: { lineStyle: { color: fontColor } },
                        axisLabel: { color: fontColor }
                    },
                    yAxis: {
                        type: 'value',
                        name: 'hPa',
                        min: minY,
                        max: maxY,
                        axisLine: { lineStyle: { color: fontColor } },
                        axisLabel: { color: fontColor }
                    },
                    series: [{
                        name: 'Presión Relativa',
                        data: presiones,
                        type: 'line',
                        smooth: true,
                        lineStyle: { width: 2, color: 'green' }, // puedes cambiar a tu color deseado
                        markPoint: {
                            data: [
                                { type: 'max', name: 'Máx', itemStyle: { color: 'darkgreen' } },
                                { type: 'min', name: 'Mín', itemStyle: { color: 'lightgreen' } }
                            ]
                        }
                    }]
                };

                pressureChart.setOption(option);
            })
            .catch(err => console.error("Error al cargar datos de presión 24h:", err));
    });

    // Cerrar modal al hacer click en la X
    closeBtn.addEventListener("click", function () {
        modal.style.display = "none";
        if (pressureChart) {
            pressureChart.dispose();
            pressureChart = null;
        }
    });

    // Cerrar modal al hacer click fuera del contenido
    window.addEventListener("click", function (e) {
        if (e.target === modal) {
            modal.style.display = "none";
            if (pressureChart) {
                pressureChart.dispose();
                pressureChart = null;
            }
        }
    });
});
