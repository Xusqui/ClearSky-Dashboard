<!DOCTYPE html>
<?php
    // index.php
    //DEBUG:
    //ini_set('display_startup_errors', 1);
    //error_reporting(E_ALL);
    //ini_set('display_errors', 1);
    // CONFIGURACIÓN
    include __DIR__ . "/static/config/config.php";

    /*-----------------------------------------
    Vamos a obtener la fecha de actualización.
    -----------------------------------------*/
    $ch = curl_init();
    $entity = "sensor.ws2900_v2_02_03_wind_direction";
    curl_setopt($ch, CURLOPT_URL, "$ha_url/api/states/$entity");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, ["Authorization: Bearer $token", "Content-Type: application/json"]);
    $response = curl_exec($ch);
    curl_close($ch);

    if ($response === false) {
        die("Error al conectar con Home Assistant");
    }
    // Decodificar JSON
    $data = json_decode($response, true);
    if (isset($data["last_updated"])) {
        $utcTime = new DateTime($data["last_updated"], new DateTimeZone("UTC"));

        // Convertir a horario de España
        $localTime = clone $utcTime;
        $localTime->setTimezone(new DateTimeZone("Europe/Madrid"));

        // Diferencia en segundos
        $now = new DateTime("now", new DateTimeZone("Europe/Madrid"));
        $diffSeconds = $now->getTimestamp() - $localTime->getTimestamp();

        // Formato de salida
        $meses = ["ene", "feb", "mar", "abr", "may", "jun", "jul", "ago", "sep", "oct", "nov", "dic"];
        $horas = $localTime->format("H");
        $minutos = $localTime->format("i");
        $dia = $localTime->format("j");
        $mes = $meses[(int) $localTime->format("n") - 1];
        $anio = $localTime->format("Y");
        $ts_formatted = "$horas:$minutos del $dia de $mes de $anio";
    } else {
        echo "No se pudo obtener la fecha de actualización";
    }

    // Calcular la fase de la luna:
    function getMoonPhaseValue($timestamp = null)
    {
        $known_new_moon = strtotime("2000-01-06 18:14:00 UTC");
        $timestamp = $timestamp ?? time();
        $days_since = ($timestamp - $known_new_moon) / 86400;
        $lunar_cycle = 29.53058867;
        $phase = fmod($days_since, $lunar_cycle) / $lunar_cycle;
        if ($phase < 0) {
            $phase += 1;
        }
        return (int) round($phase * 99);
    }

    $phase = getMoonPhaseValue();
    $moon_scale = 0.4;
