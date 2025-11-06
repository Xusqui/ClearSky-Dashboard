// Para probar la fase problemática, puedes usar esta fecha:
// const now = new Date("2025-09-08T12:00:00"); // Debería ser Cuarto Menguante
const now = new Date();
window.moon = SunCalc.getMoonIllumination(now);
window.fraction = window.moon.fraction;
window.phase = window.moon.phase;

// --- Texto de la fase lunar ---
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

document.getElementById("moon-text").textContent = phaseText;
