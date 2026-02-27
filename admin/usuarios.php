<?php
header('Content-Type: text/html; charset=utf-8');
session_start();

if (!isset($_SESSION['usuario'])) {
    header('Location: login.php');
    exit();
}

require_once '../config/database.php';

$usuarios = [];
$result = $conn->query('SELECT * FROM usuarios ORDER BY fecha_creacion DESC');
while ($row = $result->fetch_assoc()) {
    $usuarios[] = $row;
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
    <link rel="stylesheet" href="../css/admin-usuarios-inline.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="rocker-dashboard">
<div class="main-content main-content-full">
    <div class="top-header top-header-flat">
        <div class="header-left">
            <a href="index.php" class="btn btn-primary btn-link">
                <i class="fas fa-arrow-left"></i> Volver al Panel
            </a>
            <h1 class="page-title page-title-spaced">Gestionar Usuarios</h1>
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
                <form id="formCrearUsuario" class="dashboard-form form-spaced">
                    <div class="row-form user-form-grid">
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
                                <option value="superadmin">Superadmin</option>
                                <option value="asistente_admin">Asistente Admin</option>
                                <option value="decano_upg">Decano/ UPG</option>
                                <option value="director_escuela_upg">Director de Escuela / UPG</option>
                                <option value="director_area">Director de Area</option>
                                <option value="asistente">Asistente</option>
                                <option value="auditor">Auditor</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="nuevo_password">Contraseña</label>
                            <div class="password-wrapper">
                                <input type="password" id="nuevo_password" name="password" minlength="6" required>
                                <button type="button" class="password-toggle" data-action="toggle-password" data-target="nuevo_password" data-icon="nuevo_toggle_icon" aria-label="Mostrar contraseña">
                                    <i class="fas fa-eye" id="nuevo_toggle_icon"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                    <div class="form-actions">
                        <button type="submit" class="btn btn-primary">Crear Usuario</button>
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
                                    <td>
                                        <?php
                                        $rol_labels_table = [
                                            'superadmin' => 'Superadmin',
                                            'asistente_admin' => 'Asistente Admin',
                                            'decano_upg' => 'Decano/ UPG',
                                            'director_escuela_upg' => 'Director de Escuela / UPG',
                                            'director_area' => 'Director de Area',
                                            'asistente' => 'Asistente',
                                            'auditor' => 'Auditor'
                                        ];
                                        echo $rol_labels_table[$usuario['rol']] ?? $usuario['rol'];
                                        ?>
                                    </td>
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
                                        <button class="btn btn-warning" data-action="toggle-usuario" data-id="<?php echo (int)$usuario['id']; ?>">
                                            <?php echo $es_activo ? '<i class="fas fa-ban"></i> Desactivar' : '<i class="fas fa-check"></i> Activar'; ?>
                                        </button>
                                        <button class="btn btn-danger" data-action="eliminar-usuario" data-id="<?php echo (int)$usuario['id']; ?>">
                                            <i class="fas fa-trash"></i> Eliminar
                                        </button>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="7" class="table-empty">No hay usuarios registrados.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="../js/admin-usuarios.js"></script>
</body>
</html>
