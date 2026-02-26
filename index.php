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
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/admin-dashboard-rocker.css">
    <style>
        /* Font changed to Arial */

        * {
            font-family: Arial;
        }

        body.reclamaciones-page {
            background: #f0f2f5;
        }

        .reclamaciones-container {
            max-width: 1000px;
            margin: 0 auto;
            padding: 2rem 1rem;
        }

        /* Header */
        .reclamaciones-header {
            background: linear-gradient(135deg, #1e3a5f 0%, #0f2433 100%);
            color: white;
            padding: 3rem 2rem;
            border-radius: 12px;
            margin-bottom: 2rem;
            box-shadow: 0 10px 30px rgba(0,0,0,0.2);
            text-align: center;
        }

        .reclamaciones-header .logo {
            font-size: 2.5rem;
            margin-bottom: 1rem;
            color: #4dabf7;
        }

        .reclamaciones-header h1 {
            margin: 0 0 0.5rem 0;
            font-size: 2rem;
            font-weight: 700;
        }

        .reclamaciones-header p {
            margin: 0.25rem 0;
            opacity: 0.9;
        }

        .ruc-info {
            margin-top: 1.5rem;
            padding: 1rem;
            background: rgba(77,171,247,0.1);
            border-radius: 8px;
            font-size: 0.9rem;
            border-left: 4px solid #4dabf7;
        }

        .nav-links {
            display: flex;
            gap: 2rem;
            justify-content: center;
            margin-top: 1.5rem;
        }

        .nav-links a {
            color: white;
            text-decoration: none;
            font-weight: 600;
            padding: 0.5rem 1rem;
            border-radius: 6px;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .nav-links a:hover {
            background: rgba(77,171,247,0.2);
            transform: translateY(-2px);
        }

        /* Formulario */
        .form-card {
            background: white;
            border-radius: 12px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
            padding: 2.5rem;
            margin-bottom: 2rem;
        }

        .form-title {
            font-size: 1.5rem;
            font-weight: 700;
            color: white;
            background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%);
            padding: 1.5rem;
            margin: -2.5rem -2.5rem 2rem -2.5rem;
            border-radius: 12px 12px 0 0;
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }

        .section-title {
            font-size: 1.2rem;
            font-weight: 700;
            color: var(--primary);
            margin: 2rem 0 1.5rem 0;
            padding-bottom: 0.75rem;
            border-bottom: 3px solid var(--primary);
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .form-row {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 1.5rem;
            margin-bottom: 1.5rem;
        }

        .form-row.full {
            grid-template-columns: 1fr;
        }

        .form-group {
            display: flex;
            flex-direction: column;
        }

        .form-group label {
            font-weight: 600;
            color: #333;
            margin-bottom: 0.5rem;
            font-size: 0.95rem;
            display: flex;
            align-items: center;
            gap: 0.25rem;
        }

        .form-group label .required {
            color: var(--danger);
        }

        .form-group input,
        .form-group select,
        .form-group textarea {
            padding: 0.85rem;
            border: 2px solid #e9ecef;
            border-radius: 8px;
            font-size: 0.95rem;
            font-family: inherit;
            background: #f8f9fa;
            transition: all 0.3s ease;
            color: #333;
        }

        .form-group input:focus,
        .form-group select:focus,
        .form-group textarea:focus {
            outline: none;
            border-color: var(--primary);
            background: white;
            box-shadow: 0 0 0 3px rgba(13,110,253,0.1);
        }

        .form-group input::placeholder,
        .form-group textarea::placeholder {
            color: #999;
        }

        .form-group textarea {
            resize: vertical;
            min-height: 100px;
            font-family: inherit;
        }

        .help-text {
            font-size: 0.85rem;
            color: #666;
            margin-top: 0.3rem;
        }

        .checkbox-group {
            display: flex;
            align-items: flex-start;
            gap: 0.75rem;
            padding: 1rem;
            background: #f8f9fa;
            border-radius: 8px;
            border-left: 4px solid var(--info);
        }

        .checkbox-group label {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            margin: 0;
            font-weight: 500;
            color: #333;
        }

        .checkbox-group input[type="checkbox"] {
            width: 20px;
            height: 20px;
            cursor: pointer;
            accent-color: var(--primary);
        }

        .checkbox-group a {
            color: var(--primary);
            text-decoration: none;
            font-weight: 600;
        }

        .checkbox-group a:hover {
            text-decoration: underline;
        }

        /* Botones */
        .button-group {
            display: flex;
            gap: 1rem;
            justify-content: center;
            margin-top: 2.5rem;
        }

        .btn-reclamacion {
            padding: 1rem 2.5rem;
            border: none;
            border-radius: 8px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }

        .btn-submit {
            background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%);
            color: white;
            flex: 1;
        }

        .btn-submit:hover {
            box-shadow: 0 8px 20px rgba(13,110,253,0.3);
            transform: translateY(-2px);
        }

        .btn-reset {
            background: var(--secondary);
            color: white;
        }

        .btn-reset:hover {
            background: #5a6268;
            box-shadow: 0 4px 12px rgba(108,117,125,0.3);
        }

        /* Footer */
        .reclamaciones-footer {
            background: linear-gradient(135deg, #1e3a5f 0%, #0f2433 100%);
            color: white;
            padding: 2rem;
            border-radius: 12px;
            text-align: center;
            margin-top: 3rem;
        }

        .reclamaciones-footer p {
            margin: 0.5rem 0;
        }

        .reclamaciones-footer a {
            color: #4dabf7;
            text-decoration: none;
            font-weight: 600;
            transition: color 0.3s ease;
        }

        .reclamaciones-footer a:hover {
            color: white;
        }

        /* Alert */
        .alert {
            padding: 1rem;
            border-radius: 8px;
            margin-bottom: 1.5rem;
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }

        .alert i {
            font-size: 1.2rem;
        }

        .alert-info {
            background: #cfe2ff;
            color: #084298;
            border: 1px solid #b6d4fe;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .reclamaciones-container {
                padding: 1rem 0.5rem;
            }

            .form-card {
                padding: 1.5rem;
            }

            .form-title {
                margin: -1.5rem -1.5rem 1.5rem -1.5rem;
                font-size: 1.2rem;
            }

            .form-row {
                grid-template-columns: 1fr;
                gap: 1rem;
            }

            .reclamaciones-header {
                padding: 2rem 1rem;
            }

            .reclamaciones-header h1 {
                font-size: 1.5rem;
            }

            .nav-links {
                flex-direction: column;
                gap: 0.5rem;
            }

            .button-group {
                flex-direction: column;
            }

            .btn-reclamacion {
                justify-content: center;
            }
        }
    </style>
</head>
<body class="reclamaciones-page">

    <div class="reclamaciones-container">
        <!-- Header -->
        <div class="reclamaciones-header">
            <div class="logo">
                <i class="fas fa-book-open"></i>
            </div>
            <h1><?php echo $institucion['razon_social']; ?></h1>
            <p style="font-size: 1.1rem; font-weight: 600;">Libro de Reclamaciones Virtual</p>
            
            <div class="ruc-info">
                <p style="margin: 0;"><strong>RUC:</strong> <?php echo $institucion['ruc']; ?></p>
                <p style="margin: 0.25rem 0 0 0;"><strong>Domicilio:</strong> <?php echo $institucion['domicilio']; ?></p>
            </div>

            <div class="nav-links">
                <a href="consultar_reclamacion.php">
                    <i class="fas fa-search"></i> Consultar Reclamación
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
                <span>Por favor completa todos los campos marcados con <span class="required" style="color: var(--danger);">*</span> para registrar tu reclamación correctamente.</span>
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
                        <input type="file" id="archivo" name="archivo" accept=".pdf,.jpg,.jpeg,.png,.doc,.docx" onchange="validarArchivo(this)">
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
        <p>© 2026 <?php echo $institucion['razon_social']; ?>. Todos los derechos son reservados.</p>
        <p style="margin-top: 1rem; font-size: 0.9rem;">
            <a href="consultar_reclamacion.php">Consultar Reclamación</a> | 
            <a href="mi_perfil.php">Mi Perfil</a> | 
            <a href="admin/login.php">Acceso Administrativo</a> |
            <a href="https://www.upeu.edu.pe/" target="_blank">Sitio Web UPeU</a>
        </p>
    </div>

<script>
    window.departamentoAreas = <?php echo json_encode(getAreasPorDepartamento()); ?>;
</script>
<script src="js/script.js"></script>

</body>
</html>
