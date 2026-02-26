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
    $nueva_password = $_POST['nueva_password'] ?? null;

    if (!$usuario_id || !$nueva_password || strlen($nueva_password) < 6) {
        echo json_encode(['success' => false, 'message' => 'Datos inválidos']);
        exit();
    }

    $password_hash = password_hash($nueva_password, PASSWORD_BCRYPT);
    $stmt = $pdo->prepare("UPDATE usuarios SET password = ? WHERE id = ?");
    $stmt->execute([$password_hash, $usuario_id]);

    echo json_encode(['success' => true, 'message' => 'Contraseña reseteada correctamente']);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>
