<?php
header('Content-Type: text/html; charset=utf-8');
session_start();

if (isset($_SESSION['usuario'])) {
    header('Location: index.php');
    exit();
}

require_once '../config/database.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $usuario = trim($_POST['usuario'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($usuario === '' || $password === '') {
        $error = 'Completa usuario y contraseña';
    } else {
        $stmt = $conn->prepare('SELECT id, usuario, password, rol, estado, nombre, apellido_paterno, apellido_materno, dni, email, telefono, foto FROM usuarios WHERE usuario = ? LIMIT 1');
        $stmt->bind_param('s', $usuario);
        $stmt->execute();
        $result = $stmt->get_result();
        $user = $result->fetch_assoc();

        if (!$user || !password_verify($password, $user['password'])) {
            $error = 'Credenciales inválidas';
        } else {
            $estado_raw = strtolower(trim((string)($user['estado'] ?? '')));
            $estado_inactivo = ($estado_raw === '0' || $estado_raw === 'inactivo' || $estado_raw === 'deshabilitado' || $estado_raw === 'bloqueado');
            if ($estado_inactivo) {
                $error = 'Usuario inactivo. Contacta al administrador';
            } else {
                $_SESSION['id'] = (int)$user['id'];
                $_SESSION['usuario'] = $user['usuario'];
                $_SESSION['rol'] = $user['rol'];
                $_SESSION['nombre'] = $user['nombre'] ?? '';
                $_SESSION['apellido_paterno'] = $user['apellido_paterno'] ?? '';
                $_SESSION['apellido_materno'] = $user['apellido_materno'] ?? '';
                $_SESSION['dni'] = $user['dni'] ?? '';
                $_SESSION['email'] = $user['email'] ?? '';
                $_SESSION['telefono'] = $user['telefono'] ?? '';
                $_SESSION['foto'] = $user['foto'] ?? '';
                header('Location: index.php');
                exit();
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Acceso Administrativo - UPeU</title>
    <link rel="stylesheet" href="../css/style.css">
    <link rel="stylesheet" href="../css/admin-login.css">
    <link rel="stylesheet" href="../css/admin-login-inline.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="admin-login">
    <div class="login-container">
        <div class="login-card">
            <h1>Acceso Administrativo</h1>

            <?php if ($error !== ''): ?>
                <div class="alert alert-danger alert-spaced">
                    <?php echo htmlspecialchars($error, ENT_QUOTES, 'UTF-8'); ?>
                </div>
            <?php endif; ?>

            <form method="POST" action="">
                <div class="form-group">
                    <label for="usuario">Usuario</label>
                    <input type="text" id="usuario" name="usuario" placeholder="Ingresa tu usuario" required>
                </div>

                <div class="form-group">
                    <label for="password">Contraseña</label>
                    <div class="password-wrapper">
                        <input type="password" id="password" name="password" placeholder="Ingresa tu contraseña" required>
                        <button type="button" class="password-toggle" data-action="toggle-password" data-target="password" data-icon="toggleIcon" aria-label="Mostrar contraseña">
                            <i class="fas fa-eye" id="toggleIcon"></i>
                        </button>
                    </div>
                </div>

                <button type="submit" class="btn btn-primary btn-block">Ingresar</button>
            </form>
        </div>
    </div>

    <script src="../js/admin-login.js"></script>
</body>
</html>
