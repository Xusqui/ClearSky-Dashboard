/* theme-switcher.css */
/* ============================
Botones de cambio de tema
============================ */
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
.theme-buttons {
    display: flex;
    align-items: center;
    gap: 8px;
    margin-left: auto;
}

.theme-buttons button {
    background: var(--theme-toggle-bg, transparent);
    border: 1px solid var(--theme-toggle-border, rgba(0,0,0,0.1));
    border-radius: 50%;
    padding: 6px;
    cursor: pointer;
    transition: background-color 0.3s, transform 0.2s;
    width: 32px;
    height: 32px;
    display: flex;
    justify-content: center;
    align-items: center;
}

.theme-buttons button:hover {
    transform: scale(1.1);
}

.theme-buttons button.active {
    background: var(--theme-toggle-active-bg, rgba(0,0,0,0.1));
    color: var(--theme-toggle-active-color, var(--font-color));
}

.theme-buttons img {
    width: 18px;
    height: 18px;
    display: block;
}


/* ======================================================
THEME OVERRIDES: Día 🌞 / Noche 🌙 / Auto 🌓
====================================================== */

/* AUTO (según sistema) → simplemente no aplica nada */
:root[data-theme="auto"] {
    color-scheme: light dark;
}

/* ======================================================
MODO DÍA
====================================================== */
:root[data-theme="light"] {
    color-scheme: light;
    --bg-color: rgb(255, 255, 255);
    --color-font: 30, 32, 35;
    --font-color: rgb(30, 32, 35);
    --color-secondary-font: rgb(105, 105, 105);
    --link-color: rgb(0, 111, 158);
    --black-or-white: 0, 0, 0;
    --wu-white: rgb(255, 255, 255);
    --wu-white-raw: 255, 255, 255;
    --wu-white20: rgb(243, 215, 214);
    --wu-white40: rgb(231, 174, 173);
    --wu-white60: rgb(220, 134, 133);
    --wu-white80: rgb(208, 93, 92);
    --wu-red: rgb(196, 53, 51);
    --wu-red-raw: 196, 53, 51;
    --wu-red20: rgb(205, 71, 55);
    --wu-red40: rgb(213, 89, 59);
    --wu-red60: rgb(222, 106, 64);
    --wu-red80: rgb(230, 124, 68);
    --wu-orange: rgb(239, 142, 72);
    --wu-orange-raw: 239, 142, 72;
    --wu-orange20: rgb(240, 151, 73);
    --wu-orange40: rgb(241, 160, 75);
    --wu-orange60: rgb(243, 170, 76);
    --wu-orange80: rgb(244, 179, 78);
    --wu-yellow: rgb(245, 188, 79);
    --wu-yellow-raw: 245, 188, 79;
    --wu-yellow20: rgb(217, 182, 80);
    --wu-yellow40: rgb(190, 176, 81);
    --wu-yellow60: rgb(162, 169, 81);
    --wu-yellow80: rgb(135, 163, 82);
    --wu-green: rgb(107, 157, 83);
    --wu-green-raw: 107, 157, 83;
    --wu-green20: rgb(101, 159, 109);
    --wu-green40: rgb(96, 161, 136);
    --wu-green60: rgb(90, 163, 162);
    --wu-green80: rgb(85, 165, 189);
    --wu-lightblue: rgb(79, 167, 215);
    --wu-lightblue-raw: 79, 167, 215;
    --wu-lightblue20: rgb(70, 150, 206);
    --wu-lightblue40: rgb(61, 133, 196);
    --wu-lightblue60: rgb(51, 116, 187);
    --wu-lightblue80: rgb(42, 99, 177);
    --wu-darkblue: rgb(33, 82, 168);
    --wu-darkblue-raw: 33, 82, 168;
    --wu-darkblue20: rgb(59, 83, 166);
    --wu-darkblue40: rgb(84, 85, 163);
    --wu-darkblue60: rgb(110, 86, 161);
    --wu-darkblue80: rgb(135, 88, 158);
    --wu-purple: rgb(161, 89, 156);
    --wu-purple-raw: 161, 89, 156;
    --wu-purple20: rgb(129, 71, 125);
    --wu-purple40: rgb(97, 53, 94);
    --wu-purple60: rgb(64, 36, 62);
    --wu-purple80: rgb(32, 18, 31);
    --wu-black: rgb(0, 0, 0);
    --wu-black-raw: 0, 0, 0;
    --nav-menu-bg-color: rgb(247, 247, 247);
    --nav-menu-bg-color-hover: rgb(255, 255, 255);
    --nav-menu-font-color: rgba(0, 0, 0, 0.87);
    --nav-menu-font-color-hover: rgb(0, 0, 0);
    --nav-menu-item-border-color: rgb(255, 255, 255);
    --search-bg-color: rgb(231, 231, 231);
    --search-focus-box-shadow: 1px 1px 2px rgba(0, 0, 0, 0.2);
    --action-bar-location-hover-color: rgba(0, 0, 0, .05);
    --action-bar-border-color: rgba(0, 0, 0, .05);
    --page-nav-bg-color: rgb(247, 247, 247);
    --page-nav-border-color: rgb(215, 215, 215);
    --input-bg-color: rgb(247, 247, 247);
    --input-border-color: rgb(163, 163, 163);
    --blue-button-highlight: rgb(0, 143, 191);
    --color-box-bg: rgb(242, 242, 242);
    --color-box-alternate-bg: rgba(0, 0, 0, .1);
    --color-box-title-bg: rgba(0, 0, 0, .1);
    --color-box-border: rgba(0, 0, 0, .1);
    --footer-bg-color: rgb(30, 32, 35);
    --footer-font-color: rgb(255, 255, 255);
    --footer-border-color: rgba(255, 255, 255, .05);
    --map-bg-color: rgb(203, 210, 211);
    --map-border-color: rgb(0, 0, 0, .1);
    --map-controls-title-bg-color: rgb(247, 247, 247);
    --map-controls-label-font-color: rgb(68, 68, 68);
    --map-controls-border-color: rgba(0, 0, 0, .1);
    --switch-bg-color: #ccc;
    --switch-cirlce-color: #fff;
    --widget-empty: #e2e8f0;
    --sun-moon-forecast-bg-color: rgba(0, 0, 0, 0.05);
    --sun-moon-forecast-border-color: rgba(0, 0, 0, 0.2);
    --dashboard-widget-border-color: rgba(0, 0, 0, .1);
    --dashboard-widget-box-shadow-color: rgba(0, 0, 0, .1);
    --humidity-comfortable-color: var(--wu-green-raw);
    --humidity-humid-color: var(--wu-lightblue-raw);
    --humidity-dry-color: var(--wu-red-raw);
    --dashboard-body-border-color: rgba(0, 0, 0, .05);
    --chart-light-color: #fff;
    --chart-dark-color:#e9ecef;
    /* Variables para el selector de tema */
    --theme-toggle-bg: rgba(0,0,0,0.04);
    --theme-toggle-border: rgba(0,0,0,0.08);
    --theme-toggle-color: var(--tt-color);
    --theme-toggle-active-bg: var(--wu-lightblue);
    --theme-toggle-active-color: var(--wu-white);
    --theme-toggle-radius: 10px;
    --theme-toggle-padding: 6px;
    --theme-toggle-gap: 6px;
    --theme-toggle-icon-size: 18px;
    --moon_sunlight: rgb(255, 255, 220);
    --moon_shadow: rgb(24,24,24);
    --moon_surround: rgb(230,230,230);
    /* Variables de Diseño Observatorio (Modo Claro) */
    --obs-bg-dark: rgb(250, 250, 250); /* Fondo casi blanco */
    --obs-neon-light: rgb(0, 150, 136); /* Verde oscuro/cian para texto en modo claro */
    --obs-neon-shadow: 0, 150, 136;
    --obs-accent-light: rgb(255, 140, 0); /* Naranja oscuro */
    --obs-description-bg: rgba(0, 0, 0, 0.03);
    --obs-close-color: rgb(200, 0, 100);
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
    --icon-espacioprofundo: url('<?= $root ?>/static/images/icons/espacioprofundo-day.png');
}

