<?php
// widget_wind.php
?>
<!--    ****************************************************+
        ***************** WIDGET DEL VIENTO *****************
        ***************************************************** -->
<div class="widget" id="wind_widget">
    <div class="title">Viento</div>
    <?php
        function windDirection($degrees)
        {
            // Definimos los 16 rumbos de la rosa de los vientos
            $dirs = [
                "N",
                "NNE",
                "NE",
                "ENE",
                "E",
                "ESE",
                "SE",
                "SSE",
                "S",
                "SSO",
                "SO",
                "OSO",
                "O",
                "ONO",
                "NO",
                "NNO",
            ];
            // Cada dirección ocupa 22.5º (360 / 16)
            $index = round($degrees / 22.5) % 16;
            return $dirs[$index];
        }
        $wind_dir = 180; // Direccón inicial del viento: 180º
        $wind_direction = windDirection($wind_dir);
    ?>
    <wind-widget-view id="wind-widget-view" data-pws-id="<?= $observatorio ?>" data-status="connected" data-unit="m" data-wind-speed="<?= $wind ?>" data-wind-gust="<?= $gust ?>" data-wind-dir="<?= $wind_dir ?>" data-description="gentle" data-main-value="<?= $wind ?>" aria-valuenow="<?= $wind ?>" data-secondary-value="<?= $gust ?>" class="widget-view loaded show-wind">
        <div class="graphic-container">
            <div class="wind-compass">
                <div class="wind-arrow-pointer-wrapper" id="wind-arrow-pointer-wrapper" style="transform: rotate(<?= $wind_dir ?>deg);">
                    <div class="wind-arrow-pointer"></div>
                </div>
                <div class="wind-lines" id="wind-widget-lines" style="transform: rotate(<?= $wind_dir ?>deg);">
                    <div class="wind-line"></div>
                    <div class="wind-line"></div>
                    <div class="wind-line"></div>
                    <div class="wind-line"></div>
                    <div class="wind-line"></div>
                    <div class="wind-line"></div>
                    <div class="wind-line"></div>
                    <div class="wind-line"></div>
                    <div class="wind-line"></div>
                    <div class="wind-line"></div>
                </div>
            </div>
        </div>
        <div class="value-container">
            <div class="main-value value-unit speed" id="wind-widget-main-display"><?= $wind ?></div>
            <div class="secondary-value">
                <span class="uppercase">Rachas</span> <span id="wind-widget-secondary-display" class="secondary-value value-unit speed uppercase"><?= $gust ?></span>
            </div>
            <div class="tertiary-value" id="wind-widget-tertiary-value"><?= $wind_dir ?>° <?= $wind_direction ?></div>
            <div class="tertiary-value value-unit speed" id="wind-widget-cuaternary-value">Máx: <?= $gust_max ?> </div>
        </div>
    </wind-widget-view>
</div>
