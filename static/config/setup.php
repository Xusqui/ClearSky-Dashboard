<?php
// setup.php
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();
// === CONEXIÓN A LA BASE DE DATOS ===
// Asegúrate de que config_db.php existe y define $db_url, $db_user, $db_pass, $db_database
include './config_db.php';

// === INCLUIR ESQUEMA CENTRAL ===
require_once './config_schema.php';
require_once './meteo_schema.php'; // ¡Añadido el esquema de la tabla 'meteo'!
require_once __DIR__ . '/../modules/security/ip_allowlist.php';

$conn = new mysqli($db_url, $db_user, $db_pass, $db_database);
if ($conn->connect_error) {
	die("Error de conexión: " . $conn->connect_error);
}

// === ESTRUCTURA DE LAS TABLAS DESEADAS ===
$desired_config_columns = $config_schema;
$desired_meteo_columns = $meteo_schema;
$meteo_indexes_to_add = $meteo_indexes ?? []; // Usamos los índices si están definidos

$setup_warning = ''; // variable para mostrar tarjeta de aviso

// ------------------------------------------------------------------
// === CREACIÓN Y ACTUALIZACIÓN DE TABLA 'config' ===
// ------------------------------------------------------------------

// === CREAR TABLA 'config' SI NO EXISTE ===
$table_exists = $conn->query("SHOW TABLES LIKE 'config'")->num_rows > 0;
if (!$table_exists) {
	// Tomamos la definición de 'id' del esquema central
	$id_definition = $desired_config_columns['id'];

	// La tabla se crea solo con la columna 'id' inicialmente
	if ($conn->query("CREATE TABLE config (id $id_definition)") === false) {
		$setup_warning .= "No existe la tabla 'config'. ";
	} else {
		$setup_warning .= "Se creó la tabla 'config'. ";
	}
}

// === AÑADIR COLUMNAS FALTANTES en 'config' ===
$result = $conn->query("SHOW COLUMNS FROM config");
$existing_columns = [];
if ($result) {
	while ($row = $result->fetch_assoc()) {
		$existing_columns[] = $row['Field'];
	}
}
foreach ($desired_config_columns as $col => $definition) {
	if (!in_array($col, $existing_columns)) {
		// Añadir todas las columnas del esquema que no existan
		if ($col !== 'id') {
			if ($conn->query("ALTER TABLE config ADD COLUMN $col $definition") === false) {
				$setup_warning .= "No se pudo crear la columna '$col' en 'config'. " . $conn->error . ". ";
			} else {
				$setup_warning .= "Se añadió la columna '$col' a 'config'. ";
			}
		}
	}
}

// ------------------------------------------------------------------
// === CREACIÓN Y ACTUALIZACIÓN DE TABLA 'meteo' ===
// ------------------------------------------------------------------

// === CREAR TABLA 'meteo' SI NO EXISTE ===
$meteo_table_exists = $conn->query("SHOW TABLES LIKE 'meteo'")->num_rows > 0;
if (!$meteo_table_exists) {
	// 1. Construir la definición de columnas
	$definitions = [];

	// Llenar la matriz con todas las definiciones de columna (usando backticks)
	foreach ($desired_meteo_columns as $col => $definition) {
		// Usamos backticks para la columna, ya que la exportación lo usa.
		$definitions[] = "`$col` $definition";
	}

	// 3. Crear la consulta final sin la definición de PK redundante
	$create_table_sql = "CREATE TABLE `meteo` (" . implode(', ', $definitions) . ") ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci";

	if ($conn->query($create_table_sql) === false) {
		// Este error solo debería saltar si falla la conexión o hay un tipo de dato que no existe.
		die("FATAL ERROR CREATING METEO TABLE: " . $conn->error);
	} else {
		$setup_warning .= "Se creó la tabla 'meteo'. ";

		// 4. Añadir índices adicionales (solo KEY_TIMESTAMP, si es necesario)
		if (isset($meteo_indexes_to_add['KEY_TIMESTAMP'])) {
			$key_cols = implode(', ', $meteo_indexes_to_add['KEY_TIMESTAMP']);
			$index_name = 'idx_' . implode('_', $meteo_indexes_to_add['KEY_TIMESTAMP']);

			$check_index_query = $conn->query("SHOW INDEX FROM meteo WHERE Key_name = '$index_name'");

			if (!$check_index_query || $check_index_query->num_rows === 0) {
				if ($conn->query("CREATE INDEX `$index_name` ON `meteo` (`$key_cols`)") === false) {
					$setup_warning .= "No se pudo crear el índice '$index_name' en 'meteo'. ERROR SQL: " . $conn->error . ". ";
				} else {
					$setup_warning .= "Se creó el índice '$index_name' en 'meteo'. ";
				}
			}
		}
	}
}