/* ======================================================
MODO NOCHE
====================================================== */
:root[data-theme="dark"] {
    color-scheme: dark;
    --bg-color: rgb(30, 32, 35);
    --color-font: 255, 255, 255;
    --font-color: rgb(255, 255, 255);
    --color-secondary-font: rgb(195, 195, 195);
    --black-or-white: 255, 255, 255;
    --link-color: rgb(200, 200, 255);
    --nav-menu-bg-color: rgb(51, 51, 52);
    --nav-menu-bg-color-hover: rgb(76, 76, 76);
    --nav-menu-font-color: rgb(231, 231, 231);
    --nav-menu-font-color-hover: rgb(255, 255, 255);
    --nav-menu-item-border-color: rgb(30, 32, 35);
    --search-focus-box-shadow: 1px 1px 2px rgba(255, 255, 255, 0.2);
    --action-bar-location-hover-color: rgba(255, 255, 255, .05);
    --action-bar-border-color: rgba(255, 255, 255, .05);
    --page-nav-bg-color: rgb(51, 51, 52);
    --page-nav-border-color: rgb(76, 76, 76);
    --input-bg-color: rgb(51, 51, 52);
    --input-border-color: rgb(76, 76, 76);
    --color-box-bg: rgb(41, 43, 46);
    --color-box-alternate-bg: rgba(255, 255, 255, .1);
    --color-box-title-bg: rgba(255, 255, 255, .1);
    --color-box-border: rgba(255, 255, 255, .1);
    --map-bg-color: rgb(52, 51, 50);
    --map-border-color: rgb(255, 255, 255, .1);
    --map-controls-label-font-color: rgb(247, 247, 247);
    --switch-bg-color: #333;
    --switch-cirlce-color: #666;
    --sun-moon-forecast-bg-color: rgba(255, 255, 255, 0.07);
    --sun-moon-forecast-border-color: rgba(255, 255, 255, 0.15);
    --dashboard-widget-border-color: rgba(255, 255, 255, .1);
    --dashboard-widget-box-shadow-color: rgba(255, 255, 255, .1);
    --widget-empty: rgb(83, 84, 87);
    --dashboard-body-border-color: rgba(255, 255, 255, .05);
    --chart-light-color: #f8f9fa;
    --chart-dark-color:#e9ecef;
    --theme-toggle-bg: rgba(255,255,255,0.03);
    --theme-toggle-border: rgba(255,255,255,0.06);
    --theme-toggle-color: var(--font-color);
    --theme-toggle-active-bg: var(--wu-lightblue-40, var(--wu-lightblue));
    --theme-toggle-active-color: var(--wu-black);
    /* Variables de Diseño Observatorio (Modo Oscuro) */
    --obs-bg-dark: rgb(1, 4, 28); /* Fondo muy oscuro, casi negro-azul */
    --obs-neon-light: rgb(0, 255, 192); /* Verde Neón brillante para texto */
    --obs-neon-shadow: 0, 255, 192;
    --obs-accent-light: rgb(255, 192, 0); /* Amarillo/Naranja brillante para énfasis */
    --obs-description-bg: rgba(255, 255, 255, 0.05); /* Fondo ligeramente transparente */
    --obs-close-color: rgb(255, 0, 112);
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

/* ===============================================
Invertir iconos en modo oscuro
=============================================== */

/* Por defecto (modo claro o auto) */
:root[data-theme="light"] .theme-buttons img,
:root[data-theme="auto"] .theme-buttons img {
    filter: invert(0);
}

/* En modo oscuro */
:root[data-theme="dark"] .theme-buttons img {
    filter: invert(1) brightness(1.2);
}
