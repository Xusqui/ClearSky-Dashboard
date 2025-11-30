// modal_ephemeris.js

// ** ASUME QUE ESTAS VARIABLES ESTÁN DEFINIDAS GLOBALMENTE **
// const LAT = 36.566578;
// const LON = -4.60272;
// const ELEV = 25;
// **********************************************************

// Definición de las coordenadas de tu observatorio
// Usamos las variables globales LAT y LON, asumiendo que existen.
// NOTA: Se mantiene la lógica del Sistema Solar (Planetas, Luna) con Orb.js

// -------------------------------------------------------------
// Paso 1: Inicialización de Cuerpos Celestes y Observador
// -------------------------------------------------------------

// Objetos del Sistema Solar (Usan Orb.VSOP, Orb.Sun, Orb.Luna) - MANTENIDO
 import { ORB_TARGETS } from './solar_system.js';

// Ubicación del observador en el formato requerido por Orb.Observation - MANTENIDO
const YOUR_LOCATION = {
    "latitude": LAT,
    "longitude": LON,
    "altitude": ELEV
};

// -------------------------------------------------------------
// Paso 2: Funciones de Cálculo Universal (Solo para Sistema Solar)
// -------------------------------------------------------------

/**
 * Calcula las coordenadas Altitud (Alt) y Azimut (Az) para un objeto dado.
 * Solo se usa para objetos del Sistema Solar (Planetas/Luna) en este archivo.
 * @param {object} targetObject - Objeto con la instancia de Orb (Planeta/Luna).
 * @param {Date} time - Objeto Date para el momento del cálculo.
 * @returns {object} {alt: number, az: number}
 */
