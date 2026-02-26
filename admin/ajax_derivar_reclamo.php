<?php
header('Content-Type: application/json; charset=utf-8');
session_start();
require_once '../config/database.php';

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
    
    echo json_encode(['success' => true, 'message' => 'Reclamación derivada correctamente']);

} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>
