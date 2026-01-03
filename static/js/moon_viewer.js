import { vertexShaderSource, fragmentShaderSource } from './moon_shaders.js';

const DEG = Math.PI / 180;
const RAD = 180 / Math.PI;

let observerLat = typeof latitude !== 'undefined' ? parseFloat(latitude) : 40.4168;
let observerLon = typeof longitude !== 'undefined' ? parseFloat(longitude) : -3.7038;
let simOffsetMs = 0;
let speedHoursPerSecond = 0;
const MAX_HOURS_PER_SECOND = 12;
let lastRealTime = 0;

let gl;
let program;
let texture;
let textureLoaded = false;

function initWebGL() {
  const canvas = document.getElementById('moonCanvas');
  gl = canvas.getContext('webgl') || canvas.getContext('experimental-webgl');
  if (!gl) {
    document.getElementById('loading').textContent = 'WebGL no soportado';
    return false;
  }
  const vertexShader = gl.createShader(gl.VERTEX_SHADER);
  gl.shaderSource(vertexShader, vertexShaderSource);
  gl.compileShader(vertexShader);
  if (!gl.getShaderParameter(vertexShader, gl.COMPILE_STATUS)) {
    console.error('Vertex shader error:', gl.getShaderInfoLog(vertexShader));
    return false;
  }
  const fragmentShader = gl.createShader(gl.FRAGMENT_SHADER);
  gl.shaderSource(fragmentShader, fragmentShaderSource);
  gl.compileShader(fragmentShader);
  if (!gl.getShaderParameter(fragmentShader, gl.COMPILE_STATUS)) {
    console.error('Fragment shader error:', gl.getShaderInfoLog(fragmentShader));
    return false;
  }
  program = gl.createProgram();
  gl.attachShader(program, vertexShader);
  gl.attachShader(program, fragmentShader);
  gl.linkProgram(program);
  if (!gl.getProgramParameter(program, gl.LINK_STATUS)) {
    console.error('Program link error:', gl.getProgramInfoLog(program));
    return false;
  }
  const vertices = new Float32Array([-1, -1, 1, -1, -1, 1, 1, 1]);
  const buffer = gl.createBuffer();
  gl.bindBuffer(gl.ARRAY_BUFFER, buffer);
  gl.bufferData(gl.ARRAY_BUFFER, vertices, gl.STATIC_DRAW);
  const positionLocation = gl.getAttribLocation(program, 'a_position');
  gl.enableVertexAttribArray(positionLocation);
  gl.vertexAttribPointer(positionLocation, 2, gl.FLOAT, false, 0, 0);
  return true;
}

function loadTexture() {
  texture = gl.createTexture();
  const image = new Image();
  image.onload = function () {
    gl.bindTexture(gl.TEXTURE_2D, texture);
    gl.texImage2D(gl.TEXTURE_2D, 0, gl.RGBA, gl.RGBA, gl.UNSIGNED_BYTE, image);
    gl.texParameteri(gl.TEXTURE_2D, gl.TEXTURE_WRAP_S, gl.REPEAT);
    gl.texParameteri(gl.TEXTURE_2D, gl.TEXTURE_WRAP_T, gl.CLAMP_TO_EDGE);
    gl.texParameteri(gl.TEXTURE_2D, gl.TEXTURE_MIN_FILTER, gl.LINEAR);
    gl.texParameteri(gl.TEXTURE_2D, gl.TEXTURE_MAG_FILTER, gl.LINEAR);
    textureLoaded = true;
    // document.getElementById('loading').style.display = 'none';
    updateMoon();
  };
  image.onerror = function () {
    // document.getElementById('loading').textContent = 'Error cargando textura';
  };
  image.src = './static/images/moon_texture.jpg';
}

