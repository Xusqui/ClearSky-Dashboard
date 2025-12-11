<?php
// widget_sun.php
?>
<!-- Widget Sol -->
<div class="big-card-sun sun-card" id="big-card-sun">
    <div id="sun-arc-container" class="sun-arc-container">
        <svg id="sun-arc" width="100%" height="100%" viewBox="0 0 100 100">
            <defs>
                <linearGradient id="sunArcGradient" x1="0%" y1="0%" x2="100%" y2="0%">
                    <stop offset="0%" stop-color="#FFd107" />   <!-- amarillo amanecer -->
                    <stop offset="50%" stop-color="#FFaB3B" />  <!-- naranja mediodía -->
                    <stop offset="100%" stop-color="#FF5722" /> <!-- rojizo atardecer -->
                </linearGradient>
                <linearGradient id="sunArcGradientInverted" x1="0%" y1="0%" x2="100%" y2="0%">
                    <stop offset="0%" stop-color="#FF5722" />
                    <stop offset="50%" stop-color="#FFaB3B" />
                    <stop offset="100%" stop-color="#FFd107" />
                </linearGradient>
                <filter id="dropShadow" x="-50%" y="-50%" width="200%" height="200%">
                    <feDropShadow dx="0" dy="0" stdDeviation="2" flood-color="#000" flood-opacity="0.5"/>
                </filter>
                <filter id="softShadow" x="-50%" y="-50%" width="200%" height="200%">
                    <feDropShadow  dy="0.5"
                                  dx="0"
                                  stdDeviation="1.5"
                                  flood-color="black"
                                  flood-opacity="0.25"
                    />
                </filter>
            </defs>
            <path d="M 10 60 A 35 35 0 0 1 90 60"
                  stroke="url(#sunArcGradientInverted)"
                  stroke-width="5"
                  fill="none"
                  stroke-linecap="round"
                  opacity="1"
                  />
            <path d="M 10 60 A 35 35 0 0 1 90 60"
                  stroke="url(#sunArcGradient)"
                  stroke-width="4"
                  fill="none"
                  stroke-linecap="round"
                  opacity="0.9"
                  />
            <text id="solar-noontime" x="50" y="13" text-anchor="middle" font-size="7" fill="gray" font-weight="bold">Cénit</text>
            <image id="sun-icon" href="./static/images/icons/sun.svg" width="30" height="30" x="0" y="0" filter="url(#dropShadow)" opacity="1" visibility="hidden"/>
            <image id="sunrise-icon" href="./static/images/icons/sunrise.svg" width="18" height="18" x="3" y="60" filter="url(#softShadow)"/>
            <text id="sunrise-time" x="11" y="80" text-anchor="middle" font-size="9" fill="gray">sunrise</text>
            <image id="sunset-icon" href="./static/images/icons/sunset.svg" width="18" height="18" x="83" y="60" filter="url(#softShadow)"/>
            <text id="sunset-time" x="89" y="80" text-anchor="middle" font-size="9" fill="gray">sunset</text>
        </svg>
    </div>
</div>
