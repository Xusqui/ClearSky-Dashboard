<?php
// modal_ephemeris.php
?>
<div id="ephemerisModal" class="modal">
    <div class="modal-content">
        <button class="close" aria-label="Cerrar" id="closeEphemerisModal">
            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none"
                 stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round">
                <line x1="18" y1="6" x2="6" y2="18" />
                <line x1="6" y1="6" x2="18" y2="18" />
            </svg>
        </button>

        <div class="infografia ephemeris-infografia">
            <h1 class="seeing-modal-title">🔭 Objetos Visibles Hoy</h1>

            <h2 class="seeing-group-title" id="ephemeris-time-title">Posiciones Altazimutales Calculadas: --:--:--</h2>

            <h2 class="seeing-group-title">Sistema Solar (Planetas, Luna y Sol)</h2>
            <div id="solar-system-cards-container" class="ephemeris-card-grid">
            </div>

            <div class="seeing-group-title-container">
                <h2 class="seeing-group-title">Catálogo Messier (Cielo Profundo)</h2>
                <button id="toggleDSOOrder" class="sort-toggle-button" title="Alternar Orden">
                    Ordenar por Altitud ⬆️
                </button>
            </div>
            <div id="dso-cards-container" class="ephemeris-card-grid">
            </div>

            <div class="footer">
                <p class="ephemeris-attribution">
                    Cálculos de efemérides realizados localmente usando astro.xusqui.com api y datos del Catálogo Messier.
                </p>
            </div>
        </div>
    </div>
</div>
