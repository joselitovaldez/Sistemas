<?php
session_start();

if (!isset($_SESSION['folio_generado'])) {
    header('Location: index.php');
    exit();
}

$folio = $_SESSION['folio_generado'];
unset($_SESSION['folio_generado']);

require_once 'includes/functions.php';
$reclamacion = obtenerReclamacion($folio);

$institucion = array(
    'razon_social' => 'Universidad Peruana Unión',
    'ruc' => '20138122256',
    'domicilio' => 'Km 19 Carretera Central, Ñaña, Lurigancho, Lima, Perú'
);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Confirmación - Libro de Reclamaciones UPeU</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/confirmacion.css">
</head>
<body>

<header>
    <div class="header-content">
        <h1>📋 Libro de Reclamaciones Virtual</h1>
        <p>Universidad Peruana Unión</p>
        <div class="ruc-info">
            <strong><?php echo $institucion['razon_social']; ?></strong>
        </div>
    </div>
</header>

<div class="container">
    <div class="alert alert-success">
        ✓ ¡Tu reclamación ha sido registrada exitosamente!
    </div>

    <div class="card confirmation">
        <h2>✓ Confirmación de Registro</h2>
        
        <p style="margin: 1.5rem 0; font-size: 1.1rem;">Tu folio de atención:</p>
        <div class="folio" onclick="copiarTexto('<?php echo $folio; ?>')" style="cursor: pointer; padding: 1rem; background: white; border-radius: 4px; margin: 1rem 0; border: 2px dashed var(--primary);">
            <?php echo $folio; ?> (Click para copiar)
        </div>
        
        <p style="margin: 1.5rem 0;">Por favor, <strong>guarda tu folio</strong> para poder consultar el estado de tu reclamación.</p>
        
        <hr style="margin: 2rem 0; border: none; border-top: 1px solid #ddd;">
        
        <h3 style="color: var(--primary); margin-bottom: 1rem;">Resumen de tu Reclamación</h3>
        
        <div class="summary-section">
            <h4 style="color: var(--primary); margin: 0 0 0.75rem;">Datos Personales</h4>
            <div class="summary-grid">
                <div class="summary-item">
                    <div class="summary-label">Nombres</div>
                    <div class="summary-value"><?php echo $reclamacion['nombres'] . ' ' . $reclamacion['apellido_paterno'] . ' ' . $reclamacion['apellido_materno']; ?></div>
                </div>
                <div class="summary-item">
                    <div class="summary-label">Email</div>
                    <div class="summary-value"><?php echo $reclamacion['email']; ?></div>
                </div>
                <div class="summary-item">
                    <div class="summary-label">Teléfono</div>
                    <div class="summary-value"><?php echo $reclamacion['telefono']; ?></div>
                </div>
            </div>
        </div>

        <div class="summary-section">
            <h4 style="color: var(--primary); margin: 0 0 0.75rem;">Campus / Área</h4>
            <div class="summary-grid">
                <div class="summary-item">
                    <div class="summary-label">Campus</div>
                    <div class="summary-value"><?php echo $reclamacion['campus']; ?></div>
                </div>
                <div class="summary-item">
                    <div class="summary-label">Área</div>
                    <div class="summary-value"><?php echo $reclamacion['departamento']; ?></div>
                </div>
                <div class="summary-item">
                    <div class="summary-label">Área / Subárea</div>
                    <div class="summary-value"><?php echo $reclamacion['area']; ?></div>
                </div>
            </div>
        </div>

        <div class="summary-section">
            <div class="summary-grid">
                <div class="summary-item">
                    <div class="summary-label">Tipo de Registro</div>
                    <div class="summary-value"><?php echo $reclamacion['tipo_registro']; ?></div>
                </div>
                <div class="summary-item">
                    <div class="summary-label">Asunto</div>
                    <div class="summary-value"><?php echo substr($reclamacion['descripcion_asunto'], 0, 100) . '...'; ?></div>
                </div>
                <div class="summary-item">
                    <div class="summary-label">Fecha de Registro</div>
                    <div class="summary-value"><?php echo str_replace(['AM', 'PM'], ['.a.m.', '.p.m.'], date('d/m/Y h:i A', strtotime($reclamacion['fecha_registro']))); ?></div>
                </div>
            </div>
        </div>
        
        <hr style="margin: 2rem 0; border: none; border-top: 1px solid #ddd;">
        
        <div style="background: #fef5e7; padding: 1rem; border-left: 4px solid var(--secondary); border-radius: 4px; margin: 1rem 0;">
            <p><strong>⚠️ Importante:</strong> Recibirás una respuesta en tu email dentro de <strong>30 días hábiles</strong> conforme a lo establecido en el Código de Protección y Defensa del Consumidor.</p>
        </div>
        
        <div style="margin-top: 2rem;">
            <a href="consultar_reclamacion.php" class="btn btn-primary" style="display: inline-block; margin-right: 1rem;">Consultar Estado</a>
            <a href="index.php" class="btn btn-secondary">Nueva Reclamación</a>
        </div>
    </div>
</div>

<footer>
    <p>Copyright © UPeU 2026. Todos los derechos son reservados — 
    <a href="https://www.upeu.edu.pe/">Universidad Peruana Unión</a></p>
</footer>

<script src="js/script.js"></script>

</body>
</html>
