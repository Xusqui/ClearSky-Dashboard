<?php
// widget_seeing.php
?>
<!--    ****************************************************+
        **************** WIDGET DE SEEING ******************
        ***************************************************** -->
<div class="widget" id="seeing">
    <div class="title">Seeing</div>
    <seeing-widget-view id="seeing-widget-view" data-pws-id="<?= $observatorio ?>" data-status="connected" data-unit="" class="widget-view loaded show-wind">
        <div class="graphic-container">
            <div class="svg-container">
                <svg viewBox="0 0 1190 1706" xmlns="http://www.w3.org/2000/svg" style="width: 100%; height: auto;">
                    <g id="stars"></g>
                    <?php // Incluye la imagen del telescopio.
                    include "./static/images/telescope.svg"; ?>
                </svg>
            </div>
        </div>
        <div class="value-container">
            <div class="tertiary-value" id="seeing-description">Vis:</div>
        </div>
    </seeing-widget-view>
</div>
