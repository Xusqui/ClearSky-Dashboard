<?php
// modal_info.php
?>
<!--*************************************************************
    ************ DATOS DE LA ESTACIÓN METEOROLÓGICA *************
    *********************** M O D A L ***************************
    ************************************************************* -->
<!-- Modal oculto por defecto -->
<div id="pws-info-dialog" class="modal"
     data-lat="<?= $lat ?>"
     data-lon="<?= $lon ?>">
    <div class="modal-content">
        <button class="close" id="pws-info-dialog-close" aria-label="Cerrar">
            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none"
                 stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <line x1="18" y1="6" x2="6" y2="18"></line>
                <line x1="6" y1="6" x2="18" y2="18"></line>
            </svg>
        </button>
        <h2 class="pws-info-title">Estación Meteorológica / Observatorio: <?= $observatorio ?></h2>
        <div class="pws-info-body">
            <div class="pws-info-map-wrapper">
                <div id="pws-map-container">
                </div>
            </div>
            <div class="pws-info-details-wrapper">
                <h3>📍 Ubicación</h3>
                <div class="pws-info-card-grid">
                    <div class="pws-info-card">
                        <h4>Latitud</h4>
                        <p><?= $latitud ?></p>
                    </div>
                    <div class="pws-info-card">
                        <h4>Longitud</h4>
                        <p><?= $longitud ?></p>
                    </div>
                    <div class="pws-info-card">
                        <h4>Elevación</h4>
                        <p><?= $elev ?> m</p>
                    </div>
                    <div class="pws-info-card">
                        <h4>Ciudad / País</h4>
                        <p><?= $city ?>, <?= $country ?></p>
                    </div>
                </div>
                <h3>💻 Equipo</h3>
                <div class="pws-info-card-stack">
                    <div class="pws-info-card">
                        <h4>Hardware</h4>
                        <p><?= $hardware ?></p>
                    </div>
                    <div class="pws-info-card">
                        <h4>Software</h4>
                        <p><?= $software ?></p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
