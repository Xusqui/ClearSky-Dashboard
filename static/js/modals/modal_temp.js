/* modal_temp.js */
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

// Abrir modal al hacer click en los widgets
document.getElementById("temp_widget").addEventListener("click", openTempModal);
document.getElementById("dew_point").addEventListener("click", openTempModal);

function openTempModal() {
    var modal = document.getElementById("tempModal");
    modal.style.display = "block";

    // --- NUEVO: Establecer fechas por defecto (últimas 24h) ---
    var now = new Date();
    var yesterday = new Date(now.getTime() - 24 * 60 * 60 * 1000);

    // Usamos los IDs específicos de este modal
    var startInput = document.getElementById("temp_startDate");
    var endInput = document.getElementById("temp_endDate");

    startInput.value = formatLocalDateTime(yesterday);
    endInput.value = formatLocalDateTime(now);
    // -----------------------------------------------------

    // Cargar gráfico inicial con las fechas por defecto
    loadTempChart(startInput.value, endInput.value);
}

// Cerrar modal al hacer click en el botón de cerrar
document.getElementById("closeModal").addEventListener("click", function () {
    closeTempModal();
});

// Cerrar modal al hacer click fuera del contenido
window.addEventListener("click", function (event) {
    var modal = document.getElementById("tempModal");
    if (event.target === modal) {
        closeTempModal();
    }
});

// --- NUEVO: Event Listener para el botón de actualizar ---
document.getElementById("temp_updateChartBtn").addEventListener("click", function() {
    var startDate = document.getElementById("temp_startDate").value;
    var endDate = document.getElementById("temp_endDate").value;

    if (!startDate || !endDate) {
        alert("Por favor, selecciona un rango de fechas y horas válido.");
        return;
    }
    if (new Date(startDate) >= new Date(endDate)) {
        alert("La fecha de inicio debe ser anterior a la fecha de fin.");
        return;
    }

    // Volver a cargar el gráfico con el nuevo rango
    loadTempChart(startDate, endDate);
});
// ---------------------------------------------------

// Función para cerrar modal y destruir gráfico
function closeTempModal() {
    var modal = document.getElementById("tempModal");
    modal.style.display = "none";

    var chartDom = document.getElementById("tempChart");
    var myChart = echarts.getInstanceByDom(chartDom);
    if (myChart) {
        myChart.dispose(); // destruye la instancia de ECharts
    }
}

