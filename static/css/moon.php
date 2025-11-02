<?php
header("Content-Type: text/css");

/* Lee los parámetros */
$phase = isset($_GET['position']) ? floatval($_GET['position']) : 0;
$scale = isset($_GET['scale']) ? floatval($_GET['scale']) : 1;

/* Limita valores válidos */
//$phase = max(0, min(100, $phase));
$scale = max(0.05, min(5, $scale)); // evita valores extremos

/* Ajuste para animación reverse */
$phase = -1 * $phase;

/* Función para escalar valores en rem */
function rem($value, $scale) {
  return round($value * $scale, 3) . "rem";
}
?>
/* Contenedor principal */
.moon-card-container {
position: relative;
top: 50%;
transform: translateY(-50%);
}

/* Esfera principal de la luna */
.moon {
display: inline-block;
width: <?= rem(19, $scale) ?>;
height: <?= rem(19, $scale) ?>;
border-radius: 50%;
overflow: hidden;
margin: 0 <?= rem(0.25, $scale) ?>;
}

/* Capa de luz */
.light {
box-sizing: border-box;
width: <?= rem(20, $scale) ?>;
height: <?= rem(20, $scale) ?>;
background-color: var(--moon_shadow);
border-radius: 50%;
animation: cycle 100s linear paused reverse;
animation-delay: <?= $phase ?>s;
filter: blur(<?= rem(0.5, $scale) ?>);
}

/* Textura de la superficie */
.texture {
width: <?= rem(19, $scale) ?>;
height: <?= rem(19, $scale) ?>;
border-radius: 50%;
position: absolute;
margin-top: <?= rem(-20, $scale) ?>;
background-image: url(https://xusqui.com/weather/static/images/icons/moons/full_moon2.svg);
background-position: center center;
background-size: 100%;
background-repeat: no-repeat;
mix-blend-mode: multiply;
}

/* Capa de sombreado interior */
.sphere {
width: <?= rem(19.75, $scale) ?>;
height: <?= rem(19.75, $scale) ?>;
border-radius: 100%;
box-shadow:
inset 0 0 <?= rem(10, $scale) ?> #000,
inset 0 0 <?= rem(5, $scale) ?> var(--moon-surround),
inset 0 0 <?= rem(3, $scale) ?> #000;
position: absolute;
margin-top: <?= rem(-20.3, $scale) ?>;
margin-left: <?= rem(-0.3, $scale) ?>;
filter: blur(<?= rem(2, $scale) ?>);
}

#moonFeatureModal .modal-content {
max-width: 450px;
margin: auto;
position: relative;
}

#moonFeatureCanvas {
border-radius: 50%;
box-shadow: inset 0 0 20px #000, 0 0 10px rgba(0,0,0,0.3);
}

/* Animación de las fases */
@keyframes cycle {
0% {
border-left: 0 solid var(--moon_sunlight);
border-right: <?= rem(10, $scale) ?> solid var(--moon_shadow);
background-color: var(--moon_shadow);
transform: rotate(-10deg);
}
24.9999% {
background-color: var(--moon_shadow);
}
25% {
border-left: <?= rem(10, $scale) ?> solid var(--moon_sunlight);
border-right: <?= rem(10, $scale) ?> solid var(--moon_shadow);
background-color: var(--moon_sunlight);
}
50% {
border-left: 0 solid var(--moon_sunlight);
border-right: 0 solid var(--moon_shadow);
background-color: var(--moon_sunlight);
transform: rotate(0deg);
}
50.0001% {
border-left: 0 solid var(--moon_shadow);
}
74.9999% {
background-color: var(--moon_sunlight);
border-right: 0 solid var(--moon_sunlight);
}
75% {
border-left: <?= rem(10, $scale) ?> solid var(--moon_shadow);
border-right: <?= rem(10, $scale) ?> solid var(--moon_sunlight);
background-color: var(--moon_shadow);
}
100% {
border-left: <?= rem(10, $scale) ?> solid var(--moon_shadow);
border-right: 0 solid var(--moon_sunlight);
background-color: var(--moon_shadow);
transform: rotate(10deg);
}
}
