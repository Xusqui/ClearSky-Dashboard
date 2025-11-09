<?php
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
    --icon-tornado: url('<?= $root ?>/static/images/icons/tornado.svg');
    --icon-tropical-storm: url('<?= $root ?>/static/images/icons/tropical-storm.svg');
    --icon-hurricane: url('<?= $root ?>/static/images/icons/hurricane.svg');
    --icon-strong-storms: url('<?= $root ?>/static/images/icons/strong-storms.svg');
    --icon-thunderstorms: url('<?= $root ?>/static/images/icons/thunderstorms.svg');
    --icon-rain-snow: url('<?= $root ?>/static/images/icons/rain-snow.svg');
    --icon-rain-sleet: url('<?= $root ?>/static/images/icons/rain-sleet.svg');
    --icon-wintry-mix: url('<?= $root ?>/static/images/icons/wintry-mix.svg');
    --icon-freezing-drizzle: url('<?= $root ?>/static/images/icons/freezing-drizzle.svg');
    --icon-drizzle: url('<?= $root ?>/static/images/icons/drizzle.svg');
    --icon-freezing-rain: url('<?= $root ?>/static/images/icons/freezing-rain.svg');
    --icon-showers: url('<?= $root ?>/static/images/icons/showers.svg');
    --icon-rain: url('<?= $root ?>/static/images/icons/rain.svg');
    --icon-flurries: url('<?= $root ?>/static/images/icons/flurries.svg');
    --icon-snow-showers: url('<?= $root ?>/static/images/icons/snow-showers.svg');
    --icon-blowing-drifting-snow: url('<?= $root ?>/static/images/icons/blowing-drifting-snow.svg');
    --icon-snow: url('<?= $root ?>/static/images/icons/snow.svg');
    --icon-hail: url('<?= $root ?>/static/images/icons/hail.svg');
    --icon-sleet: url('<?= $root ?>/static/images/icons/sleet.svg');
    --icon-blowing-dust-sandstorm: url('<?= $root ?>/static/images/icons/blowing-dust-sandstorm.svg');
    --icon-foggy: url('<?= $root ?>/static/images/icons/foggy.svg');
    --icon-haze: url('<?= $root ?>/static/images/icons/haze.svg');
    --icon-smoke: url('<?= $root ?>/static/images/icons/smoke.svg');
    --icon-breezy: url('<?= $root ?>/static/images/icons/breezy.svg');
    --icon-windy: url('<?= $root ?>/static/images/icons/windy.svg');
    --icon-frigid-ice-crystals: url('<?= $root ?>/static/images/icons/frigid-ice-crystals.svg');
    --icon-cloudy: url('<?= $root ?>/static/images/icons/cloudy.svg');
    --icon-mostly-cloudy-night: url('<?= $root ?>/static/images/icons/mostly-cloudy-night.svg');
    --icon-mostly-cloudy-day: url('<?= $root ?>/static/images/icons/mostly-cloudy-day.svg');
    --icon-partly-cloudy-night: url('<?= $root ?>/static/images/icons/partly-cloudy-night.svg');
    --icon-partly-cloudy-day: url('<?= $root ?>/static/images/icons/partly-cloudy-day.svg');
    --icon-clear-night: url('<?= $root ?>/static/images/icons/clear-night.svg');
    --icon-sunny-day: url('<?= $root ?>/static/images/icons/sunny-day.svg');
    --icon-fair-mostly-clear-night: url('<?= $root ?>/static/images/icons/fair-mostly-clear-night.svg');
    --icon-fair-mostly-sunny-day: url('<?= $root ?>/static/images/icons/fair-mostly-sunny-day.svg');
    --icon-mixed-rain-and-hail: url('<?= $root ?>/static/images/icons/mixed-rain-and-hail.svg');
    --icon-hot-day: url('<?= $root ?>/static/images/icons/hot-day.svg');
    --icon-isolated-thunderstorms-day: url('<?= $root ?>/static/images/icons/isolated-thunderstorms-day.svg');
    --icon-scattered-thunderstorms-day: url('<?= $root ?>/static/images/icons/scattered-thunderstorms-day.svg');
    --icon-scattered-showers-day: url('<?= $root ?>/static/images/icons/scattered-showers-day.svg');
    --icon-heavy-rain: url('<?= $root ?>/static/images/icons/heavy-rain.svg');
    --icon-scattered-snow-showers-day: url('<?= $root ?>/static/images/icons/scattered-snow-showers-day.svg');
    --icon-heavy-snow: url('<?= $root ?>/static/images/icons/heavy-snow.svg');
    --icon-blizzard: url('<?= $root ?>/static/images/icons/blizzard.svg');
    --icon-not-available: url('<?= $root ?>/static/images/icons/not-available.svg');
    --icon-scattered-showers-night: url('<?= $root ?>/static/images/icons/scattered-showers-night.svg');
    --icon-scattered-snow-showers-night: url('<?= $root ?>/static/images/icons/scattered-snow-showers-night.svg');
    --icon-scattered-thunderstorms-night: url('<?= $root ?>/static/images/icons/scattered-thunderstorms-night.svg');
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
    --icon-wu-logo: url('<?= $root ?>/static/images/icons/wu-logo.svg');
    --icon-copyright: url('<?= $root ?>/static/images/icons/copyright-light.svg');
    --image-background-moon-card: url('<?= $root ?>/static/images/backgrounds/stars1.png');
}

