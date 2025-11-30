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

// -----------------------------------------------------------
// Paso 1.5: Variables Globales de Estado y Caché de Datos
// -----------------------------------------------------------

// --- VARIABLES GLOBALES PARA ESTADO DE ORDENACIÓN (DSO) ---
// Almacena los DSO visibles para re-renderizado sin recalcular
let visibleDSOData = [];
// 'altitud' (default) o 'messier'
let dsoOrderState = 'altitud';
// -----------------------------------------------------------

// --- NUEVAS VARIABLES GLOBALES PARA SISTEMA SOLAR ---
let visibleSolarSystemData = []; // Para almacenar los cuerpos visibles CON alt/az y detalles raw
let solarSystemDetailsCache = []; // Para almacenar todos los detalles del JSON
const SOLAR_SYSTEM_DATA_URL = './static/solar_system/sistema_solar.json';
// -----------------------------------------------------------

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

/**
 * Carga los detalles de los objetos del Sistema Solar desde el archivo JSON.
 * Se llama al abrir el modal de efemérides.
 * @returns {Promise<void>}
 */
async function fetchSolarSystemDetails() {
    if (solarSystemDetailsCache.length > 0) {
        return; // Ya cargado, se evita re-fetch.
    }
    try {
        const response = await fetch(SOLAR_SYSTEM_DATA_URL);
        if (!response.ok) {
            throw new Error(`Error HTTP: ${response.status}`);
        }
        const data = await response.json();
        solarSystemDetailsCache = data.bodies;
    } catch (error) {
        console.error("Error al cargar los detalles del Sistema Solar:", error);
    }
}


// -------------------------------------------------------------
// Paso 3: Renderizado y Manejo de DOM
// -------------------------------------------------------------


/**
 * Genera el string HTML para una tarjeta de efemérides.
 * @param {object} body - Objeto del cuerpo celeste con propiedades añadidas `alt` y `az`, y opcionales `messierId` o `ssId`.
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

    // EXTRACCIÓN DEL NÚMERO MESSIER O ID DEL SISTEMA SOLAR
    let dataAttribute = '';
    let cardClass = 'ephemeris-item-card';

    const messierId = body.messierId || body.name.match(/^M(\d+)/)?.[1];

    if (messierId && body.type !== 'Planeta' && body.type !== 'Luna' && body.name !== 'Sol') {
        dataAttribute = `data-messier-id="${messierId}"`;
        // Clase para distinguir las tarjetas Messier clicables
        cardClass += ' clickable-dso';
    } else if (body.ssId) { // NUEVA LÓGICA PARA SISTEMA SOLAR
        dataAttribute = `data-ss-id="${body.ssId}"`;
        // Clase para distinguir las tarjetas SS clicables
        cardClass += ' clickable-ss';
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
 * Muestra el modal de detalles del objeto Messier. (Lógica Mantenida)
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
    // const coords = details.coordenadas_ecuatoriales || {};

    content.innerHTML = `
       <div class="messier-detail-header">
            <div>
                <img src="${imageUrl}" alt="${details.nombre_comun}" class="messier-detail-image" onerror="this.onerror=null;this.src='./static/messier/messier_images/placeholder.jpg';">
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
 * NUEVA FUNCIÓN: Muestra el modal de detalles del objeto del Sistema Solar.
 * @param {object} details - Los datos 'raw' del objeto SS cargados desde el JSON.
 */
