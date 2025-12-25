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
			body { font-family:'Segoe UI', Roboto,sans-serif; display:flex; justify-content:center; align-items:center; height:100vh; background:linear-gradient(135deg,#1f4037,#99f2c8); }
			.container { background:#fff; padding:2rem 3rem; border-radius:16px; box-shadow:0 8px 25px rgba(0,0,0,0.2); width:320px; text-align:center; }
			input { width:100%; padding:0.6rem; margin-top:1rem; border-radius:8px; border:1px solid #ccc; font-size:1rem; }
			button { margin-top:1rem; width:100%; background:#1f4037; color:white; padding:0.7rem; border:none; border-radius:10px; font-weight:bold; cursor:pointer; }
			button:hover { background:#26735c; }
			.error { margin-top:1rem; color:#b00020; font-weight:bold; }
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
			<?php if($error): ?><div class="error"><?=htmlspecialchars($error)?></div><?php endif; ?>
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
			body { font-family:'Segoe UI', Roboto,sans-serif; display:flex; justify-content:center; align-items:center; height:100vh; background:linear-gradient(135deg,#1f4037,#99f2c8); }
			.container { background:#fff; padding:2rem 3rem; border-radius:16px; box-shadow:0 8px 25px rgba(0,0,0,0.2); width:320px; text-align:center; }
			input { width:100%; padding:0.6rem; margin-top:1rem; border-radius:8px; border:1px solid #ccc; font-size:1rem; }
			button { margin-top:1rem; width:100%; background:#1f4037; color:white; padding:0.7rem; border:none; border-radius:10px; font-weight:bold; cursor:pointer; }
			button:hover { background:#26735c; }
			.error { margin-top:1rem; color:#b00020; font-weight:bold; }
		</style>
	</head>
	<body>
		<div class="container">
			<h1>Ingrese contraseña 🔒</h1>
			<form method="POST">
				<input type="password" name="password" placeholder="Contraseña" required>
				<button type="submit">Acceder</button>
			</form>
			<?php if($error): ?><div class="error"><?=htmlspecialchars($error)?></div><?php endif; ?>
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
$send_ha = $config['send_ha'] ?? true;
$ha_token = $config['ha_token'] ?? '';
$send_meteoclimatic = $config['send_meteoclimatic'] ?? false;
$meteoclimatic_code = $config['meteoclimatic_code'] ?? '';
$meteoclimatic_token = $config['meteoclimatic_token'] ?? '';
$log_weather = $config['log_weather'] ?? '';

// Variables de token truncado (Solo para mostrar si hay valor)
$text_in_local_token = ($local_token && strlen($local_token) > 12) ? substr($local_token, 0, 6) . '(...)' . substr($local_token, -6) : 'set/unset';
$text_in_ha_token = ($ha_token && strlen($ha_token) > 12) ? substr($ha_token, 0, 6) . '(...)' . substr($ha_token, -6) : 'set/unset';

// El código de Meteoclimatic no se trunca, solo se verifica si está establecido
$text_in_meteoclimatic_code = $meteoclimatic_code ? htmlspecialchars($meteoclimatic_code) : 'unset';
$text_in_meteoclimatic_token = ($meteoclimatic_token && strlen($meteoclimatic_token) > 12) ? substr($meteoclimatic_token, 0, 6) . '(...)' . substr($meteoclimatic_token, -6) : 'set/unset';


// === COMPROBAR SI ALGUNA VARIABLE ESTÁ VACÍA (Para mostrar el aviso) ===
$vars_to_check = [
	'observatorio' => $observatorio,
	'latitud'      => $latitud,
	'longitud'     => $longitud,
	'elevacion'    => $elevacion,
	'hardware'     => $hardware,
	'software'     => $software,
	'city'         => $city,
	'country'      => $country,
	'tz'           => $tz,
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
	$send_local_post = (int)($_POST['send_local'] ?? 0);
	$local_token_post = trim($_POST['local_token'] ?? '');
	$send_ha_post = (int)($_POST['send_ha'] ?? 0);
	$ha_token_post = trim($_POST['ha_token'] ?? '');
	$send_meteoclimatic_post = (int)($_POST['send_meteoclimatic'] ?? 0);
	$meteoclimatic_code_post = trim($_POST['meteoclimatic_code'] ?? '');
	$meteoclimatic_token_post = trim($_POST['meteoclimatic_token'] ?? '');
	$log_weather_post = (int)($_POST['log_weather'] ?? 0);

	// Validar campos obligatorios
	$missing_field = '';
	if (!$observatorio) $missing_field = 'observatorio';
	elseif ($latitud === false || $latitud === '') $missing_field = 'latitud';
	elseif ($longitud === false || $longitud === '') $missing_field = 'longitud';
	elseif ($elevacion === false || $elevacion === '') $missing_field = 'elevacion';
	elseif (!$hardware) $missing_field = 'hardware';
	elseif (!$software) $missing_field = 'software';
	elseif (!$tz) $missing_field = 'tz';

	// Validación: El token local es OBLIGATORIO para el acceso a la API (independientemente de send_local)
	elseif (!$local_token_post) {
		$missing_field = 'Token de Acceso a la API (Token Local)';
	}
	// Validación de Tokens de envío (obligatorio si el envío está activado)
	elseif ($send_ha_post && !$ha_token_post) {
		$missing_field = 'Home Assistant Token';
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
		$send_ha = $send_ha_post;
		$ha_token = $ha_token_post;
		$send_meteoclimatic = $send_meteoclimatic_post;
		$meteoclimatic_code = $meteoclimatic_code_post;
		$meteoclimatic_token = $meteoclimatic_token_post;
		$log_weather = $log_weather_post;

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
		$original_lat_num = is_numeric($original_lat) ? (float)$original_lat : null;
		$original_lon_num = is_numeric($original_lon) ? (float)$original_lon : null;

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
				curl_close($ch);

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

		// --- PREPARACIÓN DE LA CONSULTA SQL (18 parámetros) ---
		$sql = "
			INSERT INTO config (id, observatorio, latitud, longitud, elevacion, hardware, software, city, country, tz, password, send_local, local_token, send_ha, ha_token, send_meteoclimatic, meteoclimatic_code, meteoclimatic_token, log_weather)
			VALUES (1, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?,?)
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
				send_ha = VALUES(send_ha),
				ha_token = VALUES(ha_token),
				send_meteoclimatic = VALUES(send_meteoclimatic),
				meteoclimatic_code = VALUES(meteoclimatic_code),
				meteoclimatic_token = VALUES(meteoclimatic_token),
				log_weather = VALUES(log_weather)
		";

		$stmt = $conn->prepare($sql);
		if (!$stmt) {
			$setup_warning .= "Error al preparar la consulta: " . $conn->error;
		} else {
			// Tipos: s d d i s s s s s s i s i s i s s (17 parámetros)
			$stmt->bind_param("sddissssssisssissi",
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
							 $send_ha_post,
							 $ha_token_post,
							 $send_meteoclimatic_post,
							 $meteoclimatic_code_post,
							 $meteoclimatic_token_post,
							 $log_weather_post
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
		<title>Configuración inicial</title>
		<link rel="stylesheet" type="text/css" href="../css/setup.css?v=<?php echo time(); ?>">
	</head>
	<body>
		<div class="container">
			<h1>Configuración inicial 🌍</h1>
			<form method="POST">
				<?php if (!empty($setup_warning)): ?>
				<div class="warning-card">
					Es necesario volver a introducir los parámetros de configuración porque: <br>
					<?= nl2br(htmlspecialchars($setup_warning)) ?>
				</div>
				<?php endif; ?>

				<label for="observatorio">Nombre del observatorio / estación</label>
				<input type="text" name="observatorio" id="observatorio" value="<?= htmlspecialchars($observatorio) ?>" required>

				<label for="latitud">Latitud</label>
				<input type="number" step="0.000001" name="latitud" id="latitud" value="<?= htmlspecialchars($latitud) ?>" required>

				<label for="longitud">Longitud</label>
				<input type="number" step="0.000001" name="longitud" id="longitud" value="<?= htmlspecialchars($longitud) ?>" required>

				<label for="elevacion">Elevación (m)</label>
				<label for="elevacion">Elevación (m)</label>
					<div class="input-group-horizontal">
    					<input type="number" name="elevacion" id="elevacion" value="<?= htmlspecialchars($elevacion) ?>" required>
    					<button type="button" id="calcElevation" class="btn-inline">Calcular</button>
					</div>

				<label for="hardware">Hardware</label>
				<input type="text" name="hardware" id="hardware" value="<?= htmlspecialchars($hardware) ?>" required>

				<label for="software">Software</label>
				<input type="text" name="software" id="software" value="<?= htmlspecialchars($software) ?>" required>

				<label for="tz">Zona horaria</label>
				<select name="tz" id="tz" required>
					<?php foreach ($timezones as $tz_name): ?>
					<option value="<?= htmlspecialchars($tz_name) ?>" <?= ($tz_name === $tz) ? 'selected' : '' ?>>
						<?= htmlspecialchars($tz_name) ?>
					</option>
					<?php endforeach; ?>
				</select>

				<label for="password">Cambiar contraseña (opcional)</label>
				<input type="password" name="password" id="password" placeholder="Dejar vacío para no cambiar">

				<div style="margin-top: 1.5rem; border-top: 1px solid #ccc; padding-top: 1.5rem;">
					<h2>Seguridad de Acceso a la API</h2>
					<label for="local_token">Token de Acceso a la API (API Key)</label>
					<div class="token-container">
						<input type="text" name="local_token" id="local_token" value="<?= htmlspecialchars($local_token) ?>" required>
					</div>
					<p style="font-size: 0.8em; color: #555; margin-top: 0.5rem;">Este token es **obligatorio** para que la estación meteorológica pueda enviar datos a `api_data.php`.</p>
				</div>
				<div style="margin-top: 1.5rem; border-top: 1px solid #ccc; padding-top: 1.5rem;">
				    <h2>Utilizar Logs</h2>
				    <h3>Usar weather_data.log</h3>
				    <label for="log_weather">Guardar los datos en la bitácora local (Activarlo sólo para pruebas, puede crear un archivo muy grande)</label>
				    <select name="log_weather" id="log_weather">
				        <option value="1" <?= $log_weather == 1 ? 'selected' : '' ?>>Sí</option>
				        <option value="0" <?= $log_weather == 0 ? 'selected' : '' ?>>No</option>
				    </select>

					<h2>Opciones de Envío de Datos</h2>

					<h3 style="margin-top: 1rem;">1. Base de Datos Local</h3>
					<label for="send_local">Guardar datos en la Base de Datos local</label>
					<select name="send_local" id="send_local">
						<option value="1" <?= $send_local == 1 ? 'selected' : '' ?>>Sí</option>
						<option value="0" <?= $send_local == 0 ? 'selected' : '' ?>>No</option>
					</select>

					<h3 style="margin-top: 2rem;">2. Home Assistant</h3>
					<label for="send_ha">Enviar datos a Home Assistant</label>
					<select name="send_ha" id="send_ha" data-token-group="ha">
						<option value="1" <?= $send_ha == 1 ? 'selected' : '' ?>>Sí</option>
						<option value="0" <?= $send_ha == 0 ? 'selected' : '' ?>>No</option>
					</select>

					<div id="token-group-ha" class="token-group" style="display: <?= $send_ha == 1 ? 'block' : 'none' ?>;">
						<label for="ha_token">Long Live Token de Home Assistant</label>
						<div class="token-container">
							<input type="text" name="ha_token" id="ha_token" value="<?= htmlspecialchars($ha_token) ?>" <?= $send_ha == 1 ? 'required' : '' ?>>
						</div>
					</div>

					<h3 style="margin-top: 2rem;">3. Meteoclimatic</h3>
					<label for="send_meteoclimatic">Enviar datos a Meteoclimatic</label>
					<select name="send_meteoclimatic" id="send_meteoclimatic" data-token-group="meteoclimatic">
						<option value="1" <?= $send_meteoclimatic == 1 ? 'selected' : '' ?>>Sí</option>
						<option value="0" <?= $send_meteoclimatic == 0 ? 'selected' : '' ?>>No</option>
					</select>

					<div id="token-group-meteoclimatic" class="token-group" style="display: <?= $send_meteoclimatic == 1 ? 'block' : 'none' ?>;">

						<label for="meteoclimatic_code">Código de la estación (ej. ESAN00000000001XX)</label>
						<div class="token-container">
							<input type="text" name="meteoclimatic_code" id="meteoclimatic_code" value="<?= htmlspecialchars($meteoclimatic_code) ?>" <?= $send_meteoclimatic == 1 ? 'required' : '' ?>>
						</div>

						<label for="meteoclimatic_token" style="margin-top: 1rem; display: block;">Clave API de Meteoclimatic</label>
						<div class="token-container">
							<input type="text" name="meteoclimatic_token" id="meteoclimatic_token" value="<?= htmlspecialchars($meteoclimatic_token) ?>" <?= $send_meteoclimatic == 1 ? 'required' : '' ?>>
						</div>
					</div>
				</div>

				<button type="submit" style="margin-top: 2rem;">Guardar configuración</button>
			</form>

			<footer>Weather Setup · v4.0</footer>
		</div>

		<script>
			document.addEventListener('DOMContentLoaded', function () {

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
document.addEventListener('DOMContentLoaded', function () {
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

	</body>
</html>