?>
<html lang="es">
    <head>
        <link rel="icon" type="image/x-icon" href="./favicon.ico"/>
        <title>Estación Meteorológica <?= $observatorio ?></title>
        <script src="https://unpkg.com/maplibre-gl/dist/maplibre-gl.js"></script>
        <link rel="stylesheet" type="text/css" href="https://unpkg.com/maplibre-gl/dist/maplibre-gl.css" />
        <link rel="stylesheet" type="text/css" href="./static/css/images.php?v=<?= time() ?>" />
        <link rel="stylesheet" type="text/css" href="./static/css/global.css?v=<?= time() ?>" />
        <link rel="stylesheet" type="text/css" href="./static/css/colors.css?v=<?= time() ?>" />
        <link rel="stylesheet" type="text/css" href="./static/css/theme-switcher.css?v=<?= time() ?>" />
        <link rel="stylesheet" type="text/css" href="./static/css/dashboard-header.css?v=<?= time() ?>" />
        <link rel="stylesheet" type="text/css" href="./static/css/dashboard-body.css?v=<?= time() ?>" />
        <link rel="stylesheet" type="text/css" href="./static/css/dashboard-footer.css?v=<?= time() ?>" />
        <!-- Widgets' & Modals CSS -->
        <link rel="stylesheet" type="text/css" href="./static/css/dew-point-widget.css?v=<?= time() ?>" />
        <link rel="stylesheet" type="text/css" href="./static/css/humidity-widget.css?v=<?= time() ?>" />
        <link rel="stylesheet" type="text/css" href="./static/css/humidity-int-widget.css?v=<?= time() ?>" />
        <link rel="stylesheet" type="text/css" href="./static/css/pressure-widget.css?v=<?= time() ?>" />
        <link rel="stylesheet" type="text/css" href="./static/css/rain-widget.css?v=<?= time() ?>" />
        <link rel="stylesheet" type="text/css" href="./static/css/solar-radiation-widget.css?v=<?= time() ?>" />
        <link rel="stylesheet" type="text/css" href="./static/css/temp-widget.css?v=<?= time() ?>" />
        <link rel="stylesheet" type="text/css" href="./static/css/uv-widget.css?v=<?= time() ?>" />
        <link rel="stylesheet" type="text/css" href="./static/css/widget-base.css?v=<?= time() ?>" />
        <link rel="stylesheet" type="text/css" href="./static/css/widget-wind.css?v=<?= time() ?>" />
        <link rel="stylesheet" type="text/css" href="./static/css/widget-moon.css?v=<?= time() ?>" />
        <link rel="stylesheet" type="text/css" href="./static/css/widget-sun.css?v=<?= time() ?>" />
        <link rel="stylesheet" type="text/css" href="./static/css/forecast.css?v=<?= time() ?>" />
        <link rel="stylesheet" type="text/css" href="./static/css/widget-seeing.css?v=<?= time() ?>" />
        <link rel="stylesheet" type="text/css" href="./static/css/modal-seeing.css?v=<?= time() ?>" />
        <link rel="stylesheet" type="text/css" href="./static/css/modal-credits.css?v=<?= time() ?>" />
        <link rel="stylesheet" type="text/css" href="./static/css/modal-dates.css?v=<?= time() ?>" />
        <link rel="stylesheet" type="text/css" href="./static/css/modal-pws.css?v=<?= time() ?>" />
        <link rel="stylesheet" type="text/css" href="./static/css/modal-moon.css?v=<?= time() ?>" />
        <link rel="stylesheet" type="text/css" href="./static/css/moon-phase.php?position=<?= $phase ?>&scale=<?= $moon_scale ?>&v=<?= time() ?>">
    </head>
    <body>
        <div class="widgets">
            <content-router-wc>
                <dashboard-header-view>
                    <div class="max-width">
                        <div class="elevation-coordinates">Elevación:&nbsp;<strong><?= $elev ?></strong>m, Latitud:&nbsp;<strong><?= $latitud ?></strong>&nbsp;Longitud:&nbsp;<strong><?= $longitud ?></strong>&nbsp;Zona horaria:&nbsp;<strong><?= $tz ?></strong>
                            <!-- Enlace setup -->
                            <a href="./static/config/setup.php" class="setup-link"><setup-button></setup-button>&nbsp;Setup</a>
                        </div>
                        <div class="name-actions">
                            <h1><?= $observatorio ?></h1>
                            <pws-info title="PWS Info" id="PWS_info"></pws-info>
                            <!-- Selector de tema de color -->
                            <div class="theme-buttons">
                                <button data-theme="light" title="Modo Día">
                                    <img src="./static/images/icons/day.svg" alt="Día" />
                                </button>
                                <button data-theme="dark" title="Modo Noche">
                                    <img src="./static/images/icons/night.svg" alt="Noche" />
                                </button>
                                <button data-theme="auto" title="Modo Automático">
                                    <img src="./static/images/icons/auto.svg" alt="Auto" />
                                </button>
                            </div>
                        </div>
                        <div class="location-info">
                            <span>En <?= $city ?>, a las</span>
                            <span class="long" id="pws-status-time-long"><?= $ts_formatted ?>.</span>
                            <!-- El script de "actualizado hace x segundos", está dentro del wind_widget.js -->
                            <span class="ago" id="pws-status-time-ago" data-updated="<?= $localTime->getTimestamp() ?>">Actualizado hace 0 sec</span>
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
        <script src="./static/config/conf_to_js.php"></script>
        <script type="module" src="./static/js/widgets/wind_widget.js?v=<?= time() ?>"></script>
        <script type="module" src="./static/js/widgets/dew_widget.js?v=<?= time() ?>"></script>
        <script type="module" src="./static/js/widgets/temp_widget.js?v=<?= time() ?>"></script>
        <script type="module" src="./static/js/widgets/humidity_widget.js?v=<?= time() ?>"></script>
        <script type="module" src="./static/js/widgets/rain_widget.js?v=<?= time() ?>"></script>
        <script type="module" src="./static/js/widgets/pressure_widget.js?v=<?= time() ?>"></script>
        <script type="module" src="./static/js/widgets/uv_widget.js?v=<?= time() ?>"></script>
        <script type="module" src="./static/js/widgets/solar_widget.js?v=<?= time() ?>"></script>
        <script type="module" src="./static/js/widgets/temp_interior_widget.js?v=<?= time() ?>"></script>
        <script type="module" src="./static/js/widgets/humidity_interior_widget.js?v=<?= time() ?>"></script>
        <script src="./static/js/widgets/seeing_widget.js?v=<?= time() ?>"></script>
        <script type="module" src="./static/js/widgets/forecast.js?v=<?= time() ?>"></script>
        <script type="module" src="./static/js/date-time.js?v=<?= time() ?>"></script>
        <script src="https://unpkg.com/suncalc@1.9.0/suncalc.js"></script>
        <script src="./static/js/moon.js?v<?= time() ?>"></script>
        <script src="./static/js/sun.js?lat=<?= $lat ?>&lon=<?= $lon ?>&v=<?= time() ?>"></script>
        <script type="module" src="./static/js/modals/modal_moon.js?v<?= time() ?>"></script>
        <script src="https://cdn.jsdelivr.net/npm/echarts/dist/echarts.min.js"></script>
        <script type="module" src="./static/js/modals/modal_temp.js?v=<?= time() ?>"></script>
        <script type="module" src="./static/js/modals/modal_humidity.js?v=<?= time() ?>"></script>
        <script type="module" src="./static/js/modals/modal_wind.js?v=<?= time() ?>"></script>
        <script type="module" src="./static/js/modals/modal_rain.js?v=<?= time() ?>"></script>
        <script type="module" src="./static/js/modals/modal_pressure.js?v=<?= time() ?>"></script>
        <script type="module" src="./static/js/modals/modal_solar.js?v=<?= time() ?>"></script>
        <script type="module" src="./static/js/modals/modal_tempint.js?v=<?= time() ?>"></script>
        <script type="module" src="./static/js/modals/modal_humidityint.js?v=<?= time() ?>"></script>
        <script type="module" src="./static/js/modals/modal_seeing.js?v=<?= time() ?>"></script>
        <script src="./static/js/modals/modal_sun.js?lat=<?= $lat ?>&lon=<?= $lon ?>&v=<?= time() ?>"></script>
        <script type="module" src="./static/js/widgets/pws_info.js?v=<?= time() ?>"></script>
        <script type="module" src="./static/js/theme-switcher.js?v=<?= time() ?>"></script>
        <script type="module" src="./static/js/modals/modal_credits.js?v=<?= time() ?>"></script>
        <!-- SCRIPT de depuración -->
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
        </script>
    </body>
</html>
