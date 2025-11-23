// modal_ephemeris.js

// ** ASUME QUE ESTAS VARIABLES ESTÁN DEFINIDAS GLOBALMENTE **
// const LAT = 36.566578;
// const LON = -4.60272;
// const ELEV = 25;
// **********************************************************

// Definición de las coordenadas de tu observatorio
// Usamos las variables globales LAT y LON, asumiendo que existen.

// -------------------------------------------------------------
// Paso 1: Inicialización de Cuerpos Celestes y Observador
// -------------------------------------------------------------

// Objetos del Sistema Solar (Usan Orb.VSOP, Orb.Sun, Orb.Luna)
const ORB_TARGETS = {
    'Mercurio': { instance: new Orb.VSOP("Mercury"), type: 'Planeta', nakedEye: true },
    'Venus': { instance: new Orb.VSOP("Venus"), type: 'Planeta', nakedEye: true },
    'Marte': { instance: new Orb.VSOP("Mars"), type: 'Planeta', nakedEye: true },
    'Júpiter': { instance: new Orb.VSOP("Jupiter"), type: 'Planeta', nakedEye: true },
    'Saturno': { instance: new Orb.VSOP("Saturn"), type: 'Planeta', nakedEye: true },
    'Urano': { instance: new Orb.VSOP("Uranus"), type: 'Planeta', nakedEye: false },
    'Neptuno': { instance: new Orb.VSOP("Neptune"), type: 'Planeta', nakedEye: false },
    'Sol': { instance: new Orb.Sun(), type: 'Estrella', nakedEye: true },
    'Luna': { instance: new Orb.Luna(), type: 'Luna', nakedEye: true },
};

// Ubicación del observador en el formato requerido por Orb.Observation
const YOUR_LOCATION = {
    "latitude": LAT,
    "longitude": LON,
    "altitude": ELEV
};

