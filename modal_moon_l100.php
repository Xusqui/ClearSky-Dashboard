<?php
// modal_moon_l100.php
?>
<!--*************************************************************
***** LOCALIZAR ACCIDENTE GEOGRÁFICO EN LA SUPERFICIE LUNAR *****
************************* M O D A L *****************************
****************************************************************-->
<!-- Modal Oculto por defecto -->
<div id="moonFeatureModal" class="modal">
    <div class="modal-in-modal-content">
        <button class="close" id="closeMoonFeatureModal" aria-label="Cerrar">
            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none"
                 stroke-width="3" stroke-linecap="round" stroke-linejoin="round">
                <line x1="18" y1="6" x2="6" y2="18" />
                <line x1="6" y1="6" x2="18" y2="18" />
            </svg>
        </button>
        <canvas id="moonFeatureCanvas" width="400" height="400" style="display:block;margin:2rem auto;border-radius:50%;background: var(--wu-darkblue80);"></canvas>
        <p id="moonFeatureInfo" style="text-align:center;margin-top:0.5rem;font-size:0.9rem;color: var(--font-secondary-color);"></p>
        <p id="moonFeatureDescription" style="text-align:center;margin-top:0.5rem;font-size:0.9rem;color: var(--font-secondary-color);"></p>
    </div>
</div>
