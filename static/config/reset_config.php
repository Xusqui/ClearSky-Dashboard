<!DOCTYPE html>
<?php
// === CONEXIÓN A LA BASE DE DATOS ===
include './config_db.php';

$conn = new mysqli($db_url, $db_user, $db_pass, $db_database);
if ($conn->connect_error) {
    die("Error de conexión: " . $conn->connect_error);
}

// === VARIABLES DE ESTADO ===
$solicitarPassword = false;
$errorPassword = "";

// === COMPROBAR SI HAY CONTRASEÑA DEFINIDA ===
$result = $conn->query("SELECT password FROM config LIMIT 1");
$config = $result ? $result->fetch_assoc() : null;
$passwordDefinida = !empty($config['password']);

// === SI SE INTENTA BORRAR ===
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['confirmar'])) {
    if ($passwordDefinida) {
        // Comprobar si se envió la contraseña
        if (!empty($_POST['password'])) {
            // Usar password_verify() para comparar con el hash
            if (password_verify($_POST['password'], $config['password'])) {
                $conn->query("DROP TABLE IF EXISTS config");
                header("Location: ./setup.php");
                exit;
            } else {
                $solicitarPassword = true;
                $errorPassword = "❌ Contraseña incorrecta.";
            }
        } else {
            $solicitarPassword = true;
        }
    } else {
        // Si no hay contraseña, borrar directamente y redirigir
        $conn->query("DROP TABLE IF EXISTS config");
        header("Location: ./setup.php");
        exit;
    }
}
?>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Borrar configuración</title>
<style>
    body {
        font-family: 'Segoe UI', Roboto, sans-serif;
        background: linear-gradient(135deg, #ff9966, #ff5e62);
        color: #222;
        display: flex;
        justify-content: center;
        align-items: center;
        height: 100vh;
        margin: 0;
    }
    .container {
        background: #fff;
        padding: 2.5rem 3rem;
        border-radius: 16px;
        box-shadow: 0 8px 25px rgba(0,0,0,0.25);
        max-width: 420px;
        width: 90%;
        text-align: center;
        animation: fadeIn 0.7s ease-out;
    }
    h1 {
        color: #c0392b;
        margin-bottom: 1rem;
    }
    p {
        font-size: 1.05rem;
    }
    form {
        margin-top: 1.5rem;
    }
    input[type="password"] {
        padding: 0.5rem;
        font-size: 1rem;
        width: 100%;
        margin-bottom: 0.5rem;
        border-radius: 6px;
        border: 1px solid #ccc;
    }
    button {
        background: #c0392b;
        color: white;
        border: none;
        padding: 0.8rem 1.2rem;
        font-size: 1rem;
        border-radius: 8px;
        cursor: pointer;
        transition: background 0.3s, transform 0.1s;
        margin: 0.5rem;
    }
    button:hover {
        background: #e74c3c;
    }
    button:active {
        transform: scale(0.97);
    }
    .cancelar {
        background: #777;
    }
    .error {
        color: #e74c3c;
        font-weight: bold;
    }
    footer {
        margin-top: 2rem;
        color: #888;
        font-size: 0.85rem;
    }
    @keyframes fadeIn {
        from { opacity: 0; transform: translateY(20px); }
        to { opacity: 1; transform: translateY(0); }
    }
</style>
</head>
<body>
<div class="container">
    <h1>⚠️ Borrar configuración</h1>

    <?php if ($solicitarPassword): ?>
        <p>Introduce la contraseña para borrar la configuración:</p>
        <?php if ($errorPassword) echo "<p class='error'>$errorPassword</p>"; ?>
        <form method="POST">
            <input type="password" name="password" placeholder="Contraseña" required>
            <button type="submit" name="confirmar" value="1">Borrar configuración</button>
            <button type="button" class="cancelar" onclick="window.location='index.php'">Cancelar</button>
        </form>
    <?php else: ?>
        <p>¿Seguro que quieres borrar todos los datos de configuración?<br>
        Esta acción no se puede deshacer.</p>
        <form method="POST">
            <button type="submit" name="confirmar" value="1">Sí, borrar</button>
            <button type="button" class="cancelar" onclick="window.location='index.php'">Cancelar</button>
        </form>
    <?php endif; ?>

    <footer>Weather Tools · v2.2</footer>
</div>
</body>
</html>
