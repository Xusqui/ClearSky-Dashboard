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
        <script src="https://unpkg.com/maplibre-gl/dist/maplibre-gl.js"></script>
        <link rel="stylesheet" type="text/css" href="https://unpkg.com/maplibre-gl/dist/maplibre-gl.css" />
        <link rel="stylesheet" type="text/css" href="./static/css/images.php" />
        <link rel="stylesheet" type="text/css" href="./static/css/global.css" />
        <link rel="stylesheet" type="text/css" href="./static/css/colors.css" />
        <link rel="stylesheet" type="text/css" href="./static/css/theme-switcher.php" />
        <link rel="stylesheet" type="text/css" href="./static/css/dashboard-header.css" />
        <link rel="stylesheet" type="text/css" href="./static/css/dashboard-body.css" />
        <link rel="stylesheet" type="text/css" href="./static/css/dashboard-footer.css" />
        <!-- Widgets' & Modals CSS -->
        <link rel="stylesheet" type="text/css" href="./static/css/dew-point-widget.css" />
        <link rel="stylesheet" type="text/css" href="./static/css/humidity-widget.css" />
        <link rel="stylesheet" type="text/css" href="./static/css/humidity-int-widget.css" />
        <link rel="stylesheet" type="text/css" href="./static/css/pressure-widget.css" />
        <link rel="stylesheet" type="text/css" href="./static/css/rain-widget.css" />
        <link rel="stylesheet" type="text/css" href="./static/css/solar-radiation-widget.css" />
        <link rel="stylesheet" type="text/css" href="./static/css/temp-widget.css" />
        <link rel="stylesheet" type="text/css" href="./static/css/uv-widget.css" />
        <link rel="stylesheet" type="text/css" href="./static/css/widget-base.css" />
        <link rel="stylesheet" type="text/css" href="./static/css/widget-wind.css" />
        <link rel="stylesheet" type="text/css" href="./static/css/widget-moon.css" />
        <link rel="stylesheet" type="text/css" href="./static/css/widget-sun.css" />
        <link rel="stylesheet" type="text/css" href="./static/css/forecast.css" />
        <link rel="stylesheet" type="text/css" href="./static/css/widget-seeing.css" />
        <link rel="stylesheet" type="text/css" href="./static/css/modal-seeing.css" />
        <link rel="stylesheet" type="text/css" href="./static/css/modal-credits.css" />
        <link rel="stylesheet" type="text/css" href="./static/css/modal-dates.css" />
        <link rel="stylesheet" type="text/css" href="./static/css/modal-pws.css" />
        <link rel="stylesheet" type="text/css" href="./static/css/modal-moon.css" />
        <link rel="stylesheet" type="text/css" href="./static/css/widget-ephemeris.css" />
        <link rel="stylesheet" type="text/css" href="./static/css/modal-ephemeris.css" />
        <link rel="stylesheet" type="text/css" href="./static/css/modal-messier.css" />
        <link rel="stylesheet" type="text/css" href="./static/css/modal-sistema-solar.css" />
        <link rel="stylesheet" type="text/css" href="./static/css/modal-catalogo.css" />
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
                                <a href="./static/config/setup.php" class="setup-link"><setup-button></setup-button>&nbsp;Setup</a>
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
                                    require_once './widget_sun.php';
                                    require_once './widget_ephemeris.php';
                                    require_once './widget_moon.php';
                                ?>
                                <!-- Tarjeta Previsión -->
                                <div id="forecast" class="forecast-container">
                                    <!-- Previsión meteorológica 6h -->
                                </div>
                            </div>
                        </sun-moon-forecast>
                        <!--############################################################
                            ######################## WIDGETS ###########################
                            ############################################################-->
                        <div class="widgets">
                            <?php
                            require_once './widget_temp_ext.php';
                            require_once './widget_dew.php';
                            require_once './widget_hum_ext.php';
                            require_once './widget_wind.php';
                            require_once './widget_rain.php';
                            require_once './widget_press.php';
                            require_once './widget_uv.php';
                            require_once './widget_radiation.php';
                            require_once './widget_temp_int.php';
                            require_once './widget_hum_int.php';
                            require_once './widget_seeing.php';
                            ?>
                        </div>
                        <!--############################################################
                            ################## FIN DE LOS WIDGETS ######################
                            ############################################################-->

                        <!--############################################################
                            ################### GRÁFICAS MODALES #######################
                            ############################################################-->
                        <?php
                        include_once './modal_temp_ext.php';
                        include_once './modal_hum_ext.php';
                        include_once './modal_wind.php';
                        include_once './modal_rain.php';
                        include_once './modal_press.php';
                        include_once './modal_uv.php';
                        include_once './modal_temp_int.php';
                        include_once './modal_hum_int.php';
                        include_once './modal_seeing.php';
                        include_once './modal_info.php';
                        include_once './modal_moon.php';
                        include_once './modal_moon_l100.php';
                        include_once './modal_moon_zoom.php';
                        include_once './modal_sun.php';
                        include_once './modal_credits.php';
                        include_once './modal_ephemeris.php';
                        include_once './modal_messier.php';
                        include_once './modal_sistema_solar.php';
                        include_once './modal_catalogo.php';
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
        <script src="./static/js/other/orb.v2.js"></script>
        <script src="./static/js/other/astronomy.browser.js"></script>
        <script src="./static/config/conf_to_js.php"></script>
        <script src="./static/js/moon.js?v<?= time() ?>"></script>
        <script src="./static/js/sun.js"></script>
        <script type="module" src="./static/js/theme-switcher.js"></script>
        <!-- JS de widgets-->
        <script src="./static/js/widgets/update_status.js"></script>
        <script src="./static/js/widgets/forecast.js"></script>
        <!-- JS de Modales -->
        <script type="module" src="./static/js/modals/modal_moon.js?v<?= time() ?>"></script>
        <script src="./static/js/modals/modal_temp.js"></script>
        <script src="./static/js/modals/modal_humidity.js"></script>
        <script src="./static/js/modals/modal_wind.js"></script>
        <script src="./static/js/modals/modal_rain.js"></script>
        <script src="./static/js/modals/modal_pressure.js"></script>
        <script src="./static/js/modals/modal_solar.js"></script>
        <script src="./static/js/modals/modal_tempint.js"></script>
        <script src="./static/js/modals/modal_humidityint.js"></script>
        <script src="./static/js/modals/modal_seeing.js"></script>
        <script src="./static/js/modals/modal_sun.js?lat=<?= $lat ?>&lon=<?= $lon ?>&v=<?= time() ?>"></script>
        <script type="module" src="./static/js/modals/modal_pws_info.js"></script>
        <script src="./static/js/modals/modal_credits.js"></script>
        <script type="module" src="./static/js/modals/modal_ephemeris.js"></script>
        <script src="./static/js/modals/modal_catalogo.js"></script>
        <script src="./static/js/modals/modal_catalogo_detalle.js"></script>
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
