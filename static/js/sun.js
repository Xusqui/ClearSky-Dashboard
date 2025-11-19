// sun.js
// --- Sol ---
function updateSunPosition() {
    const scriptURL = document.querySelector('script[src*="sun.js"]').src;
    const params = new URLSearchParams(scriptURL.split('?')[1]);
    const lat = parseFloat(params.get('lat'));
    const lon = parseFloat(params.get('lon'));
    const now = new Date();
    const times = SunCalc.getTimes(now, lat, lon);
    const sunrise = times.sunrise;
    const sunset = times.sunset;
    const noontime = times.solarNoon;
    function formatTime(date) {
        return date.toLocaleTimeString("es-ES", { hour: "2-digit", minute: "2-digit" });
    }
    document.getElementById("sunrise-time").textContent = formatTime(sunrise);
    document.getElementById("sunset-time").textContent = formatTime(sunset);
    document.getElementById("solar-noontime").textContent = formatTime(noontime);
    const sunIcon = document.getElementById("sun-icon");
    if (now < sunrise || now > sunset) {
        sunIcon.setAttribute("visibility", "hidden");
        return;
    } else {
        sunIcon.setAttribute("visibility", "visible");
    }
    const dayProgress = (now - sunrise) / (sunset - sunrise);
    const radiusX = 40;
    const radiusY = 35;
    const centerX = 50;
    const centerY = 56;
    const angle = Math.PI * (1 - dayProgress);
    const x = centerX + radiusX * Math.cos(angle);
    const y = centerY - radiusY * Math.sin(angle);
    sunIcon.setAttribute("x", x - 15);
    sunIcon.setAttribute("y", y - 15);
}
updateSunPosition();
setInterval(updateSunPosition, 60000);
