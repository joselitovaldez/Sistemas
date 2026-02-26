<?php
header('Content-Type: text/html; charset=utf-8');
session_start();

if (!isset($_SESSION['usuario'])) {
    header('Location: login.php');
    exit();
}

require_once '../config/database.php';

$usuarios = [];
if ($_SESSION['rol'] === 'admin' || $_SESSION['rol'] === 'superadmin') {
    $result = $conn->query('SELECT * FROM usuarios ORDER BY fecha_creacion DESC');
    while ($row = $result->fetch_assoc()) {
        $usuarios[] = $row;
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Usuarios - Panel Administrativo</title>
    <link rel="stylesheet" href="../css/style.css">
    <link rel="stylesheet" href="../css/admin-dashboard-rocker.css">
    <link rel="stylesheet" href="../css/admin-usuarios.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="rocker-dashboard">
<div class="main-content" style="margin-left: 0;">
    <div class="top-header" style="border-radius: 0;">
        <div class="header-left">
            <a href="index.php" class="btn-action btn-toggle" style="text-decoration: none;">
                <i class="fas fa-arrow-left"></i> Volver al Panel
            </a>
            <h1 class="page-title" style="margin-left: 1rem;">Gestionar Usuarios</h1>
        </div>
    </div>

    <div class="content-section active">
        <div class="dashboard-card">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-users"></i> Usuarios
                </h3>
            </div>
            <div class="card-body">
                <?php if ($_SESSION['rol'] === 'admin' || $_SESSION['rol'] === 'superadmin'): ?>
                <form id="formCrearUsuario" class="dashboard-form" style="margin-bottom: 1.5rem;">
                    <div class="row-form" style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 1rem;">
                        <div class="form-group">
                            <label for="nuevo_usuario">Usuario</label>
                            <input type="text" id="nuevo_usuario" name="usuario" required>
                        </div>
                        <div class="form-group">
                            <label for="nuevo_nombre">Nombre</label>
                            <input type="text" id="nuevo_nombre" name="nombre" required>
                        </div>
                        <div class="form-group">
                            <label for="nuevo_apellido_paterno">Apellido Paterno</label>
                            <input type="text" id="nuevo_apellido_paterno" name="apellido_paterno" required>
                        </div>
                        <div class="form-group">
                            <label for="nuevo_apellido_materno">Apellido Materno</label>
                            <input type="text" id="nuevo_apellido_materno" name="apellido_materno">
                        </div>
                        <div class="form-group">
                            <label for="nuevo_dni">DNI</label>
                            <input type="text" id="nuevo_dni" name="dni" maxlength="8" required>
                        </div>
                        <div class="form-group">
                            <label for="nuevo_telefono">Teléfono</label>
                            <input type="text" id="nuevo_telefono" name="telefono" maxlength="9" required>
                        </div>
                        <div class="form-group">
                            <label for="nuevo_email">Email</label>
                            <input type="email" id="nuevo_email" name="email" required>
                        </div>
                        <div class="form-group">
                            <label for="nuevo_rol">Rol</label>
                            <select id="nuevo_rol" name="rol" required>
                                <option value="asistente">Asistente</option>
                                <option value="analista">Analista</option>
                                <option value="supervisor">Supervisor</option>
                                <option value="direccion">Dirección</option>
                                <option value="decanatura">Decanatura</option>
                                <option value="admin">Admin</option>
                                <?php if ($_SESSION['rol'] === 'superadmin'): ?>
                                <option value="superadmin">Superadmin</option>
                                <?php endif; ?>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="nuevo_password">Contraseña</label>
                            <div class="password-wrapper">
                                <input type="password" id="nuevo_password" name="password" minlength="6" required>
                                <button type="button" class="password-toggle" onclick="togglePassword('nuevo_password', 'nuevo_toggle_icon')" aria-label="Mostrar contraseña">
                                    <i class="fas fa-eye" id="nuevo_toggle_icon"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                    <div style="margin-top: 1rem;">
                        <button type="submit" class="btn-action btn-toggle">Crear Usuario</button>
                    </div>
                </form>

                <div class="table-responsive">
                    <table class="modern-table">
                        <thead>
                            <tr>
                                <th>Usuario</th>
                                <th>Nombre</th>
                                <th>DNI</th>
                                <th>Email</th>
                                <th>Rol</th>
                                <th>Estado</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($usuarios)): ?>
                                <?php foreach ($usuarios as $usuario): ?>
                                <tr>
                                    <td><?php echo $usuario['usuario']; ?></td>
                                    <td><?php echo $usuario['nombre'] . ' ' . ($usuario['apellido_paterno'] ?? ''); ?></td>
                                    <td><?php echo $usuario['dni']; ?></td>
                                    <td><?php echo $usuario['email']; ?></td>
                                    <td><?php echo ucfirst($usuario['rol']); ?></td>
                                    <td>
                                        <?php
                                        $estado = $usuario['estado'] ?? 'activo';
                                        $estado_norm = strtolower(trim((string)$estado));
                                        $es_activo = ($estado_norm === '1' || $estado_norm === 'activo');
                                        ?>
                                        <span class="badge badge-status badge-<?php echo $es_activo ? 'success' : 'danger'; ?>">
                                            <?php echo $es_activo ? 'Activo' : 'Inactivo'; ?>
                                        </span>
                                    </td>
                                    <td>
                                        <button class="btn-action btn-toggle<?php echo $es_activo ? '' : ' inactive'; ?>" onclick="toggleUsuario(<?php echo (int)$usuario['id']; ?>)">
                                            <?php echo $es_activo ? 'Desactivar' : 'Activar'; ?>
                                        </button>
                                        <button class="btn-action btn-toggle" onclick="eliminarUsuario(<?php echo (int)$usuario['id']; ?>)" style="background: #dc3545;">
                                            Eliminar
                                        </button>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="7" style="text-align: center;">No hay usuarios registrados.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
                <?php else: ?>
                <p>No tienes permisos para ver esta sección.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<script>
function mostrarAlerta(mensaje) {
    alert(mensaje);
}

function togglePassword(inputId, iconId) {
    const input = document.getElementById(inputId);
    const icon = document.getElementById(iconId);
    if (!input || !icon) return;

    if (input.type === 'password') {
        input.type = 'text';
        icon.classList.remove('fa-eye');
        icon.classList.add('fa-eye-slash');
    } else {
        input.type = 'password';
        icon.classList.remove('fa-eye-slash');
        icon.classList.add('fa-eye');
    }
}

async function crearUsuario(event) {
    event.preventDefault();
    const form = event.target;
    const formData = new FormData(form);

    try {
        const response = await fetch('ajax_crear_usuario.php', {
            method: 'POST',
            body: formData
        });
        const data = await response.json();
        if (data.success) {
            mostrarAlerta('Usuario creado correctamente');
            window.location.reload();
        } else {
            mostrarAlerta(data.message || 'No se pudo crear el usuario');
        }
    } catch (error) {
        mostrarAlerta('Error al crear usuario');
    }
}

async function toggleUsuario(usuarioId) {
    const formData = new FormData();
    formData.append('usuario_id', usuarioId);

    try {
        const response = await fetch('ajax_toggle_usuario.php', {
            method: 'POST',
            body: formData
        });
        const data = await response.json();
        if (data.success) {
            mostrarAlerta('Estado actualizado');
            window.location.reload();
        } else {
            mostrarAlerta(data.message || 'No se pudo actualizar el estado');
        }
    } catch (error) {
        mostrarAlerta('Error al actualizar estado');
    }
}

async function eliminarUsuario(usuarioId) {
    if (!confirm('¿Seguro que deseas eliminar este usuario?')) {
        return;
    }

    const formData = new FormData();
    formData.append('usuario_id', usuarioId);

    try {
        const response = await fetch('ajax_eliminar_usuario.php', {
            method: 'POST',
            body: formData
        });
        const data = await response.json();
        if (data.success) {
            mostrarAlerta('Usuario eliminado');
            window.location.reload();
        } else {
            mostrarAlerta(data.message || 'No se pudo eliminar el usuario');
        }
    } catch (error) {
        mostrarAlerta('Error al eliminar usuario');
    }
}

document.addEventListener('DOMContentLoaded', () => {
    const form = document.getElementById('formCrearUsuario');
    if (form) {
        form.addEventListener('submit', crearUsuario);
    }
});
</script>
</body>
</html>
