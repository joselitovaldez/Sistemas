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
    $nombre = trim($_POST['nombre'] ?? '');
    $apellido_paterno = trim($_POST['apellido_paterno'] ?? '');
    $apellido_materno = trim($_POST['apellido_materno'] ?? '');
    $dni = trim($_POST['dni'] ?? '');
    $telefono = trim($_POST['telefono'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $rol = trim($_POST['rol'] ?? '');

    if (!$usuario_id) {
        echo json_encode(['success' => false, 'message' => 'ID de usuario no válido']);
        exit();
    }

    if ($nombre === '' || $apellido_paterno === '' || $email === '' || $rol === '') {
        echo json_encode(['success' => false, 'message' => 'Completa los campos obligatorios']);
        exit();
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo json_encode(['success' => false, 'message' => 'Email inválido']);
        exit();
    }

    if ($dni !== '' && !preg_match('/^[0-9]{8}$/', $dni)) {
        echo json_encode(['success' => false, 'message' => 'DNI inválido']);
        exit();
    }

    if ($telefono !== '' && !preg_match('/^[0-9]{9}$/', $telefono)) {
        echo json_encode(['success' => false, 'message' => 'Teléfono inválido']);
        exit();
    }

    $stmt = $conn->prepare("UPDATE usuarios SET nombre = ?, apellido_paterno = ?, apellido_materno = ?, dni = ?, telefono = ?, email = ?, rol = ? WHERE id = ?");
    $stmt->bind_param('sssssssi', $nombre, $apellido_paterno, $apellido_materno, $dni, $telefono, $email, $rol, $usuario_id);

    if ($stmt->execute()) {
        // Si el usuario está editando su propio perfil, actualizar la sesión
        if (isset($_SESSION['id']) && $_SESSION['id'] == $usuario_id) {
            $_SESSION['nombre'] = $nombre;
            $_SESSION['apellido_paterno'] = $apellido_paterno;
            $_SESSION['apellido_materno'] = $apellido_materno;
            $_SESSION['telefono'] = $telefono;
            $_SESSION['email'] = $email;
            $_SESSION['rol'] = $rol;
        }
        
        echo json_encode(['success' => true, 'message' => 'Usuario actualizado correctamente']);
    } else {
        echo json_encode(['success' => false, 'message' => 'No se pudo actualizar el usuario']);
    }
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
}
?>

