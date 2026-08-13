<!DOCTYPE html>
<?php
// index.php
//DEBUG PHP:
//ini_set('display_startup_errors', 1);
//error_reporting(E_ALL);
//ini_set('display_errors', 1);
// CONFIGURACIÓN
include __DIR__ . "/static/config/config.php";

/**
 * Versión de caché basada en filemtime() del archivo real, en vez de time().
 * Así el navegador solo re-descarga el asset cuando cambia de verdad, no en
 * cada carga de página.
 */
function asset_v(string $relativePath): string
{
    $mtime = @filemtime(__DIR__ . '/' . ltrim($relativePath, './'));
    return $mtime !== false ? (string) $mtime : (string) time();
}
?>
<html lang="es">

<head>
    <link rel="icon" type="image/x-icon" href="./favicon.ico" />
    <title>Estación Meteorológica <?= $observatorio ?></title>
    <!-- defer: maplibre-gl solo se usa dentro de un listener de clic en
    modal_pws_info.js (al abrir el mapa), nunca al cargar la página, así que
    no necesita bloquear el parseo del <head>.
    Versión fijada a 5.24.0: la URL apuntaba a 'latest' sin pin, y maplibre-gl
    6.0.0 eliminó el bundle UMD (dist/maplibre-gl.js) que expone
    'window.maplibregl' — ahora unpkg solo sirve módulos ES (.mjs) en esa
    ruta, así que el script dejaba de definir el global y modal_pws_info.js
    fallaba con 'maplibregl is not defined'. 5.24.0 es la última 5.x que aún
    publica ese bundle clásico. -->
    <script defer src="https://unpkg.com/maplibre-gl@5.24.0/dist/maplibre-gl.js"></script>
    <link rel="stylesheet" type="text/css" href="https://unpkg.com/maplibre-gl@5.24.0/dist/maplibre-gl.css" />
    <link rel="stylesheet" type="text/css" href="./static/css/images.php?v=<?= asset_v('./static/css/images.php'); ?>" />
    <link rel="stylesheet" type="text/css" href="./static/css/global.css?v=<?= asset_v('./static/css/global.css'); ?>" />
    <link rel="stylesheet" type="text/css" href="./static/css/colors.css?v=<?= asset_v('./static/css/colors.css'); ?>" />
    <link rel="stylesheet" type="text/css" href="./static/css/theme-switcher.php?v=<?= asset_v('./static/css/theme-switcher.php'); ?>" />
    <link rel="stylesheet" type="text/css" href="./static/css/dashboard-header.css?v=<?= asset_v('./static/css/dashboard-header.css'); ?>" />
    <link rel="stylesheet" type="text/css" href="./static/css/dashboard-body.css?v=<?= asset_v('./static/css/dashboard-body.css'); ?>" />
    <link rel="stylesheet" type="text/css" href="./static/css/dashboard-footer.css?v=<?= asset_v('./static/css/dashboard-footer.css'); ?>" />
    <!-- Widgets' & Modals CSS -->
    <link rel="stylesheet" type="text/css" href="./static/css/dew-point-widget.css?v=<?= asset_v('./static/css/dew-point-widget.css'); ?>" />
    <link rel="stylesheet" type="text/css" href="./static/css/humidity-widget.css?v=<?= asset_v('./static/css/humidity-widget.css'); ?>" />
    <link rel="stylesheet" type="text/css" href="./static/css/humidity-int-widget.css?v=<?= asset_v('./static/css/humidity-int-widget.css'); ?>" />
    <link rel="stylesheet" type="text/css" href="./static/css/pressure-widget.css?v=<?= asset_v('./static/css/pressure-widget.css'); ?>" />
    <link rel="stylesheet" type="text/css" href="./static/css/rain-widget.css?v=<?= asset_v('./static/css/rain-widget.css'); ?>" />
    <link rel="stylesheet" type="text/css" href="./static/css/solar-radiation-widget.css?v=<?= asset_v('./static/css/solar-radiation-widget.css'); ?>" />
    <link rel="stylesheet" type="text/css" href="./static/css/temp-widget.css?v=<?= asset_v('./static/css/temp-widget.css'); ?>" />
    <link rel="stylesheet" type="text/css" href="./static/css/temp-int-widget.css?v=<?= asset_v('./static/css/temp-int-widget.css'); ?>" />
    <link rel="stylesheet" type="text/css" href="./static/css/uv-widget.css?v=<?= asset_v('./static/css/uv-widget.css'); ?>" />
    <link rel="stylesheet" type="text/css" href="./static/css/eclipse-widget.css?v=<?= asset_v('./static/css/eclipse-widget.css'); ?>" />
    <link rel="stylesheet" type="text/css" href="./static/css/widget-base.css?v=<?= asset_v('./static/css/widget-base.css'); ?>" />
    <link rel="stylesheet" type="text/css" href="./static/css/widget-wind.css?v=<?= asset_v('./static/css/widget-wind.css'); ?>" />
    <link rel="stylesheet" type="text/css" href="./static/css/widget-moon.css?v=<?= asset_v('./static/css/widget-moon.css'); ?>" />
    <link rel="stylesheet" type="text/css" href="./static/css/widget-sun.css?v=<?= asset_v('./static/css/widget-sun.css'); ?>" />
    <link rel="stylesheet" type="text/css" href="./static/css/forecast.css?v=<?= asset_v('./static/css/forecast.css'); ?>" />
    <link rel="stylesheet" type="text/css" href="./static/css/widget-seeing.css?v=<?= asset_v('./static/css/widget-seeing.css'); ?>" />
    <link rel="stylesheet" type="text/css" href="./static/css/observing-checklist-widget.css?v=<?= asset_v('./static/css/observing-checklist-widget.css'); ?>" />
    <link rel="stylesheet" type="text/css" href="./static/css/modal-seeing.css?v=<?= asset_v('./static/css/modal-seeing.css'); ?>" />
    <link rel="stylesheet" type="text/css" href="./static/css/modal-credits.css?v=<?= asset_v('./static/css/modal-credits.css'); ?>" />
    <link rel="stylesheet" type="text/css" href="./static/css/modal-dates.css?v=<?= asset_v('./static/css/modal-dates.css'); ?>" />
    <link rel="stylesheet" type="text/css" href="./static/css/modal-pws.css?v=<?= asset_v('./static/css/modal-pws.css'); ?>" />
    <link rel="stylesheet" type="text/css" href="./static/css/modal-moon.css?v=<?= asset_v('./static/css/modal-moon.css'); ?>" />
    <link rel="stylesheet" type="text/css" href="./static/css/widget-ephemeris.css?v=<?= asset_v('./static/css/widget-ephemeris.css'); ?>" />
    <link rel="stylesheet" type="text/css" href="./static/css/modal-ephemeris.css?v=<?= asset_v('./static/css/modal-ephemeris.css'); ?>" />
    <link rel="stylesheet" type="text/css" href="./static/css/modal-messier.css?v=<?= asset_v('./static/css/modal-messier.css'); ?>" />
    <link rel="stylesheet" type="text/css" href="./static/css/modal-sistema-solar.css?v=<?= asset_v('./static/css/modal-sistema-solar.css'); ?>" />
    <link rel="stylesheet" type="text/css" href="./static/css/modal-catalogo.css?v=<?= asset_v('./static/css/modal-catalogo.css'); ?>" />
    <link rel="stylesheet" type="text/css" href="./static/css/modal-dso.css?v=<?= asset_v('./static/css/modal-dso.css'); ?>" />
