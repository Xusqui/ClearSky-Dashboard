<?php
// /static/widgets/widget_eclipse.php
?>
<!--    ****************************************************+
        ************* WIDGET DE ECLIPSE SOLAR ****************
        ***************************************************** -->
<div class="widget" id="eclipse_widget">
    <div class="title">Próximo Eclipse Solar</div>
    <eclipse-widget-view data-pws-id="<?= $observatorio ?>" data-status="connected" class="widget-view loaded">
        <div class="graphic-container">
            <div class="eclipse-container">
                <svg width="100%" height="100%" viewBox="0 0 100 100">
                    <defs>
                        <radialGradient id="eclipseSunGradient" cx="50%" cy="50%" r="50%">
                            <stop offset="0%" stop-color="#FFe27a" />
                            <stop offset="70%" stop-color="#FFaB3B" />
                            <stop offset="100%" stop-color="#FF7a1a" />
                        </radialGradient>
                    </defs>
                    <circle id="eclipse-sun" cx="50" cy="50" r="26" fill="url(#eclipseSunGradient)" />
                    <circle id="eclipse-moon" cx="106" cy="50" r="26" />
                </svg>
            </div>
        </div>
        <div class="value-container">
            <div class="main-value" id="eclipse-widget-date">--</div>
            <div class="secondary-value" id="eclipse-widget-kind">Calculando…</div>
            <div class="tertiary-value" id="eclipse-widget-times">-- – --</div>
            <div class="cuaternary-value" id="eclipse-widget-altitude"></div>
        </div>
    </eclipse-widget-view>
</div>
