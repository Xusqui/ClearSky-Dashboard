<!DOCTYPE html>
<?php
// index.php
//DEBUG PHP:
//ini_set('display_startup_errors', 1);
//error_reporting(E_ALL);
//ini_set('display_errors', 1);
// CONFIGURACIÓN
include __DIR__ . "/static/config/config.php";
?>
<html lang="es">
    <head>
        <link rel="icon" type="image/x-icon" href="./favicon.ico"/>
        <title>Estación Meteorológica <?= $observatorio ?></title>
        <script src="https://unpkg.com/maplibre-gl/dist/maplibre-gl.js?v=<?= time(); ?>"></script>
        <link rel="stylesheet" type="text/css" href="https://unpkg.com/maplibre-gl/dist/maplibre-gl.css?v=<?= time(); ?>" />
        <link rel="stylesheet" type="text/css" href="./static/css/images.php?v=<?= time(); ?>" />
        <link rel="stylesheet" type="text/css" href="./static/css/global.css?v=<?= time(); ?>" />
        <link rel="stylesheet" type="text/css" href="./static/css/colors.css?v=<?= time(); ?>" />
        <link rel="stylesheet" type="text/css" href="./static/css/theme-switcher.php?v=<?= time(); ?>" />
        <link rel="stylesheet" type="text/css" href="./static/css/dashboard-header.css?v=<?= time(); ?>" />
        <link rel="stylesheet" type="text/css" href="./static/css/dashboard-body.css?v=<?= time(); ?>" />
        <link rel="stylesheet" type="text/css" href="./static/css/dashboard-footer.css?v=<?= time(); ?>" />
        <!-- Widgets' & Modals CSS -->
        <link rel="stylesheet" type="text/css" href="./static/css/dew-point-widget.css?v=<?= time(); ?>" />
        <link rel="stylesheet" type="text/css" href="./static/css/humidity-widget.css?v=<?= time(); ?>" />
        <link rel="stylesheet" type="text/css" href="./static/css/humidity-int-widget.css?v=<?= time(); ?>" />
        <link rel="stylesheet" type="text/css" href="./static/css/pressure-widget.css?v=<?= time(); ?>" />
        <link rel="stylesheet" type="text/css" href="./static/css/rain-widget.css?v=<?= time(); ?>" />
        <link rel="stylesheet" type="text/css" href="./static/css/solar-radiation-widget.css?v=<?= time(); ?>" />
        <link rel="stylesheet" type="text/css" href="./static/css/temp-widget.css?v=<?= time(); ?>" />
        <link rel="stylesheet" type="text/css" href="./static/css/uv-widget.css?v=<?= time(); ?>" />
        <link rel="stylesheet" type="text/css" href="./static/css/widget-base.css?v=<?= time(); ?>" />
        <link rel="stylesheet" type="text/css" href="./static/css/widget-wind.css?v=<?= time(); ?>" />
        <link rel="stylesheet" type="text/css" href="./static/css/widget-moon.css?v=<?= time(); ?>" />
        <link rel="stylesheet" type="text/css" href="./static/css/widget-sun.css?v=<?= time(); ?>" />
        <link rel="stylesheet" type="text/css" href="./static/css/forecast.css?v=<?= time(); ?>" />
        <link rel="stylesheet" type="text/css" href="./static/css/widget-seeing.css?v=<?= time(); ?>" />
        <link rel="stylesheet" type="text/css" href="./static/css/modal-seeing.css?v=<?= time(); ?>" />
        <link rel="stylesheet" type="text/css" href="./static/css/modal-credits.css?v=<?= time(); ?>" />
        <link rel="stylesheet" type="text/css" href="./static/css/modal-dates.css?v=<?= time(); ?>" />
        <link rel="stylesheet" type="text/css" href="./static/css/modal-pws.css?v=<?= time(); ?>" />
        <link rel="stylesheet" type="text/css" href="./static/css/modal-moon.css?v=<?= time(); ?>" />
        <link rel="stylesheet" type="text/css" href="./static/css/widget-ephemeris.css?v=<?= time(); ?>" />
        <link rel="stylesheet" type="text/css" href="./static/css/modal-ephemeris.css?v=<?= time(); ?>" />
        <link rel="stylesheet" type="text/css" href="./static/css/modal-messier.css?v=<?= time(); ?>" />
        <link rel="stylesheet" type="text/css" href="./static/css/modal-sistema-solar.css?v=<?= time(); ?>" />
        <link rel="stylesheet" type="text/css" href="./static/css/modal-catalogo.css?v=<?= time(); ?>" />
        <link rel="stylesheet" type="text/css" href="./static/css/modal-dso.css?v=<?= time(); ?>" />
        <!-- El enlace de la hoja de estilos (css) moon-phase.php se actualiza dinámicamente dentro del archivo /static/js/moon.js -->
        <link id="moon-phase-css" rel="stylesheet" type="text/css" href="./static/css/moon-phase.php?position=&scale=0.4&bright=1&v=<?= time() ?>">
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
                            }
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
        <script src="https://unpkg.com/suncalc@1.9.0/suncalc.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/echarts/dist/echarts.min.js"></script>
        <script src="./static/js/other/astronomy.browser.js?v=<?= time(); ?>"></script>
        <script src="./static/js/other/orb.v2.js?v=<?= time(); ?>"></script>
        <script src="./static/config/conf_to_js.php?v=<?= time(); ?>"></script>
        <script src="./static/js/moon.js?v<?= time() ?>"></script>
        <script src="./static/js/sun.js?v<?= time() ?>"></script>
        <script type="module" src="./static/js/theme-switcher.js?v=<?= time(); ?>"></script>
        <!-- JS de widgets-->
        <script src="./static/js/widgets/update_status.js?v=<?= time(); ?>"></script>
        <script src="./static/js/widgets/forecast.js?v=<?= time(); ?>"></script>
        <!-- JS de Modales -->
        <script type="module" src="./static/js/modals/modal_moon.js?v<?= time() ?>"></script>
        <script src="./static/js/modals/modal_temp.js?v=<?= time(); ?>"></script>
        <script src="./static/js/modals/modal_humidity.js?v=<?= time(); ?>"></script>
        <script src="./static/js/modals/modal_wind.js?v=<?= time(); ?>"></script>
        <script src="./static/js/modals/modal_rain.js?v=<?= time(); ?>"></script>
        <script src="./static/js/modals/modal_pressure.js?v=<?= time(); ?>"></script>
        <script src="./static/js/modals/modal_solar.js?v=<?= time(); ?>"></script>
        <script src="./static/js/modals/modal_tempint.js?v=<?= time(); ?>"></script>
        <script src="./static/js/modals/modal_humidityint.js?v=<?= time(); ?>"></script>
        <script src="./static/js/modals/modal_seeing.js?v=<?= time(); ?>"></script>
        <script src="./static/js/modals/modal_sun.js?lat=<?= $lat ?>&lon=<?= $lon ?>&v=<?= time() ?>"></script>
        <script type="module" src="./static/js/modals/modal_pws_info.js?v=<?= time(); ?>"></script>
        <script src="./static/js/modals/modal_credits.js?v=<?= time(); ?>"></script>
        <script type="module" src="./static/js/modals/modal_ephemeris.js?v=<?= time() ?>"></script>
        <script src="./static/js/modals/modal_catalogo.js?v=<?= time(); ?>"></script>
        <script src="./static/js/modals/modal_catalogo_detalle.js?v=<?= time(); ?>"></script>
        <script src="./static/js/modals/modal_dso.js?v=<?= time(); ?>"></script>
        <!-- SCRIPT de depuración
        <script>
            (function() {
                // Captura errores de carga de recursos (img, script, link)
                window.addEventListener('error', function(event) {
                    let target = event.target || event.srcElement;
                    if (target && (target.src || target.href)) {
                        let url = target.src || target.href;
                        let tipo = target.tagName.toLowerCase();
                        console.log(`❌ Recurso fallido: [${tipo}] ${url}`);

                        // Intento de stack trace
                        if (event.error && event.error.stack) {
                            console.log('Stack trace:', event.error.stack);
                        } else {
                            console.trace();
                        }
                    } else {
                        // Errores JS normales
                        console.log('💥 Error JS:', event.message);
                        console.trace();
                    }
                }, true);
            })();
        </script> -->
    </body>
</html>