// === AÑADIR COLUMNAS FALTANTES en 'meteo' ===
if ($conn->query("SHOW TABLES LIKE 'meteo'")->num_rows > 0) { // Solo si la tabla existe o se acaba de crear
	$meteo_result = $conn->query("SHOW COLUMNS FROM meteo");
	$existing_meteo_columns = [];
	if ($meteo_result) {
		while ($row = $meteo_result->fetch_assoc()) {
			$existing_meteo_columns[] = $row['Field'];
		}
	}

	foreach ($desired_meteo_columns as $col => $definition) {
		if (!in_array($col, $existing_meteo_columns)) {
			// Añadir la columna faltante
			if ($conn->query("ALTER TABLE meteo ADD COLUMN $col $definition") === false) {
				$setup_warning .= "No se pudo crear la columna '$col' en 'meteo'. " . $conn->error . ". ";
			} else {
				$setup_warning .= "Se añadió la columna '$col' a 'meteo'. ";
			}
		}
	}
}

// ------------------------------------------------------------------
// === AUTENTICACIÓN Y CARGA DE DATOS (Continúa con el código original) ===
// ------------------------------------------------------------------

// ... (El resto del código PHP y HTML sigue sin cambios) ...

// === Cargar configuración (para verificar la contraseña) ===
$config = $conn->query("SELECT * FROM config WHERE id = 1")->fetch_assoc() ?? [];
$password_hash = $config['password'] ?? '';

// --- 1) CREAR CONTRASEÑA SI NO EXISTE ---
if (!$password_hash) {
	$error = '';
	if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['password'], $_POST['password_confirm'])) {
		$pass = $_POST['password'];
		$pass_confirm = $_POST['password_confirm'];
		if ($pass !== $pass_confirm) {
			$error = "Las contraseñas no coinciden.";
		} elseif (strlen($pass) < 4) {
			$error = "La contraseña debe tener al menos 4 caracteres.";
		} else {
			$hash = password_hash($pass, PASSWORD_DEFAULT);
			$hash_escaped = $conn->real_escape_string($hash);
			$conn->query("INSERT INTO config (id, password) VALUES (1,'$hash_escaped') ON DUPLICATE KEY UPDATE password='$hash_escaped'");

			if ($conn->error) {
				$error = "Error al guardar la contraseña: " . $conn->error;
			} else {
				$_SESSION['authenticated'] = true;
				header("Location: " . $_SERVER['PHP_SELF']);
				exit;
			}
		}
	}
?>
	<!DOCTYPE html>
	<html lang="es">

	<head>
		<meta charset="UTF-8">
		<title>Crear contraseña</title>
		<style>
			body {
				font-family: 'Segoe UI', Roboto, sans-serif;
				display: flex;
				justify-content: center;
				align-items: center;
				height: 100vh;
				background: linear-gradient(135deg, #1f4037, #99f2c8);
			}

			.container {
				background: #fff;
				padding: 2rem 3rem;
				border-radius: 16px;
				box-shadow: 0 8px 25px rgba(0, 0, 0, 0.2);
				width: 320px;
				text-align: center;
			}

			input {
				width: 100%;
				padding: 0.6rem;
				margin-top: 1rem;
				border-radius: 8px;
				border: 1px solid #ccc;
				font-size: 1rem;
			}

			button {
				margin-top: 1rem;
				width: 100%;
				background: #1f4037;
				color: white;
				padding: 0.7rem;
				border: none;
				border-radius: 10px;
				font-weight: bold;
				cursor: pointer;
			}

			button:hover {
				background: #26735c;
			}

			.error {
				margin-top: 1rem;
				color: #b00020;
				font-weight: bold;
			}
		</style>
	</head>

	<body>
		<div class="container">
			<h1>Crear contraseña 🔑</h1>
			<form method="POST">
				<input type="password" name="password" placeholder="Contraseña" required>
				<input type="password" name="password_confirm" placeholder="Repetir contraseña" required>
				<button type="submit">Guardar contraseña</button>
			</form>
			<?php if ($error): ?>
				<div class="error"><?= htmlspecialchars($error) ?></div><?php endif; ?>
		</div>
	</body>

	</html>
<?php
	exit;
}

// --- 2) AUTENTICACIÓN SI HAY CONTRASEÑA ---
if ($password_hash && !isset($_SESSION['authenticated'])) {
	$error = '';
	if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['password'])) {
		if (password_verify($_POST['password'], $password_hash)) {
			$_SESSION['authenticated'] = true;
			header("Location: " . $_SERVER['PHP_SELF']);
			exit;
		} else {
			$error = "Contraseña incorrecta.";
		}
	}
