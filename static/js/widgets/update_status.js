//update_status.js
/**
 * Script para actualizar el estado de la estación meteorológica cada segundo.
 * También gestiona la recarga de scripts de widgets si se detecta una nueva actualización en la BD.
 */

// URL del script PHP que devuelve el estado
const STATUS_API_URL = './static/modules/widgets/get_last_update.php';
/**
 * Script para actualizar el estado de la estación meteorológica cada segundo
 * y gestionar la carga y recarga centralizada de los scripts de los widgets.
 */

// Selectores del DOM
const statusTimeLongEl = document.getElementById('pws-status-time-long');
const statusTimeAgoEl = document.getElementById('pws-status-time-ago');
const headElement = document.head; // Usamos <head> para cargar los scripts
const statusContainerEl = document.querySelector('.pws-status-container');
const statusTextEl = document.querySelector('.pws-status-text');

// Array de URLs de los scripts de widgets a cargar/recargar.
const WIDGET_SCRIPTS = [
    "./static/js/widgets/temp_widget.js",
    "./static/js/widgets/dew_widget.js",
    "./static/js/widgets/humidity_widget.js",
    "./static/js/widgets/wind_widget.js",
    "./static/js/widgets/rain_widget.js",
    "./static/js/widgets/pressure_widget.js",
    "./static/js/widgets/uv_widget.js",
    "./static/js/widgets/solar_widget.js",
    "./static/js/widgets/temp_interior_widget.js",
    "./static/js/widgets/humidity_interior_widget.js",
    "./static/js/widgets/seeing_widget.js",
];

// Variable global para almacenar la última diferencia conocida de tiempo
let lastKnownDiffSeconds = Infinity; // Inicializado alto para forzar la primera carga si es necesario

/**
 * 1. Carga inicial de todos los scripts de widgets.
 */
function loadWidgetScripts() {
    WIDGET_SCRIPTS.forEach(scriptUrl => {
        const script = document.createElement('script');
        // Usamos Date.now() para asegurar que siempre se cargue la versión más reciente
        script.src = `${scriptUrl}?v=${Date.now()}`;
        headElement.appendChild(script);
    });
}

/**
 * 2. Recarga dinámica de todos los scripts de widgets (al detectar nueva actualización).
 */
function reloadWidgetScripts() {
    WIDGET_SCRIPTS.forEach(scriptUrl => {
        // Buscar el script existente en el DOM.
        // Buscamos un script cuya ruta comience con la URL base, ignorando el parámetro de versión.
        const existingScript = document.querySelector(`script[src^="${scriptUrl}"]`);

        if (existingScript) {
            // Crear una nueva etiqueta script.
            const newScript = document.createElement('script');
            newScript.src = `${scriptUrl}?v=${Date.now()}`;

            // Reemplazar la etiqueta antigua por la nueva.
            existingScript.parentNode.replaceChild(newScript, existingScript);
        } else {
            // Si por alguna razón no se encuentra, lo añadimos de nuevo.
            const newScript = document.createElement('script');
            newScript.src = `${scriptUrl}?v=${Date.now()}`;
            headElement.appendChild(newScript);
            console.warn(`⚠️ Script no encontrado, recargado como nuevo: ${scriptUrl}`);
        }
    });
}

/**
 * 3. Función principal para obtener el estado del servidor y actualizar el DOM.
 */
async function updateStatusFromAPI() {
    try {
        const response = await fetch(STATUS_API_URL);

        if (!response.ok) {
            throw new Error(`Error en la respuesta de la API: ${response.statusText}`);
        }

        const data = await response.json();

        // **Lógica de Recarga Automática de Widgets**
        const currentDiffSeconds = data.diff_seconds;

        // Comprobamos si el tiempo de actualización actual es *menor* que la diferencia previa.
        if (currentDiffSeconds < lastKnownDiffSeconds) {
            reloadWidgetScripts();
        }

        // Actualizar la última diferencia conocida DESPUÉS de la comprobación.
        lastKnownDiffSeconds = currentDiffSeconds;

        // =======================================================
        // 🚨 LÓGICA DE ESTADO ONLINE / OFFLINE
        // =======================================================
        // 🔑 Modificado: Usar el valor de la BD para calcular el umbral
        // Usamos 90s como valor de respaldo si station_interval_sec no se recibe (aunque no debería)
        // El valor que usamos es un minuto más de lo que debería ser el intervalo establecido en la estación.
        const STATION_INTERVAL = data.station_interval_sec || 30;
        const OFFLINE_THRESHOLD = STATION_INTERVAL + 60;

        let newStatusClass = 'pws-online';
        let newStatusText = 'PWS online';

        if (currentDiffSeconds > OFFLINE_THRESHOLD) {
            newStatusClass = 'pws-offline';
            newStatusText = 'PWS Desconectada';
        }

        // Aplicar los cambios al DOM
        if (statusContainerEl && statusTextEl) {
            // Eliminar la clase vieja y añadir la nueva
            statusContainerEl.classList.remove('pws-online', 'pws-offline');
            statusContainerEl.classList.add(newStatusClass);

            // Actualizar el texto
            statusTextEl.textContent = newStatusText;

            // Opcional: Actualizar el tooltip (si es necesario)
            // Esto solo se puede hacer si el elemento tiene un método para actualizar el título.
            // Si es un <pws-info> con atributo title, se puede actualizar así:
            const pwsInfoEl = document.getElementById("PWS_info");
            if(pwsInfoEl) {
                pwsInfoEl.setAttribute("title", `Última actualización: ${data.ts_formatted}`);
            }
        }
        // =======================================================

        // **Actualización del DOM**
        if (statusTimeLongEl) {
            statusTimeLongEl.innerText = data.ts_formatted;
        }

        if (statusTimeAgoEl) {
            statusTimeAgoEl.innerText = `Actualizado hace ${currentDiffSeconds} sec`;
            statusTimeAgoEl.dataset.updated = data.local_timestamp;
        }

    } catch (error) {
        console.error('Fallo al actualizar el estado:', error);
        if (statusTimeAgoEl) {
             statusTimeAgoEl.innerText = 'Error al cargar estado';
        }
    }
}

// =========================================================
// INICIO DE LA EJECUCIÓN
// =========================================================

// 1. Cargar los scripts de los widgets inmediatamente al iniciar el script de estado
loadWidgetScripts();

// 2. Iniciar la actualización de estado inmediata y luego configurar el intervalo
updateStatusFromAPI(); // Primera ejecución
setInterval(updateStatusFromAPI, 1000); // Ejecutar cada 1000 milisegundos (1 segundo)
