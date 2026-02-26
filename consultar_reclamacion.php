<?php
require_once 'includes/functions.php';

$reclamacion = null;
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['folio'])) {
    $folio = sanitizar($_POST['folio']);
    $reclamacion = obtenerReclamacion($folio);
    if (!$reclamacion) {
        $error = 'Folio no encontrado. Verifica que sea correcto.';
    }
}

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
    <title>Consultar Reclamación - UPeU</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/consultar.css">
</head>
<body>

<header>
    <div class="header-content">
        <div style="display: flex; justify-content: space-between; align-items: center;">
            <div>
                <h1>📋 Consultar Reclamación</h1>
                <p>Verifica el estado de tu reclamo en tiempo real</p>
            </div>
            <div style="text-align: right;">
                <a href="index.php" style="color: white; text-decoration: none; margin-right: 1rem;">📝 Nueva</a>
                <a href="mi_perfil.php" style="color: white; text-decoration: none;">👤 Mi Perfil</a>
            </div>
        </div>
    </div>
</header>

<div class="container">
    <div class="search-hero">
        <div class="search-form-container">
            <h2>🔍 Busca tu Reclamación</h2>
            <p>Ingresa el folio que recibiste para ver el estado de tu reclamación</p>
            
            <?php if ($error): ?>
                <div class="alert alert-danger" style="margin-bottom: 1.5rem;">✗ <?php echo $error; ?></div>
            <?php endif; ?>
            
            <form method="POST">
                <div class="search-input-group">
                    <input type="text" name="folio" placeholder="Ej. UPeU-000055" 
                           value="<?php echo $_POST['folio'] ?? ''; ?>" required>
                    <button type="submit">Buscar</button>
                </div>
            </form>
            
            <p style="margin-top: 1rem; font-size: 0.85rem; opacity: 0.8;">
                💡 Usa el folio que te dimos cuando registraste tu reclamación
            </p>
        </div>
    </div>

    <?php if ($reclamacion): ?>
    <div class="result-container">
        <!-- Sidebar -->
        <div class="result-sidebar">
            <div class="result-status">
                <div class="status-icon">
                    <?php 
                        $estado = $reclamacion['estado'];
                        if ($estado === 'Resuelto') {
                            echo '✓';
                        } elseif ($estado === 'En revisión') {
                            echo '⏳';
                        } elseif ($estado === 'No procede') {
                            echo '✗';
                        } else {
                            echo '📋';
                        }
                    ?>
                </div>
                <p style="margin: 0.5rem 0 0; color: #666; font-size: 0.9rem;">ESTADO ACTUAL</p>
                <span class="status-badge <?php 
                    if ($estado === 'Resuelto') echo 'resuelto';
                    elseif ($estado === 'En revisión') echo 'revision';
                    elseif ($estado === 'No procede') echo 'noprocede';
                    else echo 'pendiente';
                ?>">
                    <?php echo $estado; ?>
                </span>
            </div>

            <div class="folio-display">
                <p>FOLIO DE ATENCIÓN</p>
                <div class="folio" onclick="copiarTexto('<?php echo $reclamacion['folio']; ?>')" style="cursor: pointer;">
                    <?php echo $reclamacion['folio']; ?>
                </div>
                <p style="margin-top: 0.5rem; font-size: 0.75rem;">Click para copiar</p>
            </div>

            <div style="background: white; padding: 1rem; border-radius: 8px; font-size: 0.9rem;">
                <p style="margin: 0; color: #666;">📅 Registrado</p>
                <p style="margin: 0.5rem 0 0; font-weight: bold; color: var(--primary);">
                    <?php echo date('d/m/Y', strtotime($reclamacion['fecha_registro'])); ?>
                </p>
                <p style="margin: 0.5rem 0 0; color: #999; font-size: 0.85rem;">
                    a las <?php echo str_replace(['AM', 'PM'], ['.a.m.', '.p.m.'], date('h:i A', strtotime($reclamacion['fecha_registro']))); ?>
                </p>
            </div>

            <button class="btn btn-primary btn-block" style="margin-top: 1rem;" onclick="location.reload()">
                🔄 Actualizar
            </button>
            <button class="btn btn-secondary btn-block" style="margin-top: 0.5rem;" onclick="location.href='consultar_reclamacion.php'">
                🔍 Nueva Búsqueda
            </button>
        </div>

        <!-- Contenido principal -->
        <div>
            <!-- Datos Personales -->
            <div class="detail-card">
                <h3>👤 Datos Personales</h3>
                <div class="detail-row">
                    <span class="detail-label">Nombres</span>
                    <span class="detail-value"><?php echo $reclamacion['nombres'] . ' ' . $reclamacion['apellido_paterno'] . ' ' . $reclamacion['apellido_materno']; ?></span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">DNI/CE</span>
                    <span class="detail-value"><?php echo $reclamacion['dni_ce']; ?></span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Email</span>
                    <span class="detail-value"><?php echo $reclamacion['email']; ?></span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Teléfono</span>
                    <span class="detail-value"><?php echo $reclamacion['telefono']; ?></span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Campus</span>
                    <span class="detail-value"><?php echo $reclamacion['campus']; ?></span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Área</span>
                    <span class="detail-value"><?php echo $reclamacion['departamento']; ?></span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Subárea</span>
                    <span class="detail-value"><?php echo $reclamacion['area']; ?></span>
                </div>
            </div>

            <!-- Bien Contratado -->
            <div class="detail-card">
                <h3>🛍️ Bien Contratado</h3>
                <div class="detail-row">
                    <span class="detail-label">Tipo</span>
                    <span class="detail-value"><?php echo $reclamacion['tipo_bien']; ?></span>
                </div>
                <div style="padding: 0.75rem 0; border-bottom: 1px solid #f0f0f0;">
                    <p style="margin: 0; font-weight: 600; color: var(--dark); margin-bottom: 0.5rem;">Descripción</p>
                    <p style="margin: 0; color: #666;"><?php echo nl2br($reclamacion['descripcion_asunto']); ?></p>
                </div>
            </div>

            <!-- Reclamación -->
            <div class="detail-card">
                <h3>📝 Reclamación</h3>
                <div class="detail-row">
                    <span class="detail-label">Tipo</span>
                    <span class="detail-value"><?php echo $reclamacion['tipo_registro']; ?></span>
                </div>
                <div style="padding: 0.75rem 0; border-bottom: 1px solid #f0f0f0;">
                    <p style="margin: 0; font-weight: 600; color: var(--dark); margin-bottom: 0.5rem;">Detalle</p>
                    <p style="margin: 0; color: #666;"><?php echo nl2br($reclamacion['detalle_reclamacion']); ?></p>
                </div>
                <?php if ($reclamacion['pedido']): ?>
                <div style="padding: 0.75rem 0;">
                    <p style="margin: 0; font-weight: 600; color: var(--dark); margin-bottom: 0.5rem;">Pedido</p>
                    <p style="margin: 0; color: #666;"><?php echo nl2br($reclamacion['pedido']); ?></p>
                </div>
                <?php endif; ?>
            </div>

            <!-- Archivos -->
            <?php if ($reclamacion['archivo_adjunto']): ?>
            <div class="detail-card">
                <h3>📎 Archivo Adjunto</h3>
                <a href="descargar.php?archivo=<?php echo urlencode($reclamacion['archivo_adjunto']); ?>" class="btn btn-primary">
                    ⬇️ Descargar Archivo
                </a>
            </div>
            <?php endif; ?>

            <!-- Respuesta o Pendiente -->
            <?php if ($reclamacion['respuesta']): ?>
                <div class="response-box">
                    <h4>✓ Respuesta Recibida</h4>
                    <p><?php echo nl2br($reclamacion['respuesta']); ?></p>
                    <p style="margin-top: 1rem; font-size: 0.85rem; color: #155724;">
                        Respondida el <?php echo date('d \d\e F \d\e Y', strtotime($reclamacion['fecha_respuesta'])); ?>
                    </p>
                </div>
            <?php else: ?>
                <div class="pending-box">
                    <p>
                        ⏳ <strong>Tu reclamación está siendo procesada.</strong><br>
                        Recibirás una respuesta dentro de los 30 días hábiles establecidos conforme al Código de Protección y Defensa del Consumidor.
                    </p>
                </div>
            <?php endif; ?>
        </div>
    </div>
    <?php endif; ?>
</div>

<footer>
    <p>Copyright © UPeU 2026. Todos los derechos son reservados — 
    <a href="https://www.upeu.edu.pe/">Universidad Peruana Unión</a></p>
    <p style="margin-top: 1rem; font-size: 0.9rem;">
        <a href="index.php">Nueva Reclamación</a> | 
        <a href="mi_perfil.php">Mi Perfil</a> | 
        <a href="admin/login.php">Acceso Administrativo</a>
    </p>
</footer>

<script src="js/script.js"></script>

</body>
</html>