function getHorizontalPosition(targetObject, time) {
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

// --- VARIABLES GLOBALES PARA ESTADO DE ORDENACIÓN ---
// Almacena los DSO visibles para re-renderizado sin recalcular
let visibleDSOData = [];
// 'altitud' (default) o 'messier'
let dsoOrderState = 'altitud';
// -----------------------------------------------------------


/**
 * Genera el string HTML para una tarjeta de efemérides.
 * @param {object} body - Objeto del cuerpo celeste con propiedades añadidas `alt` y `az`.
 * @returns {string} HTML de la tarjeta.
 */
function createHtmlCard(body) {
    let icon;
    // La propiedad 'type' es crucial para el icono, debe estar en el objeto 'body'
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

    // Usamos body.messierId si existe para evitar la regex en objetos SS
    // El objeto DSO lo tiene, el objeto SS es null
    const messierId = body.messierId || body.name.match(/^M(\d+)/)?.[1];

    if (messierId && body.type !== 'Planeta' && body.type !== 'Luna' && body.name !== 'Sol') {
        dataAttribute = `data-messier-id="${messierId}"`;
        // Añadimos una clase para distinguir las tarjetas clicables
        cardClass += ' clickable-dso';
    }

    const visibilityText = body.nakedEye ? 'Ojo Desnudo' : 'Telescopio';

    return `
        <div class="${cardClass}" title="${body.type}" ${dataAttribute}>
            <h3 class="ephemeris-item-header">${icon} ${body.name}</h3>
            <p class="ephemeris-item-value">
                <span style="font-size: 0.5em; color: var(--color-secondary-font); margin-right: -5px;">Alt:</span>
                ${body.alt.toFixed(2)}<span style="font-size: 1em; var(--color-secondary-font); position: relative; top: -7px;">°</span>
            </p>
            <div class="ephemeris-item-details">
                Azimut: ${body.az.toFixed(2)}° <br>
                <span class="visibility-status">${visibilityText}</span>
            </div>
        </div>`;
}

/**
 * Muestra el modal de detalles del objeto Messier.
 * Se utiliza el objeto 'raw' del API directamente.
 * @param {string} messierId - El número del objeto Messier (ej: '1', '2', etc.).
 * @param {object} details - Los datos 'raw' del objeto Messier cargados desde la API.
 */
function showMessierDetailModal(messierId, details) {
    const modal = document.getElementById('messierDetailModal');
    const content = document.getElementById('messierDetailContent');

    if (!modal || !content || !details) {
        console.error('Elementos DOM no encontrados o datos no disponibles.');
        return;
    }

    // --- MAPEO DE ICONOS PARA VISIBILIDAD ---
    const visibilityMap = {
        'ojo': '👁️',
        'binoculares': '🔍',
        'telescopio': '🔭'
    };

    // --- Construir el contenido HTML de los detalles ---
    const visibilityHtml = Object.entries(details.visibilidad || {}).map(([key, value]) => {

        if (key.startsWith('imagen') || !value) return '';

        const displayKey = key.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase());
        let icon = '⭐';

        const lowerKey = key.toLowerCase();

        if (lowerKey.includes('ojo')) {
            icon = visibilityMap['ojo'];
        } else if (lowerKey.includes('binoculares')) {
            icon = visibilityMap['binoculares'];
        } else if (lowerKey.includes('telescopio')) {
            icon = visibilityMap['telescopio'];
        }

        return `<p><strong>${icon} ${displayKey}:</strong> ${value}</p>`;
    }).join('');

    // Ajustamos la URL de la imagen si necesitas que funcione localmente con el id
    const imageUrl = `./static/messier/messier_images/messier${messierId}.jpg`;
    const coords = details.coordenadas_ecuatoriales || {};

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

/**
 * Ordena y renderiza las tarjetas DSO en el DOM.
 */
function renderDSOData() {
    const dsoContainer = document.getElementById('dso-cards-container');
    // Usamos el ID del botón que debes tener en tu HTML
    const toggleButton = document.getElementById('toggleDSOOrder');

    if (!dsoContainer || !toggleButton) return;

    // Lógica de Ordenación
    if (dsoOrderState === 'altitud') {
        // Altitud descendente (los más altos primero)
        visibleDSOData.sort((a, b) => b.alt - a.alt);
        toggleButton.innerHTML = 'Ordenar por Número M #️⃣';
    } else { // 'messier'
        // Orden ascendente por número Messier (ej. M1, M2, M3...)
        visibleDSOData.sort((a, b) => {
            // Extraer el número de la cadena 'M## (Nombre)'
            const numA = parseInt(a.messierId || 9999);
            const numB = parseInt(b.messierId || 9999);
            return numA - numB;
        });
        toggleButton.innerHTML = 'Ordenar por Altitud ⬆️';
    }

    // Inserción en el DOM
    if (visibleDSOData.length > 0) {
        dsoContainer.innerHTML = visibleDSOData.map(createHtmlCard).join('');
    } else {
        dsoContainer.innerHTML = '<p style="text-align: center; color: #aaa;">Ningún objeto Messier visible con elevación suficiente a esta hora.</p>';
    }
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

    // Manejo de eventos del modal de efemérides (Apertura y Cierre)
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

    // --- MANEJO DE EVENTOS PARA EL BOTÓN DE ORDENACIÓN DSO ---
    const toggleOrderButton = document.getElementById('toggleDSOOrder');
    if (toggleOrderButton) {
        toggleOrderButton.addEventListener('click', () => {
            // 1. Cambiar el estado de ordenación
            dsoOrderState = dsoOrderState === 'altitud' ? 'messier' : 'altitud';
            // 2. Re-renderizar con el nuevo orden
            renderDSOData();
        });
    }
    // -------------------------------------------------------------


    // --- MANEJO DE CLIC PARA LAS TARJETAS DSO (Usa la caché global) ---
    if (dsoContainer) {
        dsoContainer.addEventListener('click', async (event) => {
            const card = event.target.closest('.clickable-dso');

            if (card) {
                const messierId = card.getAttribute('data-messier-id');

                if (messierId) {
                    // Busca el objeto completo en la caché de visibles
                    const dsoObject = visibleDSOData.find(dso => dso.messierId === messierId);

                    if (dsoObject && dsoObject.raw_details) {
                        // Pasa los detalles 'raw' que ya están cargados desde la API
                        showMessierDetailModal(messierId, dsoObject.raw_details);
                    } else {
                        console.warn(`No se encontraron detalles para M${messierId} en la caché.`);
                    }
                }
            }
        });
    }
    // -------------------------------------------------

    async function fetchAndDisplayLocalEphemerides() {
        // Limpiar los contenedores y mostrar mensaje de carga inicial
        solarSystemContainer.innerHTML = '<p class="loading-message" style="text-align: center;">Calculando Hora de Referencia...</p>';
        dsoContainer.innerHTML = '<p class="loading-message" style="text-align: center;">Obteniendo objetos Messier desde la API...</p>';

        visibleDSOData = []; // Limpiamos el caché de DSO visibles

        // --- CALCULAR HORA DE REFERENCIA CON SUN CALC (LÓGICA ASTRONÓMICA) ---
        const now = new Date();
        let calculationTime = now;
        let timeLabel = `Ahora (${now.toLocaleTimeString('es-ES', { hour: '2-digit', minute: '2-digit' })})`;
        const minAlt = 30; // Altitud mínima de visibilidad

        try {
            // Utilizamos SunCalc (asumido global)
            const times = SunCalc.getTimes(now, LAT, LON);
            const nightEnd = times.nightEnd;
            const night = times.night;

            const nowMs = now.getTime();
            const nightEndMs = nightEnd.getTime();
            const nightMs = night.getTime();

            // Si es de DÍA, calculamos para el Anochecer Astronómico
            if (nowMs >= nightEndMs && nowMs < nightMs) {
                calculationTime = night;
                timeLabel = `Anochecer Astronómico (${night.toLocaleTimeString('es-ES', { hour: '2-digit', minute: '2-digit' })})`;
            }
            // Si es de NOCHE, usamos la hora actual.

        } catch (e) {
            console.warn("Advertencia: SunCalc no está disponible o falló. Usando la hora actual.", e);
        }
        // ---------------------------------------------------

        // Actualizar el título del modal con el tiempo de referencia
        dateTitleElement.innerHTML = `Posiciones Altazimutales Calculadas: ${timeLabel}`;

        const visibleSolarSystem = [];

        // --- 1. LÓGICA MANTENIDA: CÁLCULO DE OBJETOS DEL SISTEMA SOLAR (Planetas/Luna) ---
        // (Usando Orb.js y la hora de cálculo)
        Object.keys(ORB_TARGETS).forEach((name) => {
            const body = ORB_TARGETS[name];

            try {
                if (name === 'Sol') { return; }

                const coords = getHorizontalPosition(body, calculationTime);
                const alt = coords.alt;
                const az = coords.az;

                if (alt < minAlt) { return; }

                // El objeto del sistema solar usa la estructura antigua
                const visibleBody = {
                    ...body,
                    alt,
                    az,
                    name: name, // Asegura que el nombre (ej. 'Luna') esté disponible
                    messierId: null // No es Messier
                };
                visibleSolarSystem.push(visibleBody);

            } catch (e) {
                console.error(`Error calculando ${name}:`, e);
            }
        });

        // Ordenar el Sistema Solar
        visibleSolarSystem.sort((a, b) => b.alt - a.alt);
        // Inserción en el DOM
        if (visibleSolarSystem.length > 0) {
            solarSystemContainer.innerHTML = visibleSolarSystem.map(createHtmlCard).join('');
        } else {
            solarSystemContainer.innerHTML = '<p style="text-align: center; color: #aaa;">Ningún objeto del Sistema Solar visible con elevación suficiente (Sol excluido) a esta hora.</p>';
        }
        // ---------------------------------------------------------------------------------


        // --- 2. NUEVA LÓGICA: FETCH DE OBJETOS DSO DESDE LA API ---

        const apiLat = YOUR_LOCATION.latitude;
        const apiLon = YOUR_LOCATION.longitude;
        const datetime_str = calculationTime.toISOString().split('.')[0];

        const apiEndpoint = `https://astro.xusqui.com/messier_visible_objects?lat=${apiLat}&lon=${apiLon}&datetime_str=${encodeURIComponent(datetime_str)}&min_alt=${minAlt}`;

        try {
            const response = await fetch(apiEndpoint);
            if (!response.ok) {
                throw new Error(`Error HTTP: ${response.status} - ${await response.text()}`);
            }
            const dsoApiData = await response.json();

            // Mapear los datos de la API al formato que renderDSOData espera
            visibleDSOData = dsoApiData
                // Filtramos los objetos que no son Messier o no tienen raw data, si es necesario
                .filter(dso => dso.messier_number)
                .map(dso => {
                    const messierId = dso.messier_number.replace('M', '');

                    // Intentar inferir el tipo o usar el nombre común si el tipo no está en raw
                    const objectType = dso.raw.type || dso.raw.clasificacion || dso.nombre_comun.includes('Galaxia') ? 'Galaxia' : 'Objeto de Cielo Profundo';

                    // Asumir 'Ojo Desnudo' si la descripción incluye 'fácil' o 'sencillo'
                    const isNakedEye = dso.raw.visibilidad?.A_ojo_desnudo?.toLowerCase().includes('fácil') || dso.raw.visibilidad?.A_ojo_desnudo?.toLowerCase().includes('sencillo');

                    return {
                        // Nombre en formato M## (Nombre Común) para ordenación y visualización
                        name: `${dso.messier_number} (${dso.nombre_comun.replace('Ã¡', 'á').replace('Ãº', 'ú')})`, // Limpieza de caracteres si es necesario
                        type: objectType,
                        alt: dso.altitude_deg,
                        az: dso.azimuth_deg,
                        messierId: messierId,
                        nakedEye: isNakedEye,
                        raw_details: dso.raw // Guardamos todos los detalles para el modal
                    };
                });

        } catch (error) {
            console.error("Error al obtener objetos visibles de la API:", error);
            // Mostrar un mensaje de error claro
            dsoContainer.innerHTML = `<p style="text-align: center; color: var(--color-danger, red);">❌ Error de conexión o API: ${error.message}</p>`;
            // Salimos para no intentar renderizar datos fallidos
            return;
        }

        // Llama a la nueva función para ordenar y renderizar los DSO
        renderDSOData();
    }
});
