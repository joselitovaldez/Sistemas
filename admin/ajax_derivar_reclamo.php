<?php
header('Content-Type: application/json; charset=utf-8');
session_start();
require_once '../config/database.php';
require_once '../includes/notificaciones.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Método no permitido']);
    exit();
}

if (!$conn) {
    echo json_encode(['success' => false, 'message' => 'Error de conexión a base de datos']);
    exit();
}

try {
    $reclamacion_id = isset($_POST['id']) ? intval($_POST['id']) : null;
    $area_destino = isset($_POST['area_destino']) ? trim($_POST['area_destino']) : null;
    $usuario_asignado = isset($_POST['usuario_asignado']) ? intval($_POST['usuario_asignado']) : null;

    if (!$reclamacion_id || !$area_destino) {
        throw new Exception('Datos incompletos. ID: ' . $reclamacion_id . ', Área: ' . $area_destino);
    }
    
    // Primero verificar que la reclamación existe
    $checkSql = "SELECT id FROM reclamaciones WHERE id = ?";
    $checkStmt = $conn->prepare($checkSql);
    $checkStmt->bind_param("i", $reclamacion_id);
    $checkStmt->execute();
    $result = $checkStmt->get_result();
    
    if ($result->num_rows === 0) {
        throw new Exception('Reclamación con ID ' . $reclamacion_id . ' no encontrada');
    }
    $checkStmt->close();
    
    // Actualizar la reclamación con área y usuario asignado
    $sql = "UPDATE reclamaciones SET area = ?, estado = 'En revisión', asignado_a = ?, fecha_asignacion = NOW() WHERE id = ?";
    $stmt = $conn->prepare($sql);
    
    if (!$stmt) {
        throw new Exception('Error en la preparación: ' . $conn->error);
    }
    
    $stmt->bind_param("sii", $area_destino, $usuario_asignado, $reclamacion_id);
    
    if (!$stmt->execute()) {
        throw new Exception('Error en la ejecución: ' . $stmt->error);
    }
    
    $stmt->close();
    
    // Obtener nombre del usuario asignado
    $sqlUsuario = "SELECT CONCAT(nombre, ' ', IFNULL(apellido_paterno, ''), ' ', IFNULL(apellido_materno, '')) as nombre_completo FROM usuarios WHERE id = ?";
    $stmtUsuario = $conn->prepare($sqlUsuario);
    $stmtUsuario->bind_param("i", $usuario_asignado);
    $stmtUsuario->execute();
    $resultUsuario = $stmtUsuario->get_result();
    $usuarioData = $resultUsuario->fetch_assoc();
    $stmtUsuario->close();
    
    $nombreUsuario = $usuarioData['nombre_completo'] ?? 'Usuario Asignado';
    
    // Enviar notificaciones por email
    $emailAsignado = enviarNotificacionAsignacion($conn, $reclamacion_id, $usuario_asignado);
    $emailReclamante = enviarNotificacionReclamante($conn, $reclamacion_id, $nombreUsuario);
    
    echo json_encode([
        'success' => true, 
        'message' => 'Reclamación derivada correctamente',
        'emails_enviados' => [
            'usuario_asignado' => $emailAsignado,
            'reclamante' => $emailReclamante
        ]
    ]);

} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>
