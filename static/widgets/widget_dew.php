<?php
// /static/widgets/widget_dew.php
?>
<!--    ****************************************************+
        ************** WIDGET DE PUNTO DE ROCÍO *************
        ***************************************************** -->
<div class="widget" id="dew_point">
    <div class="title">Punto de Rocío</div>
    <!--Calcular porcentaje de la gota, inicialmente 0-->
    <?php
    $dew = 0;
    $inner_percent = (100 * $dew) / 49;
    ?>
    <dew-point-widget-view data-pws-id="<?= $observatorio ?>" data-status="connected" data-unit="m" data-temp="<?= $temp ?>" data-dew-point="<?= $dew ?>" data-main-value="<?= $dew ?>" aria-valuenow="<?= $dew ?>" class="widget-view loaded" style="--dewpoint-droplet-width: <?= $inner_percent ?>%;">
        <div class="graphic-container">
            <div class="dew-container">
                <div class="droplet"></div>
            </div>
        </div>
        <div class="value-container">
            <div class="main-value value-unit degrees" id="dewpoint-widget-main-display"><?= $dew ?></div>
            <div class="dew-indicators">
                <span id="dew-trend" class="dew-trend">■</span>
                <span id="dew-prob" class="dew-prob">💧 0%</span>
                <span id="fog-prob" class="fog-prob">🌫 0%</span>
            </div>
        </div>
    </dew-point-widget-view>
</div>
