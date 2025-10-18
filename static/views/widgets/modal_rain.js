document.addEventListener("DOMContentLoaded", function () {
    const modal = document.getElementById("rain-modal");
    const span = modal.querySelector(".close");
    const widget = document.getElementById("rain-widget"); 

    widget.onclick = function () {
        modal.style.display = "block";
        
        // Llamada AJAX para traer datos de estado de lluvia
        fetch("/weather/static/modules/get_rain_historic_data.php")
            .then(response => response.json())
            .then(data => {
                let estado = (parseFloat(data.status) > 0) ? "Lloviendo" : "No llueve";
                document.getElementById("rain-status").textContent = estado;
                document.getElementById("rain-rate").textContent  = (data.rate ? data.rate + " mm/h" : "N/A");
                document.getElementById("rain-today").textContent = (data.daily ? data.daily + " mm" : "N/A");
                document.getElementById("rain-hour").textContent = (data.hourly ? data.hourly + " mm" : "N/A");
                document.getElementById("rain-month").textContent = (data.monthly ? data.monthly + " mm" : "N/A");
                document.getElementById("rain-total").textContent = (data.total ? data.total + " mm" : "N/A");
            })
            .catch(error => console.error("Error cargando datos de lluvia:", error));

        // Llamada AJAX para traer datos mensuales para la gráfica
        fetch("/weather/static/modules/get_rain_monthly.php")
            .then(response => response.json())
            .then(data => {
                const chartDom = document.getElementById("rain-month-chart");
                const myChart = echarts.init(chartDom);

                const rootStyle = getComputedStyle(document.documentElement);
                const barColor = rootStyle.getPropertyValue('--wu-lightblue').trim();
                const fontColor = rootStyle.getPropertyValue('--font-color').trim();
                const bgColor = rootStyle.getPropertyValue('--bg-color').trim();

                const option = {
                    backgroundColor: bgColor,
                    tooltip: { trigger: 'axis',
                              backgroundColor : bgColor,
                              textStyle: { color: fontColor } },
                    xAxis: {
                        type: 'category',
                        data: ['Ene','Feb','Mar','Abr','May','Jun','Jul','Ago','Sep','Oct','Nov','Dic'],
                        axisLine: { lineStyle: { color: fontColor } },
                        axisLabel: { color: fontColor }
                    },
                    yAxis: {
                        type: 'value',
                        name: 'mm',
                        axisLine: { lineStyle: { color: fontColor } },
                        axisLabel: { color: fontColor }
                    },
                    series: [{
                        name: 'Precipitación mensual',
                        data: data,
                        type: 'bar',
                        itemStyle: { color: barColor }
                    }]
                };

                myChart.setOption(option);
            })
            .catch(error => console.error("Error cargando datos mensuales:", error));
    };

    // Cerrar modal al hacer click en la X
    span.onclick = function () {
        modal.style.display = "none";
    };

    // Cerrar modal al hacer click fuera
    window.onclick = function (event) {
        if (event.target == modal) {
            modal.style.display = "none";
        }
    };
});
