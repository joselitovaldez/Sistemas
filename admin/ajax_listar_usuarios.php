<?php
header('Content-Type: application/json; charset=utf-8');
session_start();

require_once '../config/database.php';

$sql = "SELECT id, usuario, nombre, apellido_paterno, apellido_materno, email, rol, estado, fecha_creacion FROM usuarios ORDER BY fecha_creacion DESC";
$result = $conn->query($sql);

$usuarios = [];
while ($row = $result->fetch_assoc()) {
    $usuarios[] = $row;
}

echo json_encode([
    'success' => true,
    'usuarios' => $usuarios
]);
?>
