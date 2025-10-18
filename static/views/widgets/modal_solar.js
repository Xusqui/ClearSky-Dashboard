document.addEventListener("DOMContentLoaded", function () {
    const widgets = [
        document.getElementById("uvi_widget"),
        document.getElementById("solar_widget")
    ];
    const modal = document.getElementById("uvSolarModal");
    const closeBtn = document.getElementById("closeUvSolarModal");
    let uvChart = null;
    let solarChart = null;

    function openModal() {
        modal.style.display = "block";

        // Destruir gráficas previas
        if (uvChart) { uvChart.dispose(); uvChart = null; }
        if (solarChart) { solarChart.dispose(); solarChart = null; }

        const uvDom = document.getElementById("uvChart");
        const solarDom = document.getElementById("solarChart");

        uvChart = echarts.init(uvDom);
        solarChart = echarts.init(solarDom);

        fetch("/weather/static/modules/get_uv_solar_last24h.php")
            .then(res => res.json())
            .then(data => {
            const labels = data.map(row => row.hora);
            const uvValues = data.map(row => parseFloat(row.indice_uv));
            const solarValues = data.map(row => parseFloat(row.radiacion_solar));

            const minUv = Math.min(...uvValues) - 1;
            const maxUv = Math.max(...uvValues) + 1;
            const minSolar = Math.min(...solarValues) - 5;
            const maxSolar = Math.max(...solarValues) + 5;

            const rootStyle = getComputedStyle(document.documentElement);
            const fontColor = rootStyle.getPropertyValue('--font-color').trim();
            const bgColor = rootStyle.getPropertyValue('--bg-color').trim();
            const lowColor = rootStyle.getPropertyValue('--wu-lightblue').trim();
            const highColor = rootStyle.getPropertyValue('--wu-red').trim();


            // Gráfica UV
            uvChart.setOption({
                backgroundColor: bgColor,
                tooltip: { trigger: 'axis', backgroundColor : bgColor, textStyle: { color: fontColor } },
                visualMap: {
                    show: false, //No mostrar la barra de color
                    min: 0,
                    max: 11,
                    inRange: {
                        color: [lowColor, highColor] // colores de los valores
                    }
                },
                xAxis: {
                    type: 'category',
                    data: labels,
                    axisLine: { lineStyle: { color: fontColor } },
                    axisLabel: { color: fontColor }
                },
                yAxis: {
                    type: 'value',
                    name: 'Índice UV',
                    min: 0,
                    max: maxUv+1,
                    axisLine: { lineStyle: { color: fontColor } },
                    axisLabel: { color: fontColor }
                },
                series: [{
                    name: 'Índice UV',
                    data: uvValues,
                    type: 'line',
                    smooth: false,
                    lineStyle: { width: 1 }, // naranja
                    markPoint: {
                        data: [
                            { type: 'max', name: 'Máx', itemStyle: { color: highColor } },
                            { type: 'min', name: 'Mín', itemStyle: { color: lowColor } }
                        ]
                    }
                }]
            });

            // Gráfica radiación solar
            solarChart.setOption({
                backgroundColor: bgColor,
                tooltip: { trigger: 'axis', backgroundColor : bgColor , textStyle: { color: fontColor } },
                visualMap: {
                    show: false,       // no mostrar barra de color
                    min: Math.min(...solarValues),
                    max: Math.max(...solarValues),
                    inRange: {
                        color: [lowColor, highColor] // colores de los valores
                    }
                },
                xAxis: {
                    type: 'category',
                    data: labels,
                    axisLine: { lineStyle: { color: fontColor } },
                    axisLabel: { color: fontColor }
                },
                yAxis: {
                    type: 'value',
                    name: 'W/m²',
                    min: Math.min(...solarValues) - 5,
                    max: Math.max(...solarValues) + 5,
                    axisLine: { lineStyle: { color: fontColor } },
                    axisLabel: { color: fontColor }
                },
                series: [{
                    name: 'Radiación Solar',
                    type: 'line',
                    smooth: true,
                    data: solarValues,
                    symbolSize: 8,
                    lineStyle: { width: 2 },
                    markPoint: {
                        data: [
                            { type: 'max', name: 'Máx', itemStyle: { color: highColor } },
                            { type: 'min', name: 'Mín', itemStyle: { color: lowColor } }
                        ]
                    }
                }]
            });

        })
            .catch(err => console.error("Error cargando datos UV/Solar:", err));
    }

    // Abrir modal al hacer click en cualquiera de los widgets
    widgets.forEach(w => {
        if (w) w.addEventListener("click", openModal);
    });

    // Cerrar modal al hacer click en la X
    closeBtn.addEventListener("click", () => {
        modal.style.display = "none";
        if (uvChart) { uvChart.dispose(); uvChart = null; }
        if (solarChart) { solarChart.dispose(); solarChart = null; }
    });

    // Cerrar modal al hacer click fuera del contenido
    window.addEventListener("click", (e) => {
        if (e.target === modal) {
            modal.style.display = "none";
            if (uvChart) { uvChart.dispose(); uvChart = null; }
            if (solarChart) { solarChart.dispose(); solarChart = null; }
        }
    });
});
