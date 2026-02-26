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
    $reclamacion_id = $_POST['id'] ?? null;
    $respuesta = $_POST['respuesta'] ?? null;

    if (!$reclamacion_id || !$respuesta) {
        throw new Exception('Datos incompletos');
    }

    $sql = "UPDATE reclamaciones SET respuesta = ?, estado = 'Resuelto', fecha_respuesta = NOW() WHERE id = ?";
    $stmt = $conn->prepare($sql);
    
    if (!$stmt) {
        throw new Exception('Error en la preparación: ' . $conn->error);
    }
    
    $stmt->bind_param("si", $respuesta, $reclamacion_id);
    
    if (!$stmt->execute()) {
        throw new Exception('Error en la ejecución: ' . $stmt->error);
    }
    
    if ($stmt->affected_rows === 0) {
        throw new Exception('Reclamación no encontrada');
    }
    
    echo json_encode(['success' => true, 'message' => 'Respuesta guardada correctamente']);
    $stmt->close();
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>
