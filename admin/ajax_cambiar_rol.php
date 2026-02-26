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
    $nuevo_rol = $_POST['nuevo_rol'] ?? null;

    if (!$usuario_id || !$nuevo_rol) {
        echo json_encode(['success' => false, 'message' => 'Datos incompletos']);
        exit();
    }

    $stmt = $pdo->prepare("UPDATE usuarios SET rol = ? WHERE id = ?");
    $stmt->execute([$nuevo_rol, $usuario_id]);

    echo json_encode(['success' => true, 'message' => 'Rol actualizado correctamente']);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>
