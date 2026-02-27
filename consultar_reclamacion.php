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
    <link rel="stylesheet" href="css/consultar-inline.css">
</head>
<body>

<header>
    <div class="header-content">
        <div class="header-row">
            <div>
                <h1>📋 Consultar Reclamación</h1>
                <p>Verifica el estado de tu reclamo en tiempo real</p>
            </div>
            <div class="header-actions">
                <a href="index.php" class="header-link header-link-spaced">📝 Nueva</a>
                <a href="mi_perfil.php" class="header-link">👤 Mi Perfil</a>
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
                <div class="alert alert-danger alert-spaced">✗ <?php echo $error; ?></div>
            <?php endif; ?>
            
            <form method="POST">
                <div class="search-input-group">
                    <input type="text" name="folio" placeholder="Ej. UPeU-000055" 
                           value="<?php echo $_POST['folio'] ?? ''; ?>" required>
                    <button type="submit">Buscar</button>
                </div>
            </form>
            
            <p class="search-hint">
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
                <p class="status-label">ESTADO ACTUAL</p>
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
                <div class="folio folio-clickable" data-action="copy-folio" data-folio="<?php echo $reclamacion['folio']; ?>">
                    <?php echo $reclamacion['folio']; ?>
                </div>
                <p class="copy-hint">Click para copiar</p>
            </div>

            <div class="result-info-box">
                <p class="result-info-label">📅 Registrado</p>
                <p class="result-info-date">
                    <?php echo date('d/m/Y', strtotime($reclamacion['fecha_registro'])); ?>
                </p>
                <p class="result-info-time">
                    a las <?php echo str_replace(['AM', 'PM'], ['.a.m.', '.p.m.'], date('h:i A', strtotime($reclamacion['fecha_registro']))); ?>
                </p>
            </div>

            <button class="btn btn-primary btn-block btn-spaced" type="button" data-action="reload-page">
                🔄 Actualizar
            </button>
            <button class="btn btn-secondary btn-block btn-spaced-sm" type="button" data-action="new-search">
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
                <div class="detail-block detail-block-divider">
                    <p class="detail-block-title">Descripción</p>
                    <p class="detail-block-text"><?php echo nl2br($reclamacion['descripcion_asunto']); ?></p>
                </div>
            </div>

            <!-- Reclamación -->
            <div class="detail-card">
                <h3>📝 Reclamación</h3>
                <div class="detail-row">
                    <span class="detail-label">Tipo</span>
                    <span class="detail-value"><?php echo $reclamacion['tipo_registro']; ?></span>
                </div>
                <div class="detail-block detail-block-divider">
                    <p class="detail-block-title">Detalle</p>
                    <p class="detail-block-text"><?php echo nl2br($reclamacion['detalle_reclamacion']); ?></p>
                </div>
                <?php if ($reclamacion['pedido']): ?>
                <div class="detail-block">
                    <p class="detail-block-title">Pedido</p>
                    <p class="detail-block-text"><?php echo nl2br($reclamacion['pedido']); ?></p>
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
                    <p class="response-date">
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
    
</footer>

<script src="js/script.js"></script>
<script src="js/consultar.js"></script>

</body>
</html>
