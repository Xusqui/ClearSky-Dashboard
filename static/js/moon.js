// Para probar la fase problemática, puedes usar esta fecha:
// const now = new Date("2025-09-08T12:00:00"); // Debería ser Cuarto Menguante
const now = new Date();
window.moon = SunCalc.getMoonIllumination(now);
window.fraction = window.moon.fraction;
window.phase = window.moon.phase;

/*const radius = 40;
const cx = 20 + 40;
const cy = 5 + 40;
const offset = 0;
const arcRadius = radius - offset;
const arcDiameter = arcRadius * 2;

let waxingArc = arcRadius;
let waxingSweep = 1;
let waningArc = arcRadius;
let waningSweep = 1;

let sweep = 0;
let arcFraction = 1 - fraction * 2;

if (arcFraction < 0) {
    arcFraction = -arcFraction;
    sweep = 1;
}

if (phase <= 0.5) {
    // creciente
    waxingArc = arcFraction * arcRadius;
    waxingSweep = sweep;
} else {
    // menguante
    waningArc = arcFraction * arcRadius;
    waningSweep = sweep;
}

const pathD = `
  M ${cx} ${cy}
  m 0 ${-arcRadius}
  a ${waningArc} ${arcRadius} 0 0 ${waningSweep} 0 ${arcDiameter}
  a ${waxingArc} ${arcRadius} 0 0 ${waxingSweep} 0 ${-arcDiameter}
  z
`;

document.getElementById("mask-path").setAttribute("d", pathD);
*/
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
