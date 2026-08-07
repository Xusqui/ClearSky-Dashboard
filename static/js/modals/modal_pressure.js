/* modal_pressure.js */
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
    const widget = document.getElementById("pressure_widget");
    const modal = document.getElementById("pressureModal");
    const closeBtn = document.getElementById("closePressureModal");
    const updateBtn = document.getElementById("pressure_updateChartBtn"); // Nuevo
    const startInput = document.getElementById("pressure_startDate"); // Nuevo
    const endInput = document.getElementById("pressure_endDate"); // Nuevo
    const chartDom = document.getElementById("pressureChart");

    let pressureChart = null; // variable global

    // --- Función para Cargar el Gráfico ---
    function loadPressureChart(startDate, endDate) {
        // Destruir gráfico previo si existía
        if (pressureChart) {
            pressureChart.dispose();
            pressureChart = null;
        }
        pressureChart = echarts.init(chartDom);

        // Construir URL
        var fetchUrl = "./static/modules/modals/get_pressure_historic.php";
        if (startDate && endDate) {
            fetchUrl += `?start=${encodeURIComponent(startDate)}&end=${encodeURIComponent(endDate)}`;
        }

        // Obtener colores
        const rootStyle = getComputedStyle(document.documentElement);
        const fontColor = rootStyle.getPropertyValue('--font-color').trim();
        const bgColor = rootStyle.getPropertyValue('--bg-color').trim();
        const greenColor = rootStyle.getPropertyValue('--green') || 'green'; // Usar variable CSS si existe

        // Mostrar "cargando"
        pressureChart.showLoading({
            text: 'Cargando datos...',
            color: greenColor,
            textColor: fontColor,
            maskColor: 'rgba(255, 255, 255, 0.1)'
        });

        fetch(fetchUrl)
            .then(response => response.json())
            .then(data => {
                pressureChart.hideLoading();

                if (data.error) {
                    console.error(data.message);
                    return;
                }

                if (data.length === 0) {
                    chartDom.innerHTML = `<p style="text-align:center; color:${fontColor}; padding-top: 50px;">No hay datos disponibles para el rango seleccionado.</p>`;
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
                const extPresion = { min: extremoPeriodo("presion_relativa", "min"), max: extremoPeriodo("presion_relativa", "max") };

                if (data.length > 5000) {
                    // Decimación: evita renderizar decenas de miles de puntos si el
                    // usuario elige un rango de varios días sin agregar en el backend.
                    // Se fuerza a conservar las filas que contienen los extremos
                    // reales para que no desaparezcan de la gráfica.
                    const step = Math.ceil(data.length / 2000);
                    const keepIdx = new Set();
                    for (let i = 0; i < data.length; i += step) keepIdx.add(i);
                    keepIdx.add(extPresion.min.idx);
                    keepIdx.add(extPresion.max.idx);
                    data = data.filter((_, i) => keepIdx.has(i));
                }

                const labels = data.map(row => row.hora);
                const presiones = data.map(row => redondear1(parseFloat(row.presion_relativa)));

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
                const presMinArr = data.map(row => extremoFila(row, "presion_relativa", "min"));
                const presMaxArr = data.map(row => extremoFila(row, "presion_relativa", "max"));

                // Escala Y dinámica basada en los extremos reales del periodo
                const minY = extPresion.min.valor - 2;
                const maxY = extPresion.max.valor + 2;

                const maxIdx = data.findIndex(row => extremoFila(row, "presion_relativa", "max") === extPresion.max.valor);
                const minIdx = data.findIndex(row => extremoFila(row, "presion_relativa", "min") === extPresion.min.valor);

                const option = {
                    backgroundColor: bgColor,
                    tooltip: { trigger: 'axis', backgroundColor : bgColor, textStyle: { color: fontColor } },

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
                            fillerColor: 'rgba(0, 128, 0, 0.2)', // Verde del tema
                            handleStyle: {
                                color: greenColor
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
                        name: 'hPa',
                        min: minY.toFixed(1),
                        max: maxY.toFixed(1),
                        axisLine: { lineStyle: { color: fontColor } },
                        axisLabel: { color: fontColor }
                    },
                    series: [
                        ...serieBanda(presMinArr, presMaxArr, greenColor, "bandaPresion"),
                        {
                        name: 'Presión Relativa',
                        data: presiones,
                        type: 'line',
                        smooth: true,
                        lineStyle: { width: 2, color: greenColor }, // Color de tu CSS
                        markPoint: {
                            data: [
                                { name: 'Máx', coord: [maxIdx, extPresion.max.valor], value: extPresion.max.valor, itemStyle: { color: 'darkgreen' } },
                                { name: 'Mín', coord: [minIdx, extPresion.min.valor], value: extPresion.min.valor, itemStyle: { color: 'lightgreen' } }
                            ]
                        }
                    }]
                };

                pressureChart.setOption(option);
            })
            .catch(err => {
                pressureChart.hideLoading();
                console.error("Error al cargar datos de presión:", err)
            });
    }

    // --- Función para Cerrar el Modal ---
    function closePressureModal() {
        modal.style.display = "none";
        if (pressureChart) {
            pressureChart.dispose();
            pressureChart = null;
        }
    }

    // --- Event Listeners ---

    // Abrir modal al hacer click en el widget
    widget.addEventListener("click", function () {
        modal.style.display = "block";

        // Establecer fechas por defecto
        var now = new Date();
        var yesterday = new Date(now.getTime() - 24 * 60 * 60 * 1000);
        startInput.value = formatLocalDateTime(yesterday);
        endInput.value = formatLocalDateTime(now);

        // Cargar gráfico con fechas por defecto
        loadPressureChart(startInput.value, endInput.value);
    });

    // Botón de actualizar
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

        loadPressureChart(startDate, endDate);
    });

    // Cerrar modal al hacer click en la X
    closeBtn.addEventListener("click", closePressureModal);

    // Cerrar modal al hacer click fuera del contenido
    window.addEventListener("click", function (e) {
        if (e.target === modal) {
            closePressureModal();
        }
    });
});
