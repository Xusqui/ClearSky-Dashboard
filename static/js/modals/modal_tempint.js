/* modal_tempint.js */
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

// Abrir modal al hacer click en el widget
document.getElementById("tempint_widget").addEventListener("click", function () {
    var modal = document.getElementById("tempIntModal");
    modal.style.display = "block";

    // --- NUEVO: Establecer fechas por defecto (últimas 24h) ---
    var now = new Date();
    var yesterday = new Date(now.getTime() - 24 * 60 * 60 * 1000);

    document.getElementById("startDate").value = formatLocalDateTime(yesterday);
    document.getElementById("endDate").value = formatLocalDateTime(now);
    // -----------------------------------------------------

    // Cargar gráfico inicial (con las fechas por defecto o las últimas 24h)
    loadTempIntChart(formatLocalDateTime(yesterday), formatLocalDateTime(now));
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

// --- NUEVO: Event Listener para el botón de actualizar ---
document.getElementById("updateChartBtn").addEventListener("click", function() {
    var startDate = document.getElementById("startDate").value;
    var endDate = document.getElementById("endDate").value;

    if (!startDate || !endDate) {
        alert("Por favor, selecciona un rango de fechas y horas válido.");
        return;
    }
    if (new Date(startDate) >= new Date(endDate)) {
        alert("La fecha de inicio debe ser anterior a la fecha de fin.");
        return;
    }

    // Volver a cargar el gráfico con el nuevo rango
    loadTempIntChart(startDate, endDate);
});
// ---------------------------------------------------

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
// --- MODIFICADO: Acepta parámetros startDate y endDate ---
function loadTempIntChart(startDate, endDate) {
    var chartDom = document.getElementById("tempIntChart");

    // --- MODIFICADO: Destruir gráfico anterior si existe ANTES de inicializar uno nuevo ---
    // Esto es crucial para que el botón "Actualizar" funcione
    var myChart = echarts.getInstanceByDom(chartDom);
    if (myChart) {
        myChart.dispose();
    }
    myChart = echarts.init(chartDom);
    // ---------------------------------------------------------------------------------

    var rootStyle = getComputedStyle(document.documentElement);
    var fontColor = rootStyle.getPropertyValue("--font-color").trim();
    var bgColor = rootStyle.getPropertyValue("--bg-color").trim();
    var redColor = rootStyle.getPropertyValue("--red").trim();
    // (Omitido el resto de colores por brevedad, ya los tienes)

    // --- MODIFICADO: Construir la URL de fetch dinámicamente ---
    var fetchUrl = "./static/modules/modals/get_tempint_historic.php";
    if (startDate && endDate) {
        // Añadir parámetros a la URL
        fetchUrl += `?start=${encodeURIComponent(startDate)}&end=${encodeURIComponent(endDate)}`;
    }
    // -----------------------------------------------------------

    // Mostrar un "cargando" mientras se obtienen los datos (opcional pero recomendado)
    myChart.showLoading({
        text: 'Cargando datos...',
        color: redColor,
        textColor: fontColor,
        maskColor: 'rgba(255, 255, 255, 0.1)'
    });

    fetch(fetchUrl)
        .then((response) => response.json())
        .then((data) => {
        // Ocultar el "cargando"
        myChart.hideLoading();

        if (data.error) {
            console.error(data.message);
            // Aquí podrías mostrar un error en el gráfico
            return;
        }

        if (data.length === 0) {
            // Mostrar mensaje si no hay datos en ese rango
            chartDom.innerHTML = `<p style="text-align:center; color:${fontColor}; padding-top: 50px;">No hay datos disponibles para el rango seleccionado.</p>`;
            return;
        }

        // Extremos reales del periodo, calculados ANTES de decimar: en rangos
        // largos el backend agrega por día/hora con AVG, lo que aplana los
        // picos. Las columnas "_min"/"_max" que manda en esos casos llevan el
        // real de cada día/hora; si no vienen (rango corto, sin agregar),
        // cada fila ya es un dato real y se usa el propio campo.
        // Redondeo a 1 decimal: AVG() en MySQL (usado en los rangos
        // agregados por día/hora) genera muchos decimales (ej. 22.072892...)
        // que no aportan precisión real y quedan feos en pantalla. Se aplica
        // en todos los valores mostrados (línea, banda y etiquetas Máx/Mín)
        // para que sean consistentes entre sí.
        function redondear1(v) {
            return Math.round(v * 10) / 10;
        }
        function extremoFila(row, campo, tipo) {
            var key = campo + "_" + tipo;
            var v = row[key] !== undefined ? row[key] : row[campo];
            return (v === null || v === undefined) ? NaN : redondear1(parseFloat(v));
        }
        function extremoPeriodo(campo, tipo) {
            var mejor = null, mejorIdx = -1;
            data.forEach((row, i) => {
                var v = extremoFila(row, campo, tipo);
                if (isNaN(v)) return;
                if (mejor === null || (tipo === "max" ? v > mejor : v < mejor)) {
                    mejor = v;
                    mejorIdx = i;
                }
            });
            return { valor: mejor, idx: mejorIdx };
        }
        var extTemp = { min: extremoPeriodo("temperatura_interior", "min"), max: extremoPeriodo("temperatura_interior", "max") };

        if (data.length > 5000) {
            // Decimación: evita renderizar decenas de miles de puntos si el
            // usuario elige un rango de varios días sin agregar en el backend.
            // Se fuerza a conservar las filas que contienen los extremos
            // reales para que no desaparezcan de la gráfica.
            const step = Math.ceil(data.length / 2000);
            var keepIdx = new Set();
            for (var i = 0; i < data.length; i += step) keepIdx.add(i);
            keepIdx.add(extTemp.min.idx);
            keepIdx.add(extTemp.max.idx);
            data = data.filter((_, i) => keepIdx.has(i));
        }

        var labels = data.map((row) => row.hora);
        var temperaturas = data.map((row) => redondear1(parseFloat(row.temperatura_interior)));

        // Banda sombreada mín-máx real: así el "Máx"/"Mín" del periodo
        // coincide con el borde de la banda dibujada, en vez de quedar como
        // una etiqueta flotando por encima de una línea que nunca llega a
        // ese valor. Truco de ECharts: dos series apiladas (stack), la
        // primera invisible hasta el mínimo, la segunda visible con la
        // diferencia hasta el máximo -> el área visible va exactamente de
        // mín a máx.
        function serieBanda(minArr, maxArr, color, stackId) {
            var delta = maxArr.map((v, i) => v - minArr[i]);
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
        var tempMinArr = data.map((row) => extremoFila(row, "temperatura_interior", "min"));
        var tempMaxArr = data.map((row) => extremoFila(row, "temperatura_interior", "max"));

        // Escala Y dinámica basada en los extremos reales del periodo, no en
        // la serie (potencialmente promediada) que se dibuja.
        var minY = extTemp.min.valor - 2;
        var maxY = extTemp.max.valor + 2;

        var maxIdx = data.findIndex((row) => extremoFila(row, "temperatura_interior", "max") === extTemp.max.valor);
        var minIdx = data.findIndex((row) => extremoFila(row, "temperatura_interior", "min") === extTemp.min.valor);

        var option = {
            backgroundColor: bgColor,
            tooltip: { trigger: "axis", backgroundColor: bgColor, textStyle: { color: fontColor } },
            legend: { data: ["Temperatura Interior"], textStyle: { color: fontColor } },

            // --- NUEVO: DataZoom para hacer zoom/scroll si hay muchos datos ---
            dataZoom: [
                {
                    type: 'inside', // Permite hacer zoom con la rueda del ratón
                    start: 0,
                    end: 100
                },
                {
                    type: 'slider', // Muestra una barra de scroll inferior
                    start: 0,
                    end: 100,
                    backgroundColor: 'rgba(0,0,0,0.1)',
                    borderColor: '#777',
                    fillerColor: 'rgba(255, 0, 0, 0.2)', // Color rojo de tu tema
                    handleStyle: {
                        color: redColor
                    },
                    textStyle: {
                        color: fontColor
                    }
                }
            ],
            // -----------------------------------------------------------------

            xAxis: {
                type: "category",
                data: labels,
                axisLine: { lineStyle: { color: fontColor } },
                axisLabel: { color: fontColor }
            },
            yAxis: {
                type: "value",
                name: "°C",
                min: minY.toFixed(1), // Asegurar que los límites sean fijos
                max: maxY.toFixed(1),
                axisLine: { lineStyle: { color: fontColor } },
                axisLabel: { color: fontColor }
            },
            series: [
                ...serieBanda(tempMinArr, tempMaxArr, redColor, "bandaTempInt"),
                {
                    name: "Temperatura Interior",
                    data: temperaturas,
                    type: "line",
                    smooth: true,
                    lineStyle: { width: 2, color: redColor },
                    markPoint: {
                        data: [
                            { name: "Máx", coord: [maxIdx, extTemp.max.valor], value: extTemp.max.valor, itemStyle: { color: "darkred" } },
                            { name: "Mín", coord: [minIdx, extTemp.min.valor], value: extTemp.min.valor, itemStyle: { color: "orange" } }
                        ]
                    }
                }
            ]
        };

        myChart.setOption(option);
    })
        .catch((err) => {
        myChart.hideLoading();
        console.error("Error al cargar datos:", err)
    });
}
