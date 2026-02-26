<?php
header('Content-Type: application/json; charset=utf-8');
session_start();

require_once '../config/database.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Método no permitido']);
    exit();
}

try {
    $usuario_id = intval($_POST['usuario_id'] ?? 0);

    if (!$usuario_id) {
        throw new Exception('Usuario no especificado');
    }

    if ($usuario_id == $_SESSION['id']) {
        throw new Exception('No puedes eliminar tu propio usuario');
    }

    $stmt = $conn->prepare("DELETE FROM usuarios WHERE id=?");
    $stmt->bind_param("i", $usuario_id);

    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Usuario eliminado exitosamente']);
    } else {
        throw new Exception('Error al eliminar usuario');
    }
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>
