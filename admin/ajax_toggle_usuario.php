<?php
header('Content-Type: application/json; charset=utf-8');
session_start();
require_once '../config/database.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Método no permitido']);
    exit();
}

try {
    $usuario_id = isset($_POST['usuario_id']) ? (int)$_POST['usuario_id'] : 0;
    $estado_solicitado = $_POST['estado'] ?? null;

    if ($usuario_id <= 0) {
        echo json_encode(['success' => false, 'message' => 'Usuario inválido']);
        exit();
    }

    $stmt = $conn->prepare('SELECT estado FROM usuarios WHERE id = ?');
    $stmt->bind_param('i', $usuario_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();

    if (!$row) {
        echo json_encode(['success' => false, 'message' => 'Usuario no encontrado']);
        exit();
    }

    $estado_actual = $row['estado'];
    $estado_actual_norm = strtolower(trim((string)$estado_actual));
    $es_numerico = is_numeric($estado_actual);

    if ($estado_solicitado !== null) {
        $estado_req = strtolower(trim((string)$estado_solicitado));
        $activo = in_array($estado_req, ['1', 'activo', 'activa', 'habilitado', 'habilitada'], true);
        $nuevo_estado = $es_numerico ? ($activo ? 1 : 0) : ($activo ? 'Activo' : 'Inactivo');
    } else {
        if ($es_numerico) {
            $nuevo_estado = ((int)$estado_actual === 1) ? 0 : 1;
        } else {
            $nuevo_estado = ($estado_actual_norm === 'activo' || $estado_actual_norm === '1') ? 'Inactivo' : 'Activo';
        }
    }

    $stmt_update = $conn->prepare('UPDATE usuarios SET estado = ? WHERE id = ?');
    if ($es_numerico) {
        $stmt_update->bind_param('ii', $nuevo_estado, $usuario_id);
    } else {
        $stmt_update->bind_param('si', $nuevo_estado, $usuario_id);
    }

    if ($stmt_update->execute()) {
        echo json_encode(['success' => true, 'message' => 'Estado actualizado', 'estado' => $nuevo_estado]);
    } else {
        echo json_encode(['success' => false, 'message' => 'No se pudo actualizar el estado']);
    }
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>
