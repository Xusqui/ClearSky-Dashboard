// moon.js
// Asume que conf_to_js.php ha definido: const LAT = '...'; const LON = '...';

// Para probar la fase problemática, puedes usar esta fecha:
// const now = new Date("2025-09-08T12:00:00"); // Debería ser Cuarto Menguante
//const now = new Date();
const now = new Date("2025-12-05T12:00:00");
// --- Obtener coordenadas del observador (usando las constantes de conf_to_js.php) ---
// Convertir las constantes string a números de punto flotante
const latitude = parseFloat(LAT);
const longitude = parseFloat(LON);
const elevation = parseFloat(ELEV);

let observer = new Astronomy.Observer(latitude, longitude, elevation);
let startAstro = Astronomy.MakeTime(now);

let moonRise = Astronomy.SearchRiseSet('Moon', observer, +1, startAstro, 1);
let moonSet = Astronomy.SearchRiseSet('Moon', observer, -1, startAstro, 1);

const moonRiseTime = moonRise.date;
const moonSetTime = moonSet.date;

window.moon = SunCalc.getMoonIllumination(now);
window.fraction = window.moon.fraction;
window.phase = window.moon.phase;

// Función auxiliar para formatear la hora (HH:MM)
function formatTime(date) {
    // Si la hora es nula (ej. la luna no sale/se pone hoy) retorna un guion
    if (!date) return '—';
    // Opciones para asegurar formato de 24h
    const options = { hour: '2-digit', minute: '2-digit', hourCycle: 'h23' };
    return date.toLocaleTimeString(navigator.language, options);
}
const formatterRiseTime = formatTime(moonRiseTime);
const formatterSetTime = formatTime(moonSetTime);

// --- Texto de la fase lunar ---
const fractionPercent = Math.round(window.fraction * 100);
let phaseText = "";
const tol = 0.02; // tolerancia

if ((window.phase >= 0 && window.phase < tol) || window.phase > 1 - tol) {
    phaseText = "Luna nueva";
} else if (window.phase >= tol && window.phase < 0.25 - tol) {
    phaseText = "Luna creciente";
} else if (window.phase >= 0.25 - tol && window.phase <= 0.25 + tol) {
    phaseText = "Cuarto creciente";
} else if (window.phase > 0.25 + tol && window.phase < 0.5 - tol) {
    phaseText = "Gibosa creciente";
} else if (window.phase >= 0.5 - tol && window.phase <= 0.5 + tol) {
    phaseText = "Luna llena";
} else if (window.phase > 0.5 + tol && window.phase < 0.75 - tol) {
    phaseText = "Gibosa menguante";
} else if (window.phase >= 0.75 - tol && window.phase <= 0.75 + tol) {
    phaseText = "Cuarto menguante";
} else if (window.phase > 0.75 + tol && window.phase < 1 - tol) {
    phaseText = "Luna menguante";
}

phaseText += ` (${fractionPercent}%)`;

window.phaseText = phaseText;

// Asignar el texto de la fase y porcentaje
document.getElementById("moon-text").textContent = phaseText;

// **********************************************
// NUEVO: Asignar las horas de salida y puesta
// **********************************************
document.getElementById("moon-rise-time").textContent = formatterRiseTime;
document.getElementById("moon-set-time").textContent = formatterSetTime;
// **********************************************
// NUEVO: Generar y enlazar el CSS dinámico
// **********************************************

// Asumimos que $moon_scale se conoce en JS o es un valor fijo
const moonScale = '0.4'; // Reemplaza con el valor real de $moon_scale

const phasePercentage = Math.round(window.phase * 101);

// Obtener la referencia al <link> existente (si tiene un ID)
let cssLink = document.getElementById('moon-phase-css');

// Si no existe, lo creamos
if (!cssLink) {
    cssLink = document.createElement('link');
    cssLink.rel = 'stylesheet';
    cssLink.type = 'text/css';
    cssLink.id = 'moon-phase-css';
    document.head.appendChild(cssLink);
}

// Generar la URL con el valor calculado de window.phase
const newHref = `./static/css/moon-phase.php?position=${phasePercentage}&scale=${moonScale}&bright=1&v=${Date.now()}`;

// Actualizar el href del enlace
cssLink.href = newHref;
