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
import { ORB_TARGETS } from './solar_system.js';

// Ubicación del observador en el formato requerido por Orb.Observation
const YOUR_LOCATION = {
    "latitude": LAT,
    "longitude": LON,
    "altitude": ELEV
};

// Catálogo de Objetos de Cielo Profundo (DSO) - CATÁLOGO MESSIER COMPLETO
// RA y DEC en grados decimales. Distance es aproximada.
import { DSO_CATALOG } from './messier_catalog.js';

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
 * SE ELIMINA EL ENLACE EXTERNO.
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
        case 'Nebulosa Planetaria': icon = '🌀'; break;
        default: icon = '⭐';
    }

    // EXTRACCIÓN DEL NÚMERO MESSIER
    let dataAttribute = '';
    let cardClass = 'ephemeris-item-card';

    // El formato es 'M## (Nombre)'
    const messierMatch = body.name.match(/^M(\d+)/);

    if (messierMatch && body.type !== 'Planeta' && body.type !== 'Luna' && body.name !== 'Sol') {
        const messierId = messierMatch[1]; // Captura solo el número
        // El ID de Messier es el número para buscar en el JSON
        dataAttribute = `data-messier-id="${messierId}"`;
        // Añadimos una clase para distinguir las tarjetas clicables
        cardClass += ' clickable-dso';
    }

    // El contenedor ahora es solo un div para que la lógica de clic sea interna.
    return `
        <div class="${cardClass}" title="${body.type}" ${dataAttribute}>
            <h3 class="ephemeris-item-header">${icon} ${body.name}</h3>
            <p class="ephemeris-item-value">
                <span style="font-size: 0.5em; color: var(--color-secondary-font); margin-right: -5px;">Alt:</span>
                ${body.alt.toFixed(2)}<span style="font-size: 1em; var(--color-secondary-font); position: relative; top: -7px;">º</span>
            </p>
            <div class="ephemeris-item-details">
                Azimut: ${body.az.toFixed(2)}° <br>
                <span class="visibility-status">${body.nakedEye ? 'Ojo Desnudo' : 'Telescopio'}</span>
            </div>
        </div>`;
}

// Variable para cachear los datos del catálogo Messier
let cachedMessierData = null;

/**
 * Carga el archivo JSON del catálogo Messier.
 * @returns {Promise<Array<object>>} Promesa que resuelve con los datos del catálogo.
 */
async function loadMessierData() {
    if (cachedMessierData) {
        return cachedMessierData;
    }

    const jsonPath = './static/messier/messier_data.json';
    try {
        const response = await fetch(jsonPath);
        if (!response.ok) {
            throw new Error(`Error HTTP: ${response.status}`);
        }
        const data = await response.json();
        // Indexar los datos por número de Messier (por ejemplo, '1' para M1) para un acceso rápido
        cachedMessierData = data.reduce((acc, obj) => {
            // El número en el JSON es "M1", "M2", etc.
            const match = obj.messier_number.match(/M(\d+)/);
            if (match) {
                // Usa solo el número como clave ('1', '2', etc.)
                acc[match[1]] = obj;
            }
            return acc;
        }, {});

        return cachedMessierData;

    } catch (error) {
        console.error("Error al cargar el archivo JSON de Messier:", error);
        return {}; // Devuelve un objeto vacío en caso de fallo
    }
}

/**
 * Muestra el modal de detalles del objeto Messier.
 * @param {string} messierId - El número del objeto Messier (ej: '1', '2', etc.).
 * @param {object} details - Los datos del objeto Messier cargados del JSON.
 */
