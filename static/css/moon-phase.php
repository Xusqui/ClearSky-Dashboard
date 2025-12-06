<?php
/* moon-phase.php */
header("Content-Type: text/css");

/* Lee los parámetros */
$phase = isset($_GET['position']) ? floatval($_GET['position']) : 0;
$scale = isset($_GET['scale']) ? floatval($_GET['scale']) : 1;
$bright = isset($_GET['bright']) ? floatval($_GET['bright']) : 1;

/* Limita valores válidos */
$phase = max(0, min(100, $phase));
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
  display: flex;
  flex-direction: column; /* Luna arriba, texto debajo */
  align-items: center;    /* Centra horizontalmente */
  justify-content: center;/* Centra verticalmente */
  height: 100%;
  top: 50%;
  transform: translateY(-50%);
}

/* Esfera principal de la luna */
.moon {
  position: relative; /* Necesario para posicionar las capas internas */
  display: flex;
  align-items: center;
  justify-content: center;
  width: <?= rem(19, $scale) ?>;
  height: <?= rem(19, $scale) ?>;
  border-radius: 50%;
  overflow: hidden;
  margin: 0 <?= rem(5.25, $scale) ?>;

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
  filter: blur(<?= rem(0.1, $scale) ?>);
}

/* Textura de la superficie */
.texture {
  width: <?= rem(19, $scale) ?>;
  height: <?= rem(19, $scale) ?>;
  border-radius: 50%;
  position: absolute;
  margin-top: <?= rem(-20, $scale) ?>;
  background-image: url(../images/icons/moons/full_moon-2.png);
  background-position: center center;
  background-size: 102%;
  background-repeat: no-repeat;
  mix-blend-mode: multiply;
  transform: translateY(52%);
  filter: brightness(<?php echo $bright; ?>);
}

/* Capa de sombreado interior */
.sphere {
  width: <?= rem(19.75, $scale) ?>;
  height: <?= rem(19.75, $scale) ?>;
  border-radius: 10%;
  box-shadow:
    inset 0 0 <?= rem(2, $scale) ?> #999,
    inset 0 0 <?= rem(0, $scale) ?> #000,
    inset 0 0 <?= rem(0, $scale) ?> #000;
  position: absolute;
  margin-top: <?= rem(-20.3, $scale) ?>;
  margin-left: <?= rem(-0.3, $scale) ?>;
  filter: blur(<?= rem(1, $scale) ?>);
  transform:translateY(52%);
}

.moon-card .moon-phase-name #moon-text {
  color: var(--color-secondary-font);
  font-size: 0.7rem;
  font-weight: 800;
  text-align: center;
  margin-top: 0.5rem;
  letter-spacing: 0.05rem;
}

#moonFeatureModal .modal-content {
  max-width: 450px;
  margin: auto;
  position: relative;
}

#moonFeatureCanvas {
  border-radius: 50%;
  box-shadow: inset 0 0 20px #000, 0 0 10px rgba(0,0,0,0.3);
  cursor: zoom-in;
}

/* Estilos para la Salida y Puesta de la Luna */
/* NUEVO: Contenedor para la fila principal (Horizontal Flexbox) */
.moon-main-row {
    display: flex;
    align-items: center; /* Centra verticalmente los elementos (texto y luna) */
    justify-content: center;
    width: 100%;
}

/* NUEVO: Estilos base para la información de Salida y Puesta */
.moon-rise-info,
.moon-set-info {
    /* Ajustamos el tamaño de fuente como discutimos antes */
    width: <?= rem(10, $scale) ?>; /* Define un espacio fijo */
    font-size: <?= rem(2, $scale) ?>;
    color: var(--color-secondary-font);
    font-weight: 500;
    display: flex;
    align-items: center;
    height: <?= rem(19, $scale) ?>; /* Altura igual a la luna para centrar verticalmente */
}

/* Estilo para el párrafo que contiene el icono, etiqueta y hora */
.moon-rise-info p,
.moon-set-info p {
    margin: 0;
    display: flex;
    align-items: center;
    gap: 0.3rem;
    white-space: nowrap; /* Evita saltos de línea */
    font-size: 0.9rem;
    font-weight: 400;
    text-transform: uppercase;
}

.time-group {
    display: flex;
    flex-direction: column; /* ¡Esto apila la hora y la etiqueta! */
    line-height: 1.1; /* Controla el espacio entre las líneas */
    width: 100%; /* Ocupa el espacio disponible en su contenedor */
}

/* ---------------------------------
   IZQUIERDA: Salida (Moon Rise Info)
   Objetivo: Alinear el texto hacia la derecha (cerca de la luna)
   Formato: [Hora] Salida: [Icono]
   --------------------------------- */
.moon-rise-info {
    justify-content: flex-end;
}

.moon-rise-info p {
  margin: 0;
  display: flex;
  align-items: center;
  gap: 0.3rem;
  white-space: nowrap;
}

/* Estilos para la hora (span), para que sea más grande y destaque */
.moon-rise-info span,
.moon-set-info span {
    font-weight: 800;
    letter-spacing: -0.05rem;
    font-size: 1.7rem;
    color: var(--font-color);
    margin-bottom: 0.1rem;
}
.moon-rise-info i,
.moon-set-info i {
    color: var(--accent-color, #FFD700); /* Color de acento para los iconos */
    font-size: 0.9em; /* Ligeramente más pequeños que el texto circundante */
}

/* IZQUIERDA: Salida (Alinear texto a la derecha, cerca de la luna) */
.moon-rise-info .time-group {
    align-items: flex-end; /* Alinea los elementos (Hora y Salida) a la derecha */
}
/* ---------------------------------
   DERECHA: Puesta (Moon Set Info)
   Objetivo: Alinear el texto hacia la izquierda (cerca de la luna)
   Formato: [Icono] Puesta: [Hora]
   --------------------------------- */
.moon-set-info {
    justify-content: flex-start;
}

/* DERECHA: Puesta (Alinear texto a la izquierda, cerca de la luna) */
.moon-set-info .time-group {
    align-items: flex-start; /* Alinea los elementos (Hora y Puesta) a la izquierda */
}
/* El orden por defecto (row) ya es el deseado */

/* ... Código @keyframes cycle ... */
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
    border-left: <?= rem(10,$scale) ?> solid var(--moon_sunlight);
  }
  25% {
    border-left: 0 solid var(--moon_sunlight);
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
    border-left: 0rem solid var(--moon_shadow);
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
