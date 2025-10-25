<!DOCTYPE html>
<?php
//DEBUG:
//ini_set('display_startup_errors', 1);
//error_reporting(E_ALL);
//ini_set('display_errors', 1);
// CONFIGURACIÓN
include __DIR__ . '/static/config/config.php';
// ======================
// SENSORES (entity_id ajustados a los míos (Xisco) reales de Home Assistant)
// ======================
//temperatura = sensor.ws2900_v2_02_03_outdoor_temperature
//Sensación Térmica = sensor.ws2900_v2_02_03_feels_like_temperature
//Humedad = sensor.ws2900_v2_02_03_humidity
//Presión Relativa = sensor.ws2900_v2_02_03_relative_pressure
//Presión Absoluta = sensor.ws2900_v2_02_03_absolute_pressure
//Velocidad del viento = sensor.ws2900_v2_02_03_wind_speed
//Dirección del viento = sensor.ws2900_v2_02_03_wind_direction
//Rachas = sensor.ws2900_v2_02_03_wind_gust
//Lluvia diaria = sensor.ws2900_v2_02_03_daily_rain
//Índice UV = sensor.ws2900_v2_02_03_uv_index
//Radiación solar = sensor.ws2900_v2_02_03_solar_radiation
//Punto de rocío = sensor.ws2900_v2_02_03_dewpoint
//Temperatura interior = sensor.ws2900_v2_02_03_indoor_temperature
//Humedad interior = sensor.ws2900_v2_02_03_indoor_humidity
//$gust_max = sensor.ws2900_v2_02_03_max_daily_gust
//$rain_rate = sensor.ws2900_v2_02_03_rain_rate

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
?>

