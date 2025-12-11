<?php
// widget_rain.php
?>
<!--    ****************************************************+
        ************** WIDGET DE PRECIPITACIÓN **************
        ***************************************************** -->
<div class="widget" id="rain-widget">
    <div class="title">Precipitación</div>
    <?php
        $stroke_bucket_top = "transparent";
        $fill_bucket_top = "transparent";
        $fill_bucket_bottom = "var(--widget-empty)";
        $water_start = 440;
        $daily_rain = 0; //Inicialmente 0mm
        $max_rain = 200; //Máxima cantidad de lluvia que se puede registar en un día.
        $h_min = 40;
        $h_max = 440; //Altura máxima en píxeles del pluviómetro
        // Vamos a calcular la altura del pluviómetro
        $heigh = ($daily_rain / $max_rain) * ($h_max - $h_min);
        if ($heigh > 400) {
            $heigh = 400;
        }
        $water_start = $h_max - $heigh;
        $stroke_bucket_top = "var(--wu-lightblue20)";
        $fill_bucket_top = "var(--wu-lightblue)";
        $fill_bucket_bottom = "var(--wu-lightblue20)";
    ?>
    <rain-widget-view id="widget_de_lluvia" data-pws-id="<?= $observatorio ?>" data-status="connected" data-unit="m" data-precip-rate="0" data-precip-total="0" data-main-value="0" aria-valuenow="0" data-secondary-value="0" class="widget-view loaded">
        <div class="graphic-container">
            <div class="precip-container">
                <div class="mini-droplets">
                    <div class="mini-drop"></div>
                    <div class="mini-drop"></div>
                    <div class="mini-drop"></div>
                </div>
                <div class="precip-bucket">
                    <svg version="1.1" id="Layer_1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px" title="precipitation" viewBox="0 0 234 482" height="100%" preserveAspectRatio="xMinYMid">
                        <ellipse fill="none" stroke="var(--widget-empty)" stroke-width="10" stroke-miterlimit="10" cx="117" cy="39" rx="107" ry="30"></ellipse><!--Boca del pluviómetro-->
                        <rect id="precip-bucket-fill" fill="var(--wu-lightblue)" x="12" width="208" y="<?= $water_start ?>" height="<?= $heigh ?>" style="transition: y 0.6s, height 0.6s;"></rect> <!-- Columna de agua del pluviómetro -->
                        <ellipse id="precip-bucket-top" stroke="<?= $stroke_bucket_top ?>" stroke-width="5" fill="<?= $fill_bucket_top ?>" cx="117" cy="<?= $water_start ?>" rx="107" ry="30" style="transition: cy 0.6s;"></ellipse> <!-- Parte superior de la columna de agua -->
                        <ellipse id="precip-bucket-bottom" fill="<?= $fill_bucket_bottom ?>" cx="117" cy="440" rx="107" ry="30"></ellipse> <!-- Parte inferior de la columna de agua / pluviómetro -->
                        <path fill="none" stroke="var(--widget-empty)" stroke-width="10" stroke-miterlimit="10" d="M10,39v394c0,16.6,47.9,40,107,40s107-23.4,107-40V39"></path><!--Cristal exterior del pluviómetro-->
                    </svg>
                </div>
            </div>
        </div>
        <div class="value-container">
            <div class="main-value value-unit precip-total" id="rain-widget-main-display"><?= $daily_rain ?></div>
            <div class="secondary-value value-unit precip-rate uppercase" id="rain-widget-secondary-display"><?= $rain_rate ?></div>
        </div>
    </rain-widget-view>
</div>
