<?php
// Función para enviar notificación por email al usuario asignado
function enviarNotificacionAsignacion($conn, $reclamacion_id, $usuario_id) {
    try {
        // Obtener datos del reclamo
        $sqlReclamo = "SELECT r.folio, r.nombres, r.apellido_paterno, r.apellido_materno, 
                             r.departamento, r.area, r.tipo_bien, r.tipo_registro, 
                             r.descripcion_asunto, r.email as email_usuario
                      FROM reclamaciones r WHERE r.id = ?";
        $stmtReclamo = $conn->prepare($sqlReclamo);
        $stmtReclamo->bind_param("i", $reclamacion_id);
        $stmtReclamo->execute();
        $resultReclamo = $stmtReclamo->get_result();
        $reclamo = $resultReclamo->fetch_assoc();
        $stmtReclamo->close();
        
        if (!$reclamo) {
            return false;
        }
        
        // Obtener datos del usuario asignado
        $sqlUsuario = "SELECT nombre, apellido_paterno, apellido_materno, email FROM usuarios WHERE id = ?";
        $stmtUsuario = $conn->prepare($sqlUsuario);
        $stmtUsuario->bind_param("i", $usuario_id);
        $stmtUsuario->execute();
        $resultUsuario = $stmtUsuario->get_result();
        $usuario = $resultUsuario->fetch_assoc();
        $stmtUsuario->close();
        
        if (!$usuario || !$usuario['email']) {
            return false;
        }
        
        // Construir el correo
        $to = $usuario['email'];
        $subject = "Nuevo Reclamo Asignado - Folio: " . $reclamo['folio'];
        
        $nombreUsuario = $usuario['nombre'] . ' ' . ($usuario['apellido_paterno'] ?? '') . ' ' . ($usuario['apellido_materno'] ?? '');
        $nombreReclamante = $reclamo['nombres'] . ' ' . $reclamo['apellido_paterno'] . ' ' . $reclamo['apellido_materno'];
        
        $message = "
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset='UTF-8'>
            <style>
                body { font-family: Arial, sans-serif; }
                .container { max-width: 600px; margin: 0 auto; background: #f5f5f5; padding: 20px; }
                .header { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 20px; border-radius: 8px 8px 0 0; }
                .content { background: white; padding: 20px; }
                .info-box { background: #f8f9fa; padding: 15px; border-left: 4px solid #667eea; margin: 15px 0; border-radius: 4px; }
                .label { font-weight: 600; color: #667eea; font-size: 0.9em; text-transform: uppercase; }
                .value { color: #2c3e50; margin: 5px 0 15px 0; }
                .footer { background: #f5f5f5; padding: 15px; text-align: center; font-size: 0.85em; color: #999; border-radius: 0 0 8px 8px; }
                .btn { display: inline-block; padding: 10px 20px; background: #667eea; color: white; text-decoration: none; border-radius: 4px; margin: 15px 0; }
            </style>
        </head>
        <body>
            <div class='container'>
                <div class='header'>
                    <h2>📋 Nuevo Reclamo Asignado</h2>
                </div>
                <div class='content'>
                    <p>Estimado(a) <strong>$nombreUsuario</strong>,</p>
                    
                    <p>Te notificamos que se ha asignado un nuevo reclamo a tu área. Por favor, revísalo y toma las acciones necesarias.</p>
                    
                    <div class='info-box'>
                        <div class='label'>Folio del Reclamo</div>
                        <div class='value'>" . $reclamo['folio'] . "</div>
                        
                        <div class='label'>Departamento</div>
                        <div class='value'>" . $reclamo['departamento'] . "</div>
                        
                        <div class='label'>Área</div>
                        <div class='value'>" . $reclamo['area'] . "</div>
                        
                        <div class='label'>Tipo de Bien Contratado</div>
                        <div class='value'>" . $reclamo['tipo_bien'] . "</div>
                        
                        <div class='label'>Tipo de Reclamo</div>
                        <div class='value'>" . $reclamo['tipo_registro'] . "</div>
                        
                        <div class='label'>Asunto</div>
                        <div class='value'>" . $reclamo['descripcion_asunto'] . "</div>
                    </div>
                    
                    <div class='info-box'>
                        <div class='label'>Datos del Reclamante</div>
                        <div class='value'>
                            <strong>Nombre:</strong> $nombreReclamante<br>
                            <strong>Email:</strong> " . $reclamo['email_usuario'] . "<br>
                        </div>
                    </div>
                    
                    <p>Por favor, accede al sistema de administración para ver los detalles completos del reclamo.</p>
                    
                    <hr style='border: none; border-top: 1px solid #ddd; margin: 20px 0;'>
                    
                    <p style='color: #666; font-size: 0.9em;'>
                        Este es un mensaje automático. Por favor, no respondas a este correo.
                    </p>
                </div>
                <div class='footer'>
                    <p>Sistema de Gestión de Reclamos - UPeU 2026</p>
                </div>
            </div>
        </body>
        </html>
        ";
        
        // Headers del correo
        $headers = "MIME-Version: 1.0" . "\r\n";
        $headers .= "Content-type: text/html; charset=UTF-8" . "\r\n";
        $headers .= "From: sistema@upeu.edu.pe" . "\r\n";
        
        // Enviar correo
        return mail($to, $subject, $message, $headers);
        
    } catch (Exception $e) {
        error_log("Error al enviar notificación: " . $e->getMessage());
        return false;
    }
}

// Función para enviar notificación al reclamante sobre su derivación
function enviarNotificacionReclamante($conn, $reclamacion_id, $usuario_asignado_nombre) {
    try {
        // Obtener datos del reclamo
        $sqlReclamo = "SELECT r.folio, r.nombres, r.email FROM reclamaciones r WHERE r.id = ?";
        $stmtReclamo = $conn->prepare($sqlReclamo);
        $stmtReclamo->bind_param("i", $reclamacion_id);
        $stmtReclamo->execute();
        $resultReclamo = $stmtReclamo->get_result();
        $reclamo = $resultReclamo->fetch_assoc();
        $stmtReclamo->close();
        
        if (!$reclamo || !$reclamo['email']) {
            return false;
        }
        
        $to = $reclamo['email'];
        $subject = "Actualización de tu Reclamo - Folio: " . $reclamo['folio'];
        
        $message = "
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset='UTF-8'>
            <style>
                body { font-family: Arial, sans-serif; }
                .container { max-width: 600px; margin: 0 auto; background: #f5f5f5; padding: 20px; }
                .header { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 20px; border-radius: 8px 8px 0 0; }
                .content { background: white; padding: 20px; }
                .info-box { background: #f0f8ff; padding: 15px; border-left: 4px solid #667eea; margin: 15px 0; border-radius: 4px; }
                .footer { background: #f5f5f5; padding: 15px; text-align: center; font-size: 0.85em; color: #999; border-radius: 0 0 8px 8px; }
            </style>
        </head>
        <body>
            <div class='container'>
                <div class='header'>
                    <h2>✅ Tu Reclamo ha sido Procesado</h2>
                </div>
                <div class='content'>
                    <p>Estimado(a) <strong>" . $reclamo['nombres'] . "</strong>,</p>
                    
                    <p>Tu reclamo con folio <strong>" . $reclamo['folio'] . "</strong> ha sido derivado al área correspondiente.</p>
                    
                    <div class='info-box'>
                        <p><strong>Estado:</strong> En revisión</p>
                        <p><strong>Encargado:</strong> " . $usuario_asignado_nombre . "</p>
                        <p>Pronto recibirás una respuesta a tu reclamo.</p>
                    </div>
                    
                    <p>Si tienes alguna pregunta, puedes contactar con nosotros a través del sistema o presentarse en nuestras oficinas.</p>
                    
                    <p style='color: #666; font-size: 0.9em; margin-top: 20px;'>
                        Este es un mensaje automático. Por favor, no respondas a este correo.
                    </p>
                </div>
                <div class='footer'>
                    <p>Sistema de Gestión de Reclamos - UPeU 2026</p>
                </div>
            </div>
        </body>
        </html>
        ";
        
        $headers = "MIME-Version: 1.0" . "\r\n";
        $headers .= "Content-type: text/html; charset=UTF-8" . "\r\n";
        $headers .= "From: sistema@upeu.edu.pe" . "\r\n";
        
        return mail($to, $subject, $message, $headers);
        
    } catch (Exception $e) {
        error_log("Error al enviar notificación al reclamante: " . $e->getMessage());
        return false;
    }
}
?>