function julianDate(date) {
  const y = date.getUTCFullYear();
  const m = date.getUTCMonth() + 1;
  const d = date.getUTCDate();
  const h = date.getUTCHours() + date.getUTCMinutes() / 60 + date.getUTCSeconds() / 3600;
  let jy = y;
  let jm = m;
  if (m <= 2) {
    jy -= 1;
    jm += 12;
  }
  const a = Math.floor(jy / 100);
  const b = 2 - a + Math.floor(a / 4);
  return Math.floor(365.25 * (jy + 4716)) + Math.floor(30.6001 * (jm + 1)) + d + h / 24 + b - 1524.5;
}

function getMoonPosition(jd) {
  const T = (jd - 2451545.0) / 36525;
  let Lp = 218.3164477 + 481267.88123421 * T - 0.0015786 * T * T + T * T * T / 538841 - T * T * T * T / 65194000;
  let D = 297.8501921 + 445267.1114034 * T - 0.0018819 * T * T + T * T * T / 545868 - T * T * T * T / 113065000;
  let M = 357.5291092 + 35999.0502909 * T - 0.0001536 * T * T + T * T * T / 24490000;
  let Mp = 134.9633964 + 477198.8675055 * T + 0.0087414 * T * T + T * T * T / 69699 - T * T * T * T / 14712000;
  let F = 93.2720950 + 483202.0175233 * T - 0.0036539 * T * T - T * T * T / 3526000 + T * T * T * T / 863310000;
  const A1 = 119.75 + 131.849 * T;
  const A2 = 53.09 + 479264.290 * T;
  const A3 = 313.45 + 481266.484 * T;
  const E = 1 - 0.002516 * T - 0.0000074 * T * T;
  Lp = ((Lp % 360) + 360) % 360;
  D = ((D % 360) + 360) % 360;
  M = ((M % 360) + 360) % 360;
  Mp = ((Mp % 360) + 360) % 360;
  F = ((F % 360) + 360) % 360;
  let sumL = 0;
  let sumB = 0;
  let sumR = 0;
  const termsL = [
    [0, 0, 1, 0, 6288774, -20905355],
    [2, 0, -1, 0, 1274027, -3699111],
    [2, 0, 0, 0, 658314, -2955968],
    [0, 0, 2, 0, 213618, -569925],
    [0, 1, 0, 0, -185116, 48888],
    [0, 0, 0, 2, -114332, -3149],
    [2, 0, -2, 0, 58793, 246158],
    [2, -1, -1, 0, 57066, -152138],
    [2, 0, 1, 0, 53322, -170733],
    [2, -1, 0, 0, 45758, -204586],
    [0, 1, -1, 0, -40923, -129620],
    [1, 0, 0, 0, -34720, 108743],
    [0, 1, 1, 0, -30383, 104755],
    [2, 0, 0, -2, 15327, 10321],
    [0, 0, 1, 2, -12528, 0],
    [0, 0, 1, -2, 10980, 79661],
    [4, 0, -1, 0, 10675, -34782],
    [0, 0, 3, 0, 10034, -23210],
    [4, 0, -2, 0, 8548, -21636],
    [2, 1, -1, 0, -7888, 24208]
  ];
  const termsB = [
    [0, 0, 0, 1, 5128122],
    [0, 0, 1, 1, 280602],
    [0, 0, 1, -1, 277693],
    [2, 0, 0, -1, 173237],
    [2, 0, -1, 1, 55413],
    [2, 0, -1, -1, 46271],
    [2, 0, 0, 1, 32573],
    [0, 0, 2, 1, 17198],
    [2, 0, 1, -1, 9266],
    [0, 0, 2, -1, 8822]
  ];
  for (let term of termsL) {
    let arg = term[0] * D + term[1] * M + term[2] * Mp + term[3] * F;
    let eCorr = 1;
    if (Math.abs(term[1]) === 1) eCorr = E;
    if (Math.abs(term[1]) === 2) eCorr = E * E;
    sumL += term[4] * eCorr * Math.sin(arg * DEG);
    sumR += term[5] * eCorr * Math.cos(arg * DEG);
  }
  for (let term of termsB) {
    let arg = term[0] * D + term[1] * M + term[2] * Mp + term[3] * F;
    let eCorr = 1;
    if (Math.abs(term[1]) === 1) eCorr = E;
    if (Math.abs(term[1]) === 2) eCorr = E * E;
    sumB += term[4] * eCorr * Math.sin(arg * DEG);
  }
  sumL += 3958 * Math.sin(A1 * DEG) + 1962 * Math.sin((Lp - F) * DEG) + 318 * Math.sin(A2 * DEG);
  sumB += -2235 * Math.sin(Lp * DEG) + 382 * Math.sin(A3 * DEG) + 175 * Math.sin((A1 - F) * DEG) + 175 * Math.sin((A1 + F) * DEG) + 127 * Math.sin((Lp - Mp) * DEG) - 115 * Math.sin((Lp + Mp) * DEG);
  const lambda = Lp + sumL / 1000000;
  const beta = sumB / 1000000;
  const distance = 385000.56 + sumR / 1000;
  const eps = 23.4392911 - 0.0130042 * T;
  const lambdaRad = lambda * DEG;
  const betaRad = beta * DEG;
  const epsRad = eps * DEG;
  const ra = Math.atan2(Math.sin(lambdaRad) * Math.cos(epsRad) - Math.tan(betaRad) * Math.sin(epsRad), Math.cos(lambdaRad)) * RAD;
  const dec = Math.asin(Math.sin(betaRad) * Math.cos(epsRad) + Math.cos(betaRad) * Math.sin(epsRad) * Math.sin(lambdaRad)) * RAD;
  return { ra: (ra + 360) % 360, dec: dec, distance: distance, eclipticLon: ((lambda % 360) + 360) % 360, eclipticLat: beta, D: D, M: M, Mp: Mp, F: F };
}

