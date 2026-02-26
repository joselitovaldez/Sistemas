<?php
header('Content-Type: application/json; charset=utf-8');
session_start();
require_once '../config/database.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Método no permitido']);
    exit();
}

try {
    $rol_nombre = $_POST['rol_nombre'] ?? null;
    $permisos = $_POST['permisos'] ?? [];

    if (!$rol_nombre) {
        echo json_encode(['success' => false, 'message' => 'Nombre de rol requerido']);
        exit();
    }

    $stmt = $pdo->prepare("INSERT INTO roles (nombre, permisos, fecha_creacion) VALUES (?, ?, NOW())");
    $stmt->execute([$rol_nombre, json_encode($permisos)]);

    echo json_encode(['success' => true, 'message' => 'Rol guardado correctamente']);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>
