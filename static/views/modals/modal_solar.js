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
    const widgets = [
        document.getElementById("uvi_widget"),
        document.getElementById("solar_widget")
    ];
    const modal = document.getElementById("uvSolarModal");
    const closeBtn = document.getElementById("closeUvSolarModal");
    const updateBtn = document.getElementById("uv_updateChartBtn"); // Nuevo
    const startInput = document.getElementById("uv_startDate"); // Nuevo
    const endInput = document.getElementById("uv_endDate"); // Nuevo

    const uvDom = document.getElementById("uvChart");
    const solarDom = document.getElementById("solarChart");

    // --- Instancias de Gráficos ---
    let uvChart = null;
    let solarChart = null;

    // --- NUEVA FUNCIÓN: Cargar Gráficos ---
    function loadUvSolarCharts(startDate, endDate) {

        // 1. Destruir gráficos anteriores
        if (uvChart) { uvChart.dispose(); uvChart = null; }
        if (solarChart) { solarChart.dispose(); solarChart = null; }

        // Limpiar HTML en caso de que hubiera un "No hay datos"
        uvDom.innerHTML = '';
        solarDom.innerHTML = '';

        // 2. Inicializar nuevos gráficos
        uvChart = echarts.init(uvDom);
        solarChart = echarts.init(solarDom);

        // 3. Construir URL
        var fetchUrl = "./static/modules/get_uv_solar_last24h.php";
        if (startDate && endDate) {
            fetchUrl += `?start=${encodeURIComponent(startDate)}&end=${encodeURIComponent(endDate)}`;
        }

        // 4. Obtener colores y mostrar "Cargando"
        const rootStyle = getComputedStyle(document.documentElement);
        const fontColor = rootStyle.getPropertyValue('--font-color').trim();
        const highColor = rootStyle.getPropertyValue('--wu-red').trim();

        uvChart.showLoading({
            text: 'Cargando datos...',
            color: highColor, // Color rojo/solar
            textColor: fontColor,
            maskColor: 'rgba(255, 255, 255, 0.1)'
        });

        // 5. Fetch de datos
        fetch(fetchUrl)
            .then(res => res.json())
            .then(data => {
                uvChart.hideLoading();

                if (data.error || data.length === 0) {
                    if(data.error) console.error(data.message);
                    uvDom.innerHTML = `<p style="text-align:center; color:${fontColor}; padding-top: 50px;">No hay datos disponibles para el rango seleccionado.</p>`;
                    solarDom.innerHTML = ''; // Limpiar también el segundo gráfico
                    return;
                }

                // 6. Procesar datos
                const labels = data.map(row => row.hora);
                const uvValues = data.map(row => parseFloat(row.indice_uv));
                const solarValues = data.map(row => parseFloat(row.radiacion_solar));

                // 7. Obtener más colores
                const bgColor = rootStyle.getPropertyValue('--bg-color').trim();
                const lowColor = rootStyle.getPropertyValue('--wu-lightblue').trim();

                // 8. Opciones de Gráficos

                // --- Gráfica UV (MODIFICADA con dataZoom y rangos Y) ---
                const maxUv = Math.max(...uvValues) + 1;
                uvChart.setOption({
                    backgroundColor: bgColor,
                    tooltip: { trigger: 'axis', backgroundColor : bgColor, textStyle: { color: fontColor } },
                    visualMap: {
                        show: false,
                        min: 0,
                        max: 11,
                        inRange: { color: [lowColor, highColor] }
                    },
                    // --- NUEVO: DataZoom ---
                    dataZoom: [
                        { type: 'inside', start: 0, end: 100 },
                        {
                            type: 'slider',
                            start: 0,
                            end: 100,
                            backgroundColor: 'rgba(0,0,0,0.1)',
                            borderColor: '#777',
                            fillerColor: 'rgba(255, 0, 0, 0.2)', // Rojo del tema
                            handleStyle: { color: highColor },
                            textStyle: { color: fontColor }
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
                        name: 'Índice UV',
                        min: 0, // UV no baja de 0
                        max: maxUv,
                        axisLine: { lineStyle: { color: fontColor } },
                        axisLabel: { color: fontColor }
                    },
                    series: [{
                        name: 'Índice UV',
                        data: uvValues,
                        type: 'line',
                        smooth: false,
                        lineStyle: { width: 1 },
                        markPoint: {
                            data: [
                                { type: 'max', name: 'Máx', itemStyle: { color: highColor } },
                                { type: 'min', name: 'Mín', itemStyle: { color: lowColor } }
                            ]
                        }
                    }]
                });

                // --- Gráfica radiación solar (MODIFICADA con dataZoom y rangos Y) ---
                const minSolar = Math.min(...solarValues);
                const maxSolar = Math.max(...solarValues);
                solarChart.setOption({
                    backgroundColor: bgColor,
                    tooltip: { trigger: 'axis', backgroundColor : bgColor , textStyle: { color: fontColor } },
                    visualMap: {
                        show: false,
                        min: minSolar,
                        max: maxSolar,
                        inRange: { color: [lowColor, highColor] }
                    },
                    // --- NUEVO: DataZoom ---
                    dataZoom: [
                        { type: 'inside', start: 0, end: 100 },
                        {
                            type: 'slider',
                            start: 0,
                            end: 100,
                            backgroundColor: 'rgba(0,0,0,0.1)',
                            borderColor: '#777',
                            fillerColor: 'rgba(255, 0, 0, 0.2)', // Rojo del tema
                            handleStyle: { color: highColor },
                            textStyle: { color: fontColor }
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
                        name: 'W/m²',
                        min: minSolar > 0 ? minSolar - 5 : 0, // Solar no baja de 0
                        max: maxSolar + 5,
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
            .catch(err => {
                uvChart.hideLoading();
                console.error("Error cargando datos UV/Solar:", err)
            });
    }

    // --- NUEVA FUNCIÓN: Cerrar Modal ---
    function closeUvSolarModal() {
        modal.style.display = "none";
        if (uvChart) { uvChart.dispose(); uvChart = null; }
        if (solarChart) { solarChart.dispose(); solarChart = null; }
    }


    // --- MODIFICADO: Event Listener para abrir el modal ---
    widgets.forEach(w => {
        if (w) w.addEventListener("click", function() {
            modal.style.display = "block";

            // Establecer fechas por defecto
            var now = new Date();
            var yesterday = new Date(now.getTime() - 24 * 60 * 60 * 1000);
            startInput.value = formatLocalDateTime(yesterday);
            endInput.value = formatLocalDateTime(now);

            // Cargar gráficos con fechas por defecto
            loadUvSolarCharts(startInput.value, endInput.value);
        });
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
        loadUvSolarCharts(startDate, endDate);
    });

    // --- MODIFICADO: Event Listeners para cerrar el modal ---
    closeBtn.addEventListener("click", closeUvSolarModal);

    window.addEventListener("click", (e) => {
        if (e.target === modal) {
            closeUvSolarModal();
        }
    });
});
