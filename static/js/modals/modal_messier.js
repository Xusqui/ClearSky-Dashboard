//modal_messier.js
document.addEventListener('DOMContentLoaded', () => {
    // --- URLs de la API ---
    const API_DATA_URL = 'https://astro.xusqui.com/messier_objects';
    // URL de imagen correcta
    const API_IMAGE_BASE_URL = 'https://astro.xusqui.com/messier/image/';

    // --- Referencias a elementos del DOM ---
    // NOTA: Los IDs deben coincidir con los de modal_messier.php y modal_messier_detalle.php
    const messierGrid = document.getElementById('messierGrid');
    const catalogoButton = document.getElementById('catalogoMessierButton');
    const catalogModal = document.getElementById('messierCatalogModal');
    const detailModal = document.getElementById('messierDetailModal');
    const detailContent = document.getElementById('messierDetailContent');
    const loadingMessage = document.getElementById('loadingMessage');

    let messierData = [];

    // --- Funciones Auxiliares ---

    /**
     * Extrae el número de Messier de una cadena (ej. "M37" -> "37").
     */
    function getNumericId(messierNumber) {
        return messierNumber.substring(1);
    }

    /**
     * Construye la URL de la imagen (ej. '.../messier/image/37').
     */
    function getImageUrl(messierNumber) {
        const numericId = getNumericId(messierNumber);
        return `${API_IMAGE_BASE_URL}${numericId}`;
    }

    // --- Carga y Renderizado de Datos ---

    async function fetchMessierData() {
        if (loadingMessage) loadingMessage.textContent = 'Cargando datos del universo... 🌌';

        try {
            const response = await fetch(API_DATA_URL);
            if (!response.ok) {
                throw new Error(`Error ${response.status}: No se pudieron cargar los datos del catálogo.`);
            }
            messierData = await response.json();
            createMessierCards(messierData);
        } catch (error) {
            console.error('Fallo en la carga de datos de la API:', error);
            messierGrid.innerHTML = `<p style="color:var(--wu-red);">Error de Conexión: ${error.message}. Intenta recargar la página.</p>`;
        }
    }

    function createMessierCards(data) {
        messierGrid.innerHTML = '';
        if (data.length === 0) {
            messierGrid.innerHTML = '<p style="color:var(--wu-yellow);">El catálogo está vacío.</p>';
            return;
        }

        data.forEach(item => {
            const card = document.createElement('div');
            card.className = 'messier-card';
            card.dataset.messierNumber = item.messier_number;

            const imageURL = getImageUrl(item.messier_number);

            card.innerHTML = `
                <img src="${imageURL}" alt="${item.messier_number_full} - ${item.nombre_comun}" loading="lazy"
                     onerror="this.onerror=null; this.src='placeholder.jpg';">
                <h3>${item.messier_number} - ${item.nombre_comun}</h3>
                <p>Tipo: ${item.type}</p>
            `;

            // ✅ Evento de clic para abrir el modal de detalle
            card.addEventListener('click', () => openDetailModal(item.messier_number));
            messierGrid.appendChild(card);
        });
    }

    // --- Lógica de Apertura y Cierre de Modales ---

    // 1. Apertura del Modal de Catálogo
    catalogoButton.addEventListener('click', () => {
        catalogModal.style.display = 'flex'; // Abre el modal
        document.body.style.overflow = 'hidden';

        if (messierData.length === 0) {
            fetchMessierData();
        }
    });

    // 2. Cierre del Modal de Catálogo
    window.closeCatalogModal = function() {
        catalogModal.style.display = 'none';
        document.body.style.overflow = '';
    };

    // 3. Apertura del Modal de Detalle
    function openDetailModal(messierNumber) {
        const item = messierData.find(m => m.messier_number === messierNumber);
        if (!item) return;

        const imageURL = getImageUrl(item.messier_number);

        // Construir el HTML de la visibilidad y coordenadas
        const visibilityHTML = Object.entries(item.visibilidad).map(([key, value]) => `
            <div class="detail-item">
                <strong>${key.replace(/_/g, ' ')}</strong>
                <span>${value}</span>
            </div>
        `).join('');

        detailContent.innerHTML = `
            <h2>${item.messier_number_full}: ${item.nombre_comun}</h2>

            <img src="${imageURL}" alt="${item.messier_number_full}"
                 onerror="this.onerror=null; this.src='placeholder.jpg';">

            <h3>Parámetros Principales</h3>
            <div class="detail-grid">
                <div class="detail-item"><strong>Tipo</strong><span>${item.type}</span></div>
                <div class="detail-item"><strong>Distancia (AL)</strong><span>${item.distancia_al}</span></div>
                <div class="detail-item"><strong>Magnitud Aparente</strong><span>${item.magnitud_aparente}</span></div>
                <div class="detail-item"><strong>Tamaño Aparente</strong><span>${item.tamano_aparente}</span></div>
            </div>

            <h3>Coordenadas</h3>
            <div class="detail-grid">
                <div class="detail-item"><strong>Ascensión Recta (RA)</strong><span>${item.coordenadas_ecuatoriales.ascension_recta}</span></div>
                <div class="detail-item"><strong>Declinación (Dec)</strong><span>${item.coordenadas_ecuatoriales.declinacion}</span></div>
            </div>

            <h3>Visibilidad Típica</h3>
            <div class="detail-grid">
                ${visibilityHTML}
            </div>

            <h3>Descripción Detallada</h3>
            <div class="description-box">
                ${item.descripcion}
            </div>
        `;

        detailModal.style.display = 'flex'; // Abre el modal
    }

    // 4. Cierre del Modal de Detalle
    window.closeDetailModal = function() {
        detailModal.style.display = 'none';
    };

    // --- Manejo de Eventos Globales ---

    // Cerrar el modal principal al hacer clic fuera
    window.addEventListener('click', (event) => {
        if (event.target === catalogModal) {
            closeCatalogModal();
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
