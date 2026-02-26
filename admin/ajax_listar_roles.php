<?php
header('Content-Type: application/json; charset=utf-8');
session_start();
require_once '../config/database.php';

try {
    $stmt = $pdo->prepare("SELECT id, nombre, permisos FROM roles ORDER BY nombre ASC");
    $stmt->execute();
    $roles = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode(['success' => true, 'roles' => $roles]);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>