@media (prefers-color-scheme: dark) {
    :root {
        --icon-settings: url('<?= $root ?>/static/images/icons/settings-dark.svg');
        --icon-city: url('<?= $root ?>/ther/static/images/icons/city-dark.svg');
        --icon-github: url('<?= $root ?>/static/images/icons/github-dark.svg');
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
    }
    /* 🔆 Ajuste visual de iconos meteorológicos en modo oscuro */
    .forecast-icon {
        filter: brightness(0.8) invert(0.3) contrast(1.5);
    }
}

.icon.tornado {
    background-repeat: no-repeat;
    background-image:var(--icon-tornado)
}

.icon.tropical-storm {
    background-repeat: no-repeat;
    background-image:var(--icon-tropical-storm)
}

.icon.hurricane {
    background-repeat: no-repeat;
    background-image:var(--icon-hurricane)
}

.icon.strong-storms {
    background-repeat: no-repeat;
    background-image:var(--icon-strong-storms)
}

.icon.thunderstorms {
    background-repeat: no-repeat;
    background-image:var(--icon-thunderstorms)
}

.icon.rain-snow {
    background-repeat: no-repeat;
    background-image:var(--icon-rain-snow)
}

.icon.rain-sleet {
    background-repeat: no-repeat;
    background-image:var(--icon-rain-sleet)
}

.icon.wintry-mix {
    background-repeat: no-repeat;
    background-image:var(--icon-wintry-mix)
}

.icon.freezing-drizzle {
    background-repeat: no-repeat;
    background-image:var(--icon-freezing-drizzle)
}

.icon.drizzle {
    background-repeat: no-repeat;
    background-image:var(--icon-drizzle)
}

.icon.freezing-rain {
    background-repeat: no-repeat;
    background-image:var(--icon-freezing-rain)
}

.icon.showers {
    background-repeat: no-repeat;
    background-image:var(--icon-showers)
}

.icon.rain {
    background-repeat: no-repeat;
    background-image:var(--icon-rain)
}

.icon.flurries {
    background-repeat: no-repeat;
    background-image:var(--icon-flurries)
}

.icon.snow-showers {
    background-repeat: no-repeat;
    background-image:var(--icon-snow-showers)
}

.icon.blowing-drifting-snow {
    background-repeat: no-repeat;
    background-image:var(--icon-blowing-drifting-snow)
}

.icon.snow {
    background-repeat: no-repeat;
    background-image:var(--icon-snow)
}

.icon.hail {
    background-repeat: no-repeat;
    background-image:var(--icon-hail)
}

.icon.sleet {
    background-repeat: no-repeat;
    background-image:var(--icon-sleet)
}

.icon.blowing-dust-sandstorm {
    background-repeat: no-repeat;
    background-image:var(--icon-blowing-dust-sandstorm)
}

.icon.foggy {
    background-repeat: no-repeat;
    background-image:var(--icon-foggy)
}

.icon.haze {
    background-repeat: no-repeat;
    background-image:var(--icon-haze)
}

.icon.smoke {
    background-repeat: no-repeat;
    background-image:var(--icon-smoke)
}

.icon.breezy {
    background-repeat: no-repeat;
    background-image:var(--icon-breezy)
}

.icon.windy {
    background-repeat: no-repeat;
    background-image:var(--icon-windy)
}

.icon.frigid-ice-crystals {
    background-repeat: no-repeat;
    background-image:var(--icon-frigid-ice-crystals)
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

.icon.mixed-rain-and-hail {
    background-repeat: no-repeat;
    background-image:var(--icon-mixed-rain-and-hail)
}

.icon.hot-day {
    background-repeat: no-repeat;
    background-image:var(--icon-hot-day)
}

.icon.isolated-thunderstorms-day {
    background-repeat: no-repeat;
    background-image:var(--icon-isolated-thunderstorms-day)
}

.icon.scattered-thunderstorms-day {
    background-repeat: no-repeat;
    background-image:var(--icon-scattered-thunderstorms-day)
}

.icon.scattered-showers-day {
    background-repeat: no-repeat;
    background-image:var(--icon-scattered-showers-day)
}

.icon.heavy-rain {
    background-repeat: no-repeat;
    background-image:var(--icon-heavy-rain)
}

.icon.scattered-snow-showers-day {
    background-repeat: no-repeat;
    background-image:var(--icon-scattered-snow-showers-day)
}

.icon.heavy-snow {
    background-repeat: no-repeat;
    background-image:var(--icon-heavy-snow)
}

.icon.blizzard {
    background-repeat: no-repeat;
    background-image:var(--icon-blizzard)
}

.icon.not-available {
    background-repeat: no-repeat;
    background-image:var(--icon-not-available)
}

.icon.scattered-showers-night {
    background-repeat: no-repeat;
    background-image:var(--icon-scattered-showers-night)
}

.icon.scattered-snow-showers-night {
    background-repeat: no-repeat;
    background-image:var(--icon-scattered-snow-showers-night)
}

.icon.scattered-thunderstorms-night {
    background-repeat: no-repeat;
    background-image:var(--icon-scattered-thunderstorms-night)
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
    background-image: var(--icon-sunset)
}
.icon.github {
    background-repeat: no-repeat;
    background-image:var(--icon-github)
}
