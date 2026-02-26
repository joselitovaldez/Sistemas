<?php
header('Content-Type: application/json; charset=utf-8');
session_start();

require_once '../config/database.php';

if (!isset($_GET['id'])) {
    echo json_encode(['success' => false, 'message' => 'ID no proporcionado']);
    exit();
}

$usuario_id = intval($_GET['id']);

$stmt = $conn->prepare("SELECT id, usuario, nombre, apellido_paterno, apellido_materno, dni, telefono, email, rol FROM usuarios WHERE id = ?");
$stmt->bind_param("i", $usuario_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $usuario = $result->fetch_assoc();
    echo json_encode([
        'success' => true,
        'usuario' => $usuario
    ]);
} else {
    echo json_encode(['success' => false, 'message' => 'Usuario no encontrado']);
}
?>
