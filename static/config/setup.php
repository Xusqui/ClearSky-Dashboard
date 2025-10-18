<?php
session_start();
// === CONEXIÓN A LA BASE DE DATOS ===
include './config_db.php';

$conn = new mysqli($db_url, $db_user, $db_pass, $db_database);
if ($conn->connect_error) {
    die("Error de conexión: " . $conn->connect_error);
}

// === ESTRUCTURA DE LA TABLA CONFIG DESEADA ===
$desired_columns = [
    'id' => 'INT PRIMARY KEY AUTO_INCREMENT',
    'latitud' => 'DECIMAL(10,6)',
    'longitud' => 'DECIMAL(10,6)',
    'elevacion' => 'INT',
    'hardware' => 'VARCHAR(255)',
    'software' => 'VARCHAR(255)',
    'observatorio' => 'VARCHAR(255)',
    'city' => 'VARCHAR(255)',
    'country' => 'VARCHAR(255)',
    'tz' => 'VARCHAR(255)',
    'password' => 'VARCHAR(255)'
];

$setup_warning = ''; // variable para mostrar tarjeta de aviso

// === CREAR TABLA SI NO EXISTE ===
$table_exists = $conn->query("SHOW TABLES LIKE 'config'")->num_rows > 0;
if (!$table_exists) {
    if ($conn->query("CREATE TABLE config (id INT PRIMARY KEY AUTO_INCREMENT)") === false) {
        $setup_warning .= "No existe la tabla 'config'. ";
    } else {
        $setup_warning .= "Se creó la tabla 'config'. ";
    }
}

// === AÑADIR COLUMNAS FALTANTES ===
$result = $conn->query("SHOW COLUMNS FROM config");
$existing_columns = [];
while ($row = $result->fetch_assoc()) {
    $existing_columns[] = $row['Field'];
}
foreach ($desired_columns as $col => $definition) {
    if (!in_array($col, $existing_columns)) {
        if ($conn->query("ALTER TABLE config ADD COLUMN $col $definition") === false) {
            $setup_warning .= "No se pudo crear la columna '$col'. ";
        } else {
            $setup_warning .= "Se añadió la columna '$col'. ";
        }
    }
}

// === Cargar configuración ===
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
            // Insertar registro si no existe
            $conn->query("INSERT INTO config (id, password) VALUES (1,'$hash') ON DUPLICATE KEY UPDATE password='$hash'");
            $_SESSION['authenticated'] = true;
            header("Location: " . $_SERVER['PHP_SELF']);
            exit;
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

// === CARGAR DATOS EXISTENTES SI LOS HAY ===
$config = $conn->query("SELECT * FROM config WHERE id = 1")->fetch_assoc() ?? [];
$observatorio = $config['observatorio'] ?? '';
$latitud = $config['latitud'] ?? '';
$longitud = $config['longitud'] ?? '';
$elevacion = $config['elevacion'] ?? '';
$hardware = $config['hardware'] ?? '';
$software = $config['software'] ?? '';
$city = $config['city'] ?? '';
$country = $config['country'] ?? '';
$tz = $config['tz'] ?? 'UTC';

// === COMPROBAR SI ALGUNA VARIABLE ESTÁ VACÍA ===
$vars_to_check = [
    'observatorio' => $observatorio,
    'latitud'      => $latitud,
    'longitud'     => $longitud,
    'elevacion'    => $elevacion,
    'hardware'     => $hardware,
    'software'     => $software,
    'city'         => $city,
    'country'      => $country,
    'tz'           => $tz
];

foreach ($vars_to_check as $var_name => $value) {
    if ($value === '' || $value === null) {
        $setup_warning .= "La variable '$var_name' está vacía. ";
    }
}

// === LISTA DE ZONAS HORARIAS ===
$timezones = timezone_identifiers_list();