function getLibration(jd, moonPos) {
  const T = (jd - 2451545.0) / 36525;
  const I = 1.54242 * DEG;
  let omega = 125.0446 - 1934.1362 * T + 0.0020708 * T * T;
  omega = ((omega % 360) + 360) % 360;
  const W = moonPos.eclipticLon - omega;
  const A = Math.atan2(Math.sin(W * DEG) * Math.cos(moonPos.eclipticLat * DEG) * Math.cos(I) - Math.sin(moonPos.eclipticLat * DEG) * Math.sin(I), Math.cos(W * DEG) * Math.cos(moonPos.eclipticLat * DEG)) * RAD;
  let librationLat = Math.asin(-Math.sin(W * DEG) * Math.cos(moonPos.eclipticLat * DEG) * Math.sin(I) - Math.sin(moonPos.eclipticLat * DEG) * Math.cos(I)) * RAD;
  let Fp = moonPos.F;
  let librationLon = A - Fp;
  while (librationLon > 180) librationLon -= 360;
  while (librationLon < -180) librationLon += 360;
  if (librationLon > 90) librationLon = 180 - librationLon;
  if (librationLon < -90) librationLon = -180 - librationLon;
  librationLon = Math.max(-8, Math.min(8, librationLon));
  librationLat = Math.max(-7, Math.min(7, librationLat));
  return { longitude: librationLon, latitude: librationLat };
}

