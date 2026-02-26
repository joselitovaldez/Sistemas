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
    $roles = $_POST['roles'] ?? [];

    if (!$usuario_id) {
        echo json_encode(['success' => false, 'message' => 'Usuario no especificado']);
        exit();
    }

    // Obtener roles del usuario
    $stmt = $pdo->prepare("SELECT roles FROM usuarios WHERE id = ?");
    $stmt->execute([$usuario_id]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    
    echo json_encode(['success' => true, 'roles' => $result['roles'] ?? 'usuario']);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>
