/* modal_humidityint.js */
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

// Abrir modal de humedad al hacer click en el widget correspondiente
document.getElementById("humint_widget").addEventListener("click", function () {
    var modal = document.getElementById("humIntModal");
    modal.style.display = "block";

    // --- NUEVO: Establecer fechas por defecto (últimas 24h) ---
    var now = new Date();
    var yesterday = new Date(now.getTime() - 24 * 60 * 60 * 1000);

    // Usamos los IDs específicos de este modal
    var startInput = document.getElementById("humInt_startDate");
    var endInput = document.getElementById("humInt_endDate");

    startInput.value = formatLocalDateTime(yesterday);
    endInput.value = formatLocalDateTime(now);
    // -----------------------------------------------------

    // Cargar gráfico inicial con las fechas por defecto
    loadHumIntChart(startInput.value, endInput.value);
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

// --- NUEVO: Event Listener para el botón de actualizar ---
document.getElementById("humInt_updateChartBtn").addEventListener("click", function() {
    var startDate = document.getElementById("humInt_startDate").value;
    var endDate = document.getElementById("humInt_endDate").value;

    if (!startDate || !endDate) {
        alert("Por favor, selecciona un rango de fechas y horas válido.");
        return;
    }
    if (new Date(startDate) >= new Date(endDate)) {
        alert("La fecha de inicio debe ser anterior a la fecha de fin.");
        return;
    }

    // Volver a cargar el gráfico con el nuevo rango
    loadHumIntChart(startDate, endDate);
});
// ---------------------------------------------------

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
// --- MODIFICADO: Acepta parámetros startDate y endDate ---
function loadHumIntChart(startDate, endDate) {
    var chartDom = document.getElementById("humIntChart");

    // --- MODIFICADO: Destruir gráfico anterior si existe ---
    var myChart = echarts.getInstanceByDom(chartDom);
    if (myChart) {
        myChart.dispose();
    }
    myChart = echarts.init(chartDom);
    // ---------------------------------------------------

    // Obtener colores del CSS
    var rootStyle = getComputedStyle(document.documentElement);
    var fontColor = rootStyle.getPropertyValue("--font-color").trim();
    var bgColor = rootStyle.getPropertyValue("--bg-color").trim();
    var blueColor = rootStyle.getPropertyValue("--purple").trim();
    var blueLight = rootStyle.getPropertyValue("--lightblue").trim();
    var darkBlue = rootStyle.getPropertyValue("--darkblue").trim();

    // --- MODIFICADO: Construir la URL de fetch dinámicamente ---
    var fetchUrl = "./static/modules/modals/get_humint_historic.php";
    if (startDate && endDate) {
        fetchUrl += `?start=${encodeURIComponent(startDate)}&end=${encodeURIComponent(endDate)}`;
    }
    // -----------------------------------------------------------

    // Mostrar "cargando"
    myChart.showLoading({
        text: 'Cargando datos...',
        color: blueColor, // Color del tema
        textColor: fontColor,
        maskColor: 'rgba(255, 255, 255, 0.1)'
    });

    fetch(fetchUrl)
        .then((response) => response.json())
        .then((data) => {
        // Ocultar "cargando"
        myChart.hideLoading();

        if (data.error) {
            console.error(data.message);
            return;
        }

        if (data.length === 0) {
            chartDom.innerHTML = `<p style="text-align:center; color:${fontColor}; padding-top: 50px;">No hay datos disponibles para el rango seleccionado.</p>`;
            return;
        }

        // Extremos reales del periodo, calculados ANTES de decimar: en
        // rangos largos el backend agrega por día/hora con AVG, lo que
        // aplana los picos. Las columnas "_min"/"_max" que manda en esos
        // casos llevan el real de cada día/hora; si no vienen (rango corto,
        // sin agregar), cada fila ya es un dato real y se usa el propio
        // campo.
        // Redondeo a 1 decimal: AVG() en MySQL (usado en los rangos agregados
        // por día/hora) genera muchos decimales (ej. 22.072892...) que no
        // aportan precisión real y quedan feos en pantalla. Se aplica en
        // todos los valores mostrados (línea, banda y etiquetas Máx/Mín)
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
        var extHum = { min: extremoPeriodo("humedad_interior", "min"), max: extremoPeriodo("humedad_interior", "max") };

        if (data.length > 5000) {
            // Decimación: evita renderizar decenas de miles de puntos si el
            // usuario elige un rango de varios días sin agregar en el backend.
            // Se fuerza a conservar las filas que contienen los extremos
            // reales para que no desaparezcan de la gráfica.
            const step = Math.ceil(data.length / 2000);
            var keepIdx = new Set();
            for (var i = 0; i < data.length; i += step) keepIdx.add(i);
            keepIdx.add(extHum.min.idx);
            keepIdx.add(extHum.max.idx);
            data = data.filter((_, i) => keepIdx.has(i));
        }

        var labels = data.map((row) => row.hora);
        var humedad_interior = data.map((row) => redondear1(parseFloat(row.humedad_interior)));

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
        var humMinArr = data.map((row) => extremoFila(row, "humedad_interior", "min"));
        var humMaxArr = data.map((row) => extremoFila(row, "humedad_interior", "max"));

        // Escala Y dinámica basada en los extremos reales del periodo,
        // acotada a 0-100%
        var minY = extHum.min.valor - 5;
        var maxY = extHum.max.valor + 5;
        if (minY < 0) minY = 0;
        if (maxY > 100) maxY = 100;

        var maxIdx = data.findIndex((row) => extremoFila(row, "humedad_interior", "max") === extHum.max.valor);
        var minIdx = data.findIndex((row) => extremoFila(row, "humedad_interior", "min") === extHum.min.valor);

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

            // --- NUEVO: DataZoom para hacer zoom/scroll ---
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
                    fillerColor: 'rgba(128, 0, 128, 0.2)', // Color púrpura/azul del tema
                    handleStyle: {
                        color: blueColor
                    },
                    textStyle: {
                        color: fontColor
                    }
                }
            ],
            // ------------------------------------------------

            xAxis: {
                type: "category",
                data: labels,
                axisLine: { lineStyle: { color: fontColor } },
                axisLabel: { color: fontColor }
            },
            yAxis: {
                type: "value",
                name: "%",
                min: minY.toFixed(1),
                max: maxY.toFixed(1),
                axisLine: { lineStyle: { color: fontColor } },
                axisLabel: { color: fontColor }
            },
            series: [
                ...serieBanda(humMinArr, humMaxArr, blueColor, "bandaHumInt"),
                {
                    name: "Humedad Interior",
                    data: humedad_interior,
                    type: "line",
                    smooth: true,
                    lineStyle: { width: 2, color: blueColor },
                    markPoint: {
                        data: [
                            { name: "Máx", coord: [maxIdx, extHum.max.valor], value: extHum.max.valor, itemStyle: { color: blueLight } },
                            { name: "Mín", coord: [minIdx, extHum.min.valor], value: extHum.min.valor, itemStyle: { color: darkBlue } }
                        ]
                    }
                }
            ]
        };

        myChart.setOption(option);
    })
        .catch((err) => {
        myChart.hideLoading();
        console.error("Error al cargar datos de humedad:", err)
    });
}
