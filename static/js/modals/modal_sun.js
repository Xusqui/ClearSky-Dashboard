// --- widget_sun.js ---
function formatTime(date) {
    return date.toLocaleTimeString("es-ES", { hour: "2-digit", minute: "2-digit" });
}

// --- Obtener lat/lon desde el script ---
const scriptURL = document.currentScript.src;
const params = new URLSearchParams(scriptURL.split('?')[1]);
const lat = parseFloat(params.get('lat'));
const lon = parseFloat(params.get('lon'));

// --- Actualiza los datos del widget solar pequeño ---
function updateSunWidget() {
    const now = new Date();
    const times = SunCalc.getTimes(now, lat, lon);
    const sunrise = times.sunrise;
    const sunset = times.sunset;

    const sunriseEl = document.getElementById("sunrise-time");
    const sunsetEl = document.getElementById("sunset-time");
    if (sunriseEl) sunriseEl.textContent = formatTime(sunrise);
    if (sunsetEl) sunsetEl.textContent = formatTime(sunset);

    const sunIcon = document.getElementById("sun-icon");
    if (!sunIcon) return;

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
updateSunWidget();
setInterval(updateSunWidget, 60000);

// --- Modal ---
const modal = document.getElementById("solarModal");
const closeBtn = document.getElementById("closeSolarModal");

// --- Mostrar modal al hacer clic en el arco solar ---
const arcContainer = document.getElementById("sun-arc-container");
arcContainer.addEventListener("click", () => {
    fillSolarModal();
    modal.style.display = "flex";
});

// --- Cerrar modal ---
closeBtn.addEventListener("click", () => {
    modal.style.display = "none";
});
window.addEventListener("click", (e) => {
    if (e.target === modal) modal.style.display = "none";
});

// --- Función que llena los datos del modal ---
function fillSolarModal() {
    const now = new Date();
    const times = SunCalc.getTimes(now, lat, lon);
    const posNow = SunCalc.getPosition(now, lat, lon);
    const azSunrise = SunCalc.getPosition(times.sunrise, lat, lon).azimuth * 180 / Math.PI + 180;
    const azSunset = SunCalc.getPosition(times.sunset, lat, lon).azimuth * 180 / Math.PI + 180;
    const maxElev = (SunCalc.getPosition(times.solarNoon, lat, lon).altitude * 180 / Math.PI).toFixed(1);

    // --- Amanecer ---
    document.getElementById("astronomicalDawn").textContent = formatTime(times.nightEnd);     // Fin de la noche astronómica
    document.getElementById("nauticalDawn").textContent = formatTime(times.nauticalDawn);     // Comienzo crepúsculo náutico
    document.getElementById("civilDawn").textContent = formatTime(times.dawn);                // Comienzo crepúsculo civil
    document.getElementById("sunriseTime").textContent = formatTime(times.sunrise);           // Amanecer
    document.getElementById("solarNoonTime").textContent = formatTime(times.solarNoon);       // Mediodía solar
    document.getElementById("sunsetTime").textContent = formatTime(times.sunset);             // Puesta de sol

    // --- Anochecer ---
    document.getElementById("civilDusk").textContent = formatTime(times.dusk);                // Fin crepúsculo civil
    document.getElementById("nauticalDusk").textContent = formatTime(times.nauticalDusk);     // Fin crepúsculo náutico
    document.getElementById("astronomicalDusk").textContent = formatTime(times.night);        // Comienzo de la noche astronómica

    // --- Azimut y elevación ---
    document.getElementById("sunriseAzimuth").textContent = azSunrise.toFixed(1) + 'º';
    document.getElementById("sunsetAzimuth").textContent = azSunset.toFixed(1) + 'º';
    document.getElementById("maxElevation").textContent = maxElev + 'º';

    // --- Duración del día ---
    const dayLength = (times.sunset - times.sunrise) / 3600000;
    document.getElementById("dayLength").textContent = dayLength.toFixed(2);
}
