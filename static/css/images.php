<?php
/* images.php */
header("Content-Type: text/css");

// Ruta absoluta del directorio donde está este script
$scriptDir = __DIR__; // p.ej. /var/www/html/weather/static/css

// Raíz del servidor web
$docRoot = realpath($_SERVER['DOCUMENT_ROOT']); // p.ej. /var/www/html

// Obtenemos la ruta relativa a la raíz del servidor
$root = str_replace('\\', '/', str_replace($docRoot, '', realpath($scriptDir.'/../..')));

// Aseguramos barra inicial y sin barra final
$root = '/' . trim($root, '/');
?>
:root {
    --icon-settings: url('<?= $root ?>/static/images/icons/settings.svg');
    --icon-city: url('<?= $root ?>/static/images/icons/location_city.svg');
    --icon-github: url('<?= $root ?>/static/images/icons/github.svg');
    --icon-rain-drop: url('<?= $root ?>/static/images/icons/rain-drop.svg');
    --icon-rain-drop-empty: url('<?= $root ?>/static/images/icons/rain-drop-empty.svg');
    --icon-pws: url('<?= $root ?>/static/images/icons/pws.svg');
    --icon-time: url('<?= $root ?>/static/images/icons/time.svg');
    --icon-arrow-back: url('<?= $root ?>/static/images/icons/arrow-back.svg');
    --icon-arrow-forward: url('<?= $root ?>/static/images/icons/arrow-forward.svg');
    --icon-arrow-dropdown-blue: url('<?= $root ?>/static/images/icons/arrow_drop_down-blue.svg');
    --icon-info: url('<?= $root ?>/static/images/icons/info.svg');
    --icon-plus: url('<?= $root ?>/static/images/icons/plus.svg');
    --icon-minus: url('<?= $root ?>/static/images/icons/minus.svg');
    --icon-arrow-right-blue: url('<?= $root ?>/./static/images/icons/arrow-right-blue.svg');
    --icon-thunderstorms: url('<?= $root ?>/static/images/icons/thunderstorms.svg');
    --icon-rain: url('<?= $root ?>/static/images/icons/rain.svg');
    --icon-snow: url('<?= $root ?>/static/images/icons/snow.svg');
    --icon-foggy: url('<?= $root ?>/static/images/icons/foggy.svg');
    --icon-breezy: url('<?= $root ?>/static/images/icons/breezy.svg');
    --icon-cloudy: url('<?= $root ?>/static/images/icons/cloudy.svg');
    --icon-mostly-cloudy-night: url('<?= $root ?>/static/images/icons/mostly-cloudy-night.svg');
    --icon-mostly-cloudy-day: url('<?= $root ?>/static/images/icons/mostly-cloudy-day.svg');
    --icon-partly-cloudy-night: url('<?= $root ?>/static/images/icons/partly-cloudy-night.svg');
    --icon-partly-cloudy-day: url('<?= $root ?>/static/images/icons/partly-cloudy-day.svg');
    --icon-clear-night: url('<?= $root ?>/static/images/icons/clear-night.svg');
    --icon-sunny-day: url('<?= $root ?>/static/images/icons/sunny-day.svg');
    --icon-fair-mostly-clear-night: url('<?= $root ?>/static/images/icons/fair-mostly-clear-night.svg');
    --icon-fair-mostly-sunny-day: url('<?= $root ?>/static/images/icons/fair-mostly-sunny-day.svg');
    --icon-heavy-rain: url('<?= $root ?>/static/images/icons/heavy-rain.svg');
    --icon-not-available: url('<?= $root ?>/static/images/icons/not-available.svg');
    --icon-thermometer: url('<?= $root ?>/static/images/icons/thermometer.svg');
    --icon-chevron-right: url('<?= $root ?>/static/images/icons/chevron-right.svg');
    --icon-sunrise: url('<?= $root ?>/static/images/icons/sunrise.svg');
    --icon-sunset: url('<?= $root ?>/static/images/icons/sunset.svg');
    --mask-gauge: url('<?= $root ?>/static/images/masks/gauge.svg');
    --mask-thermometer: url('<?= $root ?>/static/images/masks/thermometer.svg');
    --image-wind-bg: url('<?= $root ?>/static/images/widgets/wind-bg.svg');
    --image-wind-arrow-tip: url('<?= $root ?>/static/images/widgets/wind-arrow-tip.svg');
    --image-dew-point-bg: url('<?= $root ?>/static/images/widgets/dewpoint-bg.svg');
    --image-dew-point-empty-bg: url('<?= $root ?>/static/images/widgets/dewpoint-empty-bg.svg');
    --image-temp-arrow-tip: url('<?= $root ?>/static/images/widgets/temp-arrow-tip.svg');
    --image-pressure-bg: url('<?= $root ?>/static/images/widgets/pressure-bg.svg');
    --icon-weather-underground-logo: url('<?= $root ?>/static/images/icons/weather-underground-logo.svg');
    --icon-copyright: url('<?= $root ?>/static/images/icons/copyright-light.svg');
    --image-background-moon-card: url('<?= $root ?>/static/images/backgrounds/stars1.png');
    --icon-espacioprofundo: url('<?= $root ?>/static/images/icons/espacioprofundo-day.png');
}

