<?php
header('Content-Type: text/html; charset=utf-8');
require_once 'includes/functions.php';

// Información de la institución
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
    <title>Libro de Reclamaciones - UPeU</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <!-- Font changed to Arial -->
    <link rel="stylesheet" href="css/style-form.css">
    <link rel="stylesheet" href="css/index-inline.css">
    <link rel="stylesheet" href="css/admin-dashboard-rocker.css">
    
</head>
<body class="reclamaciones-page">

    <div class="reclamaciones-container">
        <!-- Header -->
        <div class="reclamaciones-header">
            <div class="logo">
                <i class="fas fa-book-open"></i>
            </div>
            <h1><?php echo $institucion['razon_social']; ?></h1>
            <p class="hero-subtitle">Libro de Reclamaciones Virtual</p>
            
            <div class="ruc-info">
                <p class="institucion-line"><strong>RUC:</strong> <?php echo $institucion['ruc']; ?></p>
                <p class="institucion-line institucion-line-spaced"><strong>Domicilio:</strong> <?php echo $institucion['domicilio']; ?></p>
            </div>

            <div class="nav-links">
                <a href="consultar_reclamacion.php">
                    <i class="fas fa-search"></i> Consultar Reclamo
                </a>
                <a href="mi_perfil.php">
                    <i class="fas fa-user-circle"></i> Mi Perfil
                </a>
                <a href="admin/login.php">
                    <i class="fas fa-lock"></i> Acceso Administrativo
                </a>
            </div>
        </div>

        <!-- Formulario -->
        <div class="form-card">
            <h2 class="form-title">
                <i class="fas fa-file-alt"></i> Registrar Nueva Reclamación
            </h2>

            <div class="alert alert-info">
                <i class="fas fa-info-circle"></i>
                <span>Por favor completa todos los campos marcados con <span class="required required-danger">*</span> para registrar tu reclamación correctamente.</span>
            </div>
            
            <form id="formReclamacion" method="POST" action="procesar_reclamacion.php" enctype="multipart/form-data">
                
                <!-- INFORMACIÓN DEL CONSUMIDOR -->
                <h3 class="section-title">
                    <i class="fas fa-user"></i> Información del Consumidor
                </h3>

                <div class="form-row">
                    <div class="form-group">
                        <label for="campus">
                            <i class="fas fa-building"></i> Campus
                            <span class="required">*</span>
                        </label>
                        <select id="campus" name="campus" required>
                            <option value="">--- Selecciona un campus ---</option>
                            <?php foreach (getCampus() as $key => $valor): ?>
                                <option value="<?php echo $key; ?>"><?php echo $valor; ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="departamento">
                            <i class="fas fa-layer-group"></i> Departamento
                            <span class="required">*</span>
                        </label>
                        <select id="departamento" name="departamento" required>
                            <option value="">--- Selecciona un departamento ---</option>
                            <?php foreach (getDepartamentos() as $key => $valor): ?>
                                <option value="<?php echo $key; ?>"><?php echo $valor; ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <div class="form-row full">
                    <div class="form-group">
                        <label for="area">
                            <i class="fas fa-map-marker-alt"></i> Área / Subárea
                            <span class="required">*</span>
                        </label>
                        <select id="area" name="area" required>
                            <option value="">--- Selecciona un área ---</option>
                        </select>
                    </div>
                </div>

                <h3 class="section-title">
                    <i class="fas fa-address-card"></i> Datos Personales
                </h3>

                <div class="form-row">
                    <div class="form-group">
                        <label for="nombres">
                            <i class="fas fa-user-edit"></i> Nombres
                            <span class="required">*</span>
                        </label>
                        <input type="text" id="nombres" name="nombres" placeholder="Ej. Juan Carlos" required>
                        <small class="help-text">Ingresa tu(s) nombre(s)</small>
                    </div>
                    <div class="form-group">
                        <label for="apellido_paterno">
                            <i class="fas fa-user-edit"></i> Apellido Paterno
                            <span class="required">*</span>
                        </label>
                        <input type="text" id="apellido_paterno" name="apellido_paterno" placeholder="Ej. García" required>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="apellido_materno">
                            <i class="fas fa-user-edit"></i> Apellido Materno
                            <span class="required">*</span>
                        </label>
                        <input type="text" id="apellido_materno" name="apellido_materno" placeholder="Ej. López" required>
                    </div>
                    <div class="form-group">
                        <label for="dni_ce">
                            <i class="fas fa-id-card"></i> DNI/CE
                            <span class="required">*</span>
                        </label>
                        <input type="text" id="dni_ce" name="dni_ce" placeholder="Ej. 12345678 o AABBCCDD" required>
                        <small class="help-text">8 dígitos (DNI) o formato CE válido</small>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="email">
                            <i class="fas fa-envelope"></i> Email
                            <span class="required">*</span>
                        </label>
                        <input type="email" id="email" name="email" placeholder="correo@ejemplo.com" required>
                    </div>
                    <div class="form-group">
                        <label for="telefono">
                            <i class="fas fa-phone"></i> Teléfono/Celular
                            <span class="required">*</span>
                        </label>
                        <input type="tel" id="telefono" name="telefono" placeholder="Ej. 987654321" required>
                    </div>
                </div>

                <div class="form-row full">
                    <div class="form-group">
                        <label for="domicilio">
                            <i class="fas fa-map-pin"></i> Domicilio
                            <span class="required">*</span>
                        </label>
                        <input type="text" id="domicilio" name="domicilio" placeholder="Calle, número, urbanización, provincia..." required>
                    </div>
                </div>

                <div class="form-row full">
                    <div class="form-group">
                        <label for="padre_madre">
                            <i class="fas fa-users"></i> Padre o Madre (si es menor de edad)
                        </label>
                        <input type="text" id="padre_madre" name="padre_madre" placeholder="Dejar en blanco si no aplica">
                    </div>
                </div>

                <!-- BIEN CONTRATADO -->
                <h3 class="section-title">
                    <i class="fas fa-box"></i> Identificación del Bien Contratado
                </h3>

                <div class="form-row full">
                    <div class="form-group">
                        <label for="tipo_bien">
                            <i class="fas fa-cube"></i> Tipo
                            <span class="required">*</span>
                        </label>
                        <select id="tipo_bien" name="tipo_bien" required>
                            <option value="">--- Selecciona un tipo ---</option>
                            <?php foreach (getTiposBien() as $key => $valor): ?>
                                <option value="<?php echo $key; ?>"><?php echo $valor; ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <div class="form-row full">
                    <div class="form-group">
                        <label for="descripcion_asunto">
                            <i class="fas fa-align-left"></i> Descripción / Asunto
                            <span class="required">*</span>
                        </label>
                        <textarea id="descripcion_asunto" name="descripcion_asunto" placeholder="Describe el bien o servicio contratado..." required></textarea>
                    </div>
                </div>

                <!-- DETALLE DE RECLAMACIÓN -->
                <h3 class="section-title">
                    <i class="fas fa-list-check"></i> Detalle de la Reclamación
                </h3>

                <div class="form-row full">
                    <div class="form-group">
                        <label for="tipo_registro">
                            <i class="fas fa-tag"></i> Tipo de Registro
                            <span class="required">*</span>
                        </label>
                        <select id="tipo_registro" name="tipo_registro" required>
                            <option value="">--- Selecciona un tipo ---</option>
                            <?php foreach (getTiposRegistro() as $key => $valor): ?>
                                <option value="<?php echo $key; ?>"><?php echo $valor; ?></option>
                            <?php endforeach; ?>
                        </select>
                        <small class="help-text">
                            <strong>Reclamo:</strong> desacuerdo por incumplimiento | 
                            <strong>Queja:</strong> insatisfacción | 
                            <strong>Sugerencia:</strong> mejora propuesta
                        </small>
                    </div>
                </div>

                <div class="form-row full">
                    <div class="form-group">
                        <label for="detalle_reclamacion">
                            <i class="fas fa-comment-dots"></i> Detalle de la Reclamación
                            <span class="required">*</span>
                        </label>
                        <textarea id="detalle_reclamacion" name="detalle_reclamacion" placeholder="Describe en detalle tu reclamación, queja o sugerencia..." required></textarea>
                        <small class="help-text">Sé específico y claro en tu descripción</small>
                    </div>
                </div>

                <div class="form-row full">
                    <div class="form-group">
                        <label for="pedido">
                            <i class="fas fa-lightbulb"></i> Pedido
                        </label>
                        <textarea id="pedido" name="pedido" placeholder="¿Qué solución o acción esperas que se tome?"></textarea>
                    </div>
                </div>

                <div class="form-row full">
                    <div class="form-group">
                        <label for="archivo">
                            <i class="fas fa-paperclip"></i> Adjuntar Archivo (opcional)
                        </label>
                        <input type="file" id="archivo" name="archivo" accept=".pdf,.jpg,.jpeg,.png,.doc,.docx">
                        <small class="help-text">Extensiones permitidas: pdf, jpg, jpeg, png, doc, docx. Máximo 5 MB por archivo.</small>
                    </div>
                </div>

                <!-- ACEPTACIÓN -->
                <div class="form-row full">
                    <div class="checkbox-group">
                        <label>
                            <input type="checkbox" id="privacidad" name="privacidad" required>
                            Declaro que he leído y acepto la 
                            <a href="https://upeu.edu.pe/politica-de-privacidad/" target="_blank">
                                política de privacidad
                            </a>
                            <span class="required">*</span>
                        </label>
                    </div>
                </div>

                <!-- Botones -->
                <div class="button-group">
                    <button type="reset" class="btn-reclamacion btn-reset">
                        <i class="fas fa-redo"></i> Limpiar
                    </button>
                    <button type="submit" class="btn-reclamacion btn-submit">
                        <i class="fas fa-paper-plane"></i> Registrar Reclamo
                    </button>
                </div>

            </form>
        </div>
    </div>

    <!-- Footer -->
    <div class="reclamaciones-footer">
        <p>© 2026 <?php echo $institucion['razon_social']; ?>. Todos los derechos son reservados. | <a href="https://www.upeu.edu.pe/" target="_blank" class="footer-links">Sitio Web UPeU</a>
        </p>
    </div>

<script type="application/json" id="departamento-areas-data"><?php echo json_encode(getAreasPorDepartamento()); ?></script>
<script src="js/script.js"></script>
<script src="js/index-page.js"></script>

</body>
</html>