// Catálogo de Objetos de Cielo Profundo (DSO) - CATÁLOGO MESSIER COMPLETO
// RA y DEC en grados decimales. Distance es aproximada.
const DSO_CATALOG = [
    { name: 'M1 (Nebulosa del Cangrejo)', type: 'Resto Supernova', nakedEye: false, ra: 83.63300, dec: 22.01400, distance: 1000 },
    { name: 'M2 (Cúmulo Globular)', type: 'Cúmulo Globular', nakedEye: false, ra: 323.35900, dec: -0.82000, distance: 1000 },
    { name: 'M3 (Cúmulo Globular)', type: 'Cúmulo Globular', nakedEye: false, ra: 205.53900, dec: 28.22500, distance: 1000 },
    { name: 'M4 (Cúmulo Globular)', type: 'Cúmulo Globular', nakedEye: false, ra: 245.89400, dec: -26.52000, distance: 1000 },
    { name: 'M5 (Cúmulo Globular)', type: 'Cúmulo Globular', nakedEye: false, ra: 229.63800, dec: 2.08300, distance: 1000 },
    { name: 'M6 (Cúmulo de la Mariposa)', type: 'Cúmulo Abierto', nakedEye: true, ra: 260.67500, dec: -32.25000, distance: 1000 },
    { name: 'M7 (Cúmulo de Ptolomeo)', type: 'Cúmulo Abierto', nakedEye: true, ra: 268.49000, dec: -34.80000, distance: 1000 },
    { name: 'M8 (Nebulosa Laguna)', type: 'Nebulosa', nakedEye: true, ra: 270.81400, dec: -24.37000, distance: 1000 },
    { name: 'M9 (Cúmulo Globular)', type: 'Cúmulo Globular', nakedEye: false, ra: 257.06000, dec: -18.30000, distance: 1000 },
    { name: 'M10 (Cúmulo Globular)', type: 'Cúmulo Globular', nakedEye: false, ra: 256.70200, dec: -4.08000, distance: 1000 },
    { name: 'M11 (Cúmulo del Pato Salvaje)', type: 'Cúmulo Abierto', nakedEye: false, ra: 279.35000, dec: -6.14000, distance: 1000 },
    { name: 'M12 (Cúmulo Globular)', type: 'Cúmulo Globular', nakedEye: false, ra: 247.45800, dec: -1.95000, distance: 1000 },
    { name: 'M13 (Gran Cúmulo de Hércules)', type: 'Cúmulo Globular', nakedEye: true, ra: 250.42100, dec: 36.45900, distance: 1000 },
    { name: 'M14 (Cúmulo Globular)', type: 'Cúmulo Globular', nakedEye: false, ra: 258.98000, dec: -3.24000, distance: 1000 },
    { name: 'M15 (Cúmulo Globular)', type: 'Cúmulo Globular', nakedEye: false, ra: 322.49200, dec: 12.16700, distance: 1000 },
    { name: 'M16 (Nebulosa del Águila)', type: 'Nebulosa', nakedEye: false, ra: 274.68800, dec: -13.80500, distance: 1000 },
    { name: 'M17 (Nebulosa Omega)', type: 'Nebulosa', nakedEye: false, ra: 275.14300, dec: -16.17000, distance: 1000 },
    { name: 'M18 (Cúmulo Abierto)', type: 'Cúmulo Abierto', nakedEye: false, ra: 275.52000, dec: -17.06000, distance: 1000 },
    { name: 'M19 (Cúmulo Globular)', type: 'Cúmulo Globular', nakedEye: false, ra: 255.45400, dec: -26.96000, distance: 1000 },
    { name: 'M20 (Nebulosa Trífida)', type: 'Nebulosa', nakedEye: false, ra: 270.76000, dec: -23.01800, distance: 1000 },
    { name: 'M21 (Cúmulo Abierto)', type: 'Cúmulo Abierto', nakedEye: false, ra: 271.05000, dec: -22.50000, distance: 1000 },
    { name: 'M22 (Cúmulo Globular)', type: 'Cúmulo Globular', nakedEye: true, ra: 277.91500, dec: -23.90000, distance: 1000 },
    { name: 'M23 (Cúmulo Abierto)', type: 'Cúmulo Abierto', nakedEye: false, ra: 269.57000, dec: -18.99000, distance: 1000 },
    { name: 'M24 (Nube Estelar de Sagitario)', type: 'Nube Estelar', nakedEye: true, ra: 274.96000, dec: -18.47000, distance: 1000 },
    { name: 'M25 (Cúmulo Abierto)', type: 'Cúmulo Abierto', nakedEye: false, ra: 278.43500, dec: -19.01000, distance: 1000 },
    { name: 'M26 (Cúmulo Abierto)', type: 'Cúmulo Abierto', nakedEye: false, ra: 280.05000, dec: -9.45000, distance: 1000 },
    { name: 'M27 (Nebulosa Dumbbell)', type: 'Nebulosa Planetaria', nakedEye: false, ra: 298.24300, dec: 22.71500, distance: 1000 },
    { name: 'M28 (Cúmulo Globular)', type: 'Cúmulo Globular', nakedEye: false, ra: 274.60000, dec: -24.87000, distance: 1000 },
    { name: 'M29 (Cúmulo Abierto)', type: 'Cúmulo Abierto', nakedEye: false, ra: 307.72000, dec: 38.38000, distance: 1000 },
    { name: 'M30 (Cúmulo Globular)', type: 'Cúmulo Globular', nakedEye: false, ra: 326.60200, dec: -23.10000, distance: 1000 },
    { name: 'M31 (Galaxia de Andrómeda)', type: 'Galaxia', nakedEye: true, ra: 10.68400, dec: 41.26900, distance: 10000000 },
    { name: 'M32 (Galaxia Elíptica)', type: 'Galaxia', nakedEye: false, ra: 10.65500, dec: 40.52800, distance: 10000000 },
    { name: 'M33 (Galaxia del Triángulo)', type: 'Galaxia Espiral', nakedEye: false, ra: 23.46300, dec: 30.66000, distance: 10000000 },
    { name: 'M34 (Cúmulo Abierto)', type: 'Cúmulo Abierto', nakedEye: false, ra: 43.16000, dec: 42.82000, distance: 1000 },
    { name: 'M35 (Cúmulo Abierto)', type: 'Cúmulo Abierto', nakedEye: false, ra: 93.30000, dec: 24.38000, distance: 1000 },
    { name: 'M36 (Cúmulo Abierto)', type: 'Cúmulo Abierto', nakedEye: false, ra: 86.80000, dec: 34.09000, distance: 1000 },
    { name: 'M37 (Cúmulo Abierto)', type: 'Cúmulo Abierto', nakedEye: false, ra: 88.58000, dec: 32.55000, distance: 1000 },
    { name: 'M38 (Cúmulo Abierto)', type: 'Cúmulo Abierto', nakedEye: false, ra: 82.02000, dec: 35.81000, distance: 1000 },
    { name: 'M39 (Cúmulo Abierto)', type: 'Cúmulo Abierto', nakedEye: true, ra: 320.67000, dec: 48.40000, distance: 1000 },
    { name: 'M40 (Doble Estrella)', type: 'Doble Estrella', nakedEye: false, ra: 184.28000, dec: 58.07000, distance: 1000 },
    { name: 'M41 (Cúmulo Abierto)', type: 'Cúmulo Abierto', nakedEye: true, ra: 100.82000, dec: -20.73000, distance: 1000 },
    { name: 'M42 (Nebulosa de Orión)', type: 'Nebulosa', nakedEye: true, ra: 83.82200, dec: -5.39100, distance: 1000 },
    { name: 'M43 (Nebulosa de De Mairan)', type: 'Nebulosa', nakedEye: false, ra: 83.85000, dec: -5.20000, distance: 1000 },
    { name: 'M44 (Cúmulo del Pesebre)', type: 'Cúmulo Abierto', nakedEye: true, ra: 129.58000, dec: 19.66000, distance: 1000 },
    { name: 'M45 (Las Pléyades)', type: 'Cúmulo Abierto', nakedEye: true, ra: 56.68000, dec: 24.11000, distance: 1000 },
    { name: 'M46 (Cúmulo Abierto)', type: 'Cúmulo Abierto', nakedEye: false, ra: 114.70000, dec: -14.72000, distance: 1000 },
    { name: 'M47 (Cúmulo Abierto)', type: 'Cúmulo Abierto', nakedEye: false, ra: 114.39000, dec: -14.43000, distance: 1000 },
    { name: 'M48 (Cúmulo Abierto)', type: 'Cúmulo Abierto', nakedEye: false, ra: 123.00000, dec: -5.75000, distance: 1000 },
    { name: 'M49 (Galaxia Elíptica)', type: 'Galaxia', nakedEye: false, ra: 187.44700, dec: 8.00000, distance: 10000000 },
    { name: 'M50 (Cúmulo Abierto)', type: 'Cúmulo Abierto', nakedEye: false, ra: 104.99000, dec: -8.38000, distance: 1000 },
    { name: 'M51 (Galaxia del Remolino)', type: 'Galaxia Espiral', nakedEye: false, ra: 202.46900, dec: 47.19500, distance: 10000000 },
    { name: 'M52 (Cúmulo Abierto)', type: 'Cúmulo Abierto', nakedEye: false, ra: 357.34000, dec: 61.35000, distance: 1000 },
    { name: 'M53 (Cúmulo Globular)', type: 'Cúmulo Globular', nakedEye: false, ra: 206.90400, dec: 18.10000, distance: 1000 },
    { name: 'M54 (Cúmulo Globular)', type: 'Cúmulo Globular', nakedEye: false, ra: 285.39000, dec: -30.47000, distance: 1000 },
    { name: 'M55 (Cúmulo Globular)', type: 'Cúmulo Globular', nakedEye: false, ra: 297.80000, dec: -30.98000, distance: 1000 },
    { name: 'M56 (Cúmulo Globular)', type: 'Cúmulo Globular', nakedEye: false, ra: 289.47000, dec: 30.13000, distance: 1000 },
    { name: 'M57 (Nebulosa Anular)', type: 'Nebulosa Planetaria', nakedEye: false, ra: 283.74700, dec: 33.02900, distance: 1000 },
    { name: 'M58 (Galaxia Espiral)', type: 'Galaxia Espiral', nakedEye: false, ra: 188.75000, dec: 11.81000, distance: 10000000 },
    { name: 'M59 (Galaxia Elíptica)', type: 'Galaxia Elíptica', nakedEye: false, ra: 191.13000, dec: 11.52000, distance: 10000000 },
    { name: 'M60 (Galaxia Elíptica)', type: 'Galaxia Elíptica', nakedEye: false, ra: 191.73900, dec: 11.33000, distance: 10000000 },
    { name: 'M61 (Galaxia Espiral)', type: 'Galaxia Espiral', nakedEye: false, ra: 185.08000, dec: 4.43000, distance: 10000000 },
    { name: 'M62 (Cúmulo Globular)', type: 'Cúmulo Globular', nakedEye: false, ra: 250.29700, dec: -30.04000, distance: 1000 },
    { name: 'M63 (Galaxia Girasol)', type: 'Galaxia Espiral', nakedEye: false, ra: 199.39000, dec: 42.01000, distance: 10000000 },
    { name: 'M64 (Galaxia Ojo Negro)', type: 'Galaxia Espiral', nakedEye: false, ra: 192.65000, dec: 21.78000, distance: 10000000 },
    { name: 'M65 (Galaxia Espiral)', type: 'Galaxia Espiral', nakedEye: false, ra: 171.60300, dec: 13.06000, distance: 10000000 },
    { name: 'M66 (Galaxia Espiral)', type: 'Galaxia Espiral', nakedEye: false, ra: 171.74500, dec: 13.25000, distance: 10000000 },
    { name: 'M67 (Cúmulo Abierto)', type: 'Cúmulo Abierto', nakedEye: false, ra: 116.89000, dec: 11.81000, distance: 1000 },
    { name: 'M68 (Cúmulo Globular)', type: 'Cúmulo Globular', nakedEye: false, ra: 194.01500, dec: -26.75000, distance: 1000 },
    { name: 'M69 (Cúmulo Globular)', type: 'Cúmulo Globular', nakedEye: false, ra: 283.47500, dec: -25.35000, distance: 1000 },
    { name: 'M70 (Cúmulo Globular)', type: 'Cúmulo Globular', nakedEye: false, ra: 285.49000, dec: -32.29000, distance: 1000 },
    { name: 'M71 (Cúmulo Globular)', type: 'Cúmulo Globular', nakedEye: false, ra: 304.75000, dec: 18.78000, distance: 1000 },
    { name: 'M72 (Cúmulo Globular)', type: 'Cúmulo Globular', nakedEye: false, ra: 313.13000, dec: -12.52000, distance: 1000 },
    { name: 'M73 (Asterismo de 4 estrellas)', type: 'Asterismo', nakedEye: false, ra: 313.31000, dec: -12.63000, distance: 1000 },
    { name: 'M74 (Galaxia Espiral)', type: 'Galaxia Espiral', nakedEye: false, ra: 23.61000, dec: 15.79000, distance: 10000000 },
    { name: 'M75 (Cúmulo Globular)', type: 'Cúmulo Globular', nakedEye: false, ra: 304.85000, dec: -21.89000, distance: 1000 },
    { name: 'M76 (Nebulosa Little Dumbbell)', type: 'Nebulosa Planetaria', nakedEye: false, ra: 25.75000, dec: 51.50000, distance: 1000 },
    { name: 'M77 (Galaxia Espiral)', type: 'Galaxia Espiral', nakedEye: false, ra: 29.86000, dec: -0.01000, distance: 10000000 },
    { name: 'M78 (Nebulosa de Reflexión)', type: 'Nebulosa', nakedEye: false, ra: 90.00000, dec: 0.00000, distance: 1000 },
    { name: 'M79 (Cúmulo Globular)', type: 'Cúmulo Globular', nakedEye: false, ra: 95.00000, dec: -24.52000, distance: 1000 },
    { name: 'M80 (Cúmulo Globular)', type: 'Cúmulo Globular', nakedEye: false, ra: 243.00000, dec: -22.98000, distance: 1000 },
    { name: 'M81 (Galaxia de Bode)', type: 'Galaxia Espiral', nakedEye: false, ra: 148.88000, dec: 69.06000, distance: 10000000 },
    { name: 'M82 (Galaxia Cigarro)', type: 'Galaxia Irregular', nakedEye: false, ra: 148.96000, dec: 69.67000, distance: 10000000 },
    { name: 'M83 (Galaxia del Molinillo Austral)', type: 'Galaxia Espiral', nakedEye: false, ra: 204.25000, dec: -29.86000, distance: 10000000 },
    { name: 'M84 (Galaxia Elíptica)', type: 'Galaxia Elíptica', nakedEye: false, ra: 187.64000, dec: 12.84000, distance: 10000000 },
    { name: 'M85 (Galaxia Lenticular)', type: 'Galaxia Lenticular', nakedEye: false, ra: 187.97000, dec: 18.11000, distance: 10000000 },
    { name: 'M86 (Galaxia Lenticular)', type: 'Galaxia Lenticular', nakedEye: false, ra: 188.46000, dec: 12.94000, distance: 10000000 },
    { name: 'M87 (Galaxia Virgo A)', type: 'Galaxia Elíptica', nakedEye: false, ra: 187.70000, dec: 12.39000, distance: 10000000 },
    { name: 'M88 (Galaxia Espiral)', type: 'Galaxia Espiral', nakedEye: false, ra: 188.75000, dec: 14.28000, distance: 10000000 },
    { name: 'M89 (Galaxia Elíptica)', type: 'Galaxia Elíptica', nakedEye: false, ra: 189.50000, dec: 12.63000, distance: 10000000 },
    { name: 'M90 (Galaxia Espiral)', type: 'Galaxia Espiral', nakedEye: false, ra: 189.70000, dec: 13.11000, distance: 10000000 },
    { name: 'M91 (Galaxia Espiral)', type: 'Galaxia Espiral', nakedEye: false, ra: 188.45000, dec: 14.30000, distance: 10000000 },
    { name: 'M92 (Cúmulo Globular)', type: 'Cúmulo Globular', nakedEye: true, ra: 254.26000, dec: 43.13000, distance: 1000 },
    { name: 'M93 (Cúmulo Abierto)', type: 'Cúmulo Abierto', nakedEye: false, ra: 114.07000, dec: -23.73000, distance: 1000 },
    { name: 'M94 (Galaxia Ojo de Gato)', type: 'Galaxia Espiral', nakedEye: false, ra: 191.04000, dec: 41.25000, distance: 10000000 },
    { name: 'M95 (Galaxia Espiral Barrada)', type: 'Galaxia Espiral', nakedEye: false, ra: 153.86000, dec: 11.81000, distance: 10000000 },
    { name: 'M96 (Galaxia Espiral)', type: 'Galaxia Espiral', nakedEye: false, ra: 154.50000, dec: 11.75000, distance: 10000000 },
    { name: 'M97 (Nebulosa del Búho)', type: 'Nebulosa Planetaria', nakedEye: false, ra: 167.92000, dec: 55.02000, distance: 1000 },
    { name: 'M98 (Galaxia Espiral)', type: 'Galaxia Espiral', nakedEye: false, ra: 184.73000, dec: 14.93000, distance: 10000000 },
    { name: 'M99 (Galaxia Espiral)', type: 'Galaxia Espiral', nakedEye: false, ra: 185.73000, dec: 13.98000, distance: 10000000 },
    { name: 'M100 (Galaxia Espiral)', type: 'Galaxia Espiral', nakedEye: false, ra: 186.20000, dec: 15.82000, distance: 10000000 },
    { name: 'M101 (Galaxia del Molinillo)', type: 'Galaxia Espiral', nakedEye: false, ra: 210.60000, dec: 54.34000, distance: 10000000 },
    { name: 'M102 (Galaxia del Huso)', type: 'Galaxia Lenticular', nakedEye: false, ra: 228.00000, dec: 55.00000, distance: 10000000 },
    { name: 'M103 (Cúmulo Abierto)', type: 'Cúmulo Abierto', nakedEye: false, ra: 40.00000, dec: 60.67000, distance: 1000 },
    { name: 'M104 (Galaxia del Sombrero)', type: 'Galaxia Espiral', nakedEye: false, ra: 184.94000, dec: -11.62000, distance: 10000000 },
    { name: 'M105 (Galaxia Elíptica)', type: 'Galaxia Elíptica', nakedEye: false, ra: 159.07000, dec: 12.60000, distance: 10000000 },
    { name: 'M106 (Galaxia Espiral)', type: 'Galaxia Espiral', nakedEye: false, ra: 184.73000, dec: 47.30000, distance: 10000000 },
    { name: 'M107 (Cúmulo Globular)', type: 'Cúmulo Globular', nakedEye: false, ra: 247.05000, dec: -13.06000, distance: 1000 },
    { name: 'M108 (Galaxia Espiral)', type: 'Galaxia Espiral', nakedEye: false, ra: 172.95000, dec: 55.67000, distance: 10000000 },
    { name: 'M109 (Galaxia Espiral Barrada)', type: 'Galaxia Espiral', nakedEye: false, ra: 177.30000, dec: 57.82000, distance: 10000000 },
    { name: 'M110 (Galaxia Elíptica)', type: 'Galaxia Elíptica', nakedEye: false, ra: 10.60000, dec: 39.80000, distance: 10000000 },
];


