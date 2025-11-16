/* modal_rain.js */

// --- NUEVAS FUNCIONES UTILITARIAS ---

// Formatea un objeto Date al formato 'YYYY-MM'
function formatLocalMonth(date) {
    const pad = (num) => (num < 10 ? '0' + num : num);
    const year = date.getFullYear();
    const month = pad(date.getMonth() + 1);
    return `${year}-${month}`;
}

// Formatea un objeto Date al formato 'YYYY-MM-DDTHH:MM' para datetime-local
function formatLocalDatetime(date) {
    const pad = (num) => (num < 10 ? '0' + num : num);
    const year = date.getFullYear();
    const month = pad(date.getMonth() + 1);
    const day = pad(date.getDate());
    const hours = pad(date.getHours());
    const minutes = pad(date.getMinutes());
    return `${year}-${month}-${day}T${hours}:${minutes}`;
}
// ---------------------------------

document.addEventListener("DOMContentLoaded", function () {
    const API_URL = "./static/modules/modals/get_rain_data_api.php";

    const modal = document.getElementById("rain-modal");
    const closeBtn = document.getElementById("closeRainModal");
    const widget = document.getElementById("rain-widget");

    // Selectores y botones para el GRÁFICO MENSUAL
    const updateMonthBtn = document.getElementById("rain_updateMonthChartBtn");
    const startMonthInput = document.getElementById("rain_startMonth");
    const endMonthInput = document.getElementById("rain_endMonth");

    // Selectores y botones para el GRÁFICO DETALLADO
    const updateDetailBtn = document.getElementById("rain_updateDetailChartBtn");
    const startDatetimeInput = document.getElementById("rain_startDatetime");
    const endDatetimeInput = document.getElementById("rain_endDatetime");

    let monthChart = null; // Instancia para el gráfico mensual (Barras)
    let detailChart = null; // Instancia para el gráfico detallado (Líneas)

    // Colores globales (obtener del CSS)
    const rootStyle = getComputedStyle(document.documentElement);
    const primaryColor = rootStyle.getPropertyValue('--wu-lightblue').trim() || '#007bff';
    const secondaryColor = rootStyle.getPropertyValue('--wu-lightgreen').trim() || '#00bfa5';
    const fontColor = rootStyle.getPropertyValue('--font-color').trim() || '#333';
    const bgColor = rootStyle.getPropertyValue('--bg-color').trim() || '#fff';

    // --- Evento para ABRIR el modal ---
    widget.onclick = function () {
        modal.style.display = "block";

        // 1. Cargar datos de estado (la cuadrícula de estadísticas)
        fetch(`${API_URL}?mode=stats`)
            .then(response => response.json())
            .then(data => {
                // Rellenar la cuadrícula de estadísticas
                let estado = (parseFloat(data.rate) > 0) ? "Llueve" : "No llueve";
                document.getElementById("rain-status").textContent = estado;
                document.getElementById("rain-rate").textContent = (data.rate ? data.rate + " mm/h" : "0.0 mm/h");
                document.getElementById("rain-evento").textContent = (data.event ? data.event + " mm" : "0.0 mm");
                document.getElementById("rain-today").textContent = (data.rain_daily ? data.rain_daily + " mm" : "0.0 mm");
                document.getElementById("rain-hour").textContent = (data.hourly ? data.hourly + " mm" : "0.0 mm");
                document.getElementById("rain-month").textContent = (data.monthly ? data.monthly + " mm" : "0.0 mm");
                document.getElementById("rain-total").textContent = (data.total ? data.total + " mm" : "0.0 mm");
                document.getElementById("rain-week").textContent = (data.rain_weekly ? data.rain_weekly + " mm" : "0.0 mm");
                document.getElementById("rain-year").textContent = (data.rain_yearly ? data.rain_yearly + " mm" : "0.0 mm");
            })
            .catch(error => console.error("Error cargando estadísticas de lluvia:", error));

        // 2. Establecer fechas por defecto para el gráfico MENSUAL (Año actual)
        const now = new Date();
        const year = now.getFullYear();
        const defaultStartMonth = `${year}-01`;
        const defaultEndMonth = formatLocalMonth(now);

        startMonthInput.value = defaultStartMonth;
        endMonthInput.value = defaultEndMonth;

        // 3. Establecer fechas por defecto para el gráfico DETALLADO (Últimas 24h)
        const yesterday = new Date(now.getTime() - 24 * 60 * 60 * 1000);

        startDatetimeInput.value = formatLocalDatetime(yesterday);
        endDatetimeInput.value = formatLocalDatetime(now);

        // 4. Cargar ambos gráficos con los datos por defecto
        loadMonthChart(defaultStartMonth, defaultEndMonth);
        loadDetailChart(startDatetimeInput.value, endDatetimeInput.value);
    };

    // --- Evento para ACTUALIZAR el gráfico MENSUAL ---
    updateMonthBtn.addEventListener("click", function() {
        const startDate = startMonthInput.value;
        const endDate = endMonthInput.value;

        if (!startDate || !endDate) return alert("Por favor, selecciona un mes de inicio y fin.");
        if (new Date(startDate) > new Date(endDate)) return alert("El mes de inicio debe ser anterior o igual al mes de fin.");

        loadMonthChart(startDate, endDate);
    });

    // --- Evento para ACTUALIZAR el gráfico DETALLADO ---
    updateDetailBtn.addEventListener("click", function() {
        const startDate = startDatetimeInput.value;
        const endDate = endDatetimeInput.value;

        if (!startDate || !endDate) return alert("Por favor, selecciona una fecha/hora de inicio y fin.");
        if (new Date(startDate) > new Date(endDate)) return alert("La fecha/hora de inicio debe ser anterior o igual a la de fin.");

        loadDetailChart(startDate, endDate);
    });

    // --- Función para CERRAR el modal ---
    function closeRainModal() {
        modal.style.display = "none";
        // Destruir gráficos al cerrar
        if (monthChart) { monthChart.dispose(); monthChart = null; }
        if (detailChart) { detailChart.dispose(); detailChart = null; }
    }

    // Eventos de cierre
    closeBtn.onclick = closeRainModal;
    window.onclick = function (event) {
        if (event.target == modal) {
            closeRainModal();
        }
    };

    // --- FUNCIÓN A: Cargar Gráfico MENSUAL (Barras - lluvia_mes) ---
    function loadMonthChart(startMonth, endMonth) {
        const chartDom = document.getElementById("rain-month-chart");
        chartDom.innerHTML = '';
        if (monthChart) { monthChart.dispose(); }
        monthChart = echarts.init(chartDom);

        const fetchUrl = `${API_URL}?mode=monthly&start=${encodeURIComponent(startMonth)}&end=${encodeURIComponent(endMonth)}`;

        monthChart.showLoading({ text: 'Cargando datos...', color: primaryColor, textColor: fontColor });

        fetch(fetchUrl)
            .then(response => response.json())
            .then(chartData => {
                monthChart.hideLoading();

                if (chartData.error || chartData.data.length === 0) {
                    const message = chartData.message || "No hay datos de precipitación para el rango seleccionado.";
                    chartDom.innerHTML = `<p style="text-align:center; color:${fontColor}; padding-top: 50px;">${message}</p>`;
                    return;
                }

                const dataMax = Math.max(...chartData.data);
                const maxY = dataMax > 0 ? dataMax * 1.2 : 1;

                const option = {
                    backgroundColor: bgColor,
                    tooltip: { trigger: 'axis', backgroundColor : bgColor, textStyle: { color: fontColor } },
                    dataZoom: [{ type: 'inside', start: 0, end: 100 }],
                    xAxis: {
                        type: 'category',
                        data: chartData.labels,
                        axisLine: { lineStyle: { color: fontColor } },
                        axisLabel: { color: fontColor, rotate: 30 }
                    },
                    yAxis: {
                        type: 'value',
                        name: 'mm',
                        min: 0,
                        max: maxY,
                        axisLine: { lineStyle: { color: fontColor } },
                        axisLabel: { color: fontColor }
                    },
                    series: [{
                        name: 'Precipitación mensual',
                        data: chartData.data,
                        type: 'bar',
                        itemStyle: { color: primaryColor },
                        label: {
                            show: true,
                            position: 'top',
                            color: fontColor,
                            fontSize: 10,
                            formatter: (params) => params.value > 0 ? params.value : ''
                        }
                    }]
                };
                monthChart.setOption(option);
            })
            .catch(error => {
                monthChart.hideLoading();
                chartDom.innerHTML = `<p style="text-align:center; color:red; padding-top: 50px;">Error de conexión al cargar el gráfico mensual.</p>`;
                console.error("Error al cargar datos mensuales:", error);
            });
    }

    // --- FUNCIÓN B: Cargar Gráfico DETALLADO (Líneas - lluvia_rate, lluvia_evento) ---
    function loadDetailChart(startDate, endDate) {
        const chartDom = document.getElementById("rain-detail-chart");
        chartDom.innerHTML = '';
        if (detailChart) { detailChart.dispose(); }
        detailChart = echarts.init(chartDom);

        const fetchUrl = `${API_URL}?mode=detailed&start=${encodeURIComponent(startDate)}&end=${encodeURIComponent(endDate)}`;

        detailChart.showLoading({ text: 'Cargando datos...', color: primaryColor, textColor: fontColor });

        fetch(fetchUrl)
            .then(response => response.json())
            .then(chartData => {
                detailChart.hideLoading();

                if (chartData.error || chartData.labels.length === 0) {
                    const message = chartData.message || "No hay datos detallados de precipitación para el rango seleccionado.";
                    chartDom.innerHTML = `<p style="text-align:center; color:${fontColor}; padding-top: 50px;">${message}</p>`;
                    return;
                }

                // Ejes Y dinámicos para el Rate (Línea) y Evento (Barra)
                const rateData = chartData.series.find(s => s.name === 'Lluvia Rate').data;
                const eventData = chartData.series.find(s => s.name === 'Lluvia Evento').data;

                const maxRate = Math.max(...rateData);
                const maxEvent = Math.max(...eventData);

                // Configuración de ECharts para el gráfico de líneas (Lluvia Evento y Rate)
                const option = {
                    backgroundColor: bgColor,
                    tooltip: {
                        trigger: 'axis',
                        backgroundColor : bgColor,
                        textStyle: { color: fontColor },
                        axisPointer: { type: 'cross' }
                    },
                    legend: {
                        data: ['Lluvia Rate', 'Lluvia Evento'],
                        textStyle: { color: fontColor }
                    },
                    dataZoom: [{ type: 'inside', start: 0, end: 100 }],
                    xAxis: {
                        type: 'category',
                        data: chartData.labels, // Horas convertidas
                        axisLine: { lineStyle: { color: fontColor } },
                        axisLabel: { color: fontColor, rotate: 30 }
                    },
                    yAxis: [
                        { // Eje Y1: Lluvia Rate (Línea)
                            type: 'value',
                            name: 'mm/h (Rate)',
                            min: 0,
                            max: maxRate > 0 ? maxRate * 1.2 : 1,
                            position: 'left',
                            axisLine: { lineStyle: { color: primaryColor } },
                            splitLine: { lineStyle: { color: 'rgba(255, 255, 255, 0.1)' } },
                            axisLabel: { formatter: '{value}', color: primaryColor }
                        },
                        { // Eje Y2: Lluvia Evento (Barra)
                            type: 'value',
                            name: 'mm (Evento)',
                            min: 0,
                            max: maxEvent > 0 ? maxEvent * 1.2 : 1,
                            position: 'right',
                            offset: 0,
                            axisLine: { lineStyle: { color: secondaryColor } },
                            splitLine: { show: false }, // Ocultar líneas de división para el segundo eje
                            axisLabel: { formatter: '{value}', color: secondaryColor }
                        }
                    ],
                    series: [
                        {
                            name: 'Lluvia Rate',
                            type: 'line',
                            yAxisIndex: 0,
                            data: rateData,
                            itemStyle: { color: primaryColor },
                            smooth: true,
                            showSymbol: false
                        },
                        {
                            name: 'Lluvia Evento',
                            type: 'bar', // Representar el evento como barra (acumulado)
                            yAxisIndex: 1,
                            data: eventData,
                            itemStyle: { color: secondaryColor, opacity: 0.6 }
                        }
                    ]
                };
                detailChart.setOption(option);
            })
            .catch(error => {
                detailChart.hideLoading();
                chartDom.innerHTML = `<p style="text-align:center; color:red; padding-top: 50px;">Error de conexión al cargar el gráfico detallado.</p>`;
                console.error("Error al cargar datos detallados:", error);
            });
    }
});