// === SI SE ENVÍA EL FORMULARIO ===
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_SESSION['authenticated'])) {
    $observatorio = trim($_POST['observatorio'] ?? '');
    $latitud = $_POST['latitud'] ?? '';
    $longitud = $_POST['longitud'] ?? '';
    $elevacion = $_POST['elevacion'] ?? '';
    $hardware = trim($_POST['hardware'] ?? '');
    $software = trim($_POST['software'] ?? '');
    $tz = $_POST['tz'] ?? 'UTC';
    $password_new = $_POST['password'] ?? '';

    // Validar campos
    $missing_field = '';
    if (!$observatorio) $missing_field = 'observatorio';
    elseif (!$latitud) $missing_field = 'latitud';
    elseif (!$longitud) $missing_field = 'longitud';
    elseif (!$elevacion) $missing_field = 'elevacion';
    elseif (!$hardware) $missing_field = 'hardware';
    elseif (!$software) $missing_field = 'software';
    elseif (!$tz) $missing_field = 'tz';

    if ($missing_field) {
        $setup_warning .= "Falta el campo '$missing_field'. ";
    } else {
        // --- Obtener ciudad y país automáticamente ---
        $city = 'Desconocida';
        $country = 'Desconocido';

        if (is_numeric($latitud) && is_numeric($longitud)) {
            $url = "https://nominatim.openstreetmap.org/reverse?format=jsonv2&lat={$latitud}&lon={$longitud}&zoom=10&addressdetails=1";
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_USERAGENT, 'EstacionMeteorologica/1.0 (tu-email@example.com)');
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
            $response = curl_exec($ch);
            if (curl_errno($ch)) {
                $setup_warning .= "Error cURL: " . curl_error($ch) . ". ";
            }
            curl_close($ch);

            if ($response) {
                $data = json_decode($response, true);
                if (!empty($data['address'])) {
                    $address = $data['address'];
                    $city = $address['city'] ?? $address['town'] ?? $address['village'] ?? $address['hamlet'] ?? 'Desconocida';
                    $country = $address['country'] ?? $address['country_code'] ?? 'Desconocido';
                    $city = trim($city);
                    $country = mb_strtoupper(trim($country), 'UTF-8');
                }
            }
        }

        // Hash de nueva contraseña si se ingresó
        $password_final = $password_new ? password_hash($password_new,PASSWORD_DEFAULT) : $password_hash;

        // Insertar o actualizar registro único
        $stmt = $conn->prepare("
            INSERT INTO config (id, observatorio, latitud, longitud, elevacion, hardware, software, city, country, tz, password)
            VALUES (1, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
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
                password = VALUES(password)
        ");
        $stmt->bind_param("sddissssss", $observatorio, $latitud, $longitud, $elevacion, $hardware, $software, $city, $country, $tz, $password_final);
        if (!$stmt->execute()) {
            $setup_warning .= "Error al guardar la configuración en base de datos: " . $stmt->error;
        } else {
            header("Location: /weather/index.php");
            exit;
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
        <style>
            body { font-family: 'Segoe UI', Roboto, sans-serif; background: linear-gradient(135deg, #1f4037, #99f2c8); color: #222; display: flex; justify-content: center; align-items: center; min-height: 100vh; margin: 0; }
            .container { background: #fff; padding: 2rem 3rem; border-radius: 16px; box-shadow: 0 8px 25px rgba(0,0,0,0.2); max-width: 420px; width: 90%; animation: fadeIn 0.7s ease-out; }
            h1 { text-align: center; color: #1f4037; margin-bottom: 1.5rem; }
            label { display: block; margin-top: 1rem; font-weight: 600; }
            input, select { width: 100%; padding: 0.6rem; border-radius: 8px; border: 1px solid #ccc; margin-top: 0.3rem; font-size: 1rem; transition: border 0.3s; }
            input:focus, select:focus { outline: none; border-color: #1f4037; }
            button { margin-top: 1.8rem; width: 100%; background: #1f4037; color: white; font-size: 1.1rem; font-weight: bold; padding: 0.8rem; border: none; border-radius: 10px; cursor: pointer; transition: background 0.3s, transform 0.1s; }
            button:hover { background: #26735c; }
            button:active { transform: scale(0.97); }
            .warning-card { background: #ffe6e6; border-left: 6px solid #b00020; padding: 1rem; margin-top: 1.5rem; border-radius: 8px; font-weight: bold; color: #b00020; }
            @keyframes fadeIn { from { opacity: 0; transform: translateY(20px); } to { opacity: 1; transform: translateY(0); } }
            footer { text-align: center; font-size: 0.85rem; color: #666; margin-top: 1.5rem; }
        </style>
    </head>
    <body>
        <div class="container">
            <h1>Configuración inicial 🌍</h1>
            <form method="POST">
                <label for="observatorio">Nombre del observatorio / estación</label>
                <input type="text" name="observatorio" id="observatorio" value="<?= htmlspecialchars($observatorio) ?>" required>

                <label for="latitud">Latitud</label>
                <input type="number" step="0.000001" name="latitud" id="latitud" value="<?= htmlspecialchars($latitud) ?>" required>

                <label for="longitud">Longitud</label>
                <input type="number" step="0.000001" name="longitud" id="longitud" value="<?= htmlspecialchars($longitud) ?>" required>

                <label for="elevacion">Elevación (m)</label>
                <input type="number" name="elevacion" id="elevacion" value="<?= htmlspecialchars($elevacion) ?>" required>

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

                <button type="submit">Guardar configuración</button>
            </form>

            <?php if (!empty($setup_warning)): ?>
            <div class="warning-card">
                Es necesario volver a introducir los parámetros de configuración porque: <br>
                <?= nl2br(htmlspecialchars($setup_warning)) ?>
            </div>
            <?php endif; ?>

            <footer>Weather Setup · v2.2</footer>
        </div>
    </body>
</html>
