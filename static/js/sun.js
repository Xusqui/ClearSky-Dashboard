// sun.js
// --- Sol ---

// update_status.js reinyecta este script como una etiqueta <script> nueva
// cada vez que llega un dato reciente de la estación (ver WIDGET_SCRIPTS en
// update_status.js). Guardamos el intervalo en `window` para no acumular un
// setInterval nuevo en cada reinyección: sin esto, dejar el dashboard
// abierto varias horas iría apilando temporizadores duplicados llamando a
// updateSunPosition() en paralelo.
if (window.__sunUpdateInterval) {
    clearInterval(window.__sunUpdateInterval);
}

function updateSunPosition() {
    const sunriseTimeEl = document.getElementById("sunrise-time");
    const sunsetTimeEl = document.getElementById("sunset-time");
    const noonTimeEl = document.getElementById("solar-noontime");
    const sunIcon = document.getElementById("sun-icon");
    if (!sunriseTimeEl || !sunsetTimeEl || !noonTimeEl || !sunIcon) return; // widget no presente en esta página

    // Hora real en cada ejecución, no la "now" global de conf_to_js.php
    // (fijada una única vez al cargar la página): con la global congelada el
    // icono se quedaba clavado en la posición de la carga inicial para
    // siempre, y si la página se cargaba fuera de las horas de sol, no
    // volvía a aparecer aunque el sol real ya hubiera salido.
    const now = new Date();

    const times = SunCalc.getTimes(now, latitude, longitude);
    const sunrise = times.sunrise;
    const sunset = times.sunset;
    const noontime = times.solarNoon;

    function formatTime(date) {
        return date.toLocaleTimeString("es-ES", { hour: "2-digit", minute: "2-digit" });
    }

    // Latitudes muy altas: si el sol no sale/se pone ese día, SunCalc
    // devuelve objetos Date inválidos (no null). Comparar un Date válido con
    // uno inválido siempre da "false", así que el guard de visibilidad de
    // abajo no saltaría y se acabaría calculando con NaN. Se detecta aquí y
    // se trata igual que "no hay sol ahora mismo".
    const hasValidTimes = !isNaN(sunrise) && !isNaN(sunset);

    sunriseTimeEl.textContent = hasValidTimes ? formatTime(sunrise) : '—';
    sunsetTimeEl.textContent = hasValidTimes ? formatTime(sunset) : '—';
    noonTimeEl.textContent = !isNaN(noontime) ? formatTime(noontime) : '—';

    if (!hasValidTimes || now < sunrise || now > sunset) {
        sunIcon.setAttribute("visibility", "hidden");
        return;
    }
    sunIcon.setAttribute("visibility", "visible");

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
// Refresco propio por reloj: antes solo se recalculaba cuando
// update_status.js detectaba un dato nuevo de la estación, así que si la
// estación se desconectaba, el icono también dejaba de moverse.
window.__sunUpdateInterval = setInterval(updateSunPosition, 60 * 1000);