// -------------------------------------------------------------
// Paso 2: Funciones de Cálculo Universal
// -------------------------------------------------------------

/**
 * Calcula las coordenadas Altitud (Alt) y Azimut (Az) para un objeto dado.
 * @param {object} targetObject - Objeto con la instancia de Orb (Planeta/Luna) o coordenadas fijas (DSO).
 * @param {Date} time - Objeto Date para el momento del cálculo.
 * @returns {object} {alt: number, az: number}
 */
function getHorizontalPosition(targetObject, time) {
    // Para objetos del sistema solar, targetObject.instance es una instancia de Orb.VSOP, Orb.Sun, o Orb.Luna
    // Para DSO, targetObject.instance es un objeto {ra: number, dec: number} que simula el formato fijo.
    const observe = new Orb.Observation({
        "observer": YOUR_LOCATION,
        "target": targetObject.instance
    });
    const horizontal = observe.azel(time);

    return { alt: horizontal.elevation, az: horizontal.azimuth };
}

// -------------------------------------------------------------
// Paso 3: Renderizado y Manejo de DOM
// -------------------------------------------------------------

/**
 * Genera el string HTML para una tarjeta de efemérides.
 * @param {object} body - Objeto del cuerpo celeste con propiedades añadidas `alt` y `az`.
 * @returns {string} HTML de la tarjeta.
 */