<html lang="es">
    <head>
        <link rel="icon" type="image/x-icon" href="/weather/favicon.ico"/>
        <title>Estación Meteorológica <?php echo $observatorio; ?></title>
        <link rel="stylesheet" type="text/css" href="/weather/static/css/images.css?v=<?php echo time(); ?>">
        <link rel="stylesheet" type="text/css" href="/weather/static/css/global.css?v=<?php echo time(); ?>">
        <link rel="stylesheet" type="text/css" href="/weather/static/css/colors.css?v=<?php echo time(); ?>">
        <link rel="stylesheet" type="text/css" href="/weather/static/css/theme-switcher.css?v=<?php echo time(); ?>">
        <link rel="stylesheet" type="text/css" href="/weather/static/css/dashboard-header.css?v=<?php echo time(); ?>">
        <link rel="stylesheet" type="text/css" href="/weather/static/css/dashboard-body.css?v=<?php echo time(); ?>">
        <link rel="stylesheet" type="text/css" href="/weather/static/css/dashboard-footer.css?v=<?php echo time(); ?>">
        <!-- Widgets' CSS -->
        <link rel="stylesheet" type="text/css" href="/weather/static/css/dew-point-widget.css?v=<?php echo time(); ?>">
        <link rel="stylesheet" type="text/css" href="/weather/static/css/humidity-widget.css?v=<?php echo time(); ?>">
        <link rel="stylesheet" type="text/css" href="/weather/static/css/humidity-int-widget.css?v=<?php echo time(); ?>">
        <link rel="stylesheet" type="text/css" href="/weather/static/css/pressure-widget.css?v=<?php echo time(); ?>">
        <link rel="stylesheet" type="text/css" href="/weather/static/css/rain-widget.css?v=<?php echo time(); ?>">
        <link rel="stylesheet" type="text/css" href="/weather/static/css/solar-radiation-widget.css?v=<?php echo time(); ?>">
        <link rel="stylesheet" type="text/css" href="/weather/static/css/temp-widget.css?v=<?php echo time(); ?>">
        <link rel="stylesheet" type="text/css" href="/weather/static/css/uv-widget.css?v=<?php echo time(); ?>">
        <link rel="stylesheet" type="text/css" href="/weather/static/css/widget-base.css?v=<?php echo time(); ?>">
        <link rel="stylesheet" type="text/css" href="/weather/static/css/wind-widget.css?v=<?php echo time(); ?>">
        <link rel="stylesheet" type="text/css" href="/weather/static/css/moon.css?v=<?php echo time(); ?>">
        <link rel="stylesheet" type="text/css" href="/weather/static/css/forecast.css?v=<?php echo time(); ?>">
        <link rel="stylesheet" type="text/css" href="/weather/static/css/widget_seeing.css?v=<?php echo time(); ?>">
        <link rel="stylesheet" type="text/css" href="/weather/static/css/modal-seeing.css?v=<?php echo time(); ?>">
        <link rel="stylesheet" type="text/css" href="/weather/static/css/modal-dates.css?v=<?php echo time(); ?>">
        <link rel="stylesheet" type="text/css" href="/weather/static/css/modal-pws.css?v=<?php echo time(); ?>">
        <script src="https://unpkg.com/maplibre-gl/dist/maplibre-gl.js"></script>
        <link href="https://unpkg.com/maplibre-gl/dist/maplibre-gl.css" rel="stylesheet" />
    </head>
    <body>
        <div class="widgets">
            <content-router-wc>
                <dashboard-header-view>
                    <div class="max-width">
                        <div class="elevation-coordinates">Elevación: <strong><?php echo $elev; ?></strong> m, Latitud: <strong><?php echo $latitud; ?></strong> Longitud: <strong><?php echo $longitud; ?></strong> Zona horaria: <strong><?php echo $tz; ?></strong></div>
                        <div class="name-actions">
                            <h1><?php echo $observatorio; ?></h1>
                            <pws-info title="PWS Info" id="PWS_info">
                            </pws-info>
                        </div>
                        <div class="location-info">
                            <span>En <?php echo $city; ?>, a las</span>
                            <span class="long" id="pws-status-time-long"><?php echo $ts_formatted; ?>.</span>
                            <!-- El script de "actualizado hace x segundos", está dentro del wind_widget.js -->
                            <span class="ago" id="pws-status-time-ago" data-updated="<?php echo $localTime->getTimestamp(); ?>">Actualizado hace 0 segundos</span>
                            <!-- CONTROLES DE TEMA (añadir en dashboard-header-view, junto al setup-link) -->
                            <!-- Enlace setup -->
                            <a href="./static/config/setup.php" class="setup-link"><setup-button></setup-button> Setup</a>
                            <!-- Selector de tema de color -->
                            <div class="theme-buttons">
                                <button data-theme="light" title="Modo Día">
                                    <img src="/weather/static/images/icons/day.svg" alt="Día" />
                                </button>
                                <button data-theme="dark" title="Modo Noche">
                                    <img src="/weather/static/images/icons/night.svg" alt="Noche" />
                                </button>
                                <button data-theme="auto" title="Modo Automático">
                                    <img src="/weather/static/images/icons/auto.svg" alt="Auto" />
                                </button>
                            </div>
                        </div>
                    </div>
                </dashboard-header-view>
                <dashboard-body-view>
                    <div class="max-width">
                        <sun-moon-forecast data-last-updated-long-string="" data-last-updated-short-string="" data-pws-id="IFUENG27" data-place-id="" data-iana-time-zone="Europe/Madrid" data-time-zone-abbreviation="CEST" data-status="connected" data-obs-time-utc="" data-time-ago-string="">
                            <!-- Contenedor general de tarjetas -->
                            <div class="cards-grid">
                                <!-- Tarjeta Sol -->
                                <div class="big-card-sun sun-card">
                                    <div id="sun-arc-container">
                                        <svg id="sun-arc" width="100" height="100" viewBox="0 0 100 100">
                                            <path d="M 10 60 A 35 35 0 0 1 90 60" stroke="orange" stroke-width="4" fill="none" stroke-linecap="round"/>
                                            <image id="sun-icon" href="/weather/static/images/icons/sun.svg" width="30" height="30" x="0" y="0" visibility="hidden"/>
                                            <image id="sunrise-icon" href="/weather/static/images/icons/sunrise.svg" width="18" height="18" x="3" y="60"/>
                                            <text id="sunrise-time" x="11" y="80" text-anchor="middle" font-size="9" fill="gray">sunrise</text>
                                            <image id="sunset-icon" href="/weather/static/images/icons/sunset.svg" width="18" height="18" x="83" y="60"/>
                                            <text id="sunset-time" x="89" y="80" text-anchor="middle" font-size="9" fill="gray">sunset</text>
                                        </svg>
                                    </div>
                                </div>
                                <!-- Tarjeta Luna -->
                                <div class="big-card-moon moon-card">
                                    <div class="moon-icon">
                                        <svg width="100%" height="100%" viewBox="0 0 120 90">
                                            <defs>
                                                <mask id="moon-mask">
                                                    <rect width="120" height="90" fill="black"/>
                                                    <path id="mask-path" fill="white"/>
                                                </mask>
                                            </defs>
                                            <image id="moon-icon" href="/weather/static/images/icons/moons/full_moon.svg"
                                                   width="120" height="88" x="0" y="0" mask="url(#moon-mask)"/>
                                            <text id="moon-text" x="60" y="90" text-anchor="middle">Calculando</text>
                                        </svg>
                                    </div>
                                </div>
                                <!-- Tarjeta Previsión -->
                                <div id="forecast" class="forecast-container">
                                    <!-- Previsión meteorológica 6h -->
                                </div>
                            </div>
                        </sun-moon-forecast>
                        <div class="widgets">
                            <!--    ****************************************************+
                                    *************** WIDGET DE TEMPERATURA ***************
                                    ***************************************************** -->
                            <div class="widget" id="temp_widget">
                                <?php
                                // Calcular la posición de la aguja de la temperatura
                                $minTemp = -20;
                                $maxTemp = 50;
                                $minAngle = -145;
                                $maxAngle = 145;
                                $temp_angle = 0; // ángulo inicial
                                //(($temp - $minTemp) * ($maxAngle - $minAngle)) / ($maxTemp - $minTemp) + $minAngle;

                                // Limitamos a los extremos
                                if ($temp_angle < $minAngle) {
                                    $temp_angle = $minAngle;
                                }
                                if ($temp_angle > $maxAngle) {
                                    $temp_angle = $maxAngle;
                                }
                                ?>
                                <div class="title">Temperatura Exterior</div>
                                <temp-widget-view data-pws-id="IFUENG27" data-status="connected" data-unit="m" data-temp="<?php echo $temp; ?>" data-temp-angle="<?php echo $temp_angle; ?>" data-main-value="<?php echo $temp; ?>" aria-valuenow="<?php echo $temp; ?>" class="widget-view loaded">
                                    <div class="graphic-container">
                                        <div class="temp-gauge-container">
                                            <div class="temp-gauge-bg"></div>
                                            <div class="temp-gauge-inner"></div>
                                            <div class="temp-needle" id="temp-widget-needle" style="transform: translate(-50%, -100%) rotate(<?php echo $temp_angle; ?>deg);"></div>
                                        </div>
                                    </div>
                                    <div class="value-container">
                                        <div class="main-value value-unit degrees" id="temp-widget-main-display"><?php echo $temp; ?></div>
                                        <div class="tertiary-value uppercase Value-unit degrees" id="temp-widget-feel-display">Sensación: <?php echo $feels_like; ?></div>
                                    </div>
                                </temp-widget-view>
                            </div>

                            <!--    ****************************************************+
                                    ************** WIDGET DE PUNTO DE ROCÍO *************
                                    ***************************************************** -->
                            <div class="widget" id="dew_point">
                                <div class="title">Punto de Rocío</div>
                                <!--Calcular porcentaje de la gota, inicialmente 0-->
                                <?php $dew = 0;
                                $inner_percent = (100 * $dew) / 49; ?>
                                <dew-point-widget-view data-pws-id="IFUENG27" data-status="connected" data-unit="m" data-temp="<?php echo $temp; ?>" data-dew-point="<?php echo $dew; ?>" data-main-value="<?php echo $dew; ?>" aria-valuenow="<?php echo $dew; ?>" class="widget-view loaded" style="--dewpoint-droplet-width: <?php echo $inner_percent; ?>%;">
                                    <div class="graphic-container">
                                        <div class="dew-container">
                                            <div class="droplet"></div>
                                        </div>
                                    </div>
                                    <div class="value-container">
                                        <div class="main-value value-unit degrees" id="dewpoint-widget-main-display"><?php echo $dew; ?></div>
                                    </div>
                                </dew-point-widget-view>
                            </div>

                            <!--    ****************************************************+
                                    ************* WIDGET DE HUMEDAD EXTERIOR ************
                                    ***************************************************** -->
                            <div class="widget" id="hum_widget">
                                <div class="title">Humedad Exterior</div>
                                <humidity-widget-view data-pws-id="IFUENG27" data-status="connected" data-humidity="<?php echo $humidity; ?>" data-humidity-string="<?php echo $humid_widget; ?>" data-main-value="<?php echo $humidity; ?>" aria-valuenow="<?php echo $humidity; ?>" data-secondary-value="<?php echo $humid_widget; ?>" class="<?php echo $humid_others; ?>">
                                    <div class="graphic-container">
                                        <div class="humidity-gauge-container">
                                            <div class="humidity-gauge-bg" id="humidity-gauge-bg" style="--humidity-gauge-bg: conic-gradient(from 270deg, rgba(var(<?php echo $humidity_color; ?>), 0.8) 0deg, rgba(var(<?php echo $humidity_color; ?>), 0.8) <?php echo $angle_humidity; ?>deg, rgba(var(--black-or-white), 0.1) <?php echo $angle_humidity; ?>deg, rgba(var(--black-or-white), 0.1) 360deg);">
                                            </div>
                                            <div class="humidity-gauge-inner"></div>
                                            <div class="humidity-mist-ring mist-ring-1"></div>
                                            <div class="humidity-mist-ring mist-ring-2"></div>
                                            <div class="humidity-mist-ring mist-ring-3"></div>
                                            <div class="humidity-vapor-particles">
                                                <div class="humidity-vapor-particle"></div>
                                                <div class="humidity-vapor-particle"></div>
                                                <div class="humidity-vapor-particle"></div>
                                                <div class="humidity-vapor-particle"></div>
                                                <div class="humidity-vapor-particle"></div>
                                                <div class="humidity-vapor-particle"></div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="value-container">
                                        <div class="main-value value-unit percent" id="humidity-widget-main-display"><?php echo $humidity; ?></div>
                                        <div class="secondary-value uppercase" id="humidity-widget-text-display"><?php echo $humid_legend; ?></div>
                                        <div class="secondary-value uppercase" id="humidity-widget-humidex"><?php echo $humidex; ?></div>
                                    </div>
                                </humidity-widget-view>
                            </div>


                            <!--    ****************************************************+
                                    ***************** WIDGET DEL VIENTO *****************
                                    ***************************************************** -->
                            <div class="widget" id="wind_widget">
                                <div class="title">Viento</div>
                                <?php
                                function windDirection($degrees)
                                {
                                    // Definimos los 16 rumbos de la rosa de los vientos
                                    $dirs = [
                                        "N",
                                        "NNE",
                                        "NE",
                                        "ENE",
                                        "E",
                                        "ESE",
                                        "SE",
                                        "SSE",
                                        "S",
                                        "SSO",
                                        "SO",
                                        "OSO",
                                        "O",
                                        "ONO",
                                        "NO",
                                        "NNO",
                                    ];
                                    // Cada dirección ocupa 22.5º (360 / 16)
                                    $index = round($degrees / 22.5) % 16;
                                    return $dirs[$index];
                                }
                                $wind_dir = 180; // Direccón inicial del viento: 180º
                                $wind_direction = windDirection($wind_dir);
                                ?>
                                <wind-widget-view id="wind-widget-view" data-pws-id="IFUENG27" data-status="connected" data-unit="m" data-wind-speed="<?php echo $wind; ?>" data-wind-gust="<?php echo $gust; ?>" data-wind-dir="<?php echo $wind_dir; ?>" data-description="gentle" data-main-value="<?php echo $wind; ?>" aria-valuenow="<?php echo $wind; ?>" data-secondary-value="<?php echo $gust; ?>" class="widget-view loaded show-wind">
                                    <div class="graphic-container">
                                        <div class="wind-compass">
                                            <div class="wind-arrow-pointer-wrapper" id="wind-arrow-pointer-wrapper" style="transform: rotate(<?php echo $wind_dir; ?>deg);">
                                                <div class="wind-arrow-pointer"></div>
                                            </div>
                                            <div class="wind-lines" id="wind-widget-lines" style="transform: rotate(<?php echo $wind_dir; ?>deg);">
                                                <div class="wind-line"></div>
                                                <div class="wind-line"></div>
                                                <div class="wind-line"></div>
                                                <div class="wind-line"></div>
                                                <div class="wind-line"></div>
                                                <div class="wind-line"></div>
                                                <div class="wind-line"></div>
                                                <div class="wind-line"></div>
                                                <div class="wind-line"></div>
                                                <div class="wind-line"></div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="value-container">
                                        <div class="main-value value-unit speed" id="wind-widget-main-display"><?php echo $wind; ?></div>
                                        <div class="secondary-value">
                                            <span class="uppercase">Rachas</span> <span id="wind-widget-secondary-display" class="secondary-value value-unit speed uppercase"><?php echo $gust; ?></span>
                                        </div>
                                        <div class="tertiary-value" id="wind-widget-tertiary-value"><?php echo $wind_dir; ?>° <?php echo $wind_direction; ?></div>
                                        <div class="tertiary-value value-unit speed" id="wind-widget-cuaternary-value">Máx: <?php echo $gust_max; ?> </div>
                                    </div>
                                </wind-widget-view>
                            </div>

                            <!--    ****************************************************+
                                    ************** WIDGET DE PRECIPITACIÓN **************
                                    ***************************************************** -->
                            <div class="widget" id="rain-widget">
                                <div class="title">Precipitación</div>
                                <?php
                                $stroke_bucket_top = "transparent";
                                $fill_bucket_top = "transparent";
                                $fill_bucket_bottom = "var(--widget-empty)";
                                $water_start = 440;
                                $daily_rain = 0; //Inicialmente 0mm
                                //if ($daily_rain != 0) {
                                $max_rain = 200; //Máxima cantidad de lluvia que se puede registar en un día.
                                $h_min = 40;
                                $h_max = 440; //Altura máxima en píxeles del pluviómetro
                                // Vamos a calcular la altura del pluviómetro
                                $heigh = ($daily_rain / $max_rain) * ($h_max - $h_min);
                                if ($heigh > 400) {
                                    $heigh = 400;
                                }
                                $water_start = $h_max - $heigh;
                                $stroke_bucket_top = "var(--wu-lightblue20)";
                                $fill_bucket_top = "var(--wu-lightblue)";
                                $fill_bucket_bottom = "var(--wu-lightblue20)";
                                //}
                                ?>
                                <rain-widget-view id="widget_de_lluvia" data-pws-id="IFUENG27" data-status="connected" data-unit="m" data-precip-rate="0" data-precip-total="0" data-main-value="0" aria-valuenow="0" data-secondary-value="0" class="widget-view loaded">
                                    <div class="graphic-container">
                                        <div class="precip-container">
                                            <div class="mini-droplets">
                                                <div class="mini-drop"></div>
                                                <div class="mini-drop"></div>
                                                <div class="mini-drop"></div>
                                            </div>
                                            <div class="precip-bucket">
                                                <svg version="1.1" id="Layer_1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px" title="precipitation" viewBox="0 0 234 482" height="100%" preserveAspectRatio="xMinYMid">
                                                    <ellipse fill="none" stroke="var(--widget-empty)" stroke-width="10" stroke-miterlimit="10" cx="117" cy="39" rx="107" ry="30"></ellipse><!--Boca del pluviómetro-->
                                                    <rect id="precip-bucket-fill" fill="var(--wu-lightblue)" x="12" width="208" y="<?php echo $water_start; ?>" height="<?php echo $heigh; ?>" style="transition: y 0.6s, height 0.6s;"></rect> <!-- Columna de agua del pluviómetro -->
                                                    <ellipse id="precip-bucket-top" stroke="<?php echo $stroke_bucket_top; ?>" stroke-width="5" fill="<?php echo $fill_bucket_top; ?>" cx="117" cy="<?php echo $water_start; ?>" rx="107" ry="30" style="transition: cy 0.6s;"></ellipse> <!-- Parte superior de la columna de agua -->
                                                    <ellipse id="precip-bucket-bottom" fill="<?php echo $fill_bucket_bottom; ?>" cx="117" cy="440" rx="107" ry="30"></ellipse> <!-- Parte inferior de la columna de agua / pluviómetro -->
                                                    <path fill="none" stroke="var(--widget-empty)" stroke-width="10" stroke-miterlimit="10" d="M10,39v394c0,16.6,47.9,40,107,40s107-23.4,107-40V39"></path><!--Cristal exterior del pluviómetro-->
                                                </svg>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="value-container">
                                        <div class="main-value value-unit precip-total" id="rain-widget-main-display"><?php echo $daily_rain; ?></div>
                                        <div class="secondary-value value-unit precip-rate uppercase" id="rain-widget-secondary-display"><?php echo $rain_rate; ?></div>
                                    </div>
                                </rain-widget-view>
                            </div>

                            <!--    ****************************************************+
                                    ***************** WIDGET DE PRESIÓN *****************
                                    ***************************************************** -->
                            <div class="widget" id="pressure_widget">
                                <div class="title">Presión Relativa</div>
                                <pressure-widget-view data-pws-id="IFUENG27" data-status="connected" data-unit="m" data-pressure="<?php echo $pressure; ?>" data-pressure-angle="<?php echo $pres_angle; ?>" data-main-value="<?php echo $pressure; ?>" aria-valuenow="<?php echo $pressure; ?>" class="widget-view loaded">
                                    <div class="graphic-container">
                                        <div class="pressure-container">
                                            <div class="pressure-needle" id="pressure-widget-needle" style="transform: translate(-50%, -100%) rotate(0deg);"></div>
                                        </div>
                                    </div>
                                    <div class="value-container">
                                        <div class="main-value value-unit pressure" id="pressure-widget-main-display"><?php echo $pressure; ?></div>
                                    </div>
                                </pressure-widget-view>
                            </div>

                            <!--    ****************************************************+
                                    **************** WIDGET DE ÍNDICE UV ****************
                                    ***************************************************** -->
                            <div class="widget" id="uvi_widget">
                                <div class="title">Índice UV</div>
                                <?php
                                // Vamos a calcular cuántas barras se colorean
                                // Creamos un array con 13 posiciones, todas inicialmente "empty"
                                $filled = array_fill(1, 13, "empty");
                                // valor que marca hasta dónde se llenan: $uv;
                                $uv = 0; //Inicialmente índice = 0
                                $nivel = $uv;
                                // Recorremos el array
                                for ($i = 1; $i <= 13; $i++) {
                                    if ($i <= $nivel) {
                                        $filled[$i] = "";
                                    } else {
                                        $filled[$i] = "empty";
                                    }
                                }
                                // Vamos calcular el valor de la radiación UV
                                function uvIndexToCategory($uv)
                                {
                                    if ($uv >= 0 && $uv <= 2) {
                                        return "Muy bajo";
                                    } elseif ($uv >= 3 && $uv <= 5) {
                                        return "Moderado";
                                    } elseif ($uv >= 6 && $uv <= 7) {
                                        return "Alto";
                                    } elseif ($uv >= 8 && $uv <= 10) {
                                        return "Muy alto";
                                    } elseif ($uv >= 11) {
                                        return "Extremo";
                                    } else {
                                        return "Valor inválido";
                                    }
                                }
                                $categoria = uvIndexToCategory($uv);
                                ?>
                                <uv-widget-view data-pws-id="IFUENG27" data-status="connected" data-unit="m" data-uv="<?php echo $uv; ?>" data-main-value="<?php echo $uv; ?>" aria-valuenow="<?php echo $uv; ?>" data-secondary-value="<?php echo $uv; ?>" class="widget-view loaded">
                                    <div class="graphic-container">
                                        <div class="pyramid-container" id="uv-widget-pyramid-container">
                                            <svg width="100%" height="100%" viewBox="0 0 162 136" version="1.1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink">
                                                <title>UV</title>
                                                <g id="UV-Index-Triangle" stroke="none" stroke-width="1" fill="none" fill-rule="evenodd">
                                                    <polygon class="<?php echo $filled[13]; ?>" id="Fill-13" points="81.9537723 2.99975159 77.2979826 10.4602611 86.4956236 10.4362484" fill="var(--wu-purple)"></polygon>
                                                    <polygon class="<?php echo $filled[12]; ?>" id="Fill-12" points="92.8108692 20.7694268 70.8323051 20.8356688 76.2650231 12.1248408 87.5102538 12.0925478" fill="var(--wu-purple)"></polygon>
                                                    <polygon class="<?php echo $filled[11]; ?>" id="Fill-11" points="99.1192621 31.0946561 64.3589492 31.2022994 69.7916672 22.4914713 93.8186467 22.4177771" fill="var(--wu-purple)"></polygon>
                                                    <polygon class="<?php echo $filled[10]; ?>" id="Fill-10" points="105.434921 41.428828 57.8945103 41.5778726 63.3272282 32.8670446 100.134305 32.751121" fill="var(--wu-red)"></polygon>
                                                    <polygon class="<?php echo $filled[9]; ?>" id="Fill-9" points="111.751405 51.7620892 51.4218149 51.9450828 56.8627892 43.2334268 106.442533 43.0769299" fill="var(--wu-red)"></polygon>
                                                    <polygon class="<?php echo $filled[8]; ?>" id="Fill-8" points="118.058972 62.0882293 44.9567154 62.3192484 50.3894333 53.6092484 112.758356 53.4105223" fill="var(--wu-red)"></polygon>
                                                    <polygon class="<?php echo $filled[7]; ?>" id="Fill-7" points="124.367447 72.4134586 38.4834421 72.686707 43.9244164 63.975879 119.066832 63.7440318" fill="var(--wu-orange)"></polygon>
                                                    <polygon class="<?php echo $filled[6]; ?>" id="Fill-6" points="130.17996 81.9276369 32.5388267 82.2331783 37.4513908 74.3512038 125.38216 74.0696752" fill="var(--wu-orange)"></polygon>
                                                    <polygon class="<?php echo $filled[5]; ?>" id="Fill-5" points="136.495618 92.2528662 26.0661313 92.6006369 31.4988492 83.8889809 131.195003 83.5759873" fill="var(--wu-yellow)"></polygon>
                                                    <polygon class="<?php echo $filled[4]; ?>" id="Fill-4" points="142.804011 102.58621 19.6010318 102.96793 25.0337497 94.2562739 137.503396 93.9093312" fill="var(--wu-yellow)"></polygon>
                                                    <polygon class="<?php echo $filled[3]; ?>" id="Fill-3" points="149.111661 112.912268 13.1285841 113.342841 18.5613021 104.632013 143.819302 104.242013" fill="var(--wu-yellow)"></polygon>
                                                    <polygon class="<?php echo $filled[2]; ?>" id="Fill-2" points="155.427732 123.23758 6.66373231 123.717834 12.0964503 115.007006 150.127117 114.560701" fill="var(--wu-green)"></polygon>
                                                    <polygon class="<?php echo $filled[1]; ?>" id="Fill-1" points="5.62342462 125.373554 0.999834872 132.792662 161.264189 132.792662 156.435014 124.893299" fill="var(--wu-green)"></polygon>
                                                </g>
                                            </svg>
                                        </div>
                                    </div>
                                    <div class="value-container">
                                        <div class="main-value value-unit" id="uv-widget-main-display"><?php echo $uv; ?></div>
                                        <div class="secondary-value uppercase" id="uv-widget-secondary-display"><?php echo $categoria; ?></div>
                                    </div>
                                </uv-widget-view>
                            </div>

                            <!--    ****************************************************+
                                    ************* WIDGET DE RADIACIÓN SOLAR *************
                                    ***************************************************** -->
                            <div class="widget" id="solar_widget">
                                <div class="title">Radiación Solar</div>
                                <solar-radiation-widget-view data-pws-id="IFUENG27" data-status="connected" data-unit="m" data-solar-radiation="<?php echo $solar; ?>" data-main-value="<?php echo $solar; ?>" aria-valuenow="<?php echo $solar; ?>" data-secondary-value="<?php echo $solar; ?>" class="widget-view loaded">
                                    <div class="graphic-container">
                                        <div class="circle-container">
                                            <div class="inner-circle" id="solar-radiation-widget-inner-circle" style="width: <?php echo $percentage; ?>%;"></div>
                                            <div class="circle-ring ring-1 show" id="solar-radiation-widget-ring" style="width: <?php echo $percentage; ?>%;"></div>
                                        </div>
                                    </div>
                                    <div class="value-container">
                                        <div class="main-value value-unit radiation" id="solar-radiation-widget-main-display"><?php echo $solar; ?></div>
                                    </div>
                                </solar-radiation-widget-view>
                            </div>

                            <!--    ****************************************************+
                                    ********** WIDGET DE TEMPERATURA INTERIOR ***********
                                    ***************************************************** -->
                            <div class="widget" id="tempint_widget">
                                <?php
                                // Calcular la posición de la aguja de la temperatura interior
                                // Usamos los mismos valores de máximos y mínimos que para calcular la posición de la aguja de la temperatura exterior.
                                $in_temp = 0; // Inicialmente 0º
                                $in_temp_angle =
                                    (($in_temp - $minTemp) * ($maxAngle - $minAngle)) / ($maxTemp - $minTemp) + $minAngle;
                                // Limitamos a los extremos
                                if ($temp_angle < $minAngle) {
                                    $temp_angle = $minAngle;
                                }
                                if ($temp_angle > $maxAngle) {
                                    $temp_angle = $maxAngle;
                                }
                                ?>
                                <div class="title">Temperatura Interior</div>
                                <temp-widget-view data-pws-id="IFUENG27" data-status="connected" data-unit="m" data-temp="<?php echo $in_temp; ?>" data-temp-angle="<?php echo $in_temp_angle; ?>" data-main-value="<?php echo $in_temp; ?>" aria-valuenow="<?php echo $in_temp; ?>" class="widget-view loaded">
                                    <div class="graphic-container">
                                        <div class="temp-gauge-container">
                                            <div class="temp-gauge-bg"></div>
                                            <div class="temp-gauge-inner"></div>
                                            <div class="temp-needle" id="temp-int-widget-needle" style="transform: translate(-50%, -100%) rotate(<?php echo $in_temp_angle; ?>deg);"></div>
                                        </div>
                                    </div>
                                    <div class="value-container">
                                        <div class="main-value value-unit degrees" id="temp-int-widget-main-display"><?php echo $in_temp; ?></div>
                                    </div>
                                </temp-widget-view>
                            </div>

                            <!--    ****************************************************+
                                    ************ WIDGET DE HUMEDAD INTERIOR *************
                                    ***************************************************** -->
                            <div class="widget" id="humint_widget">
                                <div class="title">Humedad Interior</div>
                                <?php
                                // Calcular el ángulo del sector rellenado
                                $in_humidity = 0; //Inicialmente 0%
                                $in_angle_humidity = 360 * ($in_humidity / 100);
                                // Determinar estado (colores) en base a la humedad
                                if ($in_humidity < 35) {
                                    $in_humid_state = "dry";
                                    $in_humid_legend = "Seco";
                                } elseif ($in_humidity >= 60) {
                                    $in_humid_state = "humid";
                                    $in_humid_legend = "Húmedo";
                                } else {
                                    $in_humid_state = "comfortable";
                                    $in_humid_legend = "Confortable";
                                }
                                // Variables dependientes del estado
                                $in_humidity_color = "--humidity-{$in_humid_state}-color";
                                $in_humid_widget = $in_humid_state;
                                $in_humid_others = "widget-view {$in_humid_state} loaded";
                                ?>
                                <humidity-int-widget-view data-pws-id="IFUENG27" data-status="connected" data-humidity="<?php echo $in_humidity; ?>" data-humidity-string="<?php echo $in_humid_widget; ?>" data-main-value="<?php echo $in_humidity; ?>" aria-valuenow="<?php echo $in_humidity; ?>" data-secondary-value="<?php echo $in_humid_widget; ?>" class="<?php echo $in_humid_others; ?>">
                                    <div class="graphic-container">
                                        <div class="humidity-int-gauge-container">
                                            <div class="humidity-int-gauge-bg" id="humidity-int-gauge-bg" style="--humidity-int-gauge-bg: conic-gradient(from 270deg, rgba(var(<?php echo $in_humidity_color; ?>), 0.8) 0deg, rgba(var(<?php echo $in_humidity_color; ?>), 0.8) <?php echo $in_angle_humidity; ?>deg, rgba(var(--black-or-white), 0.1) <?php echo $in_angle_humidity; ?>deg, rgba(var(--black-or-white), 0.1) 360deg);">
                                            </div>
                                            <div class="humidity-int-gauge-inner"></div>
                                            <div class="humidity-int-mist-ring mist-ring-1"></div>
                                            <div class="humidity-int-mist-ring mist-ring-2"></div>
                                            <div class="humidity-int-mist-ring mist-ring-3"></div>
                                            <div class="humidity-int-vapor-particles">
                                                <div class="humidity-int-vapor-particle"></div>
                                                <div class="humidity-int-vapor-particle"></div>
                                                <div class="humidity-int-vapor-particle"></div>
                                                <div class="humidity-int-vapor-particle"></div>
                                                <div class="humidity-int-vapor-particle"></div>
                                                <div class="humidity-int-vapor-particle"></div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="value-container">
                                        <div class="main-value value-unit percent" id="humidity-int-widget-main-display"><?php echo $in_humidity; ?></div>
                                        <div class="secondary-value uppercase" id="humidity-int-widget-text-display"><?php echo $in_humid_legend; ?></div>
                                    </div>
                                </humidity-int-widget-view>
                            </div>

                            <!--    ****************************************************+
                                    **************** WIDGET DE SEEING ******************
                                    ***************************************************** -->
                            <div class="widget" id="seeing">
                                <div class="title">Seeing</div>
                                <seeing-widget-view id="seeing-widget-view" data-pws-id="IFUENG27" data-status="connected" data-unit="" class="widget-view loaded show-wind">
                                    <div class="graphic-container">
                                        <div class="svg-container">
                                            <svg viewBox="0 0 1190 1706" xmlns="http://www.w3.org/2000/svg" style="width: 100%; height: auto;">
                                                <g id="stars"></g>
                                                <g id="Telescopio">
                                                    <path id="Trazado" fill="#777777" stroke="none" d="M 434.399994 10.133301 C 430.399994 16.266724 422.533325 28.93335 416.799988 38.266724 C 411.066681 47.733276 406.133331 55.333374 405.733337 55.333374
                                                                                                       C 405.466675 55.333374 394.533325 46.800049 381.600006 36.266724 C 352.666656 12.800049 352.399994 12.666626 347.200012 12.666626
                                                                                                       C 342.666656 12.666626 332.799988 20.133301 304.533325 44.93335 C 271.600006 73.733276 240.266663 112.133301 216.133331 153.199951
                                                                                                       C 205.333328 171.599976 190.666672 201.599976 190.666672 205.333374 C 190.666672 207.333374 192.399994 210.533325 195.066666 213.199951
                                                                                                       C 197.333328 215.733276 267.733337 272.800049 351.333344 340.133301 C 434.933319 407.333374 505.333344 464 507.600006 466.133301 L 512 469.866699
                                                                                                       L 508.666656 473.733276 C 506.799988 476 505.333344 478.266724 505.333344 479.06665 C 505.333344 479.733276 510.666656 484.666626 517.333313 490.133301
                                                                                                       C 523.866638 495.599976 529.333313 500.266724 529.333313 500.666626 C 529.333313 501.06665 526.266663 505.199951 522.400024 509.866699
                                                                                                       C 515.599976 518.266724 515.599976 518.400024 518.400024 520.533325 C 528.533325 528.400024 550.533325 546.800049 550.666687 547.466675
                                                                                                       C 550.666687 547.866699 521.200012 583.733276 485.200012 627.333374 L 419.600006 706.400024 L 423.466675 709.866638
                                                                                                       C 425.600006 711.733337 429.333344 714.93335 431.733337 716.93335 C 434.799988 719.466675 435.866669 721.200012 435.066681 722.533325
                                                                                                       C 434.533325 723.466675 414.799988 746.93335 391.333344 774.533325 C 365.333344 805.200012 348.133331 824.400024 347.066681 824
                                                                                                       C 346.133331 823.599976 344.533325 822.93335 343.466675 822.533325 C 342.266663 822.133362 338.933319 825.200012 334 831.200012
                                                                                                       C 329.733337 836.400024 326 840.666687 325.466675 840.666687 C 325.066681 840.666687 320.266663 837.06665 315.066681 832.799988
                                                                                                       C 303.200012 823.06665 298.399994 821.333313 291.200012 823.599976 C 286.933319 825.06665 282.933319 829.06665 268.266663 846.666687
                                                                                                       C 258.399994 858.400024 249.866669 869.333313 249.199997 871.06665 C 247.333328 876 247.866669 883.733337 250.399994 887.599976
                                                                                                       C 251.600006 889.599976 258 895.466675 264.399994 900.666687 L 276.133331 910.266663 L 273.466675 913.200012 C 271.333344 915.333313 269.600006 916 266.666656 915.599976
                                                                                                       C 262 914.93335 260 918.133362 262.399994 922.133362 C 263.333344 923.466675 264 926.133362 264 928 C 264 929.733337 265.600006 933.06665 267.733337 935.333313
                                                                                                       C 270.799988 938.799988 272.799988 939.733337 280.399994 940.799988 C 285.466675 941.466675 290.133331 941.733337 290.799988 941.200012
                                                                                                       C 292.399994 940.266663 292.399994 935.733337 290.666656 934.666687 C 288.666656 933.466675 289.066681 932 291.866669 928.533325 L 294.399994 925.333313
                                                                                                       L 307.466675 936.133362 C 314.799988 942 322 947.599976 323.600006 948.400024 C 327.866669 950.666687 336.133331 950.266663 340.133331 947.599976
                                                                                                       C 344 945.06665 375.333344 907.599976 378.133331 902.133362 C 382.799988 893.333313 379.333344 885.466675 366 874.666687
                                                                                                       C 360.933319 870.400024 356.533325 866.93335 356.399994 866.799988 C 356.133331 866.533325 359.600006 862.133362 364.133331 856.93335
                                                                                                       L 372.133331 847.333313 L 369.333344 843.333313 L 366.399994 839.200012 L 373.733337 830.266663 C 392.399994 807.200012 452.266663 736 453.200012 735.599976
                                                                                                       C 453.733337 735.466675 457.333344 737.733337 461.066681 740.666687 L 467.866669 746.133362 L 483.466675 727.333313 C 492.133331 717.06665 499.466675 708.666687 500 708.666687
                                                                                                       C 501.066681 708.666687 520.133362 721.599976 522.133362 723.733337 C 523.200012 724.93335 522.93335 736.400024 520.799988 767.333313 L 518 809.333313 L 493.333344 810
                                                                                                       L 468.666656 810.666687 L 468.266663 839.866638 L 468 869.06665 L 464 865.333313 L 460.133331 861.599976 L 446.399994 863.200012
                                                                                                       C 438.799988 864.133362 432.266663 865.333313 431.733337 865.733337 C 431.333344 866.266663 381.600006 960.533325 321.333344 1075.333252
                                                                                                       C 260.933319 1190.133301 165.46666 1371.733398 109.066666 1479.06665 C 50.266666 1591.06665 5.2 1678.400024 3.333333 1684 C 1.466667 1689.333374 0 1695.599976 0 1697.733276
                                                                                                       C 0 1702.266724 3.6 1706 8.133333 1706 C 13.333333 1706 24 1700.133301 33.466667 1692.266724 C 41.866665 1685.06665 48.400002 1674.800049 272 1318.800049 L 502 952.666687
                                                                                                       L 517.599976 952.666687 C 526.266663 952.666687 533.333313 953.06665 533.333313 953.466675 C 533.333313 956.133362 504 1366.133301 503.466675 1370.266602
                                                                                                       C 503.066681 1374 503.733337 1376.533325 505.866669 1379.599976 C 511.333344 1387.866699 519.333313 1387.733398 525.599976 1379.333374
                                                                                                       C 527.466675 1376.800049 536.93335 1326.666626 565.333313 1169.333252 C 585.866638 1055.599976 603.06665 960.400024 603.466675 957.599976 L 604.400024 952.666687
                                                                                                       L 621.466675 952.666687 C 638.133362 952.666687 640.266663 953.200012 637.200012 956.266663 C 636.266663 957.200012 683.866638 1056 802.533325 1298.800049 L 969.333313 1640
                                                                                                       L 980.400024 1650.533325 C 996 1665.466675 1002.533325 1668.93335 1006.533325 1664.133301 C 1008.666687 1661.599976 1010.666687 1642.800049 1010.666687 1624.266724
                                                                                                       L 1010.666687 1608.666626 L 906.799988 1375.599976 C 688.93335 886.93335 700.799988 913.333313 698.133362 913.733337 C 696.799988 914 691.200012 913.200012 685.866638 912
                                                                                                       L 676 909.866638 L 675.733337 860.266663 L 675.333313 810.666687 L 650.400024 810.266663 L 625.599976 810 L 625.06665 802.93335
                                                                                                       C 624.799988 799.200012 624.666687 795.733337 624.799988 795.466675 C 625.06665 795.333313 627.333313 796.666687 629.866638 798.666687
                                                                                                       C 632.400024 800.666687 635.06665 802 635.866638 801.733337 C 636.533325 801.599976 640.133362 797.599976 644 792.93335
                                                                                                       C 647.733337 788.400024 651.466675 784.666687 652.133362 784.666687 C 652.799988 784.666687 661.200012 790.93335 670.799988 798.666687 L 688.133362 812.533325
                                                                                                       L 691.733337 808.266663 C 699.599976 799.06665 700.533325 801.06665 680.666687 785.333313 L 662.533325 771.06665 L 668.266663 764.400024
                                                                                                       C 679.333313 751.466675 679.333313 751.466675 676.533325 749.200012 C 675.06665 748.133362 662 738.533325 647.333313 727.733337
                                                                                                       C 632.666687 716.93335 617.06665 704.533325 612.666687 700 C 607.466675 694.799988 600.93335 690 594 686.400024 C 588.133362 683.333313 578.400024 677.199951 572.400024 672.800049
                                                                                                       C 561.733337 664.93335 561.599976 664.800049 562.93335 660.93335 C 564.93335 655.866699 563.333313 648.400024 559.333313 644.133301 L 556 640.533325 L 559.599976 636
                                                                                                       C 561.733337 633.333374 563.733337 631.866699 564.666687 632.533325 C 565.333313 633.06665 607.866638 664.93335 659.06665 703.200012 L 752.266663 772.799988 L 750.133362 776
                                                                                                       C 748.93335 777.866638 747.06665 780.799988 745.866638 782.666687 C 744.666687 784.400024 743.733337 788.266663 743.733337 790.93335
                                                                                                       C 743.733337 795.200012 744.666687 796.799988 749.200012 800.799988 C 760 810.400024 768.533325 812 774.400024 805.599976
                                                                                                       C 775.866638 804 781.866638 795.733337 787.866638 787.333313 C 802.666687 766.400024 802.533325 762.133362 787.599976 752.533325
                                                                                                       C 776.93335 745.599976 773.599976 746.133362 765.200012 755.466675 L 759.06665 762.266663 L 665.599976 692.533325 C 614.133362 654.266724 572 622.533325 572 622
                                                                                                       C 572 620.666626 598.93335 588.666626 600.133362 588.666626 C 600.666687 588.666626 610 596 620.799988 604.800049 C 636.93335 618.133301 640.666687 620.666626 641.866638 619.06665
                                                                                                       C 646.93335 612.266724 653.866638 604.666626 654.799988 604.666626 C 655.466675 604.666626 661.200012 608.666626 667.466675 613.599976 L 679.06665 622.533325 L 682.533325 618.266724
                                                                                                       C 686.666687 613.06665 687.866638 612.93335 692 617.333374 C 693.733337 619.199951 695.866638 620.533325 696.799988 620.133301
                                                                                                       C 697.599976 619.866699 771.466675 678.533325 860.93335 750.533325 C 950.400024 822.533325 1024.93335 882.533325 1026.666626 883.733337
                                                                                                       C 1028.533325 884.93335 1031.466675 886 1033.333374 886 C 1037.199951 886 1067.599976 866.400024 1084.266724 853.200012
                                                                                                       C 1128.800049 818.133362 1159.333374 776.93335 1179.199951 725.333313 C 1182.666626 716.533325 1186.666626 706.266663 1188 702.666687
                                                                                                       C 1191.599976 693.466675 1191.333374 690.666687 1186.400024 685.466675 C 1184 682.93335 1139.466675 646.533325 1087.333374 604.666626
                                                                                                       C 894 449.199951 866.400024 426.666626 865.466675 424.533325 C 864.400024 421.599976 841.333313 403.199951 840 404 C 839.333313 404.400024 823.06665 392 803.733337 376.400024
                                                                                                       C 784.400024 360.800049 752.93335 335.333374 733.733337 320 C 709.200012 300.266724 699.333313 291.599976 700.266663 290.400024
                                                                                                       C 701.06665 289.333374 697.599976 285.733276 688.666687 278.533325 C 679.333313 271.06665 675.466675 268.666626 674.666687 269.866699
                                                                                                       C 674 271.06665 661.200012 261.599976 630.266663 236.533325 L 586.799988 201.466675 L 590.799988 196.133301 L 594.799988 190.666626 L 588.133362 184.666626
                                                                                                       C 584.400024 181.333374 581.333313 178.266724 581.333313 177.733276 C 581.333313 177.199951 583.866638 172.800049 586.799988 168
                                                                                                       C 592.133362 159.599976 592.400024 159.333374 594.93335 161.599976 C 597.200012 163.599976 598 163.599976 601.733337 162.133301
                                                                                                       C 604.133362 161.199951 606.93335 160.133301 608.133362 159.733276 C 609.333313 159.466675 628 173.466675 657.733337 197.199951 L 705.333313 235.333374 L 712 232
                                                                                                       C 724.133362 225.866699 732 215.199951 734.133362 202.133301 L 734.93335 197.06665 L 686.799988 158.800049 L 638.666687 120.400024 L 638.93335 115.466675 L 639.333313 110.400024
                                                                                                       L 584.666687 66.666626 C 554.666687 42.666626 529.333313 22.400024 528.400024 21.733276 C 524.400024 18.666626 504.133331 33.866699 495.466675 46.266724
                                                                                                       C 490.799988 53.199951 483.733337 70.93335 484.933319 72.93335 C 485.200012 73.466675 498.266663 84.266724 514 96.800049 C 529.733337 109.333374 542.666687 120 542.666687 120.400024
                                                                                                       C 542.666687 121.466675 529.866638 132.666626 528.666687 132.666626 C 528.266663 132.666626 525.466675 130.800049 522.666687 128.533325 L 517.466675 124.266724
                                                                                                       L 510.933319 129.733276 L 504.399994 135.199951 L 479.466675 115.06665 C 465.600006 104 454.133331 94.133301 453.866669 93.333374
                                                                                                       C 453.466675 92.533325 460.799988 78.666626 470 62.533325 C 479.466675 46 486.666656 31.866699 486.666656 29.866699 C 486.666656 24 482.666656 14.533325 478.399994 10.133301
                                                                                                       C 471.600006 3.199951 464.799988 0.400024 452.533325 -0.400024 L 441.466675 -1.06665 L 434.399994 10.133301 Z M 567.06665 140.800049
                                                                                                       C 576 148.266724 583.599976 154.533325 583.866638 154.800049 C 584.533325 155.333374 574.666687 171.333374 573.599976 171.333374
                                                                                                       C 572.799988 171.333374 541.866638 145.466675 536.666687 140.400024 C 535.733337 139.599976 537.599976 137.199951 542 133.333374
                                                                                                       C 545.733337 130.133301 549.06665 127.466675 549.733337 127.333374 C 550.266663 127.333374 558 133.466675 567.06665 140.800049 Z"/>
                                                </g>
                                            </svg>
                                        </div>
                                    </div>
                                    <div class="value-container">
                                        <div class="tertiary-value" id="seeing-description">Vis:</div>
                                    </div>
                                </seeing-widget-view>
                            </div>
                        </div>

                        <!-- ############################################################
                        <!-- ################### GRÁFICAS MODALES #######################
                             ############################################################ -->

                        <!--*************************************************************
                            ***************** GRÁFICA DE TEMPERATURA *******************
                            *********************** M O D A L **************************
                            ************************************************************ -->
                        <!-- Modal oculto al inicio -->
                        <div id="tempModal" class="modal">
                            <div class="modal-content">
                                <button class="close" id="closeModal" aria-label="Cerrar">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke-width="2">
                                        <line x1="18" y1="6" x2="6" y2="18"/>
                                        <line x1="6" y1="6" x2="18" y2="18"/>
                                    </svg>
                                </button>
                                <div class="date-range-picker">
                                    <div class="date-input">
                                        <label for="temp_startDate">Desde:</label>
                                        <input type="datetime-local" id="temp_startDate" name="temp_startDate">
                                    </div>
                                    <div class="date-input">
                                        <label for="temp_endDate">Hasta:</label>
                                        <input type="datetime-local" id="temp_endDate" name="temp_endDate">
                                    </div>
                                    <button id="temp_updateChartBtn" class="update-button">Actualizar Gráfico</button>
                                </div>
                                <h2>Evolución de la Temperatura Exterior</h2>
                                <div id="tempChart" style="height:400px;"></div>
                            </div>
                        </div>

                        <!--*************************************************************
                            ******************* GRÁFICA DE HUMEDAD **********************
                            *********************** M O D A L **************************
                            ************************************************************ -->
                        <!-- Modal oculto al inicio -->
                        <div id="humModal" class="modal">
                            <div class="modal-content">
                                <button class="close" id="closeHumModal" aria-label="Cerrar">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke-width="2">
                                        <line x1="18" y1="6" x2="6" y2="18"/>
                                        <line x1="6" y1="6" x2="18" y2="18"/>
                                    </svg>
                                </button>
                                <div class="date-range-picker">
                                    <div class="date-input">
                                        <label for="hum_startDate">Desde:</label>
                                        <input type="datetime-local" id="hum_startDate" name="hum_startDate">
                                    </div>
                                    <div class="date-input">
                                        <label for="hum_endDate">Hasta:</label>
                                        <input type="datetime-local" id="hum_endDate" name="hum_endDate">
                                    </div>
                                    <button id="hum_updateChartBtn" class="update-button">Actualizar Gráfico</button>
                                </div>
                                <h2>Evolución de la Humedad Exterior</h2>
                                <div id="humChart" style="height:400px;"></div>
                            </div>
                        </div>

                        <!--*************************************************************
                            ****************** GRÁFICAS DEl VIENTO *********************
                            *********************** M O D A L **************************
                            ************************************************************ -->
                        <!-- Modal oculto al inicio -->
                        <div id="windModal" class="modal">
                            <div class="modal-content">
                                <button class="close" id="closeWindModal" aria-label="Cerrar">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <line x1="18" y1="6" x2="6" y2="18"></line>
                                        <line x1="6" y1="6" x2="18" y2="18"></line>
                                    </svg>
                                </button>
                                <div class="date-range-picker">
                                    <div class="date-input">
                                        <label for="wind_startDate">Desde:</label>
                                        <input type="datetime-local" id="wind_startDate" name="wind_startDate">
                                    </div>
                                    <div class="date-input">
                                        <label for="wind_endDate">Hasta:</label>
                                        <input type="datetime-local" id="wind_endDate" name="wind_endDate">
                                    </div>
                                    <button id="wind_updateChartBtn" class="update-button">Actualizar Gráfico</button>
                                </div>
                                <h2>Viento</h2>
                                <div id="windSpeedChart" style="height: 250px; margin-bottom: 20px;"></div>
                                <div id="windDirectionChart" style="height: 250px;"></div>
                            </div>
                        </div>

                        <!--*************************************************************
                            ********************* DATOS DE LLUVIA ***********************
                            *********************** M O D A L ***************************
                            ************************************************************* -->
                        <!-- Modal oculto por defecto -->
                        <div id="rain-modal" class="modal">
                            <div class="modal-content">
                                <button class="close" id="closeRainModal" aria-label="Cerrar">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <line x1="18" y1="6" x2="6" y2="18"></line>
                                        <line x1="6" y1="6" x2="18" y2="18"></line>
                                    </svg>
                                </button>
                                <h2>Desglose de Precipitación</h2>

                                <div class="rain-stats-grid">
                                    <div class="stat-card">
                                        <span class="stat-label">Estado</span>
                                        <span class="stat-value" id="rain-status">...</span>
                                    </div>
                                    <div class="stat-card">
                                        <span class="stat-label">Ratio</span>
                                        <span class="stat-value" id="rain-rate">...</span>
                                    </div>
                                    <div class="stat-card">
                                        <span class="stat-label">Hoy</span>
                                        <span class="stat-value" id="rain-today">...</span>
                                    </div>
                                    <div class="stat-card">
                                        <span class="stat-label">Última Hora</span>
                                        <span class="stat-value" id="rain-hour">...</span>
                                    </div>
                                    <div class="stat-card">
                                        <span class="stat-label">Este Mes</span>
                                        <span class="stat-value" id="rain-month">...</span>
                                    </div>
                                    <div class="stat-card">
                                        <span class="stat-label">Total</span>
                                        <span class="stat-value" id="rain-total">...</span>
                                    </div>
                                </div>
                                <div class="date-range-picker">
                                    <div class="date-input">
                                        <label for="rain_startMonth">Mes Inicio:</label>
                                        <input type="month" id="rain_startMonth" name="rain_startMonth">
                                    </div>
                                    <div class="date-input">
                                        <label for="rain_endMonth">Mes Fin:</label>
                                        <input type="month" id="rain_endMonth" name="rain_endMonth">
                                    </div>
                                    <button id="rain_updateChartBtn" class="update-button">Actualizar Gráfico</button>
                                </div>
                                <div id="rain-month-chart" style="height:300px; margin-top:20px;"></div>
                            </div>
                        </div>

                        <!--*************************************************************
                            ******************* GRÁFICA DE PRESIÓN *********************
                            *********************** M O D A L ***************************
                            ************************************************************* -->
                        <!-- Modal oculto por defecto -->
                        <div id="pressureModal" class="modal">
                            <div class="modal-content">
                                <button class="close" id="closePressureModal" aria-label="Cerrar">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <line x1="18" y1="6" x2="6" y2="18"></line>
                                        <line x1="6" y1="6" x2="18" y2="18"></line>
                                    </svg>
                                </button>

                                <div class="date-range-picker">
                                    <div class="date-input">
                                        <label for="pressure_startDate">Desde:</label>
                                        <input type="datetime-local" id="pressure_startDate" name="pressure_startDate">
                                    </div>
                                    <div class="date-input">
                                        <label for="pressure_endDate">Hasta:</label>
                                        <input type="datetime-local" id="pressure_endDate" name="pressure_endDate">
                                    </div>
                                    <button id="pressure_updateChartBtn" class="update-button">Actualizar Gráfico</button>
                                </div>
                                <h2>Evolución de la Presión Relativa</h2>
                                <div id="pressureChart" style="height:400px;"></div>
                            </div>
                        </div>

                        <!--*************************************************************
                            ******** GRÁFICA DE RADIACIÓN SOLAR E INDICE UV**************
                            *********************** M O D A L ***************************
                            ************************************************************* -->
                        <!-- Modal oculto por defecto -->
                        <div id="uvSolarModal" class="modal">
                            <div class="modal-content">
                                <button class="close" id="closeUvSolarModal" aria-label="Cerrar">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <line x1="18" y1="6" x2="6" y2="18"></line>
                                        <line x1="6" y1="6" x2="18" y2="18"></line>
                                    </svg>
                                </button>

                                <div class="date-range-picker">
                                    <div class="date-input">
                                        <label for="uv_startDate">Desde:</label>
                                        <input type="datetime-local" id="uv_startDate" name="uv_startDate">
                                    </div>
                                    <div class="date-input">
                                        <label for="uv_endDate">Hasta:</label>
                                        <input type="datetime-local" id="uv_endDate" name="uv_endDate">
                                    </div>
                                    <button id="uv_updateChartBtn" class="update-button">Actualizar Gráfico</button>
                                </div>
                                <h2>Índice UV y Radiación Solar</h2>
                                <div id="uvChart" style="height: 250px; margin-bottom: 20px;"></div>
                                <div id="solarChart" style="height: 250px;"></div>
                            </div>
                        </div>

                        <!--************************************************************
                            ************ GRÁFICA DE TEMPERATURA INTERIOR ***************
                            *********************** M O D A L **************************
                            ************************************************************ -->
                        <!-- Modal oculto al inicio -->
                        <div id="tempIntModal" class="modal">
                            <div class="modal-content">
                                <button class="close" id="closeTempIntModal" aria-label="Cerrar">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke-width="2">
                                        <line x1="18" y1="6" x2="6" y2="18"/>
                                        <line x1="6" y1="6" x2="18" y2="18"/>
                                    </svg>
                                </button>
                                <div class="date-range-picker">
                                    <div class="date-input">
                                        <label for="startDate">Desde:</label>
                                        <input type="datetime-local" id="startDate" name="startDate">
                                    </div>
                                    <div class="date-input">
                                        <label for="endDate">Hasta:</label>
                                        <input type="datetime-local" id="endDate" name="endDate">
                                    </div>
                                    <button id="updateChartBtn" class="update-button">Actualizar Gráfico</button>
                                </div>
                                <h2>Evolución de la Temperatura Interior</h2>
                                <div id="tempIntChart" style="height:400px;"></div>
                            </div>
                        </div>

                        <!--************************************************************
                            ************** GRÁFICA DE HUMEDAD INTERIOR *****************
                            *********************** M O D A L **************************
                            ************************************************************ -->
                        <!-- Modal oculto al inicio -->
                        <div id="humIntModal" class="modal">
                            <div class="modal-content">
                                <button class="close" id="closeHumIntModal" aria-label="Cerrar">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke-width="2">
                                        <line x1="18" y1="6" x2="6" y2="18"/>
                                        <line x1="6" y1="6" x2="18" y2="18"/>
                                    </svg>
                                </button>

                                <div class="date-range-picker">
                                    <div class="date-input">
                                        <label for="humInt_startDate">Desde:</label>
                                        <input type="datetime-local" id="humInt_startDate" name="humInt_startDate">
                                    </div>
                                    <div class="date-input">
                                        <label for="humInt_endDate">Hasta:</label>
                                        <input type="datetime-local" id="humInt_endDate" name="humInt_endDate">
                                    </div>
                                    <button id="humInt_updateChartBtn" class="update-button">Actualizar Gráfico</button>
                                </div>
                                <h2>Evolución de la Humedad Interior</h2>
                                <div id="humIntChart" style="height:400px;"></div>
                            </div>
                        </div>

                        <!--************************************************************
                            ******************** DATOS DEL SEEING **********************
                            *********************** M O D A L **************************
                            ************************************************************ -->
                        <!-- Modal oculto por defecto -->
                        <div id="seeingModal" class="modal">
                            <div class="modal-content">
                                <button class="close" aria-label="Cerrar" id="closeSeeingModal">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none"
                                         stroke-width="3" stroke-linecap="round" stroke-linejoin="round">
                                        <line x1="18" y1="6" x2="6" y2="18"></line>
                                        <line x1="6" y1="6" x2="18" y2="18"></line>
                                    </svg>
                                </button>
                                <div class="infografia">
                                    <h1 class="seeing-modal-title">🌠 Datos del Seeing Astronómico</h1>
                                    <h2 class="seeing-group-title">Datos de Superficie</h2>
                                    <div class="bloque">
                                        <div class="card">
                                            <h3 class="seeing-card-title">🌡️ Variación térmica</h3>
                                            <p class="seeing-card-value" id="t8h">-</p>
                                            <span class="seeing-card-desc">ºC (Últimas 8h)</span>
                                        </div>
                                        <div class="card">
                                            <h3 class="seeing-card-title">💧 Variación de humedad</h3>
                                            <p class="seeing-card-value" id="h8h">-</p>
                                            <span class="seeing-card-desc">% (Últimas 8h)</span>
                                        </div>
                                        <div class="card">
                                            <h3 class="seeing-card-title">🌬️ Viento actual</h3>
                                            <p class="seeing-card-value" id="wnow">-</p>
                                            <span class="seeing-card-desc">Km/h</span>
                                        </div>
                                        <div class="card">
                                            <h3 class="seeing-card-title">🌬️ Racha de viento</h3>
                                            <p class="seeing-card-value" id="gnow">-</p>
                                            <span class="seeing-card-desc">Km/h</span>
                                        </div>
                                        <div class="card">
                                            <h3 class="seeing-card-title">📉 Variación de presión</h3>
                                            <p class="seeing-card-value" id="p8h">-</p>
                                            <span class="seeing-card-desc">hPa (Últimas 8h)</span>
                                        </div>
                                        <div class="card">
                                            <h3 class="seeing-card-title">☀️ Radiación solar</h3>
                                            <p class="seeing-card-value" id="rs">-</p>
                                            <span class="seeing-card-desc">W/m²</span>
                                        </div>
                                    </div>
                                    <h2 class="seeing-group-title">Datos en Altura</h2>
                                    <div class="bloque">
                                        <div class="card">
                                            <h3 class="seeing-card-title">🌀 Temp. a 500 hPa</h3>
                                            <p class="seeing-card-value" id="t500">-</p>
                                            <span class="seeing-card-desc">ºC</span>
                                        </div>
                                        <div class="card">
                                            <h3 class="seeing-card-title">🌀 Temp. a 300 hPa</h3>
                                            <p class="seeing-card-value" id="t300">-</p>
                                            <span class="seeing-card-desc">ºC</span>
                                        </div>
                                        <div class="card">
                                            <h3 class="seeing-card-title">💨 Viento a 500 hPa</h3>
                                            <p class="seeing-card-value" id="w500">-</p>
                                            <span class="seeing-card-desc">Km/h</span>
                                        </div>
                                        <div class="card">
                                            <h3 class="seeing-card-title">💨 Viento a 300 hPa</h3>
                                            <p class="seeing-card-value" id="w300">-</p>
                                            <span class="seeing-card-desc">Km/h</span>
                                        </div>
                                        <div class="card">
                                            <h3 class="seeing-card-title">🌪️ Shear vertical</h3>
                                            <p class="seeing-card-value" id="shear">-</p>
                                            <span class="seeing-card-desc">(Turbulencia)</span>
                                        </div>
                                        <div class="card">
                                            <h3 class="seeing-card-title">📊 DeltaT</h3>
                                            <p class="seeing-card-value" id="deltaT">-</p>
                                            <span class="seeing-card-desc">(Estabilidad)</span>
                                        </div>
                                    </div>

                                    <h2 class="seeing-group-title">Cobertura de Nubes</h2>
                                    <div class="bloque">
                                        <div class="card">
                                            <h3 class="seeing-card-title">☁️ Nubes bajas</h3>
                                            <p class="seeing-card-value" id="clow">-</p>
                                            <span class="seeing-card-desc">% Cobertura</span>
                                        </div>
                                        <div class="card">
                                            <h3 class="seeing-card-title">🌥️ Nubes medias</h3>
                                            <p class="seeing-card-value" id="cmid">-</p>
                                            <span class="seeing-card-desc">% Cobertura</span>
                                        </div>
                                        <div class="card">
                                            <h3 class="seeing-card-title">🌤️ Nubes altas</h3>
                                            <p class="seeing-card-value" id="chigh">-</p>
                                            <span class="seeing-card-desc">% Cobertura</span>
                                        </div>
                                    </div>

                                    <div class="footer">
                                        <p class="seeing-result">
                                            👁️ Seeing: <strong><span id="seeingtext">-</span></strong>
                                        </p>

                                        <p class="seeing-attribution">
                                            Datos en altura y nubes de
                                            <a href="https://open-meteo.com/" target="_blank" rel="noopener noreferrer">
                                                Open-Meteo
                                            </a>
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!--*************************************************************
                            ************ DATOS DE LA ESTACIÓN METEOROLÓGICA *************
                            *********************** M O D A L ***************************
                            ************************************************************* -->
                        <!-- Modal oculto por defecto -->
                        <div id="pws-info-dialog" class="modal"
                             data-lat="<?php echo $lat; ?>"
                             data-lon="<?php echo $lon; ?>">

                            <div class="modal-content">
                                <button class="close" id="pws-info-dialog-close" aria-label="Cerrar">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none"
                                         stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <line x1="18" y1="6" x2="6" y2="18"></line>
                                        <line x1="6" y1="6" x2="18" y2="18"></line>
                                    </svg>
                                </button>

                                <h2 class="pws-info-title">Estación Meteorológica / Observatorio: <?php echo $observatorio; ?></h2>

                                <div class="pws-info-body">

                                    <div class="pws-info-map-wrapper">
                                        <div id="pws-map-container">
                                        </div>
                                    </div>

                                    <div class="pws-info-details-wrapper">

                                        <h3>📍 Ubicación</h3>
                                        <div class="pws-info-card-grid">

                                            <div class="pws-info-card">
                                                <h4>Latitud</h4>
                                                <p><?php echo $latitud; ?></p>
                                            </div>

                                            <div class="pws-info-card">
                                                <h4>Longitud</h4>
                                                <p><?php echo $longitud; ?></p>
                                            </div>

                                            <div class="pws-info-card">
                                                <h4>Elevación</h4>
                                                <p><?php echo $elev; ?> m</p>
                                            </div>

                                            <div class="pws-info-card">
                                                <h4>Ciudad / País</h4>
                                                <p><?php echo $city; ?>, <?php echo $country; ?></p>
                                            </div>

                                        </div>

                                        <h3>💻 Equipo</h3>
                                        <div class="pws-info-card-stack">

                                            <div class="pws-info-card">
                                                <h4>Hardware</h4>
                                                <p><?php echo $hardware; ?></p>
                                            </div>

                                            <div class="pws-info-card">
                                                <h4>Software</h4>
                                                <p><?php echo $software; ?></p>
                                            </div>

                                        </div>
                                    </div>

                                </div>
                            </div>
                        </div>
                    </div>
                </dashboard-body-view>
                <dashboard-footer-view>
                    <div class="max-width">
                        <div class="container">
                            <div class="footer-text">Powered by </div>
                            <a href="https://www.wunderground.com"><wu-logo title="WU Logo" id="wu-logo"></wu-logo></a>
                            <div class="footer-text"> Software</div>
                        </div>
                    </div>
                </dashboard-footer-view>
            </content-router-wc>
        </div>
        <script src="/weather/static/config/conf_to_js.php"></script>
        <script type="module" src="/weather/static/views/widgets/wind_widget.js?v=<?php echo time(); ?>"></script>
        <script type="module" src="/weather/static/views/widgets/dew_widget.js?v=<?php echo time(); ?>"></script>
        <script type="module" src="/weather/static/views/widgets/temp_widget.js?v=<?php echo time(); ?>"></script>
        <script type="module" src="/weather/static/views/widgets/humidity_widget.js?v=<?php echo time(); ?>"></script>
        <script type="module" src="/weather/static/views/widgets/rain_widget.js?v=<?php echo time(); ?>"></script>
        <script type="module" src="/weather/static/views/widgets/pressure_widget.js?v=<?php echo time(); ?>"></script>
        <script type="module" src="/weather/static/views/widgets/uv_widget.js?v=<?php echo time(); ?>"></script>
        <script type="module" src="/weather/static/views/widgets/solar_widget.js?v=<?php echo time(); ?>"></script>
        <script type="module" src="/weather/static/views/widgets/temp_interior_widget.js?v=<?php echo time(); ?>"></script>
        <script type="module" src="/weather/static/views/widgets/humidity_interior_widget.js?v=<?php echo time(); ?>"></script>
        <script src="/weather/static/views/widgets/seeing_widget.js?v=<?php echo time(); ?>"></script>
        <script type="module" src="/weather/static/views/widgets/forecast.js?v=<?php echo time(); ?>"></script>
        <script type="module" src="/weather/static/views/date-time.js?v=<?php echo time(); ?>"></script>
        <script src="https://unpkg.com/suncalc@1.9.0/suncalc.js"></script>
        <script src="/weather/static/views/moon.js?v<?php echo time(); ?>"></script>
        <script src="/weather/static/views/sun.js?lat=<?php echo $lat; ?>&lon=<?php echo $lon; ?>&v=<?php echo time(); ?>"></script>
        <script src="https://cdn.jsdelivr.net/npm/echarts/dist/echarts.min.js"></script>
        <script type="module" src="/weather/static/views/widgets/modal_temp.js?v=<?php echo time(); ?>"></script>
        <script type="module" src="/weather/static/views/widgets/modal_humidity.js?v=<?php echo time(); ?>"></script>
        <script type="module" src="/weather/static/views/widgets/modal_wind.js?v=<?php echo time(); ?>"></script>
        <script type="module" src="/weather/static/views/widgets/modal_rain.js?v=<?php echo time(); ?>"></script>
        <script type="module" src="/weather/static/views/widgets/modal_pressure.js?v=<?php echo time(); ?>"></script>
        <script type="module" src="/weather/static/views/widgets/modal_solar.js?v=<?php echo time(); ?>"></script>
        <script type="module" src="/weather/static/views/widgets/modal_tempint.js?v=<?php echo time(); ?>"></script>
        <script type="module" src="/weather/static/views/widgets/modal_humidityint.js?v=<?php echo time(); ?>"></script>
        <script type="module" src="/weather/static/views/widgets/modal_seeing.js?v=<?php echo time(); ?>"></script>
        <script type="module" src="/weather/static/views/widgets/pws_info.js?v=<?php echo time(); ?>"></script>
        <script type="module" src="/weather/static/views/theme-switcher.js?v=<?php echo time(); ?>"></script>
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
