<?php
// widget_press.php
?>
<!--    ****************************************************+
        ***************** WIDGET DE PRESIÓN *****************
        ***************************************************** -->
<div class="widget" id="pressure_widget">
    <div class="title">Presión Relativa</div>
    <pressure-widget-view data-pws-id="<?= $observatorio ?>" data-status="connected" data-unit="m" data-pressure="<?= $pressure ?>" data-pressure-angle="<?= $pres_angle ?>" data-main-value="<?= $pressure ?>" aria-valuenow="<?= $pressure ?>" class="widget-view loaded">
        <div class="graphic-container">
            <div class="pressure-container">
                <div class="pressure-needle" id="pressure-widget-needle" style="transform: translate(-50%, -100%) rotate(0deg);"></div>
            </div>
        </div>
        <div class="value-container">
            <div class="main-value value-unit pressure" id="pressure-widget-main-display"><?= $pressure ?></div>
        </div>
    </pressure-widget-view>
</div>
