document.addEventListener("DOMContentLoaded", function () {
    const widget = document.getElementById("wind_widget");
    const modal = document.getElementById("windModal");
    const closeBtn = document.getElementById("closeWindModal");
    let speedChart = null;
    let dirChart = null;

    function openModal() {
        modal.style.display = "block";

        if (speedChart) { speedChart.dispose(); speedChart = null; }
        if (dirChart) { dirChart.dispose(); dirChart = null; }

        const speedDom = document.getElementById("windSpeedChart");
        const dirDom = document.getElementById("windDirectionChart");

        speedChart = echarts.init(speedDom);
        dirChart = echarts.init(dirDom);

        fetch("/weather/static/modules/get_wind_last24h.php")
            .then(res => res.json())
            .then(data => {
            const labels = data.map(r => r.hora);
            const velocidad = data.map(r => parseFloat(r.viento_velocidad));
            const rachas = data.map(r => parseFloat(r.viento_racha));
            const direccion = data.map(r => parseFloat(r.viento_direccion));

            const rootStyle = getComputedStyle(document.documentElement);
            const fontColor = rootStyle.getPropertyValue('--font-color').trim();
            const bgColor = rootStyle.getPropertyValue('--bg-color').trim();
            const wuRed = rootStyle.getPropertyValue('--wu-red').trim();
            const wuGreen = rootStyle.getPropertyValue('--wu-green').trim();
            const wuOrange = rootStyle.getPropertyValue('--wu-orange').trim();
            const wuBlue = rootStyle.getPropertyValue('--wu-lightblue').trim();

            // ------------------------
            // Gráfico de velocidad y rachas
            // ------------------------
            speedChart.setOption({
                backgroundColor: bgColor,
                tooltip: { trigger: 'axis',
                          backgroundColor: bgColor,
                          textStyle: { color: fontColor } },
                legend: { data: ['Velocidad', 'Rachas'], textStyle: { color: fontColor } },
                xAxis: {
                    type: 'category',
                    data: labels,
                    axisLine: { lineStyle: { color: fontColor } },
                    axisLabel: { color: fontColor }
                },
                yAxis: {
                    type: 'value',
                    name: 'km/h',
                    axisLine: { lineStyle: { color: fontColor } },
                    axisLabel: { color: fontColor }
                },
                series: [
                    {
                        name: 'Velocidad',
                        data: velocidad,
                        type: 'line',
                        smooth: true,
                        lineStyle: { width: 2, color: wuBlue }
                    },
                    {
                        name: 'Rachas',
                        data: rachas,
                        type: 'line',
                        smooth: true,
                        lineStyle: { width: 2, color: wuOrange }
                    }
                ]
            });

            // ------------------------
            // Gráfico polar de dirección con gradiente
            // ------------------------
            const cardinales = ['N','NE','E','SE','S','SO','O','NO'];
            const minVel = Math.min(...velocidad);
            const maxVel = Math.max(...velocidad);

            dirChart.setOption({
                backgroundColor: bgColor,
                tooltip: { trigger: 'item',
                          backgroundColor: bgColor,
                          textStyle: { color: fontColor},
                          formatter: params => `${params.value} km/h<br />Hora: ${params.data.hora}` },
                angleAxis: {
                    type: 'category',
                    data: cardinales,
                    startAngle: 112.5,
                    clockwise: true,
                    axisLine: { lineStyle: { color: fontColor, width: 2 } },
                    axisLabel: { color: fontColor, fontWeight: 'bold', fontSize: 14 },
                    splitLine: { show: true, lineStyle: { color: fontColor, type: 'dashed' } }
                },
                radiusAxis: {
                    type: 'value',
                    min: 0,
                    max: maxVel + 5,
                    axisLine: { show: false },
                    axisLabel: { show: false },
                    splitLine: { show: true, lineStyle: { color: fontColor, type: 'dotted', width: 1 } }
                },
                polar: { center: ['50%', '50%'], radius: '84%' },
                series: [{
                    type: 'bar',
                    coordinateSystem: 'polar',
                    data: direccion.map((dir, i) => {
                        // Color gradiente proporcional entre verde y rojo
                        const t = (velocidad[i] - minVel) / (maxVel - minVel || 1);
                        const color = `rgb(${Math.round(255*t)},${Math.round(255*(1-t))},0)`; // verde->rojo
                        return { value: velocidad[i],
                                hora: labels[i],
                                itemStyle: { color },
                                barGap: '20%',
                                barCategoryGap: '30%'
                               };
                    }),
                    stack: 'a',
                    emphasis: { focus: 'series' }
                }],
                graphic: [
                    { type: 'circle', shape: { cx: '50%', cy: '50%', r: '70%' }, style: { stroke: fontColor, lineWidth: 3 }, silent: true },
                    { type: 'polygon', shape: { points: [[0,-10],[5,0],[-5,0]] }, position: ['50%','15%'], style: { fill: wuRed }, rotation: 0, silent: true }
                ]
            });
        })
            .catch(err => console.error("Error cargando datos de viento:", err));
    }

    widget.addEventListener("click", openModal);

    closeBtn.addEventListener("click", () => {
        modal.style.display = "none";
        if (speedChart) { speedChart.dispose(); speedChart = null; }
        if (dirChart) { dirChart.dispose(); dirChart = null; }
    });

    window.addEventListener("click", (e) => {
        if (e.target === modal) {
            modal.style.display = "none";
            if (speedChart) { speedChart.dispose(); speedChart = null; }
            if (dirChart) { dirChart.dispose(); dirChart = null; }
        }
    });
});