@media (prefers-color-scheme: dark) {
    :root {
        --icon-settings: url('<?= $root ?>/static/images/icons/settings-dark.svg');
        --icon-city: url('<?= $root ?>/static/images/icons/city-dark.svg');
        --icon-github: url('<?= $root ?>/static/images/icons/github-dark.svg');
        --icon-clear-night: url('<?= $root ?>/static/images/icons/clear-night.svg');
        --icon-breezy: url('<?= $root ?>/static/images/icons/breezy.svg');
        --icon-cloudy: url('<?= $root ?>/static/images/icons/cloudy.svg');
        --icon-sunrise: url('<?= $root ?>/static/images/icons/sunrise.svg');
        --icon-pws: url('<?= $root ?>/static/images/icons/pws-dark.svg');
        --icon-time: url('<?= $root ?>/static/images/icons/time-dark.svg');
        --icon-arrow-back: url('<?= $root ?>/static/images/icons/arrow-back-dark.svg');
        --icon-arrow-forward: url('<?= $root ?>/static/images/icons/arrow-forward-dark.svg');
        --icon-info: url('<?= $root ?>/static/images/icons/info-dark.svg');
        --icon-thermometer: url('<?= $root ?>/static/images/icons/thermometer-dark.svg');
        --icon-chevron-right: url('<?= $root ?>/static/images/icons/chevron-right-dark.svg');
        --icon-plus: url('<?= $root ?>/static/images/icons/plus-dark.svg');
        --icon-minus: url('<?= $root ?>/static/images/icons/minus-dark.svg');
        --image-wind-bg: url('<?= $root ?>/static/images/widgets/wind-bg-dark.svg');
        --image-wind-arrow-tip: url('<?= $root ?>/static/images/widgets/wind-arrow-tip-dark.svg');
        --icon-copyright: url('<?= $root ?>/static/images/icons/copyright-dark.svg');
        --image-pressure-bg: url('<?= $root ?>/static/images/widgets/pressure-bg-dark.svg');
        --icon-espacioprofundo: url('<?= $root ?>/static/images/icons/espacioprofundo-night.png');
    }
    /* 🔆 Ajuste visual de iconos meteorológicos en modo oscuro */
    .forecast-icon {
        filter: brightness(0.8) invert(0.3) contrast(1.5);
    }
}

.icon.thunderstorms {
    background-repeat: no-repeat;
    background-image:var(--icon-thunderstorms)
}

.icon.rain {
    background-repeat: no-repeat;
    background-image:var(--icon-rain)
}

.icon.snow {
    background-repeat: no-repeat;
    background-image:var(--icon-snow)
}

.icon.foggy {
    background-repeat: no-repeat;
    background-image:var(--icon-foggy)
}

.icon.breezy {
    background-repeat: no-repeat;
    background-image:var(--icon-breezy)
}

.icon.cloudy {
    background-repeat: no-repeat;
    background-image:var(--icon-cloudy)
}

.icon.mostly-cloudy-night {
    background-repeat: no-repeat;
    background-image:var(--icon-mostly-cloudy-night)
}

.icon.mostly-cloudy-day {
    background-repeat: no-repeat;
    background-image:var(--icon-mostly-cloudy-day)
}

.icon.partly-cloudy-night {
    background-repeat: no-repeat;
    background-image:var(--icon-partly-cloudy-night)
}

.icon.partly-cloudy-day {
    background-repeat: no-repeat;
    background-image:var(--icon-partly-cloudy-day)
}

.icon.clear-night {
    background-repeat: no-repeat;
    background-image:var(--icon-clear-night)
}

.icon.sunny-day {
    background-repeat: no-repeat;
    background-image:var(--icon-sunny-day)
}

.icon.fair-mostly-clear-night {
    background-repeat: no-repeat;
    background-image:var(--icon-fair-mostly-clear-night)
}

.icon.fair-mostly-sunny-day {
    background-repeat: no-repeat;
    background-image:var(--icon-fair-mostly-sunny-day)
}

.icon.heavy-rain {
    background-repeat: no-repeat;
    background-image:var(--icon-heavy-rain)
}

.icon.not-available {
    background-repeat: no-repeat;
    background-image:var(--icon-not-available)
}

.icon.rain-drop {
    background-repeat: no-repeat;
    background-image:var(--icon-rain-drop)
}

.icon.rain-drop-empty {
    background-repeat: no-repeat;
    background-image:var(--icon-rain-drop-empty)
}

.icon.arrow.back {
    background-repeat: no-repeat;
    background-image:var(--icon-arrow-back)
}

.icon.arrow.forward {
    background-repeat: no-repeat;
    background-image:var(--icon-arrow-forward)
}

.icon.arrow-dropdown-blue {
    background-repeat: no-repeat;
    background-image:var(--icon-arrow-dropdown-blue)
}

.icon.plus {
    background-repeat: no-repeat;
    background-image:var(--icon-plus)
}

.icon.minus {
    background-repeat: no-repeat;
    background-image:var(--icon-minus)
}

.icon.sunrise {
    background-repeat: no-repeat;
    background-image:var(--icon-sunrise)
}

.icon.sunset {
    background-repeat: no-repeat;
    background-image:var(--icon-sunset)
}
.icon.github {
    background-repeat: no-repeat;
    background-image:var(--icon-github)
}
.icon.city {
    background-repeat: no-repeat;
    background-image:var(--icon-city)
}
.icon.espacioprofundo {
    background-repeat: no-repeat;
    background-image:var(--icon-espacioprofundo)
}
