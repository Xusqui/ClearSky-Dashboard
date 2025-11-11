/* modal_wind.js */
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

document.addEventListener("DOMContentLoaded", function () {
    // --- Elementos del DOM ---
    const widget = document.getElementById("wind_widget");
    const modal = document.getElementById("windModal");
    const closeBtn = document.getElementById("closeWindModal");
    const updateBtn = document.getElementById("wind_updateChartBtn"); // Nuevo
    const startInput = document.getElementById("wind_startDate"); // Nuevo
    const endInput = document.getElementById("wind_endDate"); // Nuevo

    const speedDom = document.getElementById("windSpeedChart");
    const dirDom = document.getElementById("windDirectionChart");

    // --- Instancias de Gráficos ---
    let speedChart = null;
    let dirChart = null;

    // --- NUEVA FUNCIÓN: Cargar Gráficos ---
    function loadWindCharts(startDate, endDate) {

        // 1. Destruir gráficos anteriores
        if (speedChart) { speedChart.dispose(); speedChart = null; }
        if (dirChart) { dirChart.dispose(); dirChart = null; }

        // Limpiar HTML en caso de que hubiera un "No hay datos"
        speedDom.innerHTML = '';
        dirDom.innerHTML = '';

        // 2. Inicializar nuevos gráficos
        speedChart = echarts.init(speedDom);
        dirChart = echarts.init(dirDom);

        // 3. Construir URL
        var fetchUrl = "./static/modules/modals/get_wind_historic.php";
        if (startDate && endDate) {
            fetchUrl += `?start=${encodeURIComponent(startDate)}&end=${encodeURIComponent(endDate)}`;
        }

        // 4. Mostrar "Cargando"
        const rootStyle = getComputedStyle(document.documentElement); // Necesitamos esto para los colores
        const fontColor = rootStyle.getPropertyValue('--font-color').trim();
        const wuOrange = rootStyle.getPropertyValue('--wu-orange').trim();

        speedChart.showLoading({
            text: 'Cargando datos...',
            color: wuOrange,
            textColor: fontColor,
            maskColor: 'rgba(255, 255, 255, 0.1)'
        });

        // 5. Fetch de datos
        fetch(fetchUrl)
            .then(res => res.json())
            .then(data => {
                speedChart.hideLoading();

                if (data.error || data.length === 0) {
                    if(data.error) console.error(data.message);
                    speedDom.innerHTML = `<p style="text-align:center; color:${fontColor}; padding-top: 50px;">No hay datos disponibles para el rango seleccionado.</p>`;
                    dirDom.innerHTML = ''; // Limpiar también el segundo gráfico
                    return;
                }

                // 6. Procesar datos
                const labels = data.map(r => r.hora);
                //const velocidad = data.map(r => parseFloat(r.viento_velocidad));
                const velocidad = data.map(r => parseFloat(r.viento_velocidad))
                      .filter(val => Number.isFinite(val));
                const rachas = data.map(r => parseFloat(r.viento_racha));
                const direccion = data.map(r => parseFloat(r.viento_direccion));

                // 7. Obtener colores (ya tenemos fontColor y wuOrange)
                const bgColor = rootStyle.getPropertyValue('--bg-color').trim();
                const wuRed = rootStyle.getPropertyValue('--wu-red').trim();
                const wuGreen = rootStyle.getPropertyValue('--wu-green').trim();
                const wuBlue = rootStyle.getPropertyValue('--wu-lightblue').trim();

                // ------------------------
                // Gráfico de velocidad y rachas (MODIFICADO con dataZoom)
                // ------------------------
                speedChart.setOption({
                    backgroundColor: bgColor,
                    tooltip: { trigger: 'axis',
                                backgroundColor: bgColor,
                                textStyle: { color: fontColor } },
                    legend: { data: ['Velocidad', 'Rachas'], textStyle: { color: fontColor } },

                    // --- NUEVO: DataZoom ---
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
                            fillerColor: 'rgba(255, 165, 0, 0.2)', // Naranja del tema
                            handleStyle: {
                                color: wuOrange
                            },
                            textStyle: {
                                color: fontColor
                            }
                        }
                    ],
                    // -------------------------

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
                // Gráfico polar de dirección (Sin cambios en la opción, solo en la carga)
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
                    polar: { center: ['50%', '50%'], radius: '80%' },
                    series: [{
                        type: 'bar',
                        coordinateSystem: 'polar',
                        data: direccion.map((dir, i) => {
                            // Color gradiente proporcional entre verde y rojo
                            const t = (velocidad[i] - minVel) / (maxVel - minVel || 1);
                            const color =`rgb(${Math.round(255*t)},${Math.round(255*(1-t))},0)`; // verde->rojo

                            return { value: velocidad[i],
                                    hora: labels[i],
                                    itemStyle: {color},
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
            .catch(err => {
                speedChart.hideLoading();
                console.error("Error cargando datos de viento:", err)
            });
    }

    // --- NUEVA FUNCIÓN: Cerrar Modal ---
    function closeWindModal() {
        modal.style.display = "none";
        if (speedChart) { speedChart.dispose(); speedChart = null; }
        if (dirChart) { dirChart.dispose(); dirChart = null; }
    }


    // --- MODIFICADO: Event Listener para abrir el modal ---
    widget.addEventListener("click", function() {
        modal.style.display = "block";

        // Establecer fechas por defecto
        var now = new Date();
        var yesterday = new Date(now.getTime() - 24 * 60 * 60 * 1000);
        startInput.value = formatLocalDateTime(yesterday);
        endInput.value = formatLocalDateTime(now);

        // Cargar gráficos con fechas por defecto
        loadWindCharts(startInput.value, endInput.value);
    });

    // --- NUEVO: Event Listener para el botón de actualizar ---
    updateBtn.addEventListener("click", function() {
        var startDate = startInput.value;
        var endDate = endInput.value;

        if (!startDate || !endDate) {
            alert("Por favor, selecciona un rango de fechas y horas válido.");
            return;
        }
        if (new Date(startDate) >= new Date(endDate)) {
            alert("La fecha de inicio debe ser anterior a la fecha de fin.");
            return;
        }

        // Volver a cargar los gráficos con el nuevo rango
        loadWindCharts(startDate, endDate);
    });


    // --- MODIFICADO: Event Listeners para cerrar el modal ---
    closeBtn.addEventListener("click", closeWindModal);

    window.addEventListener("click", (e) => {
        if (e.target === modal) {
            closeWindModal();
        }
    });
});
