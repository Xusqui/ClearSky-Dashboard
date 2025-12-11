<?php
// widget_temp_int.php
?>
<!--    ****************************************************+
        ********** WIDGET DE TEMPERATURA INTERIOR ***********
        ***************************************************** -->
<div class="widget" id="tempint_widget">
    <?php
        // Calcular la posición de la aguja de la temperatura interior
        // Usamos los mismos valores de máximos y mínimos que para calcular la posición de la aguja de la temperatura exterior.
        $in_temp = 0; // Inicialmente 0º
        $in_temp_angle =
            (($in_temp - $minTemp) * ($maxAngle - $minAngle)) / ($maxTemp - $minTemp) +
            $minAngle;
        // Limitamos a los extremos
        if ($temp_angle < $minAngle) {
            $temp_angle = $minAngle;
        }
        if ($temp_angle > $maxAngle) {
            $temp_angle = $maxAngle;
        }
    ?>
    <div class="title">Temperatura Interior</div>
    <temp-widget-view data-pws-id="<?= $observatorio ?>" data-status="connected" data-unit="m" data-temp="<?= $in_temp ?>" data-temp-angle="<?= $in_temp_angle ?>" data-main-value="<?= $in_temp ?>" aria-valuenow="<?= $in_temp ?>" class="widget-view loaded">
        <div class="graphic-container">
            <div class="temp-gauge-container">
                <div class="temp-gauge-bg"></div>
                <div class="temp-gauge-inner"></div>
                <div class="temp-needle" id="temp-int-widget-needle" style="transform: translate(-50%, -100%) rotate(<?= $in_temp_angle ?>deg);"></div>
            </div>
        </div>
        <div class="value-container">
            <div class="main-value value-unit degrees" id="temp-int-widget-main-display"><?= $in_temp ?></div>
        </div>
    </temp-widget-view>
</div>
