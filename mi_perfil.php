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
        
        $ext = strtolower(pathinfo($_FILES['foto']['name'], PATHINFO_EXTENSION));
        $extensiones = array('jpg', 'jpeg', 'png', 'gif');
        
        if (in_array($ext, $extensiones) && $_FILES['foto']['size'] <= 2 * 1024 * 1024) {
            $nombre = time() . '_' . uniqid() . '.' . $ext;
            if (move_uploaded_file($_FILES['foto']['tmp_name'], $directorio . $nombre)) {
                $_SESSION['foto_usuario'] = $nombre;
                $mensaje = 'Foto actualizada exitosamente';
                $foto = $nombre;
            }
        } else {
            $error = 'Archivo inválido (JPG, PNG, GIF - máx 2MB)';
        }
    }
}

// Procesar cambio de contraseña
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['accion']) && $_POST['accion'] === 'cambiar_pass') {
    $pass_actual = $_POST['pass_actual'] ?? '';
    $pass_nueva = $_POST['pass_nueva'] ?? '';
    $pass_confirmar = $_POST['pass_confirmar'] ?? '';
    
    if (empty($pass_actual) || empty($pass_nueva) || empty($pass_confirmar)) {
        $error = 'Completa todos los campos';
    } elseif ($pass_nueva !== $pass_confirmar) {
        $error = 'Las contraseñas no coinciden';
    } elseif (strlen($pass_nueva) < 6) {
        $error = 'La contraseña debe tener al menos 6 caracteres';
    } else {
        // Verificar contraseña actual (simulado)
        if ($pass_actual === 'admin123') {
            $mensaje = 'Contraseña actualizada exitosamente';
            // En producción: actualizar en BD
        } else {
            $error = 'Contraseña actual incorrecta';
        }
    }
}

require_once 'includes/functions.php';
$institucion = array(
    'razon_social' => 'Universidad Peruana Unión',
    'ruc' => '20138122256'
);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mi Perfil - UPeU Reclamaciones</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/mi-perfil.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .form-group {
            margin-bottom: 0.9rem;
        }

        label {
            display: block;
            margin-bottom: 0.35rem;
            font-weight: 600;
            color: var(--dark);
            font-size: 0.9rem;
        }

        input[type="text"],
        input[type="email"],
        input[type="password"],
        input[type="date"],
        select,
        textarea {
            width: 100%;
            padding: 0.65rem;
            border: 2px solid #e0e0e0;
            border-radius: 8px;
            font-size: 0.9rem;
            transition: all 0.3s;
        }

        input[type="file"] {
            padding: 0.4rem;
            font-size: 0.85rem;
        }

        input:focus,
        select:focus,
        textarea:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(26, 95, 122, 0.1);
        }

        .row-form {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1rem;
        }

        .row-form.full {
            grid-template-columns: 1fr;
        }

        @media (max-width: 968px) {
            .dashboard-container {
                grid-template-columns: 1fr;
            }

            .profile-card {
                position: static;
            }

            .row-form {
                grid-template-columns: 1fr;
            }
        }

        /* Botones mejorados */
        .btn-group {
            display: flex;
            gap: 0.75rem;
            margin-top: 1rem;
        }

        .btn {
            padding: 0.65rem 1.2rem;
            border: none;
            border-radius: 8px;
            font-size: 0.9rem;
            cursor: pointer;
            transition: all 0.3s;
            font-weight: 600;
            display: inline-block;
        }

        .btn-primary {
            background: linear-gradient(135deg, var(--primary) 0%, #0e3d4f 100%);
            color: white;
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 16px rgba(26, 95, 122, 0.35);
        }

        .btn-secondary {
            background: #f0f0f0;
            color: var(--dark);
            border: 2px solid #e0e0e0;
        }

        .btn-secondary:hover {
            background: #e0e0e0;
        }

        .btn-block {
            width: 100%;
        }

        /* Alertas mejoradas */
        .alert {
            padding: 0.75rem;
            border-radius: 8px;
            margin-bottom: 1rem;
            border-left: 4px solid;
            animation: slideIn 0.3s ease;
            font-size: 0.9rem;
        }

        @keyframes slideIn {
            from { transform: translateX(-20px); opacity: 0; }
            to { transform: translateX(0); opacity: 1; }
        }

        .alert-success {
            background: #d4edda;
            color: #155724;
            border-left-color: #28a745;
        }

        .alert-danger {
            background: #f8d7da;
            color: #721c24;
            border-left-color: #dc3545;
        }

        .alert-info {
            background: #d1ecf1;
            color: #0c5460;
            border-left-color: #17a2b8;
        }

        /* Info items */
        .info-item {
            display: flex;
            justify-content: space-between;
            padding: 1rem 0;
            border-bottom: 1px solid #f0f0f0;
        }

        .info-item:last-child {
            border-bottom: none;
        }

        .info-label {
            font-weight: 600;
            color: var(--dark);
        }

        .info-value {
            color: #666;
        }

        /* Tabs */
        .tabs {
            display: flex;
            gap: 0;
            margin-bottom: 2rem;
            border-bottom: 2px solid #e0e0e0;
        }

        .tab-btn {
            padding: 1rem 1.5rem;
            border: none;
            background: transparent;
            cursor: pointer;
            font-size: 1rem;
            font-weight: 600;
            color: #999;
            transition: all 0.3s;
            border-bottom: 3px solid transparent;
            margin-bottom: -2px;
        }

        .tab-btn:hover {
            color: var(--primary);
        }

        .tab-btn.active {
            color: var(--primary);
            border-bottom-color: var(--primary);
        }

        .tab-content {
            display: none;
        }

        .tab-content.active {
            display: block;
            animation: fadeIn 0.3s ease;
        }

        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }

        /* Quick actions */
        .quick-actions {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 1rem;
            margin-top: 2rem;
        }

        .action-card {
            padding: 1.5rem;
            background: linear-gradient(135deg, #f5f7fa 0%, #fff 100%);
            border-radius: 8px;
            text-align: center;
            cursor: pointer;
            transition: all 0.3s;
            border: 2px solid #e0e0e0;
        }

        .action-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 8px 16px rgba(0,0,0,0.1);
            border-color: var(--primary);
        }

        .action-icon {
            font-size: 2rem;
            margin-bottom: 0.5rem;
        }

        .action-title {
            font-weight: 600;
            color: var(--primary);
        }

        /* Photo upload preview */
        .photo-preview {
            width: 150px;
            height: 150px;
            margin: 1rem auto;
            border-radius: 50%;
            background: #f0f0f0;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
            border: 3px dashed var(--primary);
        }

    </style>
