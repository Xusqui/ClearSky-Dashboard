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
        var fetchUrl = "./static/modules/get_pressure_last24h.php";
        if (startDate && endDate) {
            fetchUrl += `?start=${encodeURIComponent(startDate)}&end=${encodeURIComponent(endDate)}`;
        }
        
        // Obtener colores
        const rootStyle = getComputedStyle(document.documentElement);
        const fontColor = rootStyle.getPropertyValue('--font-color').trim();
        const bgColor = rootStyle.getPropertyValue('--bg-color').trim();
        const greenColor = rootStyle.getPropertyValue('--wu-green') || 'green'; // Usar variable CSS si existe

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

                const labels = data.map(row => row.hora);
                const presiones = data.map(row => parseFloat(row.presion_relativa));

                // Escala Y dinámica
                const minY = Math.min(...presiones) - 2;
                const maxY = Math.max(...presiones) + 2;

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
                    series: [{
                        name: 'Presión Relativa',
                        data: presiones,
                        type: 'line',
                        smooth: true,
                        lineStyle: { width: 2, color: greenColor }, // Color de tu CSS
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
