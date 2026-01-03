/* /static/js/widgets/dew_widget.js */
function updateDewWidget() {
    fetch('./static/modules/widgets/get_dew_data.php')
        .then(r => r.json())
        .then(d => {
            if (d.error) return console.error(d.message);

            const main = document.getElementById('dewpoint-widget-main-display');
            if (main) main.textContent = d.dew.toFixed(1);

            const dewWidget = document.querySelector('dew-point-widget-view');
            if (dewWidget) {
                dewWidget.style.setProperty('--dewpoint-droplet-width', `${d.percent}%`);
            }

            const trend = document.getElementById('dew-trend');
            if (trend) {
                trend.className = 'dew-trend'; // reset
                if (d.trend === 'up') {
                    trend.textContent = 'Subiendo ▲';
                    trend.classList.add('up');
                } else if (d.trend === 'down') {
                    trend.textContent = 'Bajando ▼';
                    trend.classList.add('down');
                } else {
                    trend.textContent = 'Estable ●';
                    trend.classList.add('flat');
                }
            }

            const dewProb = document.getElementById('dew-prob');
            if (dewProb) dewProb.textContent = `💧 ${d.dew_probability}%`;

            const fogProb = document.getElementById('fog-prob');
            if (fogProb) fogProb.textContent = `🌫 ${d.fog_probability}%`;
        })
        .catch(e => console.error("Dew error:", e));
}

updateDewWidget();