?>
	<!DOCTYPE html>
	<html lang="es">

	<head>
		<meta charset="UTF-8">
		<title>Acceso</title>
		<style>
			body {
				font-family: 'Segoe UI', Roboto, sans-serif;
				display: flex;
				justify-content: center;
				align-items: center;
				height: 100vh;
				background: linear-gradient(135deg, #1f4037, #99f2c8);
			}

			.container {
				background: #fff;
				padding: 2rem 3rem;
				border-radius: 16px;
				box-shadow: 0 8px 25px rgba(0, 0, 0, 0.2);
				width: 320px;
				text-align: center;
			}

			input {
				width: 100%;
				padding: 0.6rem;
				margin-top: 1rem;
				border-radius: 8px;
				border: 1px solid #ccc;
				font-size: 1rem;
			}

			button {
				margin-top: 1rem;
				width: 100%;
				background: #1f4037;
				color: white;
				padding: 0.7rem;
				border: none;
				border-radius: 10px;
				font-weight: bold;
				cursor: pointer;
			}

			button:hover {
				background: #26735c;
			}

			.error {
				margin-top: 1rem;
				color: #b00020;
				font-weight: bold;
			}
		</style>
	</head>

	<body>
		<div class="container">
			<h1>Ingrese contraseña 🔒</h1>
			<form method="POST">
				<input type="password" name="password" placeholder="Contraseña" required>
				<button type="submit">Acceder</button>
			</form>
			<?php if ($error): ?>
				<div class="error"><?= htmlspecialchars($error) ?></div><?php endif; ?>
		</div>
	</body>

	</html>
<?php
	exit;
}

// ------------------------------------------------------------------
// === CARGAR DATOS EXISTENTES (Recarga tras autenticación) ===
// ------------------------------------------------------------------
$config = $conn->query("SELECT * FROM config WHERE id = 1")->fetch_assoc() ?? [];
$password_hash = $config['password'] ?? '';
$observatorio = $config['observatorio'] ?? '';
$latitud = $config['latitud'] ?? '';
$longitud = $config['longitud'] ?? '';
$elevacion = $config['elevacion'] ?? '';
$hardware = $config['hardware'] ?? '';
$software = $config['software'] ?? '';
$city = $config['city'] ?? '';
$country = $config['country'] ?? '';
$tz = $config['tz'] ?? 'UTC';

// Variables de envío de datos
$send_local = $config['send_local'] ?? true;
$local_token = $config['local_token'] ?? '';
$local_allowlist_cidrs = trim((string)($config['local_allowlist_cidrs'] ?? ''));
$send_ha = $config['send_ha'] ?? true;
$ha_token = $config['ha_token'] ?? '';
$ha_ip = $config['ha_ip'] ?? '';
$ha_port = $config['ha_port'] ?? '';
$send_meteoclimatic = $config['send_meteoclimatic'] ?? false;
$meteoclimatic_code = $config['meteoclimatic_code'] ?? '';
$meteoclimatic_token = $config['meteoclimatic_token'] ?? '';
$log_weather = $config['log_weather'] ?? '';

// Variables para la presentacion gráfica
$show_dew = $config['show_dew'];
$show_UV = $config['show_UV'];
$show_solar = $config['show_solar'];
$show_in_temp = $config['show_in_temp'];
$show_in_hum = $config['show_in_hum'];
$show_sky = $config['show_sky'];

// Variables de corrección:
$rain_offset = $config['rain_offset'];

// Variables de token truncado (Solo para mostrar si hay valor)
$text_in_local_token = ($local_token && strlen($local_token) > 12) ? substr($local_token, 0, 6) . '(...)' . substr($local_token, -6) : 'set/unset';
$text_in_ha_token = ($ha_token && strlen($ha_token) > 12) ? substr($ha_token, 0, 6) . '(...)' . substr($ha_token, -6) : 'set/unset';

// El código de Meteoclimatic no se trunca, solo se verifica si está establecido
$text_in_meteoclimatic_code = $meteoclimatic_code ? htmlspecialchars($meteoclimatic_code) : 'unset';
$text_in_meteoclimatic_token = ($meteoclimatic_token && strlen($meteoclimatic_token) > 12) ? substr($meteoclimatic_token, 0, 6) . '(...)' . substr($meteoclimatic_token, -6) : 'set/unset';


// === COMPROBAR SI ALGUNA VARIABLE ESTÁ VACÍA (Para mostrar el aviso) ===
$vars_to_check = [
	'observatorio' => $observatorio,
	'latitud' => $latitud,
	'longitud' => $longitud,
	'elevacion' => $elevacion,
	'hardware' => $hardware,
	'software' => $software,
	'city' => $city,
	'country' => $country,
	'tz' => $tz,
];

foreach ($vars_to_check as $var_name => $value) {
	if ($value === '' || $value === null) {
		$setup_warning .= "La variable '$var_name' está vacía. ";
	}
}

// === LISTA DE ZONAS HORARIAS ===
$timezones = timezone_identifiers_list();

