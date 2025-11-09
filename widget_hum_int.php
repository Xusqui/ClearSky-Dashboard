<?php
// widget_hum_int.php
?>
<!--    ****************************************************+
        ************ WIDGET DE HUMEDAD INTERIOR *************
        ***************************************************** -->
<div class="widget" id="humint_widget">
    <div class="title">Humedad Interior</div>
    <?php
        // Calcular el ángulo del sector rellenado
        $in_humidity = 0; //Inicialmente 0%
        $in_angle_humidity = 360 * ($in_humidity / 100);
        // Determinar estado (colores) en base a la humedad
        if ($in_humidity < 35) {
            $in_humid_state = "dry";
            $in_humid_legend = "Seco";
        } elseif ($in_humidity >= 60) {
            $in_humid_state = "humid";
            $in_humid_legend = "Húmedo";
        } else {
            $in_humid_state = "comfortable";
            $in_humid_legend = "Confortable";
        }
        // Variables dependientes del estado
        $in_humidity_color = "--humidity-{$in_humid_state}-color";
        $in_humid_widget = $in_humid_state;
        $in_humid_others = "widget-view {$in_humid_state} loaded";
    ?>
    <humidity-int-widget-view data-pws-id="<?= $observatorio ?>" data-status="connected" data-humidity="<?= $in_humidity ?>" data-humidity-string="<?= $in_humid_widget ?>" data-main-value="<?= $in_humidity ?>" aria-valuenow="<?= $in_humidity ?>" data-secondary-value="<?= $in_humid_widget ?>" class="<?= $in_humid_others ?>">
        <div class="graphic-container">
            <div class="humidity-int-gauge-container">
                <div class="humidity-int-gauge-bg" id="humidity-int-gauge-bg" style="--humidity-int-gauge-bg: conic-gradient(from 270deg, rgba(var(<?= $in_humidity_color ?>), 0.8) 0deg, rgba(var(<?= $in_humidity_color ?>), 0.8) <?= $in_angle_humidity ?>deg, rgba(var(--black-or-white), 0.1) <?= $in_angle_humidity ?>deg, rgba(var(--black-or-white), 0.1) 360deg);"></div>
                <div class="humidity-int-gauge-inner"></div>
                <div class="humidity-int-mist-ring mist-ring-1"></div>
                <div class="humidity-int-mist-ring mist-ring-2"></div>
                <div class="humidity-int-mist-ring mist-ring-3"></div>
                <div class="humidity-int-vapor-particles">
                    <div class="humidity-int-vapor-particle"></div>
                    <div class="humidity-int-vapor-particle"></div>
                    <div class="humidity-int-vapor-particle"></div>
                    <div class="humidity-int-vapor-particle"></div>
                    <div class="humidity-int-vapor-particle"></div>
                    <div class="humidity-int-vapor-particle"></div>
                </div>
            </div>
        </div>
        <div class="value-container">
            <div class="main-value value-unit percent" id="humidity-int-widget-main-display"><?= $in_humidity ?></div>
            <div class="secondary-value uppercase" id="humidity-int-widget-text-display"><?= $in_humid_legend ?></div>
        </div>
    </humidity-int-widget-view>
</div>
