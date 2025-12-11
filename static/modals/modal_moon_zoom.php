<?php
// modal_moon_zoom.php
?>
<!--*************************************************************
************ MODAL ZOOM LUNAR A TAMAÑO COMPLETO *****************
****************************************************************-->
<!-- Modal oculto al inicio -->
<div id="moonZoomModal" class="moon-modal">
    <div class="moon-modal-content">
        <button class="moon-modal-close" id="closeMoonZoomModal" aria-label="Cerrar">
            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none"
                 stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round">
                <line x1="18" y1="6" x2="6" y2="18" />
                <line x1="6" y1="6" x2="18" y2="18" />
            </svg>

        </button>
        <div id="zoomViewer" class="zoom-viewer">
            <img id="zoomMoonImage" src="./static/images/hires/lunar-100-2.jpg" alt="Luna" />
            <div id="zoomMarker"></div>
        </div>
        <div id="zoomDescription" class="zoom-description"></div>
    </div>
</div>