function showMessierDetailModal(messierId, details) {
    const modal = document.getElementById('messierDetailModal');
    const content = document.getElementById('messierDetailContent');

    if (!modal || !content || !details) {
        console.error('Elementos DOM no encontrados o datos no disponibles.');
        return;
    }
    console.log("Claves de Visibilidad de JSON:", Object.keys(details.visibilidad));
    // --- MAPEO DE ICONOS PARA VISIBILIDAD ---
    // IMPORTANTE: Las claves deben coincidir EXACTAMENTE con las de tu JSON de datos.
    const visibilityMap = {
        'ojo': '👁️',        // Ojo
        'binoculares': '🔍',  // Lupa / Binoculares
        'telescopio': '🔭'   // Telescopio
    };
    // -----------------------------------------------------

    // --- Construir el contenido HTML de los detalles ---
    const visibilityHtml = Object.entries(details.visibilidad).map(([key, value]) => {

        // Excluimos las imágenes, que el console.log ha confirmado que existen
        if (key.startsWith('imagen')) return '';

        const displayKey = key.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase());

        let icon = '⭐'; // Por defecto, si no se encuentra

        // --- LÓGICA DE ASIGNACIÓN MEJORADA BASADA EN INCLUDES() ---
        const lowerKey = key.toLowerCase();

        if (lowerKey.includes('ojo')) {
            icon = visibilityMap['ojo'];
        } else if (lowerKey.includes('binoculares')) { // Comprobamos 'binoculares' o 'binocular'
            icon = visibilityMap['binoculares'];
        } else if (lowerKey.includes('telescopio')) { // Comprobamos cualquier clave que contenga 'telescopio'
            icon = visibilityMap['telescopio'];
        }
        // -----------------------------------------------------------

        // Creamos el párrafo con el icono delante de la clave
        return `<p><strong>${icon} ${displayKey}:</strong> ${value}</p>`;
    }).join('');

    const imageUrl = `./static/messier/messier_images/messier${messierId}.jpg`;

    content.innerHTML = `
        <div class="messier-detail-header">
            <div>
                <img src="${imageUrl}" alt="${details.nombre_comun}" class="messier-detail-image" onerror="this.onerror=null;this.src='./static/messier/placeholder.jpg';">
            </div>

            <div>
                <h2 class="messier-detail-title">${details.messier_number_full} (${details.nombre_comun})</h2>

                <div class="messier-detail-section">
                    <h3>Datos Clave</h3>
                    <p><strong>Clasificación:</strong> ${details.type || 'N/A'}</p>
                    <p><strong>Magnitud Aparente:</strong> ${details.magnitud_aparente}</p>
                    <p><strong>Tamaño Aparente:</strong> ${details.tamano_aparente}</p>
                    <p><strong>Distancia:</strong> ${details.distancia_al} años luz</p>
                </div>

                <div class="messier-detail-section" style="margin-top: 20px;">
                    <h3>Coordenadas Ecuatoriales</h3>
                    <p><strong>Ascensión Recta (RA):</strong> ${details.coordenadas_ecuatoriales.ascension_recta}</p>
                    <p><strong>Declinación (Dec):</strong> ${details.coordenadas_ecuatoriales.declinacion}</p>
                </div>
            </div>
        </div>

        <div class="messier-detail-body">

            <div class="messier-detail-section messier-visibility-description">
                <h3>🔭 Visibilidad de Observación</h3>
                ${visibilityHtml}
            </div>

            <div class="messier-detail-section messier-detail-description">
                <h3>📖 Descripción Detallada</h3>
                <p>${details.descripcion}</p>
            </div>
        </div>
    `;

    modal.style.display = 'flex';
}
// -------------------------------------------------------------
// Paso 4: Inicialización de Eventos y Modales
// -------------------------------------------------------------