// ------------------------------------------------------------------
// === SI SE ENVÍA EL FORMULARIO ===
// ------------------------------------------------------------------
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_SESSION['authenticated'])) {
	// Captura y Saneamiento de campos principales
	$observatorio = trim($_POST['observatorio'] ?? '');
	$latitud = filter_var($_POST['latitud'] ?? '', FILTER_VALIDATE_FLOAT);
	$longitud = filter_var($_POST['longitud'] ?? '', FILTER_VALIDATE_FLOAT);
	$elevacion = filter_var($_POST['elevacion'] ?? '', FILTER_VALIDATE_INT);
	$hardware = trim($_POST['hardware'] ?? '');
	$software = trim($_POST['software'] ?? '');
	$tz = $_POST['tz'] ?? 'UTC';
	$password_new = $_POST['password'] ?? '';

	// Captura de las variables de envío (Desde SELECT -> 1 o 0)
	$send_local_post = (int) ($_POST['send_local'] ?? 0);
	$local_token_post = trim($_POST['local_token'] ?? '');
	$local_allowlist_cidrs_post = trim($_POST['local_allowlist_cidrs'] ?? '');
	$send_ha_post = (int) ($_POST['send_ha'] ?? 0);
	$ha_token_post = trim($_POST['ha_token'] ?? '');
	$ha_ip_post = trim($_POST['ha_ip'] ?? '');
	$ha_port_post = filter_var($_POST['ha_port'] ?? '', FILTER_VALIDATE_INT);
	$send_meteoclimatic_post = (int) ($_POST['send_meteoclimatic'] ?? 0);
	$meteoclimatic_code_post = trim($_POST['meteoclimatic_code'] ?? '');
	$meteoclimatic_token_post = trim($_POST['meteoclimatic_token'] ?? '');
	$log_weather_post = (int) ($_POST['log_weather'] ?? 0);
	$show_dew_post = (int) ($_POST['show_dew'] ?? 0);
	$show_UV_post = (int) ($_POST['show_UV'] ?? 0);
	$show_solar_post = (int) ($_POST['show_solar'] ?? 0);
	$show_in_temp_post = (int) ($_POST['show_in_temp'] ?? 0);
	$show_in_hum_post = (int) ($_POST['show_in_hum'] ?? 0);
	$show_sky_post = (int) ($_POST['show_sky'] ?? 0);
	$rain_offset_post = filter_var($_POST['rain_offset'] ?? 0.0, FILTER_VALIDATE_FLOAT);

	// Validar campos obligatorios
	$missing_field = '';
	if (!$observatorio)
		$missing_field = 'observatorio';
	elseif ($latitud === false || $latitud === '')
		$missing_field = 'latitud';
	elseif ($longitud === false || $longitud === '')
		$missing_field = 'longitud';
	elseif ($elevacion === false || $elevacion === '')
		$missing_field = 'elevacion';
	elseif (!$hardware)
		$missing_field = 'hardware';
	elseif (!$software)
		$missing_field = 'software';
	elseif (!$tz)
		$missing_field = 'tz';

	// Validación: El token local es OBLIGATORIO para el acceso a la API (independientemente de send_local)
	elseif (!$local_token_post) {
		$missing_field = 'Token de Acceso a la API (Token Local)';
	} elseif ($local_allowlist_cidrs_post !== '') {
		$parsed_cidrs = clearsky_parse_cidr_list($local_allowlist_cidrs_post);
		$invalid_cidr = '';

		if (empty($parsed_cidrs)) {
			$missing_field = 'Rango de IP local (CIDR/IP)';
		} else {
			foreach ($parsed_cidrs as $cidr_item) {
				if (!clearsky_is_valid_cidr_entry($cidr_item)) {
					$invalid_cidr = $cidr_item;
					break;
				}
			}

			if ($invalid_cidr !== '') {
				$missing_field = "Rango de IP local inválido: {$invalid_cidr}";
			} else {
				// Normaliza y elimina duplicados para guardar formato consistente.
				$local_allowlist_cidrs_post = implode(',', $parsed_cidrs);
			}
		}
	}
	// Validación de Tokens de envío (obligatorio si el envío está activado)
	elseif ($send_ha_post && (!$ha_token_post || !$ha_ip_post || !$ha_port_post)) {
		if (!$ha_token_post) {
			$missing_field = 'Falta Token de Home Assistant';
		} elseif (!$ha_ip_post) {
			$missing_field = 'Falta IP de Home Assistant';
		} elseif (!$ha_port_post) {
			$missing_field = 'Falta el Puerto de Home Assistant';
		}
	} elseif ($send_meteoclimatic_post) {
		if (!$meteoclimatic_code_post) {
			$missing_field = 'Meteoclimatic Código de Estación';
		} elseif (!$meteoclimatic_token_post) {
			$missing_field = 'Meteoclimatic Token/API';
		}
	}

	if ($missing_field) {
		// Al fallar, reasignar variables para repoblar el formulario
		$latitud = $_POST['latitud'] ?? '';
		$longitud = $_POST['longitud'] ?? '';
		$elevacion = $_POST['elevacion'] ?? '';

		// Reasignar las variables de envío para preservar el estado del formulario al fallar
		$send_local = $send_local_post;
		$local_token = $local_token_post;
		$local_allowlist_cidrs = $local_allowlist_cidrs_post;
		$send_ha = $send_ha_post;
		$ha_token = $ha_token_post;
		$ha_ip = $ha_ip_post;
		$ha_port = $ha_port_post;
		$send_meteoclimatic = $send_meteoclimatic_post;
		$meteoclimatic_code = $meteoclimatic_code_post;
		$meteoclimatic_token = $meteoclimatic_token_post;
		$log_weather = $log_weather_post;
		$show_dew = $show_dew_post;
		$show_UV = $show_UV_post;
		$show_solar = $show_solar_post;
		$show_in_temp = $show_in_temp_post;
		$show_in_hum = $show_in_hum_post;
		$show_sky = $show_sky_post;
		$rain_offset = $rain_offset_post;

		$setup_warning .= "Falta o el formato es incorrecto para el campo **$missing_field** o el token está vacío. ";
	} else {
		// --- Lógica de Geocodificación Condicional ---

		// 1. Obtener valores originales de la DB (están en $config)
		$original_lat = $config['latitud'] ?? '';
		$original_lon = $config['longitud'] ?? '';

		// 2. Inicializar los valores de ciudad/país con los valores actuales de la DB
		$city_to_save = $city;
		$country_to_save = $country;

		// 3. Comprobar si las coordenadas POST son diferentes a las de la DB
		// Renombramos las variables saneadas del POST para mayor claridad en el bloque de geocodificación
		$latitud_post_saneada = $latitud;
		$longitud_post_saneada = $longitud;

		// Definir un pequeño margen de error (epsilon) para comparar flotantes
		$epsilon = 0.0000001;

		// Aseguramos que los valores originales sean numéricos para la comparación
		$original_lat_num = is_numeric($original_lat) ? (float) $original_lat : null;
		$original_lon_num = is_numeric($original_lon) ? (float) $original_lon : null;

		// La comparación se hace forzando a string para manejar la precisión de los flotantes.
		//$coords_changed = (string)$latitud_post_saneada !== (string)$original_lat || (string)$longitud_post_saneada !== (string)$original_lon;

		$coords_changed =
			$original_lat_num === null ||
			$original_lon_num === null ||
			abs($latitud_post_saneada - $original_lat_num) > $epsilon ||
			abs($longitud_post_saneada - $original_lon_num) > $epsilon;

		if ($coords_changed) {
			// Reiniciar valores si las coordenadas cambiaron
			$city_to_save = 'Desconocida';
			$country_to_save = 'Desconocido';

			if (is_numeric($latitud_post_saneada) && is_numeric($longitud_post_saneada)) {
				// 4. Ejecutar cURL solo si las coordenadas cambiaron
				$url = "https://nominatim.openstreetmap.org/reverse?format=jsonv2&lat={$latitud_post_saneada}&lon={$longitud_post_saneada}&zoom=10&addressdetails=1";
				$ch = curl_init();
				curl_setopt($ch, CURLOPT_URL, $url);
				curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
				curl_setopt($ch, CURLOPT_USERAGENT, 'EstacionMeteorologica/1.0 (xusqui@gmail.com)');
				curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
				curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);

				// Añadimos timeouts para evitar cuelgues de red
				curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
				curl_setopt($ch, CURLOPT_TIMEOUT, 10);

				$response = curl_exec($ch);

				if (curl_errno($ch)) {
					$setup_warning .= "Error cURL: " . curl_error($ch) . ". ";
				}
				//curl_close($ch);
				if ($response) {
					$data = json_decode($response, true);
					if (!empty($data['address'])) {
						$address = $data['address'];
						$city_to_save = $address['city'] ?? $address['town'] ?? $address['village'] ?? $address['hamlet'] ?? 'Desconocida';
						$country_to_save = $address['country'] ?? $address['country_code'] ?? 'Desconocido';
						$city_to_save = trim($city_to_save);
						$country_to_save = mb_strtoupper(trim($country_to_save), 'UTF-8');
					}
				}
			}
		}

		// Hash de nueva contraseña si se ingresó
		$password_final = $password_new ? password_hash($password_new, PASSWORD_DEFAULT) : $password_hash;

		// --- PREPARACIÓN DE LA CONSULTA SQL (24 parámetros) ---
		$sql = "
			INSERT INTO config (id, observatorio, latitud, longitud, elevacion, hardware, software, city, country, tz, password, send_local, local_token, local_allowlist_cidrs, send_ha, ha_token, ha_ip, ha_port, send_meteoclimatic, meteoclimatic_code, meteoclimatic_token, log_weather, show_in_temp, show_in_hum, show_UV, show_solar, show_dew, show_sky, rain_offset)
			VALUES (1, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
			ON DUPLICATE KEY UPDATE
				observatorio = VALUES(observatorio),
				latitud = VALUES(latitud),
				longitud = VALUES(longitud),
				elevacion = VALUES(elevacion),
				hardware = VALUES(hardware),
				software = VALUES(software),
				city = VALUES(city),
				country = VALUES(country),
				tz = VALUES(tz),
				password = VALUES(password),
				send_local = VALUES(send_local),
				local_token = VALUES(local_token),
				local_allowlist_cidrs = VALUES(local_allowlist_cidrs),
				send_ha = VALUES(send_ha),
				ha_token = VALUES(ha_token),
				ha_ip = VALUES(ha_ip),
				ha_port = VALUES(ha_port),
				send_meteoclimatic = VALUES(send_meteoclimatic),
				meteoclimatic_code = VALUES(meteoclimatic_code),
				meteoclimatic_token = VALUES(meteoclimatic_token),
				log_weather = VALUES(log_weather),
				show_in_temp = VALUES(show_in_temp),
				show_in_hum = VALUES(show_in_hum),
				show_UV = VALUES(show_UV),
				show_solar = VALUES(show_solar),
				show_dew = VALUES(show_dew),
				show_sky = VALUES(show_sky),
				rain_offset = VALUES(rain_offset)
		";

		$stmt = $conn->prepare($sql);
		if (!$stmt) {
			$setup_warning .= "Error al preparar la consulta: " . $conn->error;
		} else {
			// Tipos: 
			// s $observatorio (String)
			// d $latitud_post_saneada (Dedimal)
			// d $longitud_post_saneada (Decimal)
			// i $elevacion (Integer)
			// s $hardware (String)
			// s $software (string)
			// s $city_to_save (String)
			// s $country_to_save (String)
			// s $tz (String)
			// s $password_final (string)
			// i $send_local_post (string)
			// s $local_token_post (string)
			// s $local_allowlist_cidrs_post (String)
			// i $send_ha_post (Integer)
			// s $ha_token_post (String)
			// s $ha_ip_post (String)
			// i $ha_port_post (Integer)
			// i $send_meteoclimatic_post (Integer)
			// s $meteoclimatic_code_post (String)
			// s $meteoclimatic_token_post (String)
			// i $log_weather_post (Ingeger)
			// i $show_in_temp_post (Integer)
			// i $show_in_hum_post (Integer)
			// i $show_UV_post (Integer)
			// i $show_solar_post (Integer)
			// i $show_dew_post (Integer)
			// i $show_sky_post (Integer)
			// d $rain_offset_post (Decimal)
			// (28 parámetros)
			$stmt->bind_param(
				"sddissssssississiissiiiiiiid",
				$observatorio,
				$latitud_post_saneada,
				$longitud_post_saneada,
				$elevacion,
				$hardware,
				$software,
				$city_to_save,
				$country_to_save,
				$tz,
				$password_final,
				$send_local_post,
				$local_token_post,
				$local_allowlist_cidrs_post,
				$send_ha_post,
				$ha_token_post,
				$ha_ip_post,
				$ha_port_post,
				$send_meteoclimatic_post,
				$meteoclimatic_code_post,
				$meteoclimatic_token_post,
				$log_weather_post,
				$show_in_temp_post,
				$show_in_hum_post,
				$show_UV_post,
				$show_solar_post,
				$show_dew_post,
				$show_sky_post,
				$rain_offset_post
			);

			if (!$stmt->execute()) {
				$setup_warning .= "Error al guardar la configuración en base de datos: " . $stmt->error;
			} else {
				// El guardado fue exitoso, redirigimos
				header("Location: ../../index.php");
				exit;
			}
			$stmt->close();
		}
	}
}
?>