function getSunPosition(jd) {
  const T = (jd - 2451545.0) / 36525;
  let L0 = 280.46646 + 36000.76983 * T + 0.0003032 * T * T;
  let M = 357.52911 + 35999.05029 * T - 0.0001537 * T * T;
  const e = 0.016708634 - 0.000042037 * T - 0.0000001267 * T * T;
  L0 = ((L0 % 360) + 360) % 360;
  M = ((M % 360) + 360) % 360;
  const C = (1.914602 - 0.004817 * T - 0.000014 * T * T) * Math.sin(M * DEG) + (0.019993 - 0.000101 * T) * Math.sin(2 * M * DEG) + 0.000289 * Math.sin(3 * M * DEG);
  const sunLon = L0 + C;
  const sunAnomaly = M + C;
  const R = 1.000001018 * (1 - e * e) / (1 + e * Math.cos(sunAnomaly * DEG));
  const eps = 23.4392911 - 0.0130042 * T;
  const lambdaRad = sunLon * DEG;
  const epsRad = eps * DEG;
  const ra = Math.atan2(Math.cos(epsRad) * Math.sin(lambdaRad), Math.cos(lambdaRad)) * RAD;
  const dec = Math.asin(Math.sin(epsRad) * Math.sin(lambdaRad)) * RAD;
  return { ra: (ra + 360) % 360, dec: dec, eclipticLon: ((sunLon % 360) + 360) % 360, distance: R * 149597870.7 };
}

function getLocalSiderealTime(jd, longitude) {
  const T = (jd - 2451545.0) / 36525;
  let GMST = 280.46061837 + 360.98564736629 * (jd - 2451545.0) + 0.000387933 * T * T - T * T * T / 38710000;
  GMST = ((GMST % 360) + 360) % 360;
  return ((GMST + longitude) % 360 + 360) % 360;
}

function getHorizontalCoordinates(ra, dec, lat, lon, jd) {
  const lst = getLocalSiderealTime(jd, lon);
  let ha = lst - ra;
  while (ha < -180) ha += 360;
  while (ha > 180) ha -= 360;
  const haRad = ha * DEG;
  const decRad = dec * DEG;
  const latRad = lat * DEG;
  const alt = Math.asin(Math.sin(latRad) * Math.sin(decRad) + Math.cos(latRad) * Math.cos(decRad) * Math.cos(haRad));
  const az = Math.atan2(Math.sin(haRad), Math.cos(haRad) * Math.sin(latRad) - Math.tan(decRad) * Math.cos(latRad));
  return { altitude: alt * RAD, azimuth: ((az * RAD + 180) % 360 + 360) % 360, hourAngle: ha };
}

function getParallacticAngle(ha, dec, lat) {
  const haRad = ha * DEG;
  const decRad = dec * DEG;
  const latRad = lat * DEG;
  const sinQ = Math.sin(haRad) * Math.cos(latRad);
  const cosQ = Math.sin(latRad) * Math.cos(decRad) - Math.cos(latRad) * Math.sin(decRad) * Math.cos(haRad);
  return Math.atan2(sinQ, cosQ) * RAD;
}

function getMoonPhase(jd, moonPos, sunPos) {
  let elongation = moonPos.eclipticLon - sunPos.eclipticLon;
  while (elongation < 0) elongation += 360;
  while (elongation >= 360) elongation -= 360;
  const phaseAngle = 180 - elongation;
  const illumination = (1 + Math.cos(phaseAngle * DEG)) / 2;
  const age = elongation / 12.1907;
  let phaseName;
  if (elongation < 22.5) phaseName = 'Luna Nueva';
  else if (elongation < 67.5) phaseName = 'Creciente';
  else if (elongation < 112.5) phaseName = 'Cuarto Creciente';
  else if (elongation < 157.5) phaseName = 'Gibosa Creciente';
  else if (elongation < 202.5) phaseName = 'Luna Llena';
  else if (elongation < 247.5) phaseName = 'Gibosa Menguante';
  else if (elongation < 292.5) phaseName = 'Cuarto Menguante';
  else if (elongation < 337.5) phaseName = 'Menguante';
  else phaseName = 'Luna Nueva';
  return { illumination: illumination, phase: elongation, age: age, phaseName: phaseName, isWaxing: elongation < 180 };
}

