<?php
header('Content-Type: application/json; charset=utf-8');
session_start();
require_once '../config/database.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Método no permitido']);
    exit();
}

try {
    $usuario_id = $_POST['usuario_id'] ?? null;
    $area_id = $_POST['area_id'] ?? null;

    if (!$usuario_id || !$area_id) {
        echo json_encode(['success' => false, 'message' => 'Datos incompletos']);
        exit();
    }

    $stmt = $pdo->prepare("INSERT INTO asistentes_areas (usuario_id, area_id) VALUES (?, ?)");
    $stmt->execute([$usuario_id, $area_id]);

    echo json_encode(['success' => true, 'message' => 'Área asignada correctamente']);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>
