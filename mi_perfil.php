<?php
session_start();

// Simulando datos de usuario (en producción vendrían de la BD)
// Si no hay usuario logueado, redirigir a consultar
if (!isset($_SESSION['usuario_reclamacion'])) {
    $_SESSION['usuario_reclamacion'] = null;
}

$usuario = $_SESSION['usuario_reclamacion'];
$foto = isset($_SESSION['foto_usuario']) ? $_SESSION['foto_usuario'] : null;
$mensaje = '';
$error = '';

// Procesar cambio de foto
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['accion']) && $_POST['accion'] === 'cambiar_foto') {
    if (isset($_FILES['foto']) && $_FILES['foto']['size'] > 0) {
        $directorio = dirname(__FILE__) . '/uploads/perfiles/';
        
        // Crear directorio si no existe
        if (!is_dir($directorio)) {
            mkdir($directorio, 0755, true);
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mi Perfil - Sistema de Reclamaciones</title>
    <link rel="stylesheet" href="css/mi-perfil.css">
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/mi-perfil-inline.css">
    <link rel="stylesheet" href="css/admin-dashboard.css>
</head>
<body>
<div class="page-container">
    <!-- Sidebar -->
    <div class="sidebar">
        <script src="js/mi-perfil.js"></script>
            <div class="profile-photo">
                <?php if ($foto): ?>
                    <img src="uploads/perfiles/<?php echo $foto; ?>" alt="Foto de perfil">
                <?php else: ?>
                    👤
                <?php endif; ?>
            </div>
            
            <h2>Usuario Visitante</h2>
            <p class="profile-subtitle">Sistema de Reclamaciones UPeU</p>
            
            <div class="session-box">
                <p class="session-label">📋 ID de Sesión</p>
                <p class="session-id">UPeU-<?php echo strtoupper(substr(md5(time()), 0, 8)); ?></p>
            </div>

            <div class="profile-stats">
                <div class="stat">
                    <span class="stat-number">0</span>
                    <span class="stat-label">Reclamaciones</span>
                </div>
                <div class="stat">
                    <span class="stat-number">0</span>
                    <span class="stat-label">Resueltas</span>
                </div>
            </div>

            <button class="btn btn-primary btn-block edit-profile-btn" type="button" data-action="open-foto-tab">
                ✏️ Editar Perfil
            </button>
        </div>

        <!-- Contenido principal -->
        <div class="content-area">
            
            <?php if ($mensaje): ?>
                <div class="alert alert-success">✓ <?php echo $mensaje; ?></div>
            <?php endif; ?>

            <?php if ($error): ?>
                <div class="alert alert-danger">✗ <?php echo $error; ?></div>
            <?php endif; ?>

            <!-- Tabs -->
            <div class="tabs-card">
                <div class="tabs">
                    <button class="tab-btn active" type="button" data-tab="resumen">📊 Mi Resumen</button>
                    <button class="tab-btn" id="foto-tab" type="button" data-tab="foto">📸 Foto de Perfil</button>
                    <button class="tab-btn" type="button" data-tab="seguridad">🔐 Seguridad</button>
                    <button class="tab-btn" type="button" data-tab="mis-reclamaciones">📋 Mis Reclamaciones</button>
                </div>

                <!-- TAB: Resumen -->
                <div id="resumen" class="tab-content active">
                    <div class="info-item">
                        <span class="info-label">👤 Rol</span>
                        <span class="info-value">Usuario Visitante</span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">📅 Se unió</span>
                        <span class="info-value"><?php echo date('d/m/Y'); ?></span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">✅ Reclamaciones Totales</span>
                        <span class="info-value"><strong>0</strong></span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">⏳ En Proceso</span>
                        <span class="info-value"><strong>0</strong></span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">✓ Resueltas</span>
                        <span class="info-value"><strong class="text-success">0</strong></span>
                    </div>

                    <div class="quick-actions">
                        <a href="index.php" class="action-card">
                            <div class="action-icon">📝</div>
                            <div class="action-title">Nueva Reclamación</div>
                        </a>
                        <a href="consultar_reclamacion.php" class="action-card">
                            <div class="action-icon">🔍</div>
                            <div class="action-title">Consultar Estado</div>
                        </a>
                    </div>
                </div>

                <!-- TAB: Foto de Perfil -->
                <div id="foto" class="tab-content">
                    <form method="POST" enctype="multipart/form-data">
                        <input type="hidden" name="accion" value="cambiar_foto">
                        
                        <div class="form-group text-center">
                            <label class="label-center">Foto de Perfil Actual</label>
                            <div class="photo-preview">
                                <?php if ($foto): ?>
                                    <img src="uploads/perfiles/<?php echo $foto; ?>" alt="Foto de perfil">
                                <?php else: ?>
                                    <span class="photo-placeholder">📷</span>
                                <?php endif; ?>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="foto">Selecciona una nueva foto 📸</label>
                            <input type="file" id="foto" name="foto" accept="image/jpeg,image/png,image/gif" required>
                            <small class="help-text help-text-block">
                                JPG, PNG o GIF • Máximo 2 MB • Se recomienda foto cuadrada (1:1)
                            </small>
                        </div>

                        <div class="btn-group">
                            <button type="submit" class="btn btn-primary">Actualizar Foto</button>
                        </div>
                    </form>

                    <div class="tip-box">
                        <p class="tip-text">
                            💡 <strong>Consejo:</strong> Usa una foto clara y profesional. Se mostrará en tu perfil dentro del sistema.
                        </p>
                    </div>
                </div>

                <!-- TAB: Seguridad -->
                <div id="seguridad" class="tab-content">
                    <form method="POST">
                        <input type="hidden" name="accion" value="cambiar_pass">
                        
                        <h3 class="section-title">🔐 Cambiar Contraseña</h3>
                        
                        <div class="form-group">
                            <label for="pass_actual">Contraseña Actual <span class="required">*</span></label>
                            <div class="password-wrapper">
                                <input type="password" id="pass_actual" name="pass_actual" placeholder="Ingresa tu contraseña actual" required>
                                <button type="button" class="password-toggle" data-action="toggle-password" data-target="pass_actual">
                                    <i class="fas fa-eye"></i>
                                </button>
                            </div>
                        </div>

                        <div class="row-form">
                            <div class="form-group">
                                <label for="pass_nueva">Nueva Contraseña <span class="required">*</span></label>
                                <div class="password-wrapper">
                                    <input type="password" id="pass_nueva" name="pass_nueva" placeholder="Mínimo 6 caracteres" required minlength="6">
                                    <button type="button" class="password-toggle" data-action="toggle-password" data-target="pass_nueva">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                </div>
                                <div class="password-strength" id="password-strength">
                                    <div class="strength-bar">
                                        <div class="strength-bar-fill" id="strength-bar"></div>
                                    </div>
                                    <span class="strength-text" id="strength-text"></span>
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="pass_confirmar">Confirmar Contraseña <span class="required">*</span></label>
                                <div class="password-wrapper">
                                    <input type="password" id="pass_confirmar" name="pass_confirmar" placeholder="Repite la nueva contraseña" required>
                                    <button type="button" class="password-toggle" data-action="toggle-password" data-target="pass_confirmar">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                </div>
                            </div>
                        </div>

                        <div class="warning-box">
                            <p class="warning-text">
                                ⚠️ <strong>Importante:</strong> Usa una contraseña fuerte con números, letras y caracteres especiales.
                            </p>
                        </div>

                        <div class="btn-group">
                            <button type="submit" class="btn btn-primary">Actualizar Contraseña</button>
                            <button type="reset" class="btn btn-secondary">
                                <i class="fa-regular fa-circle-xmark"></i> Cancelar
                            </button>
                        </div>
                    </form>
                </div>

                <!-- TAB: Mis Reclamaciones -->
                <div id="mis-reclamaciones" class="tab-content">
                    <div class="empty-state">
                        <p class="empty-state-icon">📭</p>
                        <p>No tienes reclamaciones registradas.</p>
                        <p class="empty-state-note">
                            <a href="index.php" class="empty-state-link">
                                Registrar una reclamación →
                            </a>
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<footer>
    <p>Copyright © UPeU 2026. Todos los derechos son reservados</p>
</footer>

<script src="js/mi-perfil.js"></script>

</body>
</html>