</head>

<body>
    <div class="widgets">
        <content-router-wc>
            <dashboard-header-view>
                <div class="max-width">
                    <div class="elevation-coordinates">Elevación:&nbsp;<strong><?= $elev ?></strong>m, Latitud:&nbsp;<strong><?= $latitud ?></strong>&nbsp;Longitud:&nbsp;<strong><?= $longitud ?></strong>&nbsp;Zona horaria:&nbsp;<strong><?= $tz ?></strong>
                    </div>
                    <div class="name-actions">
                        <h1><?= $observatorio ?></h1>
                        <div class="pws-status-container pws-offline">
                            <pws-info title="Última actualización:" id="PWS_info"></pws-info>
                            <span class="pws-status-text">PWS Desconectada</span>
                        </div>
                        <div class="theme-buttons">
                            <button id="theme-toggle" title="Alternar Tema Automático/Día/Noche" data-theme="auto">
                                <img id="theme-icon" src="./static/images/icons/auto.svg" alt="Modo Automático" />
                            </button>
                            <!-- Enlace setup -->
                            <a href="./static/config/setup.php?v=<?= time(); ?>" class="setup-link"><setup-button></setup-button>&nbsp;Setup</a>
                        </div>
                    </div>
                    <div class="location-info">
                        <span>En <?= $city ?>, </span>
                        <span class="long" id="pws-status-time-long"></span>
                        <!-- El script de "actualizado hace x segundos", está dentro del wind_widget.js -->
                        <span class="ago" id="pws-status-time-ago" data-updated=""></span>
                    </div>
                </div>
            </dashboard-header-view>
            <dashboard-body-view>
                <div class="max-width">
                    <sun-moon-forecast data-last-updated-long-string="" data-last-updated-short-string="" data-pws-id="<?= $observatorio ?>" data-place-id="" data-iana-time-zone="Europe/Madrid" data-time-zone-abbreviation="CEST" data-status="connected" data-obs-time-utc="" data-time-ago-string="">
                        <!-- Contenedor general de tarjetas -->
                        <div class="cards-grid">
                            <?php
                            require_once __DIR__ . '/static/widgets/widget_sun.php';
                            require_once __DIR__ . '/static/widgets/widget_ephemeris.php';
                            require_once __DIR__ . '/static/widgets/widget_moon.php';
                            ?>
                            <!-- Tarjeta Previsión -->
                            <div id="forecast" class="forecast-container">
                                <!-- Previsión meteorológica 8h -->
                            </div>
                        </div>
                    </sun-moon-forecast>
                    <!-- Título sección meteorología actual -->
                    <div class="section-title">
                        <span class="section-title-text">Situación Meteorológica Actual</span>
                    </div>

                    <!--############################################################
                            ######################## WIDGETS ###########################
                            ############################################################-->
                    <div class="widgets">
                        <?php
                        require_once __DIR__ . '/static/widgets/widget_temp_ext.php';
                        if ($show_dew == 1) {
                            require_once __DIR__ . '/static/widgets/widget_dew.php';
                        }
                        require_once __DIR__ . '/static/widgets/widget_hum_ext.php';
                        require_once __DIR__ . '/static/widgets/widget_wind.php';
                        require_once __DIR__ . '/static/widgets/widget_rain.php';
                        require_once __DIR__ . '/static/widgets/widget_press.php';
                        if ($show_uv == 1) {
                            require_once __DIR__ . '/static/widgets/widget_uv.php';
                        }
                        if ($show_solar == 1) {
                            require_once __DIR__ . '/static/widgets/widget_radiation.php';
                        }
                        if ($show_in_temp == 1) {
                            require_once __DIR__ . '/static/widgets/widget_temp_int.php';
                        }
                        if ($show_in_hum == 1) {
                            require_once __DIR__ . '/static/widgets/widget_hum_int.php';
                        }
                        if ($show_sky == 1) {
                            require_once __DIR__ . '/static/widgets/widget_seeing.php';
                            require_once __DIR__ . '/static/widgets/widget_observing_checklist.php';
                        }
                        require_once __DIR__ . '/static/widgets/widget_eclipse.php';
                        ?>
                    </div>
                    <!--############################################################
                            ################## FIN DE LOS WIDGETS ######################
                            ############################################################-->

                    <!--############################################################
                            ################### GRÁFICAS MODALES #######################
                            ############################################################-->
                    <?php
                    include_once __DIR__ . '/static/modals/modal_temp_ext.php';
                    include_once __DIR__ . '/static/modals/modal_hum_ext.php';
                    include_once __DIR__ . '/static/modals/modal_wind.php';
                    include_once __DIR__ . '/static/modals/modal_rain.php';
                    include_once __DIR__ . '/static/modals/modal_press.php';
                    include_once __DIR__ . '/static/modals/modal_uv.php';
                    include_once __DIR__ . '/static/modals/modal_temp_int.php';
                    include_once __DIR__ . '/static/modals/modal_hum_int.php';
                    include_once __DIR__ . '/static/modals/modal_seeing.php';
                    include_once __DIR__ . '/static/modals/modal_info.php';
                    include_once __DIR__ . '/static/modals/modal_moon.php';
                    include_once __DIR__ . '/static/modals/modal_moon_l100.php';
                    include_once __DIR__ . '/static/modals/modal_moon_zoom.php';
                    include_once __DIR__ . '/static/modals/modal_sun.php';
                    include_once __DIR__ . '/static/modals/modal_credits.php';
                    include_once __DIR__ . '/static/modals/modal_ephemeris.php';
                    include_once __DIR__ . '/static/modals/modal_sistema_solar.php';
                    include_once __DIR__ . '/static/modals/modal_catalogo.php';
                    include_once __DIR__ . '/static/modals/modal_dso.php';
                    include_once __DIR__ . '/static/modals/modal_dso_detalle.php';
                    ?>
                    <!-- ############################################################
                             ############## FIN DE LAS GRÁFICAS MODALES #################
                             ############################################################ -->
                </div>
            </dashboard-body-view>
            <dashboard-footer-view>
                <div class="max-width">
                    <div class="container">
                        <!-- El contenedor #link-credits es ahora el elemento clickable -->
                        <div id="link-credits" class="footer-text">
                            <!-- Icono de Info a la izquierda -->
                            <span class="info-icon"></span>
                            Agradecimientos
                        </div>
                    </div>
                </div>
            </dashboard-footer-view>
        </content-router-wc>
    </div>
    <!-- JS Varios -->
    <!-- 'defer' en todos los <script> clásicos de este bloque (menos los
    type="module", que ya son diferidos por spec): hoy se ejecutan todos de
    forma síncrona en el orden en que aparecen, bloqueando la descarga uno a
    uno (grave con astronomy.browser.js y orb.v2.js, que son librerías
    grandes). 'defer' preserva exactamente ese mismo orden de ejecución
    relativo (todas las cadenas de dependencias entre ellos, ej.
    conf_to_js.php → moon.js, o conf_to_js.php → update_status.js →
    sun.js inyectado dinámicamente, siguen intactas) pero permite que el
    navegador las descargue todas en paralelo en vez de en serie. -->
    <script defer src="./static/js/other/suncalc.js?v=<?= asset_v('./static/js/other/suncalc.js'); ?>"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/echarts/dist/echarts.min.js"></script>
    <script defer src="./static/js/other/astronomy.browser.js?v=<?= asset_v('./static/js/other/astronomy.browser.js'); ?>"></script>
    <script defer src="./static/js/other/orb.v2.js?v=<?= asset_v('./static/js/other/orb.v2.js'); ?>"></script>
    <!-- conf_to_js.php se mantiene con time(): su contenido depende de la
    tabla 'config' (lat/lon/ciudad/tz), no solo del archivo, así que
    filemtime() no detectaría un cambio de configuración desde setup.php. -->
    <script defer src="./static/config/conf_to_js.php?v=<?= time(); ?>"></script>
    <script defer src="./static/js/moon.js?v=<?= asset_v('./static/js/moon.js'); ?>"></script>
    <!--<script src="./static/js/sun.js?v<?= time() ?>"></script>-->
    <script type="module" src="./static/js/theme-switcher.js?v=<?= asset_v('./static/js/theme-switcher.js'); ?>"></script>

    <!-- JS de widgets-->
    <script defer src="./static/js/widgets/update_status.js?v=<?= asset_v('./static/js/widgets/update_status.js'); ?>"></script>
    <script defer src="./static/js/widgets/forecast.js?v=<?= asset_v('./static/js/widgets/forecast.js'); ?>"></script>
    <script defer src="./static/js/widgets/eclipse_widget.js?v=<?= asset_v('./static/js/widgets/eclipse_widget.js'); ?>"></script>
    <!-- JS de Modales -->
    <script type="module" src="./static/js/modals/modal_moon.js?v=<?= asset_v('./static/js/modals/modal_moon.js'); ?>"></script>
    <script defer src="./static/js/modals/modal_temp.js?v=<?= asset_v('./static/js/modals/modal_temp.js'); ?>"></script>
    <script defer src="./static/js/modals/modal_humidity.js?v=<?= asset_v('./static/js/modals/modal_humidity.js'); ?>"></script>
    <script defer src="./static/js/modals/modal_wind.js?v=<?= asset_v('./static/js/modals/modal_wind.js'); ?>"></script>
    <script defer src="./static/js/modals/modal_rain.js?v=<?= asset_v('./static/js/modals/modal_rain.js'); ?>"></script>
    <script defer src="./static/js/modals/modal_pressure.js?v=<?= asset_v('./static/js/modals/modal_pressure.js'); ?>"></script>
    <script defer src="./static/js/modals/modal_solar.js?v=<?= asset_v('./static/js/modals/modal_solar.js'); ?>"></script>
    <script defer src="./static/js/modals/modal_tempint.js?v=<?= asset_v('./static/js/modals/modal_tempint.js'); ?>"></script>
    <script defer src="./static/js/modals/modal_humidityint.js?v=<?= asset_v('./static/js/modals/modal_humidityint.js'); ?>"></script>
    <script defer src="./static/js/modals/modal_seeing.js?v=<?= asset_v('./static/js/modals/modal_seeing.js'); ?>"></script>
    <script defer src="./static/js/modals/modal_sun.js?lat=<?= $lat ?>&lon=<?= $lon ?>&v=<?= asset_v('./static/js/modals/modal_sun.js'); ?>"></script>
    <script type="module" src="./static/js/modals/modal_pws_info.js?v=<?= asset_v('./static/js/modals/modal_pws_info.js'); ?>"></script>
    <script defer src="./static/js/modals/modal_credits.js?v=<?= asset_v('./static/js/modals/modal_credits.js'); ?>"></script>
    <script type="module" src="./static/js/modals/modal_ephemeris.js?v=<?= asset_v('./static/js/modals/modal_ephemeris.js'); ?>"></script>
    <script defer src="./static/js/modals/modal_catalogo.js?v=<?= asset_v('./static/js/modals/modal_catalogo.js'); ?>"></script>
    <script defer src="./static/js/modals/modal_catalogo_detalle.js?v=<?= asset_v('./static/js/modals/modal_catalogo_detalle.js'); ?>"></script>
    <script defer src="./static/js/modals/modal_dso.js?v=<?= asset_v('./static/js/modals/modal_dso.js'); ?>"></script>
</body>

</html>