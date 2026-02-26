<?php
header('Content-Type: application/json; charset=utf-8');
session_start();

require_once '../config/database.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Método no permitido']);
    exit();
}

try {
    $usuario = trim($_POST['usuario'] ?? '');
    $nombre = trim($_POST['nombre'] ?? '');
    $apellido_paterno = trim($_POST['apellido_paterno'] ?? '');
    $apellido_materno = trim($_POST['apellido_materno'] ?? '');
    $dni = trim($_POST['dni'] ?? '');
    $telefono = trim($_POST['telefono'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $rol = $_POST['rol'] ?? 'asistente';

    // Validaciones
    if (empty($usuario) || empty($nombre) || empty($email) || empty($password) || empty($dni) || empty($telefono)) {
        throw new Exception('Faltan campos requeridos');
    }

    if (!preg_match('/^[0-9]{8}$/', $dni)) {
        throw new Exception('DNI inválido');
    }

    if (!preg_match('/^[0-9]{9}$/', $telefono)) {
        throw new Exception('Teléfono inválido');
    }

    if (strlen($password) < 6) {
        throw new Exception('Contraseña muy corta');
    }

    // Verificar usuario único
    $stmt = $conn->prepare("SELECT id FROM usuarios WHERE usuario = ?");
    $stmt->bind_param("s", $usuario);
    $stmt->execute();
    if ($stmt->get_result()->num_rows > 0) {
        throw new Exception('Usuario ya existe');
    }

    // Verificar email único
    $stmt = $conn->prepare("SELECT id FROM usuarios WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    if ($stmt->get_result()->num_rows > 0) {
        throw new Exception('Email ya registrado');
    }

    // Crear usuario
    $password_hash = password_hash($password, PASSWORD_BCRYPT);
    $stmt = $conn->prepare("INSERT INTO usuarios (usuario, password, nombre, apellido_paterno, apellido_materno, dni, telefono, email, rol, estado) 
                            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 'activo')");
    $stmt->bind_param("sssssssss", $usuario, $password_hash, $nombre, $apellido_paterno, $apellido_materno, $dni, $telefono, $email, $rol);
    
    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Usuario creado exitosamente']);
    } else {
        throw new Exception('Error al crear usuario');
    }
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>