function createHtmlCard(body) {
    let icon;
    switch (body.type) {
        case 'Planeta': icon = '🪐'; break;
        case 'Luna': icon = '🌕'; break;
        case 'Galaxia': icon = '🌌'; break;
        case 'Nebulosa': icon = '☁️'; break;
        case 'Cúmulo Globular': icon = '⭕'; break;
        case 'Cúmulo Abierto': icon = '✨'; break;
        case 'Resto Supernova': icon = '💥'; break;
        default: icon = '⭐';
    }

    // EXTRACCIÓN DEL NÚMERO MESSIER Y PREPARACIÓN DEL ATRIBUTO
    let dataAttribute = '';

    // El formato es 'M## (Nombre)'
    const messierMatch = body.name.match(/^M(\d+)/);
    if (messierMatch && body.type !== 'Planeta' && body.type !== 'Luna') {
        const messierId = messierMatch[1]; // Captura solo el número
        dataAttribute = `data-messier-id="messier-${messierId}"`;
    }

    // AHORA SOLO RETORNAMOS EL DIV SIN LA ETIQUETA <a>
    // Y MANTENEMOS EL data-messier-id para que el JavaScript lo maneje.
    return `
        <div class="ephemeris-item-card" title="${body.type}" ${dataAttribute}>
            <h3 class="ephemeris-item-header">${icon} ${body.name}</h3>
            <p class="ephemeris-item-value">
                <span style="font-size: 0.5em; color: var(--color-secondary-font); margin-right: -5px;">Alt:</span>
                ${body.alt.toFixed(2)}<span style="font-size: 1em; var(--color-secondary-font); position: relative; top: -7px;">º</span></p>
            <div class="ephemeris-item-details">
                Azimut: ${body.az.toFixed(2)}° <br>
                <span class="visibility-status">${body.nakedEye ? 'Ojo Desnudo' : 'Telescopio'}</span>
            </div>
        </div>
    `;
}

