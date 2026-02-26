<?php
header('Content-Type: application/json; charset=utf-8');
session_start();
require_once '../config/database.php';

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    echo json_encode(['success' => false, 'message' => 'Método no permitido']);
    exit();
}

try {
    $reclamacion_id = $_GET['id'] ?? null;

    if (!$reclamacion_id) {
        echo json_encode(['success' => false, 'message' => 'Reclamación no especificada']);
        exit();
    }

    $stmt = $pdo->prepare("SELECT id, referencia, asunto, descripcion, estado FROM reclamaciones WHERE id = ?");
    $stmt->execute([$reclamacion_id]);
    $reclamacion = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$reclamacion) {
        echo json_encode(['success' => false, 'message' => 'Reclamación no encontrada']);
        exit();
    }

    echo json_encode(['success' => true, 'reclamacion' => $reclamacion]);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>