// Función para cargar datos y dibujar gráfico
// --- MODIFICADO: Acepta parámetros startDate y endDate ---
function loadTempChart(startDate, endDate) {
    var chartDom = document.getElementById("tempChart");

    // --- MODIFICADO: Destruir gráfico anterior si existe ---
    var myChart = echarts.getInstanceByDom(chartDom);
    if (myChart) {
        myChart.dispose();
    }
    myChart = echarts.init(chartDom);
    // ---------------------------------------------------

    var rootStyle = getComputedStyle(document.documentElement);
    var fontColor = rootStyle.getPropertyValue("--font-color").trim();
    var bgColor = rootStyle.getPropertyValue("--bg-color").trim();
    var redColor = rootStyle.getPropertyValue("--red").trim();
    var greenColor = rootStyle.getPropertyValue("--green").trim();
    var lightBlue = rootStyle.getPropertyValue("--lightblue").trim();
    var lightBlue80 = rootStyle.getPropertyValue("--lightblue80").trim();

    // --- MODIFICADO: Construir la URL de fetch dinámicamente ---
    var fetchUrl = "./static/modules/modals/get_temp_historic.php";
    if (startDate && endDate) {
        fetchUrl += `?start=${encodeURIComponent(startDate)}&end=${encodeURIComponent(endDate)}`;
    }
    // -----------------------------------------------------------

    // Mostrar "cargando"
    myChart.showLoading({
        text: 'Cargando datos...',
        color: redColor, // Usa uno de tus colores
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

            // Extremos reales del periodo, calculados ANTES de decimar: para
            // rangos largos el backend agrega por día/hora con AVG (para que
            // la línea no sea un muro de puntos), lo que aplana los picos. Las
            // columnas "_min"/"_max" que manda el backend en esos casos sí
            // llevan el mínimo/máximo real de cada día/hora; si no vienen
            // (rango corto, sin agregar), cada fila ya es un dato real y se
            // usa el propio campo.
            // Redondeo a 1 decimal: AVG() en MySQL (usado en los rangos
            // agregados por día/hora) genera muchos decimales (ej.
            // 22.072892...) que no aportan precisión real y quedan feos en
            // pantalla. Se aplica en todos los valores mostrados (línea,
            // banda y etiquetas Máx/Mín) para que sean consistentes entre sí.
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
            var camposExtremos = ["temperatura", "sensacion_termica", "punto_rocio"];
            var extremos = {};
            camposExtremos.forEach((campo) => {
                extremos[campo] = { min: extremoPeriodo(campo, "min"), max: extremoPeriodo(campo, "max") };
            });

            if (data.length > 5000) {
                // Decimación: evita renderizar decenas de miles de puntos si el
                // usuario elige un rango de varios días sin agregar en el backend.
                // Se fuerza a conservar las filas que contienen los extremos
                // reales para que no desaparezcan de la gráfica.
                const step = Math.ceil(data.length / 2000);
                var keepIdx = new Set();
                for (var i = 0; i < data.length; i += step) keepIdx.add(i);
                camposExtremos.forEach((campo) => {
                    keepIdx.add(extremos[campo].min.idx);
                    keepIdx.add(extremos[campo].max.idx);
                });
                data = data.filter((_, i) => keepIdx.has(i));
            }

            var labels = data.map((row) => row.hora);
            var temperaturas = data.map((row) => redondear1(parseFloat(row.temperatura)));
            var sensaciones = data.map((row) => redondear1(parseFloat(row.sensacion_termica)));
            var puntosRocio = data.map((row) => redondear1(parseFloat(row.punto_rocio)));

            // Banda sombreada mín-máx real para la temperatura (la métrica
            // principal): así el "Máx"/"Mín" del periodo coincide con el
            // borde de la banda dibujada, en vez de quedar como una etiqueta
            // flotando por encima de una línea que nunca llega a ese valor.
            // Truco de ECharts: dos series apiladas (stack), la primera
            // invisible hasta el mínimo, la segunda visible con la diferencia
            // hasta el máximo -> el área visible va exactamente de mín a máx.
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
            var tempMinArr = data.map((row) => extremoFila(row, "temperatura", "min"));
            var tempMaxArr = data.map((row) => extremoFila(row, "temperatura", "max"));

            // Escala Y dinámica basada en los extremos reales del periodo, no
            // en la serie (potencialmente promediada) que se dibuja.
            var minY = Math.min(extremos.temperatura.min.valor, ...sensaciones, ...puntosRocio) - 2;
            var maxY = Math.max(extremos.temperatura.max.valor, ...sensaciones, ...puntosRocio) + 2;

            // markPoint explícito para Temperatura (en vez de type:"max"/"min"
            // automático de ECharts, que tomaría el máximo/mínimo de la serie
            // ya dibujada -posiblemente promediada- en lugar del valor real
            // del periodo). Al coincidir con el borde de la banda sombreada,
            // el marcador queda visualmente coherente con lo dibujado.
            function markPointReal(campo, colorMax, colorMin) {
                var maxIdx = data.findIndex((row) => extremoFila(row, campo, "max") === extremos[campo].max.valor);
                var minIdx = data.findIndex((row) => extremoFila(row, campo, "min") === extremos[campo].min.valor);
                return {
                    data: [
                        { name: "Máx", coord: [maxIdx, extremos[campo].max.valor], value: extremos[campo].max.valor, itemStyle: { color: colorMax } },
                        { name: "Mín", coord: [minIdx, extremos[campo].min.valor], value: extremos[campo].min.valor, itemStyle: { color: colorMin } }
                    ]
                };
            }

            var option = {
                backgroundColor: bgColor,
                tooltip: { trigger: "axis", backgroundColor: bgColor, textStyle: { color: fontColor } },
                legend: { data: ["Temperatura", "Sensación térmica", "Punto de rocío"], textStyle: { color: fontColor } },

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
                        fillerColor: 'rgba(255, 0, 0, 0.2)', // Color rojo de tu tema
                        handleStyle: {
                            color: redColor
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
                    name: "°C",
                    min: minY.toFixed(1),
                    max: maxY.toFixed(1),
                    axisLine: { lineStyle: { color: fontColor } },
                    axisLabel: { color: fontColor }
                },
                series: [
                    ...serieBanda(tempMinArr, tempMaxArr, redColor, "bandaTemp"),
                    {
                        name: "Temperatura",
                        data: temperaturas,
                        type: "line",
                        smooth: true,
                        lineStyle: { width: 2, color: redColor },
                        markPoint: markPointReal("temperatura", "darkred", "orange")
                    },
                    {
                        name: "Sensación térmica",
                        data: sensaciones,
                        type: "line",
                        smooth: true,
                        lineStyle: { width: 2, color: greenColor },
                        // Sin banda propia (evita solapar 3 áreas a la vez);
                        // el marcador usa el máximo/mínimo de la propia línea
                        // dibujada, así que siempre queda sobre ella.
                        markPoint: {
                            data: [
                                { type: "max", name: "Máx", itemStyle: { color: "darkgreen" } },
                                { type: "min", name: "Mín", itemStyle: { color: "lightgreen" } }
                            ]
                        }
                    },
                    {
                        name: "Punto de rocío",
                        data: puntosRocio,
                        type: "line",
                        smooth: true,
                        lineStyle: { width: 2, color: lightBlue80 },
                        areaStyle: { color: lightBlue }, // relleno debajo de la línea
                        markPoint: {
                            data: [
                                { type: "max", name: "Máx", itemStyle: { color: lightBlue80 } },
                                { type: "min", name: "Mín", itemStyle: { color: lightBlue80 } }
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