function showSolarSystemDetailModal(details) {
    const modal = document.getElementById('solarSystemDetailModal');
    const content = document.getElementById('solarSystemDetailContent');

    if (!modal || !content || !details) {
        console.error('Elementos DOM para el modal SS no encontrados o datos no disponibles.');
        return;
    }

    // Función auxiliar para formatear valores de masa/volumen
    const formatExponent = (val) => {
        if (!val || !val.massValue) return 'N/A';
        // toLocaleString() para separadores de miles
        const massValue = val.massValue.toFixed(3).toLocaleString('es-ES');
        return `${massValue} x 10^${val.massExponent} kg`;
    };

    // Función auxiliar para listar lunas
    const listMoons = (moons) => {
        if (!moons || moons.length === 0) return 'Ninguna conocida';
        return moons.map(m => m.moon).join(', ');
    };

    const isPlanetText = details.isPlanet ? 'Sí' : (details.bodyType || 'No');
    // Asumimos que la imagen sigue el patrón /weather/static/solar_system/images/{id}.jpg
    const imageUrl = `./static/solar_system/images/${details.id}.jpg`;
    console.log("URL de la imagen generada:", imageUrl);
    const meanRadius = details.meanRadius.toFixed(1).toLocaleString('es-ES');

    // Mapeo simple para la luna (porque el JSON la nombra "La Lune")
    const commonName = details.id === 'luna' ? 'Luna' : details.name;

    content.innerHTML = `
    <div class="solar-system-detail-header">
        <div class="image-wrapper">
            <img src="${imageUrl}" alt="${commonName}" class="solar-system-detail-image" onerror="this.onerror=null;this.src='./static/solar_system/images/placeholder.jpg';">
        </div>

        <div class="header-info">
            <h2 class="solar-system-detail-title">${commonName} (${details.englishName})</h2>
            <p class="solar-system-detail-bodytype">Tipo de Cuerpo: ${details.bodyType || 'N/A'}</p>

            <div class="solar-system-detail-section">
                <h3>Características Físicas</h3>

                <div class="data-row"><span class="label">Radio Medio:</span> <span class="value">${meanRadius} km</span></div>
                <div class="data-row"><span class="label">Densidad:</span> <span class="value">${details.density ? details.density.toFixed(3) : 'N/A'} g/cm³</span></div>
                <div class="data-row"><span class="label">Gravedad Superficial:</span> <span class="value gravity">${details.gravity ? details.gravity.toFixed(2) : 'N/A'} m/s²</span></div>
                <div class="data-row"><span class="label">Temperatura Media:</span> <span class="value">${details.avgTemp || 'N/A'}°K</span></div>
                <div class="data-row"><span class="label">Masa:</span> <span class="value">${formatExponent(details.mass)} kg</span></div>

            </div>
        </div>
    </div>

    <div class="solar-system-detail-body">
        <div class="solar-system-detail-section">
            <h3>Datos Orbitales</h3>
            <div class="data-row"><span class="label">Período Orbital Sidéreo:</span> <span class="value">${details.sideralOrbit ? details.sideralOrbit.toFixed(2) : 'N/A'} días</span></div>
            <div class="data-row"><span class="label">Período de Rotación Sidéreo:</span> <span class="value">${details.sideralRotation ? details.sideralRotation.toFixed(2) : 'N/A'} horas</span></div>
            <div class="data-row"><span class="label">Inclinación Axial:</span> <span class="value">${details.axialTilt ? details.axialTilt.toFixed(2) : 'N/A'}°</span></div>
            <div class="data-row"><span class="label">Semieje Mayor:</span> <span class="value">${details.semimajorAxis ? details.semimajorAxis.toLocaleString('es-ES') : 'N/A'} km</span></div>
        </div>

        <div class="solar-system-detail-section">
            <h3>Lunas y Descubrimiento</h3>
            <div class="data-row"><span class="label">Es Planeta:</span> <span class="value">${isPlanetText}</span></div>
            <div class="data-row"><span class="label">Lunas:</span> <span class="value">${listMoons(details.moons)}</span></div>
            <div class="data-row"><span class="label">Descubierto por:</span> <span class="value">${details.discoveredBy || 'Desconocido'}</span></div>
            <div class="data-row"><span class="label">Fecha de Descubrimiento:</span> <span class="value">${details.discoveryDate || 'N/A'}</span></div>
        </div>
    </div>`;

    modal.style.display = 'flex';
}