function getBrightLimbAngle(moonPos, sunPos) {
  const moonRaRad = moonPos.ra * DEG;
  const moonDecRad = moonPos.dec * DEG;
  const sunRaRad = sunPos.ra * DEG;
  const sunDecRad = sunPos.dec * DEG;
  const y = Math.cos(sunDecRad) * Math.sin(sunRaRad - moonRaRad);
  const x = Math.sin(sunDecRad) * Math.cos(moonDecRad) - Math.cos(sunDecRad) * Math.sin(moonDecRad) * Math.cos(sunRaRad - moonRaRad);
  return Math.atan2(y, x) * RAD;
}

function renderMoon(libration, rotation, phaseAngle) {
  if (!textureLoaded || !gl || !program) return;
  gl.viewport(0, 0, gl.canvas.width, gl.canvas.height);
  gl.clearColor(0, 0, 0, 0);
  gl.clear(gl.COLOR_BUFFER_BIT);
  gl.useProgram(program);
  gl.activeTexture(gl.TEXTURE0);
  gl.bindTexture(gl.TEXTURE_2D, texture);
  gl.uniform1i(gl.getUniformLocation(program, 'u_texture'), 0);
  gl.uniform1f(gl.getUniformLocation(program, 'u_librationLon'), libration.longitude * DEG);
  gl.uniform1f(gl.getUniformLocation(program, 'u_librationLat'), libration.latitude * DEG);
  gl.uniform1f(gl.getUniformLocation(program, 'u_rotation'), rotation * DEG);
  gl.uniform1f(gl.getUniformLocation(program, 'u_phaseAngle'), phaseAngle);
  gl.drawArrays(gl.TRIANGLE_STRIP, 0, 4);
}

function updateMoon() {
  const targetDate = new Date(Date.now() + simOffsetMs);
  const jd = julianDate(targetDate);
  const moonPos = getMoonPosition(jd);
  const sunPos = getSunPosition(jd);
  const libration = getLibration(jd, moonPos);
  const horizontal = getHorizontalCoordinates(moonPos.ra, moonPos.dec, observerLat, observerLon, jd);
  const phase = getMoonPhase(jd, moonPos, sunPos);
  const brightLimbAngle = getBrightLimbAngle(moonPos, sunPos);
  const parallacticAngle = getParallacticAngle(horizontal.hourAngle, moonPos.dec, observerLat);
  const isBelowHorizon = horizontal.altitude < 0;
  const rotation = brightLimbAngle - parallacticAngle;
  const phaseAngle = (phase.isWaxing ? phase.phase : -phase.phase) * DEG;
  renderMoon(libration, rotation, phaseAngle);
}

function updateLocation() {
  observerLat = typeof latitude !== 'undefined' ? parseFloat(latitude) : observerLat;
  observerLon = typeof longitude !== 'undefined' ? parseFloat(longitude) : observerLon;
  updateMoon();
}

function getGeolocation() {
  if (navigator.geolocation) {
    navigator.geolocation.getCurrentPosition(
      (position) => {
        observerLat = position.coords.latitude;
        observerLon = position.coords.longitude;
        updateMoon();
      },
      (error) => {
        alert('Error obteniendo ubicacion: ' + error.message);
      }
    );
  } else {
    alert('Geolocalizacion no soportada');
  }
}

function resetTime() {
  simOffsetMs = 0;
  speedHoursPerSecond = 0;
  updateMoon();
}

function adjustTime(days) {
  simOffsetMs += days * 24 * 60 * 60 * 1000;
  updateMoon();
}

window.updateLocation = updateLocation;
window.getGeolocation = getGeolocation;
window.resetTime = resetTime;
window.adjustTime = adjustTime;

if (initWebGL()) {
  loadTexture();
}

function loop(ts) {
  if (!lastRealTime) lastRealTime = ts;
  const dt = (ts - lastRealTime) / 1000;
  lastRealTime = ts;
  simOffsetMs += speedHoursPerSecond * 3600000 * dt;
  updateMoon();
  requestAnimationFrame(loop);
}
requestAnimationFrame(loop);