</head>
<body>

<header>
    <div class="header-content">
        <div style="display: flex; justify-content: space-between; align-items: center;">
            <div>
                <h1>🎓 Mi Perfil</h1>
                <p>Centro de Control de Reclamaciones</p>
            </div>
            <div style="text-align: right;">
                <a href="index.php" style="color: white; text-decoration: none; margin-right: 2rem;">← Volver</a>
            </div>
        </div>
    </div>
</header>

<div class="container">
    <div class="dashboard-container">
        
        <!-- Panel de Perfil -->
        <div class="profile-card">
            <div class="profile-photo">
                <?php if ($foto): ?>
                    <img src="uploads/perfiles/<?php echo $foto; ?>" alt="Foto de perfil">
                <?php else: ?>
                    👤
                <?php endif; ?>
            </div>
            
            <h2>Usuario Visitante</h2>
            <p style="margin-top: 0.5rem; font-weight: 300;">Sistema de Reclamaciones UPeU</p>
            
            <div style="background: rgba(255,255,255,0.1); padding: 1rem; border-radius: 8px; margin: 1.5rem 0;">
                <p style="font-size: 0.9rem; margin: 0;">📋 ID de Sesión</p>
                <p style="font-size: 1.2rem; font-weight: bold; margin: 0.5rem 0 0;">UPeU-<?php echo strtoupper(substr(md5(time()), 0, 8)); ?></p>
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

            <button class="btn btn-primary btn-block" onclick="document.getElementById('foto-tab').click(); document.querySelector('.tab-btn.active').classList.remove('active'); document.getElementById('foto-tab').classList.add('active'); document.querySelector('.tab-content.active').classList.remove('active'); document.getElementById('foto-content').classList.add('active');" style="margin-top: 1.5rem;">
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
            <div style="background: white; border-radius: 12px; padding: 1.5rem; box-shadow: 0 4px 12px rgba(0,0,0,0.08);">
                <div class="tabs">
                    <button class="tab-btn active" onclick="cambiarTab('resumen', this)">📊 Mi Resumen</button>
                    <button class="tab-btn" id="foto-tab" onclick="cambiarTab('foto', this)">📸 Foto de Perfil</button>
                    <button class="tab-btn" onclick="cambiarTab('seguridad', this)">🔐 Seguridad</button>
                    <button class="tab-btn" onclick="cambiarTab('mis-reclamaciones', this)">📋 Mis Reclamaciones</button>
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
                        <span class="info-value"><strong style="color: var(--success);">0</strong></span>
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
                        
                        <div class="form-group" style="text-align: center;">
                            <label style="text-align: center;">Foto de Perfil Actual</label>
                            <div class="photo-preview">
                                <?php if ($foto): ?>
                                    <img src="uploads/perfiles/<?php echo $foto; ?>" alt="Foto de perfil">
                                <?php else: ?>
                                    <span style="font-size: 3rem; color: #ccc;">📷</span>
                                <?php endif; ?>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="foto">Selecciona una nueva foto 📸</label>
                            <input type="file" id="foto" name="foto" accept="image/jpeg,image/png,image/gif" required>
                            <small class="help-text" style="display: block; margin-top: 0.5rem; color: #666;">
                                JPG, PNG o GIF • Máximo 2 MB • Se recomienda foto cuadrada (1:1)
                            </small>
                        </div>

                        <div class="btn-group">
                            <button type="submit" class="btn btn-primary">Actualizar Foto</button>
                        </div>
                    </form>

                    <div style="margin-top: 2rem; padding: 1rem; background: #d1ecf1; border-left: 4px solid #17a2b8; border-radius: 8px;">
                        <p style="margin: 0; color: #0c5460;">
                            💡 <strong>Consejo:</strong> Usa una foto clara y profesional. Se mostrará en tu perfil dentro del sistema.
                        </p>
                    </div>
                </div>

                <!-- TAB: Seguridad -->
                <div id="seguridad" class="tab-content">
                    <form method="POST">
                        <input type="hidden" name="accion" value="cambiar_pass">
                        
                        <h3 style="color: var(--primary); margin-bottom: 1.5rem; font-size: 1.2rem;">🔐 Cambiar Contraseña</h3>
                        
                        <div class="form-group">
                            <label for="pass_actual">Contraseña Actual <span class="required">*</span></label>
                            <div class="password-wrapper">
                                <input type="password" id="pass_actual" name="pass_actual" placeholder="Ingresa tu contraseña actual" required>
                                <button type="button" class="password-toggle" onclick="togglePassword('pass_actual')">
                                    <i class="fas fa-eye"></i>
                                </button>
                            </div>
                        </div>

                        <div class="row-form">
                            <div class="form-group">
                                <label for="pass_nueva">Nueva Contraseña <span class="required">*</span></label>
                                <div class="password-wrapper">
                                    <input type="password" id="pass_nueva" name="pass_nueva" placeholder="Mínimo 6 caracteres" required minlength="6">
                                    <button type="button" class="password-toggle" onclick="togglePassword('pass_nueva')">
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
                                    <button type="button" class="password-toggle" onclick="togglePassword('pass_confirmar')">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                </div>
                            </div>
                        </div>

                        <div style="background: #fff3cd; padding: 1rem; border-left: 4px solid var(--warning); border-radius: 8px; margin: 1.5rem 0;">
                            <p style="margin: 0; color: #856404;">
                                ⚠️ <strong>Importante:</strong> Usa una contraseña fuerte con números, letras y caracteres especiales.
                            </p>
                        </div>

                        <div class="btn-group">
                            <button type="submit" class="btn btn-primary">Actualizar Contraseña</button>
                            <button type="reset" class="btn btn-secondary">Cancelar</button>
                        </div>
                    </form>
                </div>

                <!-- TAB: Mis Reclamaciones -->
                <div id="mis-reclamaciones" class="tab-content">
                    <div style="text-align: center; padding: 2rem; color: #999;">
                        <p style="font-size: 3rem; margin-bottom: 1rem;">📭</p>
                        <p>No tienes reclamaciones registradas.</p>
                        <p style="font-size: 0.9rem; margin-top: 1rem;">
                            <a href="index.php" style="color: var(--primary); text-decoration: none; font-weight: 600;">
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

