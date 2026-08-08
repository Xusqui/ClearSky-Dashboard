// moon.js
// Asume que conf_to_js.php ha definido: const latitude, longitude (a partir de LAT/LON).

// Función auxiliar para formatear la hora (HH:MM)
function formatTime(date) {
    // Si la hora es nula (ej. la luna no sale/se pone hoy) retorna un guion
    if (!date) return '—';
    // Opciones para asegurar formato de 24h
    const options = { hour: '2-digit', minute: '2-digit', hourCycle: 'h23' };
    return date.toLocaleTimeString(navigator.language, options);
}

// Referencia al <link> del CSS dinámico de la fase (se crea la primera vez, se reutiliza después)
let moonPhaseCssLink = null;

function updateMoonWidget() {
    const textEl = document.getElementById("moon-text");
    const riseEl = document.getElementById("moon-rise-time");
    const setEl = document.getElementById("moon-set-time");
    if (!textEl || !riseEl || !setEl) return; // widget no presente en esta página

    const now = new Date();

    const moon = SunCalc.getMoonIllumination(now);
    const fraction = moon.fraction;
    const phase = moon.phase;

    window.moon = moon;
    window.fraction = fraction;
    window.phase = phase;

    // --- Altura de la luna ---
    const moonPos = SunCalc.getMoonPosition(now, latitude, longitude);
    window.moonAltitude = moonPos.altitude * 180 / Math.PI;

    // --- Salida y puesta de la Luna ---
    const moonTimes = SunCalc.getMoonTimes(now, latitude, longitude);
    window.moonTimes = moonTimes;

    const tomorrow = new Date(now);
    tomorrow.setDate(now.getDate() + 1);
    const moonTimesTomorrow = SunCalc.getMoonTimes(tomorrow, latitude, longitude);

    // Obtener la hora de salida (siempre del día 'now')
    let moonRiseTime;
    if (moonTimes.rise) {
        moonRiseTime = formatTime(moonTimes.rise);
    } else if (moonTimesTomorrow.rise) {
        // si no sale hoy, comprobamos si sale mañana.
        moonRiseTime = formatTime(moonTimesTomorrow.rise);
    } else {
        moonRiseTime = '—';
    }

    // Obtener la hora de puesta:
    let moonSetTime;
    if (moonTimes.set) {
        // Si la Luna se pone hoy, usamos la hora de hoy.
        moonSetTime = formatTime(moonTimes.set);
    } else if (moonTimesTomorrow.set) {
        // Si no se pone hoy, comprobamos si se pone en el día de mañana
        moonSetTime = formatTime(moonTimesTomorrow.set);
    } else {
        // Si no se pone ni hoy ni mañana, devolvemos el guion.
        moonSetTime = '—';
    }

    // --- Texto de la fase lunar ---
    // Umbral único (fase < 0.5 = creciente, tramo nueva→llena) compartido con
    // modal_moon.js: antes cada uno decidía creciente/menguante con un
    // criterio distinto (aquí por rango de "phase", allí por el signo de
    // "angle", que no es un proxy fiable de antes/después de la luna llena) y
    // podían mostrar textos contradictorios para el mismo instante.
    const isWaxing = phase < 0.5;
    const fractionPercent = Math.round(fraction * 100);
    let phaseText = "";
    const tol = 0.02; // tolerancia

    if ((phase >= 0 && phase < tol) || phase > 1 - tol) {
        phaseText = "Luna nueva";
    } else if (phase >= tol && phase < 0.25 - tol) {
        phaseText = "Luna creciente";
    } else if (phase >= 0.25 - tol && phase <= 0.25 + tol) {
        phaseText = "Cuarto creciente";
    } else if (phase > 0.25 + tol && phase < 0.5 - tol) {
        phaseText = "Gibosa creciente";
    } else if (phase >= 0.5 - tol && phase <= 0.5 + tol) {
        phaseText = "Luna llena";
    } else if (phase > 0.5 + tol && phase < 0.75 - tol) {
        phaseText = "Gibosa menguante";
    } else if (phase >= 0.75 - tol && phase <= 0.75 + tol) {
        phaseText = "Cuarto menguante";
    } else if (phase > 0.75 + tol && phase < 1 - tol) {
        phaseText = "Luna menguante";
    }

    phaseText += ` (${fractionPercent}%)`;
    window.phaseText = phaseText;
    window.isWaxing = isWaxing;

    // Asignar el texto de la fase y porcentaje
    textEl.textContent = phaseText;

    // Asignar las horas de salida y puesta
    riseEl.textContent = moonRiseTime;
    setEl.textContent = moonSetTime;

    // --- Generar y enlazar el CSS dinámico de la fase ---
    const moonScale = '0.4';
    // Redondeo a 0-100 (no 0-101: moon-phase.php clampa a ese rango, así que
    // usar 101 desalineaba el icono ~1% del ciclo respecto a la fase real,
    // más notorio justo en luna llena).
    const phasePercentage = Math.round(phase * 100);

    if (!moonPhaseCssLink) {
        moonPhaseCssLink = document.getElementById('moon-phase-css');
        if (!moonPhaseCssLink) {
            moonPhaseCssLink = document.createElement('link');
            moonPhaseCssLink.rel = 'stylesheet';
            moonPhaseCssLink.type = 'text/css';
            moonPhaseCssLink.id = 'moon-phase-css';
            document.head.appendChild(moonPhaseCssLink);
        }
    }
    moonPhaseCssLink.href = `./static/css/moon-phase.php?position=${phasePercentage}&scale=${moonScale}&bright=1&v=${Date.now()}`;
}

if (typeof SunCalc === 'undefined') {
    console.error("moon.js: SunCalc no está disponible; el widget de la luna no se puede calcular.");
} else {
    updateMoonWidget();
    // Cálculo 100% en cliente (sin fetch a servidor): refresco barato para
    // mantener fase, iluminación y salida/puesta al día sin recargar la página.
    setInterval(updateMoonWidget, 5 * 60 * 1000);
}
