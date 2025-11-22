<?php
//modal_moon.php
?>
<!--*************************************************************
    ************* DATOS DE LA OBSERVACIÓN LUNAR *****************
    *********************** M O D A L ***************************
    ************************************************************* -->
<!-- Modal oculto por defecto -->
<div id="moonModal" class="modal">
    <div class="modal-content">
        <button class="close" id="closeMoonModal" aria-label="Cerrar">
            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none"
                 stroke-width="3" stroke-linecap="round" stroke-linejoin="round">
                <line x1="18" y1="6" x2="6" y2="18" />
                <line x1="6" y1="6" x2="18" y2="18" />
            </svg>
        </button>
        <div class="infografia moon-infografia">
            <h1 class="seeing-modal-title">🌙 Información de la Fase Lunar</h1>
            <h2 class="seeing-group-title">Datos de la Luna</h2>
            <div id="moon-info" class="bloque bloque-fixed-3">
                <div class="card">
                    <h3 class="seeing-card-title">Fase actual</h3>
                    <p class="seeing-card-value"><span id="moon-phase-text">Calculando...</span></p>
                </div>
                <div class="card">
                    <h3 class="seeing-card-title">Terminador visible</h3>
                    <p class="seeing-card-value"><span id="terminator-visible">Calculando...</span></p>
                </div>
                <div class="card">
                    <h3 class="seeing-card-title">Longitud Terminador</h3>
                    <p class="seeing-card-value"><span id="terminator-long">–</span></p>
                </div>
            </div>
            <h2 class="seeing-group-title">Rango de Proximidad al Terminador (Tolerancia)</h2>
            <div class="bloque bloque-fixed-1">
                <div class="card">
                    <h3 class="seeing-card-title">Tolerancia: <span id="tolerance-display">3º</span></h3>
                    <input type="range" min="0" max="180" value="3" step="1" id="tolerance-slider" style="width: 100%;">
                </div>
            </div>
            <h2 class="seeing-group-title">Catálogo Lunar 100 visibles en el terminador</h2>
            <div id="moon-features-list" class="bloque"></div>
            <div class="footer" id="moon-footer"></div>
        </div>
    </div>
</div>
