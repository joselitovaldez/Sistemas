<?php
header('Content-Type: application/json; charset=utf-8');
session_start();
require_once '../config/database.php';

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    echo json_encode(['success' => false, 'message' => 'Método no permitido']);
    exit();
}

try {
    $usuario_id = $_GET['id'] ?? null;

    if (!$usuario_id) {
        echo json_encode(['success' => false, 'message' => 'Usuario no especificado']);
        exit();
    }

    $stmt = $pdo->prepare("SELECT id, usuario, nombre, apellido_paterno, apellido_materno, dni, telefono, email, rol, estado FROM usuarios WHERE id = ?");
    $stmt->execute([$usuario_id]);
    $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$usuario) {
        echo json_encode(['success' => false, 'message' => 'Usuario no encontrado']);
        exit();
    }

    echo json_encode(['success' => true, 'usuario' => $usuario]);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>