<!DOCTYPE html>
<html lang="es">

<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>Configuración Astro-Weather</title>
	<link rel="stylesheet" type="text/css" href="../css/setup.css?v=<?php echo time(); ?>">
</head>

<body>
	<div class="container">
		<h1 class="main-title">Configuración del Observatorio</h1>

		<form method="POST">
			<div class="setup-grid">

				<div class="card card-team">
					<h2>📍 Equipo y Ubicación</h2>
					<label>Nombre de la Estación</label>
					<input type="text" name="observatorio" value="<?= htmlspecialchars($observatorio) ?>" required>

					<div class="input-row">
						<div><label>Latitud</label><input type="number" step="0.000001" name="latitud" id="latitud"
								value="<?= htmlspecialchars($latitud) ?>" required></div>
						<div><label>Longitud</label><input type="number" step="0.000001" name="longitud" id="longitud"
								value="<?= htmlspecialchars($longitud) ?>" required></div>
					</div>

					<label>Elevación (m)</label>
					<div class="input-row" style="align-items: center;">
						<input type="number" name="elevacion" id="elevacion" value="<?= htmlspecialchars($elevacion) ?>"
							required>
						<button type="button" id="calcElevation" class="btn-inline">Calcular</button>
					</div>

					<label>Hardware</label>
					<input type="text" name="hardware" value="<?= htmlspecialchars($hardware) ?>" required>
					<label>Software</label>
					<input type="text" name="software" value="<?= htmlspecialchars($software) ?>" required>

					<label>Zona Horaria</label>
					<select name="tz">
						<?php foreach ($timezones as $tz_name): ?>
							<option value="<?= htmlspecialchars($tz_name) ?>" <?= ($tz_name === $tz) ? 'selected' : '' ?>>
								<?= htmlspecialchars($tz_name) ?></option>
						<?php endforeach; ?>
					</select>
				</div>

				<div class="card card-api">
					<h2>🔐 Seguridad y Sistema</h2>
					<label>Rango IP permitido (CIDR/IP)</label>
					<input type="text" name="local_allowlist_cidrs"
						value="<?= htmlspecialchars($local_allowlist_cidrs) ?>"
						placeholder="Ej: 192.168.1.0/24,10.0.0.15">
					<small style="display:block; margin-top:8px; color:#666;">Opcional. Si se deja vacío, se aplica detección automática LAN + loopback.</small>

					<label>API Key Local</label>
					<input type="text" name="local_token" value="<?= htmlspecialchars($local_token) ?>" required>

					<label>Contraseña de Acceso</label>
					<input type="password" name="password" placeholder="Sin cambios">

					<label>Base de Datos Local (MySQL)</label>
					<select name="send_local">
						<option value="1" <?= $send_local == 1 ? 'selected' : '' ?>>Habilitada</option>
						<option value="0" <?= $send_local == 0 ? 'selected' : '' ?>>Deshabilitada</option>
					</select>

					<label>Guardar en weather_data.log</label>
					<select name="log_weather">
						<option value="1" <?= $log_weather == 1 ? 'selected' : '' ?>>Sí, registrar en archivo</option>
						<option value="0" <?= $log_weather == 0 ? 'selected' : '' ?>>No registrar</option>
					</select>

					<label>Rain Offset (Corrección)</label>
					<input type="number" step="0.001" name="rain_offset" value="<?= htmlspecialchars($rain_offset) ?>">
				</div>

				<div class="card card-ha">
					<h2>🏠 Home Assistant</h2>
					<label>¿Activar Envío?</label>
					<select name="send_ha" id="send_ha">
						<option value="1" <?= $send_ha == 1 ? 'selected' : '' ?>>Sí</option>
						<option value="0" <?= $send_ha == 0 ? 'selected' : '' ?>>No</option>
					</select>

					<div id="token-group-ha" class="service-config"
						style="<?= $send_ha == 1 ? '' : 'display: none;' ?>">
						<label>Dirección IP / Host</label>
						<input type="text" name="ha_ip" value="<?= htmlspecialchars($ha_ip) ?>">
						<label>Puerto</label>
						<input type="number" name="ha_port" value="<?= htmlspecialchars($ha_port) ?>">
						<label>Long Live Token</label>
						<input type="text" name="ha_token" value="<?= htmlspecialchars($ha_token) ?>">
					</div>
				</div>

				<div class="card card-meteoclimatic">
					<h2>☁️ Meteoclimatic</h2>
					<label>¿Activar Envío?</label>
					<select name="send_meteoclimatic" id="send_meteoclimatic">
						<option value="1" <?= $send_meteoclimatic == 1 ? 'selected' : '' ?>>Sí</option>
						<option value="0" <?= $send_meteoclimatic == 0 ? 'selected' : '' ?>>No</option>
					</select>

					<div id="token-group-meteoclimatic" class="service-config"
						style="<?= $send_meteoclimatic == 1 ? '' : 'display: none;' ?>">
						<label>Código de Estación</label>
						<input type="text" name="meteoclimatic_code"
							value="<?= htmlspecialchars($meteoclimatic_code) ?>">
						<label>API Key / Token</label>
						<input type="text" name="meteoclimatic_token"
							value="<?= htmlspecialchars($meteoclimatic_token) ?>">
					</div>
				</div>

				<div class="card card-visual" style="grid-column: span 2;">
					<h2>🎨 Panel de Visualización (Widgets)</h2>
					<div class="visual-list"
						style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px; padding: 10px;">
						<?php
						$widgets = [
							['n' => 'show_dew', 'l' => 'Punto de Rocío', 'v' => $show_dew, 'i' => 'dew.png'],
							['n' => 'show_UV', 'l' => 'Índice UV', 'v' => $show_UV, 'i' => 'uv.png'],
							['n' => 'show_solar', 'l' => 'Radiación Solar', 'v' => $show_solar, 'i' => 'solar.png'],
							['n' => 'show_in_temp', 'l' => 'Temp. Interior', 'v' => $show_in_temp, 'i' => 'temp.png'],
							['n' => 'show_in_hum', 'l' => 'Hum. Interior', 'v' => $show_in_hum, 'i' => 'hum.png'],
							['n' => 'show_sky', 'l' => 'Calidad Cielo', 'v' => $show_sky, 'i' => 'seeing.png']
						];
						foreach ($widgets as $w): ?>
							<div class="visual-item"
								style="background: #ffffff; border-radius: 20px; padding: 25px; text-align: center; border: 2px solid #f0f0f0; box-shadow: 0 4px 6px rgba(0,0,0,0.02); transition: transform 0.2s;">
								<div style="margin-bottom: 15px;">
									<img src="../images/setup/<?= $w['i'] ?>"
										style="width: 100px; height: 100px; object-fit: contain; filter: drop-shadow(0 4px 8px rgba(0,0,0,0.1));">
								</div>
								<h3 style="margin: 10px 0; font-size: 1.1rem; color: #2c3e50;"><?= $w['l'] ?></h3>
								<div style="margin-top: 15px;">
									<select name="<?= $w['n'] ?>"
										style="width: 80%; padding: 10px; border-radius: 12px; font-weight: bold; cursor: pointer; background: <?= $w['v'] == 1 ? '#e8f5e9' : '#fafafa' ?>; border: 2px solid <?= $w['v'] == 1 ? '#a5d6a7' : '#eee' ?>;">
										<option value="1" <?= $w['v'] == 1 ? 'selected' : '' ?>>MOSTRAR</option>
										<option value="0" <?= $w['v'] == 0 ? 'selected' : '' ?>>OCULTAR</option>
									</select>
								</div>
							</div>
						<?php endforeach; ?>
					</div>
				</div>

				<div class="btn-save-container">
					<button type="submit" class="btn-save">💾 Guardar Configuración</button>
				</div>
			</div>
		</form>
	</div>

	<script>
		document.addEventListener('DOMContentLoaded', function() {

			// Función para alternar la visibilidad de los campos de token/código (SOLO PARA HA Y METEOCLIMATIC)
			function toggleTokenGroup(selectElement) {
				const groupName = selectElement.getAttribute('data-token-group');
				// Ignoramos el select 'send_local' ya que ya no tiene data-token-group='local'
				if (groupName !== 'ha' && groupName !== 'meteoclimatic') return;

				const tokenGroupDiv = document.getElementById(`token-group-${groupName}`);
				const isSending = selectElement.value === '1';

				if (tokenGroupDiv) {
					tokenGroupDiv.style.display = isSending ? 'block' : 'none';

					// Recorrer todos los inputs de texto dentro del grupo (Token y Código)
					const tokenInputs = tokenGroupDiv.querySelectorAll('input[type="text"]');
					tokenInputs.forEach(input => {
						// El atributo 'required' se añade/quita basado en el estado 'Sí/No'
						input.required = isSending;
					});
				}
			}

			// Aplicar la función SÓLO a HA y Meteoclimatic
			const sendSelects = document.querySelectorAll('select[data-token-group]');

			sendSelects.forEach(select => {
				// Inicializar el estado al cargar la página
				toggleTokenGroup(select);

				// Escuchar cambios
				select.addEventListener('change', function() {
					toggleTokenGroup(this);
				});
			});
		});
	</script>
	<script>
		document.addEventListener('DOMContentLoaded', function() {
			// ... (aquí ya tienes el código de toggleTokenGroup y los selects) ...

			const btnCalc = document.getElementById('calcElevation');
			const inputLat = document.getElementById('latitud');
			const inputLon = document.getElementById('longitud');
			const inputElev = document.getElementById('elevacion');

			if (btnCalc) {
				btnCalc.addEventListener('click', async () => {
					const lat = inputLat.value;
					const lon = inputLon.value;

					if (!lat || !lon) {
						alert("Introduce latitud y longitud primero");
						return;
					}

					btnCalc.disabled = true;
					btnCalc.textContent = 'Calculando...';

					try {
						// He simplificado la ruta para asegurar que no falle por carpetas absolutas
						const response = await fetch(`get_elevation.php?lat=${encodeURIComponent(lat)}&lon=${encodeURIComponent(lon)}`);

						if (!response.ok) throw new Error("Error en el servidor");

						const data = await response.json();

						if (data.error) {
							alert("Error: " + data.error);
						} else {
							inputElev.value = data.elevation;
						}

					} catch (e) {
						alert("Error de red: No se pudo conectar con get_elevation.php");
						console.error(e);
					} finally {
						btnCalc.disabled = false;
						btnCalc.textContent = 'Calcular';
					}
				});
			}
		});
	</script>
	<footer class="setup-footer">
		<p>Astro-Weather Setup 2.0</p>
	</footer>
</body>

</html>