// modal_catalogo.js

// ** ASUME QUE LAS SIGUIENTES CONSTANTES Y LIBRERÍAS SON ACCESIBLES **
// const LAT = 36.566578;
// const LON = -4.60272;
// const ELEV = 25;
// const Astro = window.Astro; // <--- OBJETO GLOBAL DE TU LIBRERÍA DE EFEMÉRIDES
// *******************************************************************

// Ubicación del observador (utilizando las constantes globales asumidas)
//let latitude = parseFloat(LAT);
//let longitude = parseFloat(LON);
//let elevation = parseFloat(ELEV);

const MIN_OBSERVING_ALTITUDE = 15; // Altitud mínima requerida para la visibilidad


// -----------------------------------------------------
// 1. OBTENCIÓN DE ELEMENTOS Y VARIABLES GLOBALES
// -----------------------------------------------------
const ephemerisModal = document.getElementById('ephemerisModal');
const catalogoModal = document.getElementById('CatalogoModal');

// Botones de control de modales
const openCatalogoButton = document.getElementById('openCatalogoButton');
const closeEphemerisModalButton = document.getElementById('closeEphemerisModal');
const closeCatalogoModalButton = document.getElementById('closeCatalogoModal');

// Botones y contenedores de datos
const catalogoSolarButton = document.getElementById('catalogoSolarButton');
const dataContainer = document.getElementById('catalogo-data-container');
const Astro = window.Astronomy;

// Variable global para almacenar los datos del Sistema Solar una vez cargados
let solarSystemData = [];

// -----------------------------------------------------
// 2. FUNCIONES AUXILIARES PARA FORMATEO
// -----------------------------------------------------

/**
 * Ayudante para formatear la masa (ej: 5.97 x 10^24)
 * @param {Object} mass - Objeto { massValue, massExponent }
 */
function formatExponent(mass) {
    if (!mass || !mass.massValue || !mass.massExponent) return 'N/A';
    const value = mass.massValue.toLocaleString('es-ES', { maximumFractionDigits: 3 });
    // Usamos sup y sub para la notación de exponente en HTML
    return `${value} x 10<sup>${mass.massExponent}</sup>`;
}

/**
 * Ayudante para listar las lunas (o mostrar un mensaje)
 * @param {Array<Object>} moons - Array de lunas.
 */
function listMoons(moons) {
    if (!Array.isArray(moons) || moons.length === 0) {
        return 'Ninguna (o no aplica)';
    }
    const moonNames = moons.slice(0, 5).map(m => m.moon).join(', ');
    const more = moons.length > 5 ? ` y ${moons.length - 5} más` : '';
    return `${moonNames}${more}`;
}
/*
async function calculateNextObservationTime(targetKey) {
    if (typeof Astro === 'undefined' || !Astro[targetKey] || !Astro.Search) {
        return "ERROR: Librería de efemérides no disponible.";
    }


    try {
        const target = Astro[targetKey];
        const location = { lat: YOUR_LOCATION.latitude, lon: YOUR_LOCATION.longitude, elev: YOUR_LOCATION.altitude };

        let date = new Date();
        const start_time = date.getTime() / 1000;

        // Búsqueda del Tránsito (Altitud Máxima) en los próximos 360 días
        const transit = Astro.Search(target, location, start_time, 360,
            (time_seconds) => {
                const position = Astro.Equator(target, time_seconds, location);
                const horizontal = Astro.Horizon(time_seconds, location, position.ra, position.dec, 'ecliptic');
                return horizontal.alt; // Buscamos el máximo de altitud
            },
            Astro.SearchMax);

        if (transit && transit.time) {
            const transitTime = new Date(transit.time * 1000);

            const formattedDate = transitTime.toLocaleString('es-ES', {
                day: '2-digit',
                month: 'short',
                year: 'numeric',
                hour: '2-digit',
                minute: '2-digit'
            });

            // Cálculo de Altitud Máxima en ese tiempo
            const positionAtTransit = Astro.Equator(target, transit.time, location);
            const horizontalAtTransit = Astro.Horizon(transit.time, location, positionAtTransit.ra, positionAtTransit.dec, 'ecliptic');
            const alt = horizontalAtTransit.alt.toFixed(1);

            const isVisible = parseFloat(alt) >= MIN_OBSERVING_ALTITUDE;

            return `${formattedDate} (Altitud Máx: ${alt}°)${!isVisible ? ' - 🛑 Bajo horizonte' : ''}`;
        } else {
            return "No se encontró un tránsito en el próximo año.";
        }

    } catch (error) {
        console.error("Error al calcular el tiempo de observación:", error);
        return "Error en el cálculo de visibilidad.";
    }
}
*/

// -----------------------------------------------------
// 3. LÓGICA DE CARGA Y RENDERIZADO DEL CATÁLOGO SOLAR
// -----------------------------------------------------

/**
 * Carga los datos del Sistema Solar desde el archivo JSON local. (Mantenida)
 */
async function fetchSolarSystemData() {
    const url = './static/solar_system/sistema_solar.json';
    try {
        const response = await fetch(url);
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        const data = await response.json();
        solarSystemData = data.bodies;
        return data.bodies;
    } catch (error) {
        console.error("Error al cargar el catálogo del sistema solar:", error);
        return null;
    }
}

/**
 * Crea la plantilla HTML para una tarjeta de cuerpo celeste. (Mantenida)
 */
function createSolarSystemCard(body) {
    const radius = body.meanRadius ?
        `${(body.meanRadius / 1000).toFixed(0).toLocaleString()} km` : 'N/A';
    const gravity = body.gravity ? `${body.gravity} m/s²` : 'N/A';
    const moonsCount = Array.isArray(body.moons) ? body.moons.length : 0;
    const moonsText = moonsCount > 0 ? `${moonsCount} Lunas` : 'No tiene lunas';
    const bodyType = body.bodyType || 'Cuerpo Celeste';

    return `
        <div class="ephemeris-card solar-system-card clickable-card" data-id="${body.id}">
            <h3 class="card-title">${body.name}</h3>
            <p class="card-subtitle">${bodyType}</p>
            <div class="card-details">
                <p><strong>Tipo:</strong> ${bodyType}</p>
                <p><strong>Radio Medio:</strong> ${radius}</p>
                <p><strong>Gravedad:</strong> ${gravity}</p>
                <p><strong>Satélites:</strong> ${moonsText}</p>
            </div>
            ${body.isPlanet ? '<span class="planet-badge">Planeta Mayor</span>' : ''}
        </div>
    `;
}

// --- Control de Visibilidad de Modales ---

if (openCatalogoButton) {
    openCatalogoButton.addEventListener('click', () => {
        if (catalogoModal) {
            catalogoModal.style.display = 'block';
        }
    });
}

if (closeEphemerisModalButton) {
    closeEphemerisModalButton.addEventListener('click', () => {
        if (ephemerisModal) {
            ephemerisModal.style.display = 'none';
        }
    });
}

if (closeCatalogoModalButton) {
    closeCatalogoModalButton.addEventListener('click', () => {
        if (catalogoModal) {
            catalogoModal.style.display = 'none';
        }
    });
}

window.addEventListener('click', (event) => {
    if (event.target === ephemerisModal) {
        ephemerisModal.style.display = 'none';
    }
    if (event.target === catalogoModal) {
        catalogoModal.style.display = 'none';
    }
});
