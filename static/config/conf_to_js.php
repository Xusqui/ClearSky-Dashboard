<?php
require_once './config.php';
header('Content-Type: application/javascript');
echo "const CITY = '" . addslashes($city) . "';";
echo "const COUNTRY = '" . addslashes($country) . "';";
echo "const TZ = '" . addslashes($tz) . "';";
echo "const LAT = '" . addslashes($lat) . "';";
echo "const LON = '" . addslashes($lon) . "';";
echo "const ELEV = '" . addslashes($elev) . "';";
?>
