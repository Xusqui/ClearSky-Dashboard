<?php
// get_astronomy_quality.php
// UNUSED
header('Content-Type: application/json; charset=utf-8');

// ================= CONFIG =================
$CACHE_FILE = __DIR__ . '/../../cache/cache_astronomy.json';
$CACHE_TTL  = 300; // 5 minutos

// ================= CONFIG ESTACIÓN =================
require_once __DIR__ . '/../../config/config.php';

if (!isset($lat, $lon, $tz)) {
    echo json_encode([
        "error" => true,
        "message" => "Lat/Lon/TZ no definidos"
    ]);
    exit;
}

// ================= CACHÉ =================
if (file_exists($CACHE_FILE)) {
    $cache = json_decode(file_get_contents($CACHE_FILE), true);
    if ($cache && time() - $cache['timestamp'] < $CACHE_TTL) {
        $cache['cached'] = true;
        echo json_encode($cache, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        exit;
    }
}

// ================= DATOS ESTACIÓN =================
$conn = new mysqli($db_url, $db_user, $db_pass, $db_database);
if ($conn->connect_error) {
    echo json_encode(["error" => true, "message" => "DB error"]);
    exit;
}

$sql = "
SELECT
    temperatura,
    humedad,
    punto_rocio,
    viento_velocidad,
    timestamp
FROM meteo
ORDER BY timestamp DESC
LIMIT 1
";

$res = $conn->query($sql);
if (!$res || $res->num_rows === 0) {
    echo json_encode(["error" => true, "message" => "Sin datos meteo"]);
    exit;
}

$meteo = $res->fetch_assoc();
$conn->close();

// ================= OPEN-METEO =================
$url = "https://api.open-meteo.com/v1/forecast?"
    . "latitude=$lat&longitude=$lon"
    . "&hourly=cloud_cover_low,cloud_cover_mid,cloud_cover_high,"
    . "windspeed_10m,windspeed_80m,windspeed_180m"
    . "&timezone=$tz";

$ch = curl_init();
curl_setopt_array($ch, [
    CURLOPT_URL => $url,
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_CONNECTTIMEOUT => 10,
    CURLOPT_TIMEOUT => 15,
    CURLOPT_SSL_VERIFYPEER => false,
    CURLOPT_SSL_VERIFYHOST => false,
    CURLOPT_USERAGENT => 'AstronomyQuality/1.0'
]);

$json = curl_exec($ch);

if ($json === false) {
    echo json_encode([
        "error" => true,
        "message" => "Error cURL Open-Meteo",
        "curl_error" => curl_error($ch)
    ]);
    curl_close($ch);
    exit;
}

$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($httpCode !== 200) {
    echo json_encode([
        "error" => true,
        "message" => "Open-Meteo HTTP $httpCode"
    ]);
    exit;
}

$data = json_decode($json, true);

// ================= SELECCIÓN HORA ACTUAL OPEN-METEO =================
$times = $data['hourly']['time'];

$now = new DateTime('now', new DateTimeZone($tz));
$now->setTime((int)$now->format('H'), 0);

$idx = null;
foreach ($times as $i => $t) {
    if ($t === $now->format('Y-m-d\TH:00')) {
        $idx = $i;
        break;
    }
}

if ($idx === null) {
    echo json_encode([
        "error" => true,
        "message" => "No se encuentra la hora actual en Open-Meteo"
    ]);
    exit;
}

$clouds = [
    "bajas"  => $data['hourly']['cloud_cover_low'][$idx],
    "medias" => $data['hourly']['cloud_cover_mid'][$idx],
    "altas"  => $data['hourly']['cloud_cover_high'][$idx],
];

$winds_aloft = [
    "10m"  => $data['hourly']['windspeed_10m'][$idx],
    "80m"  => $data['hourly']['windspeed_80m'][$idx],
    "180m" => $data['hourly']['windspeed_180m'][$idx],
];

// ================= ALTURA LUNAR =================
// ================= LÓGICA ASTRONÓMICA =================
class AstroCalculator
{
    public static function getMoonPhase($ts)
    {
        $lp = 2551443;
        $new_moon = 1230783600;
        $phase = (($ts - $new_moon) % $lp) / $lp;
        return 1 - abs(($phase - 0.5) * 2);
    }

    public static function moonAltitude($lat, $lon, $ts)
    {
        $rad = M_PI / 180;
        $d = ($ts / 86400) - 10957.5;
        $L = $rad * (218.316 + 13.176396 * $d);
        $M = $rad * (134.963 + 13.064993 * $d);
        $F = $rad * (93.272 + 13.229350 * $d);
        $l = $L + $rad * 6.289 * sin($M);
        $b = $rad * 5.128 * sin($F);
        $e = $rad * 23.4397;
        $ra  = atan2(sin($l) * cos($e) - tan($b) * sin($e), cos($l));
        $dec = asin(sin($b) * cos($e) + cos($b) * sin($e) * sin($l));
        $lw  = -$lon * $rad;
        $phi = $lat * $rad;
        $H   = fmod(($rad * (280.16 + 360.9856235 * $d)) - $lw - $ra, 2 * M_PI);
        return round(asin(sin($phi) * sin($dec) + cos($phi) * cos($dec) * cos($H)) / $rad, 1);
    }
}

$moon_alt = AstroCalculator::moonAltitude($lat, $lon, time());
$moon_phase = AstroCalculator::getMoonPhase(time());
$moon_impact = ($moon_alt > 0) ? ($moon_phase * ($moon_alt / 90)) : 0;


// ================= SEEING REAL =================

$deltaT = abs($meteo['temperatura'] - $meteo['punto_rocio']);

// Seeing planetario (arcsec)
$seeing_planetary = 0.6;
$seeing_planetary += min($meteo['viento_velocidad'] * 0.15, 1.5);
$seeing_planetary += max(0, (3 - $deltaT)) * 0.25;
$seeing_planetary += max(0, ($meteo['humedad'] - 80)) * 0.01;
$seeing_planetary = round(min(max($seeing_planetary, 0.6), 4.0), 2);

// Seeing cielo profundo (arcsec)
$seeing_deepsky = 0.8;
$seeing_deepsky += ($winds_aloft['80m'] * 0.08);
$seeing_deepsky += ($winds_aloft['180m'] * 0.12);
$seeing_deepsky += max(0, ($meteo['humedad'] - 75)) * 0.015;
$seeing_deepsky = round(min(max($seeing_deepsky, 0.8), 5.0), 2);

// Conversión arcsec → índice
function seeing_index($arcsec, $best, $worst)
{
    $idx = 100 * (1 - (($arcsec - $best) / ($worst - $best)));
    return round(min(max($idx, 0), 100));
}

$seeing_planetary_idx = seeing_index($seeing_planetary, 0.6, 4.0);
$seeing_deepsky_idx   = seeing_index($seeing_deepsky,   0.8, 5.0);

// ================= ÍNDICES =================

// CALIDAD VISUAL
$visual = 100;
$cloud_effective = $clouds['bajas'] * 1.0
    + $clouds['medias'] * 1.0
    + $clouds['altas']  * 0.8;

$visual -= min($cloud_effective, 100);
$visual -= max(0, $moon_alt) * 1.5;
$visual -= min($meteo['humedad'] * 0.3, 30);
$visual = max(0, round($visual));

// ASTROFOTO
$astro = 100;
$astro -= ($clouds['bajas'] + $clouds['medias']) * 0.6;
$astro -= max(0, $moon_alt) * 2;
$astro -= min($meteo['humedad'] * 0.4, 35);
$astro -= min($meteo['viento_velocidad'] * 3, 30);
$astro = max(0, round($astro));

// ================= RESPUESTA =================
$output = [
    "timestamp" => time(),
    "cached" => false,
    "estacion" => [
        "temperatura" => (float)$meteo['temperatura'],
        "humedad"     => (float)$meteo['humedad'],
        "rocío"       => (float)$meteo['punto_rocio'],
        "viento"      => (float)$meteo['viento_velocidad'],
    ],
    "luna" => [
        "altura" => $moon_alt,
        "iluminacion_pct" => round($moon_phase * 100),
        "impacto_brillo" => round($moon_impact * 100)
    ],
    "nubes" => $clouds,
    "viento_altura" => $winds_aloft,
    "seeing" => [
        "planetario" => [
            "arcsec" => $seeing_planetary,
            "indice" => $seeing_planetary_idx
        ],
        "cielo_profundo" => [
            "arcsec" => $seeing_deepsky,
            "indice" => $seeing_deepsky_idx
        ]
    ],
    "calidad" => [
        "visual" => $visual,
        "astrofoto" => $astro
    ]
];

file_put_contents($CACHE_FILE, json_encode($output, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
echo json_encode($output, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
