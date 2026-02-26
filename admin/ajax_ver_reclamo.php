<?php
header('Content-Type: application/json; charset=utf-8');
session_start();
require_once '../config/database.php';

try {
    $reclamacion_id = $_GET['id'] ?? null;

    if (!$reclamacion_id) {
        throw new Exception('Reclamación no especificada');
    }

    if (!$conn) {
        throw new Exception('Error de conexión a base de datos');
    }

    // Usar SELECT * para obtener todos los campos disponibles
    $sql = "SELECT * FROM reclamaciones WHERE id = ?";
    $stmt = $conn->prepare($sql);

    if (!$stmt) {
        throw new Exception('Error en la preparación: ' . $conn->error);
    }

    $stmt->bind_param("i", $reclamacion_id);

    if (!$stmt->execute()) {
        throw new Exception('Error en la ejecución: ' . $stmt->error);
    }

    $result = $stmt->get_result();
    $reclamacion = $result->fetch_assoc();

    if (!$reclamacion) {
        throw new Exception('Reclamación no encontrada');
    }

    echo json_encode(['success' => true, 'reclamacion' => $reclamacion]);
    $stmt->close();
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>
