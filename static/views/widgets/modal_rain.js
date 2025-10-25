// --- NUEVA FUNCIÓN UTILITARIA ---
// Formatea un objeto Date al formato 'YYYY-MM' que usa input type="month"
function formatLocalMonth(date) {
    const pad = (num) => (num < 10 ? '0' + num : num);
    const year = date.getFullYear();
    const month = pad(date.getMonth() + 1); // getMonth() es 0-indexado
    return `${year}-${month}`;
}
// ---------------------------------

document.addEventListener("DOMContentLoaded", function () {
    const modal = document.getElementById("rain-modal");
    const closeBtn = document.getElementById("closeRainModal"); // ID del botón de cerrar
    const widget = document.getElementById("rain-widget");
    const updateBtn = document.getElementById("rain_updateChartBtn"); // Botón de actualizar
    const startInput = document.getElementById("rain_startMonth"); // Input de mes
    const endInput = document.getElementById("rain_endMonth"); // Input de mes

    let rainChart = null; // Variable para guardar la instancia del gráfico

    // --- Evento para ABRIR el modal ---
    widget.onclick = function () {
        modal.style.display = "block";
        
        // 1. Cargar datos de estado (la cuadrícula de estadísticas)
        // (Esto consulta 'get_rain_historic_data.php')
        fetch("./static/modules/get_rain_historic_data.php")
            .then(response => response.json())
            .then(data => {
                let estado = (parseFloat(data.status) > 0) ? "Lloviendo" : "No llueve";
                document.getElementById("rain-status").textContent = estado;
                document.getElementById("rain-rate").textContent = (data.rate ? data.rate + " mm/h" : "N/A");
                document.getElementById("rain-today").textContent = (data.daily ? data.daily + " mm" : "N/A");
                document.getElementById("rain-hour").textContent = (data.hourly ? data.hourly + " mm" : "N/A");
                document.getElementById("rain-month").textContent = (data.monthly ? data.monthly + " mm" : "N/A");
                document.getElementById("rain-total").textContent = (data.total ? data.total + " mm" : "N/A");
            })
            .catch(error => console.error("Error cargando datos de lluvia:", error));

        // 2. Establecer fechas por defecto para el gráfico (Año actual)
        const now = new Date();
        const year = now.getFullYear();
        const defaultStart = `${year}-01`; // Enero
        const defaultEnd = formatLocalMonth(now); // Mes actual

        startInput.value = defaultStart;
        endInput.value = defaultEnd;

        // 3. Cargar el gráfico con los datos por defecto
        loadRainChart(defaultStart, defaultEnd);
    };

    // --- Evento para ACTUALIZAR el gráfico ---
    updateBtn.addEventListener("click", function() {
        const startDate = startInput.value;
        const endDate = endInput.value;

        if (!startDate || !endDate) {
            alert("Por favor, selecciona un mes de inicio y fin.");
            return;
        }
        if (new Date(startDate) > new Date(endDate)) {
            alert("El mes de inicio debe ser anterior o igual al mes de fin.");
            return;
        }
        
        loadRainChart(startDate, endDate);
    });
    
    // --- Función para CERRAR el modal ---
    function closeRainModal() {
        modal.style.display = "none";
        // Destruir gráfico al cerrar
        if (rainChart) {
            rainChart.dispose();
            rainChart = null;
        }
    }

    // Eventos de cierre
    closeBtn.onclick = closeRainModal;
    window.onclick = function (event) {
        if (event.target == modal) {
            closeRainModal();
        }
    };

    // --- FUNCIÓN PRINCIPAL: Cargar Gráfico de Lluvia ---
    function loadRainChart(startMonth, endMonth) {
        const chartDom = document.getElementById("rain-month-chart");
        
        // Limpiar "No hay datos" anterior
        chartDom.innerHTML = '';

        // Destruir gráfico anterior si existe
        if (rainChart) {
            rainChart.dispose();
        }
        rainChart = echarts.init(chartDom);
        
        // Colores
        const rootStyle = getComputedStyle(document.documentElement);
        const barColor = rootStyle.getPropertyValue('--wu-lightblue').trim();
        const fontColor = rootStyle.getPropertyValue('--font-color').trim();
        const bgColor = rootStyle.getPropertyValue('--bg-color').trim();
        
        // URL con parámetros (consulta 'get_rain_monthly.php')
        const fetchUrl = `./static/modules/get_rain_monthly.php?start=${encodeURIComponent(startMonth)}&end=${encodeURIComponent(endMonth)}`;

        // Mostrar "cargando"
        rainChart.showLoading({
            text: 'Cargando datos...',
            color: barColor,
            textColor: fontColor,
            maskColor: 'rgba(255, 255, 255, 0.1)'
        });

        fetch(fetchUrl)
            .then(response => response.json())
            .then(chartData => { // Esperamos un objeto {labels: [], data: []}
                rainChart.hideLoading();

                if (chartData.error) {
                    console.error(chartData.message);
                    chartDom.innerHTML = `<p style="text-align:center; color:red; padding-top: 50px;">Error al cargar datos: ${chartData.message}</p>`;
                    return;
                }
                
                if (chartData.data.length === 0) {
                     chartDom.innerHTML = `<p style="text-align:center; color:${fontColor}; padding-top: 50px;">No hay datos de precipitación para el rango seleccionado.</p>`;
                    return;
                }

                // Cálculo de maxY para que la gráfica no se vea mal si todo es 0
                const dataMax = Math.max(...chartData.data);
                const maxY = dataMax > 0 ? dataMax * 1.2 : 1; // Si todo es 0, pone el eje en 1

                const option = {
                    backgroundColor: bgColor,
                    tooltip: { 
                        trigger: 'axis',
                        backgroundColor : bgColor,
                        textStyle: { color: fontColor }
                    },
                    dataZoom: [
                        { type: 'inside', start: 0, end: 100 },
                        {
                            type: 'slider',
                            start: 0,
                            end: 100,
                            backgroundColor: 'rgba(0,0,0,0.1)',
                            borderColor: '#777',
                            fillerColor: 'rgba(0, 150, 255, 0.2)',
                            handleStyle: { color: barColor },
                            textStyle: { color: fontColor }
                        }
                    ],
                    xAxis: {
                        type: 'category',
                        data: chartData.labels, // Labels dinámicos del JSON
                        axisLine: { lineStyle: { color: fontColor } },
                        axisLabel: { color: fontColor, rotate: 30 } // Rotar etiquetas si son muchas
                    },
                    yAxis: {
                        type: 'value',
                        name: 'mm',
                        min: 0, // La lluvia no puede ser negativa
                        max: maxY, // Eje Y dinámico
                        axisLine: { lineStyle: { color: fontColor } },
                        axisLabel: { color: fontColor }
                    },
                    series: [{
                        name: 'Precipitación mensual',
                        data: chartData.data, // Datos dinámicos del JSON
                        type: 'bar',
                        itemStyle: { color: barColor },
                        label: {
                            show: true,
                            position: 'top',
                            color: fontColor,
                            fontSize: 10,
                            formatter: (params) => {
                                // Solo mostrar etiqueta si es mayor que 0
                                if (params.value > 0) {
                                    return params.value;
                                } else {
                                    return '';
                                }
                            }
                        }
                    }]
                };

                rainChart.setOption(option);
            })
            .catch(error => {
                rainChart.hideLoading();
                console.error("Error al cargar datos mensuales:", error);
                chartDom.innerHTML = `<p style="text-align:center; color:red; padding-top: 50px;">Error de conexión al cargar el gráfico.</p>`;
            });
    }
});
