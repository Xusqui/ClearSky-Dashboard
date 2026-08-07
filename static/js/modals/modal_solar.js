// modal_solar.js
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
        var fetchUrl = "./static/modules/modals/get_uv_solar_historic.php";
        if (startDate && endDate) {
            fetchUrl += `?start=${encodeURIComponent(startDate)}&end=${encodeURIComponent(endDate)}`;
        }

        // 4. Obtener colores y mostrar "Cargando"
        const rootStyle = getComputedStyle(document.documentElement);
        const fontColor = rootStyle.getPropertyValue('--font-color').trim();
        const highColor = rootStyle.getPropertyValue('--red').trim();

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

                // Extremos reales del periodo, calculados ANTES de decimar:
                // en rangos largos el backend agrega por día/hora con AVG, lo
                // que aplana los picos. Las columnas "_min"/"_max" que manda
                // en esos casos llevan el real de cada día/hora; si no
                // vienen (rango corto, sin agregar), cada fila ya es un dato
                // real y se usa el propio campo.
                // Redondeo a 1 decimal: AVG() en MySQL (usado en los rangos
                // agregados por día/hora) genera muchos decimales que no
                // aportan precisión real y quedan feos en pantalla. Se aplica
                // en todos los valores mostrados (línea, banda y etiquetas
                // Máx/Mín) para que sean consistentes entre sí.
                function redondear1(v) {
                    return Math.round(v * 10) / 10;
                }
                function extremoFila(row, campo, tipo) {
                    const key = campo + "_" + tipo;
                    const v = row[key] !== undefined ? row[key] : row[campo];
                    return (v === null || v === undefined) ? NaN : redondear1(parseFloat(v));
                }
                function extremoPeriodo(campo, tipo) {
                    let mejor = null, mejorIdx = -1;
                    data.forEach((row, i) => {
                        const v = extremoFila(row, campo, tipo);
                        if (isNaN(v)) return;
                        if (mejor === null || (tipo === "max" ? v > mejor : v < mejor)) {
                            mejor = v;
                            mejorIdx = i;
                        }
                    });
                    return { valor: mejor, idx: mejorIdx };
                }
                const extUv = { min: extremoPeriodo("indice_uv", "min"), max: extremoPeriodo("indice_uv", "max") };
                const extSolar = { min: extremoPeriodo("radiacion_solar", "min"), max: extremoPeriodo("radiacion_solar", "max") };

                if (data.length > 5000) {
                    // Decimación: evita renderizar decenas de miles de puntos si el
                    // usuario elige un rango de varios días sin agregar en el backend.
                    // Se fuerza a conservar las filas que contienen los extremos
                    // reales para que no desaparezcan de la gráfica.
                    const step = Math.ceil(data.length / 2000);
                    const keepIdx = new Set();
                    for (let i = 0; i < data.length; i += step) keepIdx.add(i);
                    keepIdx.add(extUv.min.idx);
                    keepIdx.add(extUv.max.idx);
                    keepIdx.add(extSolar.min.idx);
                    keepIdx.add(extSolar.max.idx);
                    data = data.filter((_, i) => keepIdx.has(i));
                }

                // 6. Procesar datos
                const labels = data.map(row => row.hora);
                //const uvValues = data.map(row => parseFloat(row.indice_uv));
                //const solarValues = data.map(row => parseFloat(row.radiacion_solar));
                const uvValues = data.map(row => redondear1(parseFloat(row.indice_uv)))
                    .filter(val => Number.isFinite(val));
                const solarValues = data.map(row => redondear1(parseFloat(row.radiacion_solar)))
                    .filter(val => Number.isFinite(val));

                const uvMaxIdx = data.findIndex(row => extremoFila(row, "indice_uv", "max") === extUv.max.valor);
                const uvMinIdx = data.findIndex(row => extremoFila(row, "indice_uv", "min") === extUv.min.valor);
                const solarMaxIdx = data.findIndex(row => extremoFila(row, "radiacion_solar", "max") === extSolar.max.valor);
                const solarMinIdx = data.findIndex(row => extremoFila(row, "radiacion_solar", "min") === extSolar.min.valor);

                // 7. Obtener más colores
                const bgColor = rootStyle.getPropertyValue('--bg-color').trim();
                const lowColor = rootStyle.getPropertyValue('--lightblue').trim();

                // Banda sombreada mín-máx real: así el "Máx"/"Mín" del
                // periodo coincide con el borde de la banda dibujada, en vez
                // de quedar como una etiqueta flotando por encima de una
                // línea que nunca llega a ese valor. Truco de ECharts: dos
                // series apiladas (stack), la primera invisible hasta el
                // mínimo, la segunda visible con la diferencia hasta el
                // máximo -> el área visible va exactamente de mín a máx.
                function serieBanda(minArr, maxArr, color, stackId) {
                    const delta = maxArr.map((v, i) => v - minArr[i]);
                    return [
                        {
                            name: stackId + "_min",
                            type: "line",
                            data: minArr,
                            stack: stackId,
                            symbol: "none",
                            lineStyle: { opacity: 0 },
                            areaStyle: { opacity: 0 },
                            silent: true,
                            tooltip: { show: false }
                        },
                        {
                            name: stackId + "_range",
                            type: "line",
                            data: delta,
                            stack: stackId,
                            symbol: "none",
                            lineStyle: { opacity: 0 },
                            areaStyle: { color: color, opacity: 0.18 },
                            silent: true,
                            tooltip: { show: false }
                        }
                    ];
                }
                const uvMinArr = data.map(row => extremoFila(row, "indice_uv", "min"));
                const uvMaxArr = data.map(row => extremoFila(row, "indice_uv", "max"));
                const solarMinArr = data.map(row => extremoFila(row, "radiacion_solar", "min"));
                const solarMaxArr = data.map(row => extremoFila(row, "radiacion_solar", "max"));

                // 8. Opciones de Gráficos

                // --- Gráfica UV (MODIFICADA con dataZoom y rangos Y) ---
                // Escala Y basada en el máximo real del periodo, no en la
                // serie (potencialmente promediada) que se dibuja.
                const maxUv = extUv.max.valor + 1;
                uvChart.setOption({
                    backgroundColor: bgColor,
                    tooltip: { trigger: 'axis', backgroundColor : bgColor, textStyle: { color: fontColor } },
                    visualMap: {
                        show: false,
                        min: 0,
                        max: 11,
                        seriesIndex: 2, // solo la línea de Índice UV, no la banda mín-máx
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
                    series: [
                        ...serieBanda(uvMinArr, uvMaxArr, highColor, "bandaUv"),
                        {
                        name: 'Índice UV',
                        data: uvValues,
                        type: 'line',
                        smooth: false,
                        lineStyle: { width: 1 },
                        markPoint: {
                            data: [
                                { name: 'Máx', coord: [uvMaxIdx, extUv.max.valor], value: extUv.max.valor, itemStyle: { color: highColor } },
                                { name: 'Mín', coord: [uvMinIdx, extUv.min.valor], value: extUv.min.valor, itemStyle: { color: lowColor } }
                            ]
                        }
                    }]
                });

                // --- Gráfica radiación solar (MODIFICADA con dataZoom y rangos Y) ---
                // Escala Y basada en los extremos reales del periodo, no en
                // la serie (potencialmente promediada) que se dibuja.
                const minSolar = extSolar.min.valor;
                const maxSolar = extSolar.max.valor;
                solarChart.setOption({
                    backgroundColor: bgColor,
                    tooltip: { trigger: 'axis', backgroundColor : bgColor , textStyle: { color: fontColor } },
                    visualMap: {
                        show: false,
                        min: minSolar,
                        max: maxSolar,
                        seriesIndex: 2, // solo la línea de Radiación Solar, no la banda mín-máx
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
                    series: [
                        ...serieBanda(solarMinArr, solarMaxArr, highColor, "bandaSolar"),
                        {
                        name: 'Radiación Solar',
                        type: 'line',
                        smooth: true,
                        data: solarValues,
                        symbolSize: 8,
                        lineStyle: { width: 2 },
                        markPoint: {
                            data: [
                                { name: 'Máx', coord: [solarMaxIdx, extSolar.max.valor], value: extSolar.max.valor, itemStyle: { color: highColor } },
                                { name: 'Mín', coord: [solarMinIdx, extSolar.min.valor], value: extSolar.min.valor, itemStyle: { color: lowColor } }
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
