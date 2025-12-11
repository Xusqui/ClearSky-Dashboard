<?php
// widget_hum_ext.php
?>
<!--    ****************************************************+
        ************* WIDGET DE HUMEDAD EXTERIOR ************
        ***************************************************** -->
<div class="widget" id="hum_widget">
    <div class="title">Humedad Exterior</div>
    <humidity-widget-view data-pws-id="<?= $observatorio ?>" data-status="connected" data-humidity="<?= $humidity ?>" data-humidity-string="<?= $humid_widget ?>" data-main-value="<?= $humidity ?>" aria-valuenow="<?= $humidity ?>" data-secondary-value="<?= $humid_widget ?>" class="<?= $humid_others ?>">
        <div class="graphic-container">
            <div class="humidity-gauge-container">
                <div class="humidity-gauge-bg" id="humidity-gauge-bg" style="--humidity-gauge-bg: conic-gradient(from 270deg, rgba(var(<?= $humidity_color ?>), 0.8) 0deg, rgba(var(<?= $humidity_color ?>), 0.8) <?= $angle_humidity ?>deg, rgba(var(--black-or-white), 0.1) <?= $angle_humidity ?>deg, rgba(var(--black-or-white), 0.1) 360deg);">
                </div>
                <div class="humidity-gauge-inner"></div>
                <div class="humidity-mist-ring mist-ring-1"></div>
                <div class="humidity-mist-ring mist-ring-2"></div>
                <div class="humidity-mist-ring mist-ring-3"></div>
                <div class="humidity-vapor-particles">
                    <div class="humidity-vapor-particle"></div>
                    <div class="humidity-vapor-particle"></div>
                    <div class="humidity-vapor-particle"></div>
                    <div class="humidity-vapor-particle"></div>
                    <div class="humidity-vapor-particle"></div>
                    <div class="humidity-vapor-particle"></div>
                </div>
            </div>
        </div>
        <div class="value-container">
            <div class="main-value value-unit percent" id="humidity-widget-main-display"><?= $humidity ?></div>
            <div class="secondary-value uppercase" id="humidity-widget-text-display"><?= $humid_legend ?></div>
            <div class="secondary-value uppercase" id="humidity-widget-humidex"><?= $humidex ?></div>
        </div>
    </humidity-widget-view>
</div>
