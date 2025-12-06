// sun.js
//
// --- Sol ---
function updateSunPosition() {
    const times = SunCalc.getTimes(now, latitude, longitude);
    const sunrisefull = Astronomy.SearchRiseSet('Sun', observer, +1, startAstro, 1);
    const sunsetfull = Astronomy.SearchRiseSet('Sun', observer, -1, startAstro, 1);
    const sunrise = sunrisefull.date;
    const sunset = sunsetfull.date;
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