document.addEventListener('DOMContentLoaded', () => {
    // ... Variables DOM existentes (se mantienen)
    const solarSystemContainer = document.getElementById('solar-system-cards-container');
    const dsoContainer = document.getElementById('dso-cards-container');
    const widget = document.getElementById('ephemeris-widget');
    const modal = document.getElementById('ephemerisModal');
    const closeButton = document.getElementById('closeEphemerisModal');
    const dateTitleElement = document.getElementById('ephemeris-time-title');

    // Nuevas variables DOM para el modal de detalles
    const detailModal = document.getElementById('messierDetailModal');
    const closeDetailButton = document.getElementById('closeMessierDetailModal');


    // Manejo de eventos del modal de efemérides (Apertura y Cierre) - SE MANTIENE
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

    // Manejo de eventos del nuevo modal de detalles
    if (detailModal && closeDetailButton) {
        closeDetailButton.addEventListener('click', () => { detailModal.style.display = 'none'; });
        window.addEventListener('click', (event) => {
            if (event.target === detailModal) { detailModal.style.display = 'none'; }
        });
    }

    // --- NUEVO MANEJO DE CLIC PARA LAS TARJETAS DSO ---
    // Usamos delegación de eventos en el contenedor de DSO para ser eficiente.
    if (dsoContainer) {
        dsoContainer.addEventListener('click', async (event) => {
            // Encuentra el elemento de tarjeta clicable
            const card = event.target.closest('.clickable-dso');

            if (card) {
                // Obtiene el ID de Messier del atributo de datos
                const messierId = card.getAttribute('data-messier-id');

                if (messierId) {
                    try {
                        // Carga los datos JSON si aún no están en caché
                        const allMessierDetails = await loadMessierData();

                        // Busca los detalles específicos usando el ID
                        const details = allMessierDetails[messierId];

                        if (details) {
                            showMessierDetailModal(messierId, details);
                        } else {
                            console.warn(`No se encontraron detalles para M${messierId}`);
                        }
                    } catch (e) {
                        console.error("Fallo al procesar el clic en la tarjeta DSO:", e);
                    }
                }
            }
        });
    }
    // -------------------------------------------------

    function fetchAndDisplayLocalEphemerides() {
        // Limpiar los contenedores y mostrar mensaje de carga inicial
        solarSystemContainer.innerHTML = '<p class="loading-message" style="text-align: center;">Calculando Hora de Referencia...</p>';
        dsoContainer.innerHTML = '';

        // --- CALCULAR HORA DE REFERENCIA CON SUN CALC (LÓGICA ASTRONÓMICA) ---
        const now = new Date();
        let calculationTime = now; // Valor por defecto: Hora actual
        let timeLabel = `Ahora (${now.toLocaleTimeString('es-ES', { hour: '2-digit', minute: '2-digit' })})`;

        try {
            // Utilizamos SunCalc para obtener el inicio y fin del Crepúsculo Astronómico de HOY
            const times = SunCalc.getTimes(now, LAT, LON);

            // nightEnd: Amanecer Astronómico (Fin de la noche oscura)
            const nightEnd = times.nightEnd;

            // night: Anochecer Astronómico (Inicio de la noche oscura)
            const night = times.night;

            // Convertir a milisegundos para fácil comparación
            const nowMs = now.getTime();
            const nightEndMs = nightEnd.getTime();
            const nightMs = night.getTime();

            // Condición de DÍA: Si la hora actual está entre el Amanecer Astronómico y el Anochecer Astronómico.
            // Es decir, si estamos en el período donde el cielo no está lo suficientemente oscuro.
            // (now > nightEnd AND now < night)
            if (nowMs >= nightEndMs && nowMs < nightMs) {

                // Caso 1: Estamos de DÍA (ej: 11:00 AM).
                // Calcular para la hora del Anochecer Astronómico (Inicio de la Noche Oscura).
                calculationTime = night;
                timeLabel = `Anochecer Astronómico (${night.toLocaleTimeString('es-ES', { hour: '2-digit', minute: '2-digit' })})`;

            } else {

                // Caso 2: Estamos de NOCHE (después del anochecer o antes del amanecer astronómico).
                // Usar la hora actual para mostrar la visibilidad en este instante.
                // calculationTime ya es 'now', solo actualizamos el label
                timeLabel = `Ahora (${now.toLocaleTimeString('es-ES', { hour: '2-digit', minute: '2-digit' })})`;
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

                // Usamos el tiempo de cálculo (puesta del sol/noche)
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

        // --- ORDENAMIENTO POR ALTITUD DESCENDENTE ---
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
