// modal_dso.js

document.addEventListener('DOMContentLoaded', () => {

    // --- Endpoints de la API ---
    const ENDPOINTS = {
        MESS: {
            DATA_URL: 'https://astro.xusqui.com/messier_objects',
            IMAGE_BASE_URL: 'https://astro.xusqui.com/messier/image/',
            PREFIX: 'M' // Usado para identificar el tipo de objeto en la lógica
        },
        CALD: {
            DATA_URL: 'https://astro.xusqui.com/caldwell_objects',
            IMAGE_BASE_URL: 'https://astro.xusqui.com/caldwell/image/',
            PREFIX: 'C'
        }
    };

    // --- Referencias a elementos del DOM ---
    const messierGrid = document.getElementById('messierGrid');
    const messiername = document.getElementById('catalogo_nombre');
    // Botones de activación
    const catalogoMessierButton = document.getElementById('catalogoMessierButton');
    const catalogoCaldwellButton = document.getElementById('catalogoCaldwellButton');

    // Modales y mensajes
    const catalogModal = document.getElementById('messierCatalogModal'); // Usado para ambos
    const detailModal = document.getElementById('messierDetailModal');   // Usado para ambos
    const detailContent = document.getElementById('messierDetailContent');
    const loadingMessage = document.getElementById('loadingMessage');
    const modalTitle = document.querySelector('#messierCatalogModal .modal-content h2'); // Asumiendo que el h2 es el título

    let currentCatalog = null; // Almacena 'MESS' o 'CALD'
    let currentData = [];      // Almacena los datos del catálogo activo

    // --- Funciones Auxiliares ---

    /**
     * Extrae el ID numérico de una cadena (ej. "M37" -> "37", "C1" -> "1").
     */
    function getNumericId(objectNumber) {
        // Elimina la primera letra (M o C) y devuelve el resto.
        return objectNumber.substring(1);
    }

    /**
     * Construye la URL de la imagen.
     */
    function getImageUrl(objectNumber, catalogId) {
        const numericId = getNumericId(objectNumber);
        const baseUrl = ENDPOINTS[catalogId].IMAGE_BASE_URL;
        return `${baseUrl}${numericId}`;
    }

    // --- Carga y Renderizado de Datos ---

    async function fetchCatalogData(catalogId) {
        const { DATA_URL } = ENDPOINTS[catalogId];
        currentCatalog = catalogId;

        if (loadingMessage) loadingMessage.textContent = `Cargando datos del Catálogo ${catalogId === 'MESS' ? 'Messier' : 'Caldwell'}... 🌌`;
        messierGrid.innerHTML = ''; // Limpia la cuadrícula
        messiername.innerHTML = `Catálogo ${catalogId === 'MESS' ? 'Messier' : 'Caldwell'} completo`;

        try {
            const response = await fetch(DATA_URL);
            if (!response.ok) {
                throw new Error(`Error ${response.status}: No se pudieron cargar los datos del catálogo.`);
            }
            currentData = await response.json();
            createCatalogCards(currentData, catalogId);
        } catch (error) {
            console.error(`Fallo en la carga de datos de la API (${catalogId}):`, error);
            messierGrid.innerHTML = `<p style="color:var(--red);">Error de Conexión: ${error.message}. Intenta recargar la página.</p>`;
        }
    }

    function createCatalogCards(data, catalogId) {
        if (data.length === 0) {
            messierGrid.innerHTML = '<p style="color:var(--yellow);">El catálogo está vacío.</p>';
            return;
        }

        messierGrid.innerHTML = ''; // Limpia de nuevo

        data.forEach(item => {
            const card = document.createElement('div');
            card.className = 'messier-card';
            // Usa una clave consistente 'object_number' para ambos catálogos
            const objectNumber = item.messier_number || item.caldwell_number;
            const commonName = item.nombre_comun || item.name_common; // Adapta si los nombres de claves JSON varían
            const fullNumber = item.messier_number_full || item.caldwell_number_full || objectNumber;

            card.dataset.objectNumber = objectNumber;
            card.dataset.catalogId = catalogId;

            const imageURL = getImageUrl(objectNumber, catalogId);

            card.innerHTML = `
                <img src="${imageURL}" alt="${fullNumber} - ${commonName}" loading="lazy"
                    onerror="this.onerror=null; this.src='placeholder.jpg';">
                <h3>${objectNumber} - ${commonName || 'Sin Nombre'}</h3>
                <p>Tipo: ${item.type}</p>
            `;

            // Evento de clic para abrir el modal de detalle
            card.addEventListener('click', () => openDetailModal(objectNumber));
            messierGrid.appendChild(card);
        });
    }

    // --- Lógica de Apertura y Cierre de Modales ---

    /**
     * Abre el modal del catálogo y llama a la carga de datos si es necesario.
     */
    function openCatalogModal(catalogId) {
        const titleText = catalogId === 'MESS' ? 'Catálogo Messier' : 'Catálogo Caldwell';
        if (modalTitle) modalTitle.textContent = titleText; // Actualiza el título del modal

        catalogModal.style.display = 'flex'; // Abre el modal
        document.body.style.overflow = 'hidden';

        // Solo recarga los datos si no son los que ya están cargados.
        const objectNumberKey = catalogId === 'MESS' ? 'messier_number' : 'caldwell_number';
        if (currentCatalog !== catalogId || currentData.length === 0 || !currentData[0][objectNumberKey]) {
            fetchCatalogData(catalogId);
        }
    }

    // 1. Event Listeners para los botones
    if (catalogoMessierButton) {
        catalogoMessierButton.addEventListener('click', () => openCatalogModal('MESS'));
    }
    if (catalogoCaldwellButton) {
        catalogoCaldwellButton.addEventListener('click', () => openCatalogModal('CALD'));
    }

    // 2. Cierre del Modal de Catálogo (Mismo para ambos)
    window.closeCatalogModal = function() {
        catalogModal.style.display = 'none';
        document.body.style.overflow = '';
    };

    // 3. Apertura del Modal de Detalle (Común)
    function openDetailModal(objectNumber) {
        const catalogId = currentCatalog;
        const item = currentData.find(m => (m.messier_number || m.caldwell_number) === objectNumber);
        if (!item) return;

        const imageURL = getImageUrl(objectNumber, catalogId);

        // Asume nombres de claves de detalle consistentes o las adapta:
        const fullNumber = item.messier_number_full || item.caldwell_number_full || objectNumber;
        const commonName = item.nombre_comun || item.name_common;
        const distance = item.distancia_al || item.distance_ly;
        const magnitude = item.magnitud_aparente || item.apparent_magnitude;
        const size = item.tamano_aparente || item.apparent_size;
        const ra = item.coordenadas_ecuatoriales?.ascension_recta || item.equatorial_coords?.ra;
        const dec = item.coordenadas_ecuatoriales?.declinacion || item.equatorial_coords?.dec;
        const description = item.descripcion || item.description;
        const visibility = item.visibilidad || item.visibility;


        // Construir el HTML de la visibilidad (adaptándose a estructuras diferentes si es necesario)
        let visibilityHTML = '';
        if (visibility) {
            visibilityHTML = Object.entries(visibility).map(([key, value]) => `
                <div class="detail-item">
                    <strong>${key.replace(/_/g, ' ')}</strong>
                    <span>${value}</span>
                </div>
            `).join('');
        }

        detailContent.innerHTML = `
            <h2>${fullNumber}: ${commonName || 'Objeto Caldwell/Messier'}</h2>

            <img src="${imageURL}" alt="${fullNumber}"
                onerror="this.onerror=null; this.src='placeholder.jpg';">

            <h3>Parámetros Principales</h3>
            <div class="detail-grid">
                <div class="detail-item"><strong>Tipo</strong><span>${item.type}</span></div>
                <div class="detail-item"><strong>Distancia (AL)</strong><span>${distance || 'N/A'}</span></div>
                <div class="detail-item"><strong>Magnitud Aparente</strong><span>${magnitude || 'N/A'}</span></div>
                <div class="detail-item"><strong>Tamaño Aparente</strong><span>${size || 'N/A'}</span></div>
            </div>

            <h3>Coordenadas</h3>
            <div class="detail-grid">
                <div class="detail-item"><strong>Ascensión Recta (RA)</strong><span>${ra || 'N/A'}</span></div>
                <div class="detail-item"><strong>Declinación (Dec)</strong><span>${dec || 'N/A'}</span></div>
            </div>

            ${visibilityHTML ? `
            <h3>Visibilidad Típica</h3>
            <div class="detail-grid">
                ${visibilityHTML}
            </div>` : ''}

            <h3>Descripción Detallada</h3>
            <div class="description-box">
                ${description || 'No hay descripción disponible para este objeto.'}
            </div>
        `;

        detailModal.style.display = 'flex'; // Abre el modal
    }

    // 4. Cierre del Modal de Detalle (Común)
    window.closeDetailModal = function() {
        detailModal.style.display = 'none';
    };

    // --- Manejo de Eventos Globales (Mismos para ambos) ---

    // Cerrar el modal principal al hacer clic fuera
    window.addEventListener('click', (event) => {
        if (event.target === catalogModal) {
            closeCatalogModal();
        } else if (event.target === detailModal) {
            closeDetailModal();
        }
    });

    // Cerrar modales con la tecla ESC
    document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape') {
            if (detailModal.style.display === 'flex') {
                closeDetailModal();
            } else if (catalogModal.style.display === 'flex') {
                closeCatalogModal();
            }
        }
    });
});
