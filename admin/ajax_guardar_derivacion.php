<?php
header('Content-Type: application/json; charset=utf-8');
session_start();
require_once '../config/database.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Método no permitido']);
    exit();
}

try {
    $reclamacion_id = $_POST['reclamacion_id'] ?? null;
    $departamento_destino = $_POST['departamento_destino'] ?? null;
    $motivo = $_POST['motivo'] ?? '';

    if (!$reclamacion_id || !$departamento_destino) {
        echo json_encode(['success' => false, 'message' => 'Datos incompletos']);
        exit();
    }

    $stmt = $pdo->prepare("UPDATE reclamaciones SET departamento_id = ?, estado = 'derivada', motivo_derivacion = ?, fecha_derivacion = NOW() WHERE id = ?");
    $stmt->execute([$departamento_destino, $motivo, $reclamacion_id]);

    echo json_encode(['success' => true, 'message' => 'Derivación guardada correctamente']);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>
