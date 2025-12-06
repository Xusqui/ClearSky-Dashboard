<?php
// conf_to_js.php
// Trae las variables de php a un archivo que pueda ser incluido en javascript
// para llevar la web a otro día: const now = new Date(2025-08-30);
require_once './config.php';
header('Content-Type: application/javascript');
echo "
const CITY = '" . addslashes($city) . "';
const COUNTRY = '" . addslashes($country) . "';
const TZ = '" . addslashes($tz) . "';
const LAT = '" . addslashes($lat) . "';
const LON = '" . addslashes($lon) . "';
const ELEV = '" . addslashes($elev) . "';
const now = new Date();
const latitude = parseFloat(LAT);
const longitude = parseFloat(LON);
const elevation = parseFloat(ELEV);
let observer = new Astronomy.Observer(latitude, longitude, elevation);
let startAstro = Astronomy.MakeTime(now);
";
?>