/**
 * Ordena y renderiza las tarjetas DSO en el DOM. (Mantenida)
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

    // NUEVAS variables DOM para el modal del Sistema Solar
    const solarSystemDetailModal = document.getElementById('solarSystemDetailModal');
    const closeSolarSystemDetailButton = document.getElementById('closesolarSystemDetailModal');


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

    // Manejo de eventos del modal de detalles de DSO (Mantenido)
    if (detailModal && closeDetailButton) {
        closeDetailButton.addEventListener('click', () => { detailModal.style.display = 'none'; });
        window.addEventListener('click', (event) => {
            if (event.target === detailModal) { detailModal.style.display = 'none'; }
        });
    }

    // --- NUEVO: Manejo de eventos del modal de detalles del Sistema Solar ---
    if (solarSystemDetailModal && closeSolarSystemDetailButton) {
        closeSolarSystemDetailButton.addEventListener('click', () => { solarSystemDetailModal.style.display = 'none'; });
        window.addEventListener('click', (event) => {
            if (event.target === solarSystemDetailModal) { solarSystemDetailModal.style.display = 'none'; }
        });
    }
    // --------------------------------------------------------------------------

    // --- MANEJO DE EVENTOS PARA EL BOTÓN DE ORDENACIÓN DSO (Mantenido) ---
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


    // --- MANEJO DE CLIC PARA LAS TARJETAS DSO (Mantenido) ---
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

    // --- NUEVO: MANEJO DE CLIC PARA LAS TARJETAS DEL SISTEMA SOLAR ---
    if (solarSystemContainer) {
        solarSystemContainer.addEventListener('click', async (event) => {
            const card = event.target.closest('.clickable-ss');

            if (card) {
                const ssId = card.getAttribute('data-ss-id');

                if (ssId) {
                    // Busca el objeto completo en la caché de visibles
                    const ssObject = visibleSolarSystemData.find(ss => ss.ssId === ssId);

                    if (ssObject && ssObject.raw_details) {
                        // Usa la nueva función de modal para el Sistema Solar
                        showSolarSystemDetailModal(ssObject.raw_details);
                    } else {
                        console.warn(`No se encontraron detalles raw para SS ID: ${ssId} en la caché de visibles.`);
                    }
                }
            }
        });
    }
    // -------------------------------------------------


    async function fetchAndDisplayLocalEphemerides() {
        // Limpiar los contenedores y mostrar mensaje de carga inicial
        const solarSystemContainer = document.getElementById('solar-system-cards-container');
        const dsoContainer = document.getElementById('dso-cards-container');

        solarSystemContainer.innerHTML = '<p class="loading-message" style="text-align: center;">Calculando Hora de Referencia y cargando detalles SS...</p>';
        dsoContainer.innerHTML = '<p class="loading-message" style="text-align: center;">Obteniendo objetos Messier desde la API...</p>';

        visibleDSOData = []; // Limpiamos el caché de DSO visibles
        visibleSolarSystemData = []; // Limpiamos el caché de SS visibles

        // --- NUEVO: Cargar detalles del Sistema Solar ---
        await fetchSolarSystemDetails();
        // -----------------------------------------------

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


        // --- 1. CÁLCULO DE OBJETOS DEL SISTEMA SOLAR (Planetas/Luna) ---
        // (Usando Orb.js y la hora de cálculo)
        Object.keys(ORB_TARGETS).forEach((name) => {
            const body = ORB_TARGETS[name];

            try {
                if (name === 'Sol') { return; }

                const coords = getHorizontalPosition(body, calculationTime);
                const alt = coords.alt;
                const az = coords.az;

                if (alt < minAlt) { return; }

                // --- NUEVO: Mapeo al detalle del JSON ---
                const orbNameKey = name.toLowerCase().replace(/[^a-z0-9]/g, '');
                const ssDetail = solarSystemDetailsCache.find(d => {
                    // Intenta hacer coincidir el ID o el nombre en inglés (normalizado)
                    return d.id === orbNameKey ||
                        d.englishName?.toLowerCase().replace(/[^a-z0-9]/g, '') === orbNameKey;
                });

                const ssId = ssDetail ? ssDetail.id : name.toLowerCase(); // ID para data-ss-id
                // ----------------------------------------

                // El objeto del sistema solar usa la estructura antigua
                const visibleBody = {
                    ...body,
                    alt,
                    az,
                    name: name, // Asegura que el nombre (ej. 'Luna') esté disponible
                    messierId: null, // No es Messier
                    ssId: ssId, // <-- NUEVO: ID para enlazar con el modal
                    raw_details: ssDetail // <-- NUEVO: Detalles del JSON para el modal
                };
                visibleSolarSystemData.push(visibleBody); // <-- USO DE LA VARIABLE GLOBAL

            } catch (e) {
                console.error(`Error calculando ${name}:`, e);
            }
        });

        // Ordenar el Sistema Solar
        visibleSolarSystemData.sort((a, b) => b.alt - a.alt);
        // Inserción en el DOM
        if (visibleSolarSystemData.length > 0) {
            solarSystemContainer.innerHTML = visibleSolarSystemData.map(createHtmlCard).join('');
        } else {
            solarSystemContainer.innerHTML = '<p style="text-align: center; color: #aaa;">Ningún objeto del Sistema Solar visible con elevación suficiente (Sol excluido) a esta hora.</p>';
        }
        // ---------------------------------------------------------------------------------


        // --- 2. NUEVA LÓGICA: FETCH DE OBJETOS DSO DESDE LA API (Mantenida) ---

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
                const rawType = dso.raw.type || dso.raw.clasificacion;
                let objectType;

                if (rawType) {
                    // Mapear clasificación a tipo amigable (ej. 'Galaxia', 'Cúmulo Abierto')
                    const typeMap = {
                        'Galaxia': 'Galaxia',
                        'Cúmulo Abierto': 'Cúmulo Abierto',
                        'Cúmulo Globular': 'Cúmulo Globular',
                        'Nebulosa Difusa': 'Nebulosa',
                        'Resto de Supernova': 'Resto Supernova',
                        'Nebulosa Planetaria': 'Nebulosa Planetaria'
                    };
                    objectType = typeMap[rawType] || rawType;

                } else if (dso.nombre_comun.includes('Galaxia')) {
                    objectType = 'Galaxia';
                } else {
                    objectType = 'Objeto de Cielo Profundo';
                }

                // Asumir 'Ojo Desnudo' si la descripción incluye 'fácil' o 'sencillo'
                const isNakedEye = dso.raw.visibilidad?.A_ojo_desnudo?.toLowerCase().includes('fácil') || dso.raw.visibilidad?.A_ojo_desnudo?.toLowerCase().includes('sencillo');

                return {
                    // Nombre en formato M## (Nombre Común) para ordenación y visualización
                    name: `${dso.messier_number} (${dso.nombre_comun.replace(/Ã¡/g, 'á').replace(/Ãº/g, 'ú').replace(/Ã©/g, 'é')})`, // Limpieza de caracteres si es necesario
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
