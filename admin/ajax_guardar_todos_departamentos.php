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
    $departamentos = $_POST['departamentos'] ?? [];

    if (!$usuario_id) {
        echo json_encode(['success' => false, 'message' => 'Usuario no especificado']);
        exit();
    }

    // Eliminar asignaciones previas
    $stmt = $pdo->prepare("DELETE FROM asistentes_areas WHERE usuario_id = ?");
    $stmt->execute([$usuario_id]);

    // Insertar nuevas asignaciones
    $stmt = $pdo->prepare("INSERT INTO asistentes_areas (usuario_id, area_id) VALUES (?, ?)");
    foreach ($departamentos as $depto_id) {
        $stmt->execute([$usuario_id, $depto_id]);
    }

    echo json_encode(['success' => true, 'message' => 'Departamentos asignados correctamente']);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>