<script>
function cambiarTab(tabId, btn) {
    // Ocultar todos los tabs
    document.querySelectorAll('.tab-content').forEach(tab => {
        tab.classList.remove('active');
    });
    
    // Remover active de todos los botones
    document.querySelectorAll('.tab-btn').forEach(b => {
        b.classList.remove('active');
    });
    
    // Mostrar el tab seleccionado
    document.getElementById(tabId).classList.add('active');
    btn.classList.add('active');
}

// Mostrar/ocultar contraseña
function togglePassword(inputId) {
    const input = document.getElementById(inputId);
    const button = event.currentTarget;
    const icon = button.querySelector('i');
    
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

// Validar fuerza de contraseña
const passwordInput = document.getElementById('pass_nueva');
if (passwordInput) {
    const strengthContainer = document.getElementById('password-strength');
    const strengthBar = document.getElementById('strength-bar');
    const strengthText = document.getElementById('strength-text');

    passwordInput.addEventListener('input', function() {
        const password = this.value;
        
        if (password.length === 0) {
            strengthContainer.classList.remove('show');
            return;
        }
        
        strengthContainer.classList.add('show');
        
        let strength = 0;
        
        // Criterios de fuerza
        if (password.length >= 6) strength++;
        if (password.length >= 8) strength++;
        if (/[a-z]/.test(password) && /[A-Z]/.test(password)) strength++;
        if (/\d/.test(password)) strength++;
        if (/[^a-zA-Z0-9]/.test(password)) strength++;
        
        // Actualizar visualización
        strengthBar.className = 'strength-bar-fill';
        strengthText.className = 'strength-text';
        
        if (strength <= 2) {
            strengthBar.classList.add('weak');
            strengthText.classList.add('weak');
            strengthText.textContent = '⚠️ Débil';
        } else if (strength <= 3) {
            strengthBar.classList.add('medium');
            strengthText.classList.add('medium');
            strengthText.textContent = '✓ Media';
        } else {
            strengthBar.classList.add('strong');
            strengthText.classList.add('strong');
            strengthText.textContent = '✓✓ Fuerte';
        }
    });
}
</script>

</body>
</html>
