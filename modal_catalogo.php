<?php
    // modal_catalogo.php
?>
<div id="CatalogoModal" class="modal" style="display: none;">
    <div class="modal-content">
        <button class="close" aria-label="Cerrar" id="closeCatalogoModal">
            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none"
                stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round">
                <line x1="18" y1="6" x2="6" y2="18"></line>
                <line x1="6" y1="6" x2="18" y2="18"></line>
            </svg>
        </button>

        <div id="CatalogoContent" class="infografia ephemeris-infografia">
            <h1 class="seeing-modal-title">📚 Catálogo Astronómico</h1>

            <div id="catalogo-buttons-container" class="button-group">
                <button id="catalogoSolarButton" class="catalogo-option-button">
                    ☀️ Sistema Solar
                </button>

                <button id="catalogoMessierButton" class="catalogo-option-button">
                    🌌 Catálogo Messier
                </button>

                <button id="catalogoCaldwellButton" class="catalogo-option-button">
                    ✨ Catálogo Caldwell
                </button>
            </div>

            <div id="catalogo-data-container" class="ephemeris-card-grid">
                <p>Selecciona un catálogo para ver más detalles.</p>
            </div>
            </div>
    </div>
</div>