document.addEventListener('DOMContentLoaded', () => {
    // Nuevas variables DOM para los contenedores separados
    const solarSystemContainer = document.getElementById('solar-system-cards-container');
    const dsoContainer = document.getElementById('dso-cards-container');

    // Variables DOM existentes
    const widget = document.getElementById('ephemeris-widget');
    const modal = document.getElementById('ephemerisModal');
    const closeButton = document.getElementById('closeEphemerisModal');
    const dateTitleElement = document.getElementById('ephemeris-time-title');

    // Manejador de eventos para las tarjetas DSO (Catálogo Messier)
    if (dsoContainer) {
        dsoContainer.addEventListener('click', (event) => {
            // Usa .closest para encontrar el elemento de la tarjeta, incluso si se hace clic en un hijo
            const card = event.target.closest('.ephemeris-item-card');

            // Verifica que es una tarjeta DSO y que tiene el atributo data-messier-id
            const messierSlug = card ? card.getAttribute('data-messier-id') : null;

            if (messierSlug) {
                // ** AÑADIDO: Detener la propagación y prevenir acción por defecto **
                event.stopPropagation();
                event.preventDefault();
                // ***************************************************************

                // Construye la URL y redirige
                const url = `https://www.espacioprofundo.com/catalogo_messier/${messierSlug}/`;
                window.open(url, '_blank'); // Abre en una nueva pestaña
                // Opcional: modal.style.display = 'none'; // Cierra el modal si quieres
            }
        });
    }

    // Manejo de eventos del modal (Apertura y Cierre)
    if (widget && modal && closeButton) {
        widget.addEventListener('click', () => {
            fetchAndDisplayLocalEphemerides();
            modal.style.display = 'flex';
        });

        closeButton.addEventListener('click', () => { modal.style.display = 'none'; });
        window.addEventListener('click', (event) => {
            if (event.target === modal) { modal.style.display = 'none'; }
        });
    }

    function fetchAndDisplayLocalEphemerides() {
        // Limpiar los contenedores y mostrar mensaje de carga inicial
        solarSystemContainer.innerHTML = '<p class="loading-message" style="text-align: center;">Calculando Puesta del Sol...</p>';
        dsoContainer.innerHTML = '';

        // --- CALCULAR HORA DE REFERENCIA CON SUN CALC ---
        const now = new Date();
        let calculationTime = now; // Fallback por defecto: hora actual
        let timeLabel = `Ahora (${now.toLocaleTimeString('es-ES', { hour: '2-digit', minute: '2-digit' })})`;

        try {
            // Utilizamos SunCalc, asumiendo que está disponible globalmente
            const times = SunCalc.getTimes(now, LAT, LON);
            const sunsetTime = times.sunset;

            if (sunsetTime) {
                calculationTime = sunsetTime;
                timeLabel = `Puesta del Sol (${sunsetTime.toLocaleTimeString('es-ES', { hour: '2-digit', minute: '2-digit' })})`;
            }

        } catch (e) {
            console.warn("Advertencia: SunCalc no está disponible o falló. Usando la hora actual.", e);
            // Si SunCalc falla, se mantiene el fallback 'now'
        }
        // ---------------------------------------------------

        // Actualizar el título del modal con el tiempo de referencia
        dateTitleElement.innerHTML = `Posiciones Altazimutales Calculadas: ${timeLabel}`;


        const visibleSolarSystem = [];
        const visibleDSO = [];

        // Itera sobre todos los objetivos, incluyendo planetas y DSO
        const allTargets = [
            ...Object.keys(ORB_TARGETS).map(name => ({ ...ORB_TARGETS[name], name: name, instance: ORB_TARGETS[name].instance })),
            // Uso correcto: para DSO, la instancia es un objeto con RA/DEC para que Orb.Observation lo procese como FixedStar
            ...DSO_CATALOG.map(dso => ({ ...dso, instance: { ra: dso.ra, dec: dso.dec } }))
        ];

        allTargets.forEach((body) => {
            try {
                // Omitir el Sol para visibilidad nocturna
                if (body.name === 'Sol') {
                    return;
                }

                // Usamos el tiempo de cálculo (puesta del sol)
                const coords = getHorizontalPosition(body, calculationTime);
                const alt = coords.alt; // Altitud en grados
                const az = coords.az;   // Azimut en grados

                // Criterio de visibilidad: Altitud superior a 10 grados
                if (alt < 10.0) {
                    return;
                }

                // Añadir las coordenadas al objeto y clasificarlo
                const visibleBody = { ...body, alt, az };

                const isSolarSystem = body.type === 'Planeta' || body.type === 'Luna';

                if (isSolarSystem) {
                    visibleSolarSystem.push(visibleBody);
                } else { // DSO
                    visibleDSO.push(visibleBody);
                }

            } catch (e) {
                console.error(`Error calculando ${body.name}:`, e);
            }
        });

        // --- ORDENAMIENTO POR ALTITUD DESCENDENTE (CORRECCIÓN) ---
        visibleSolarSystem.sort((a, b) => b.alt - a.alt);
        visibleDSO.sort((a, b) => b.alt - a.alt);
        // ---------------------------------------------------------


        // --- 3. Inserción en el DOM ---
        // Contenedor del Sistema Solar
        if (visibleSolarSystem.length > 0) {
            solarSystemContainer.innerHTML = visibleSolarSystem.map(createHtmlCard).join('');
        } else {
            solarSystemContainer.innerHTML = '<p style="text-align: center; color: #aaa;">Ningún objeto del Sistema Solar visible con elevación suficiente (Sol excluido) a esta hora.</p>';
        }

        // Contenedor de DSO
        if (visibleDSO.length > 0) {
            dsoContainer.innerHTML = visibleDSO.map(createHtmlCard).join('');
        } else {
            dsoContainer.innerHTML = '<p style="text-align: center; color: #aaa;">Ningún objeto Messier visible con elevación suficiente a esta hora.</p>';
        }
    }
});
