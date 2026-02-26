<?php
header('Content-Type: application/json; charset=utf-8');
session_start();
require_once '../config/database.php';

if (!isset($_SESSION['id'])) {
	echo json_encode(['success' => false, 'message' => 'Sesión no válida']);
	exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
	echo json_encode(['success' => false, 'message' => 'Método no permitido']);
	exit();
}

try {
	$usuario_id = (int)$_SESSION['id'];
	$nombre = trim($_POST['nombre'] ?? '');
	$apellido_paterno = trim($_POST['apellido_paterno'] ?? '');
	$apellido_materno = trim($_POST['apellido_materno'] ?? '');
	$telefono = trim($_POST['telefono'] ?? '');
	$email = trim($_POST['email'] ?? '');
	$password_nueva = $_POST['password_nueva'] ?? '';

	if ($nombre === '' || $apellido_paterno === '' || $email === '') {
		echo json_encode(['success' => false, 'message' => 'Completa los campos obligatorios']);
		exit();
	}

	if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
		echo json_encode(['success' => false, 'message' => 'Email inválido']);
		exit();
	}

	if ($telefono !== '' && !preg_match('/^[0-9]{9}$/', $telefono)) {
		echo json_encode(['success' => false, 'message' => 'Teléfono inválido']);
		exit();
	}

	if ($password_nueva !== '' && strlen($password_nueva) < 6) {
		echo json_encode(['success' => false, 'message' => 'La contraseña debe tener al menos 6 caracteres']);
		exit();
	}

	if ($password_nueva !== '') {
		$password_hash = password_hash($password_nueva, PASSWORD_BCRYPT);
		$stmt = $conn->prepare('UPDATE usuarios SET nombre = ?, apellido_paterno = ?, apellido_materno = ?, telefono = ?, email = ?, password = ? WHERE id = ?');
		$stmt->bind_param('ssssssi', $nombre, $apellido_paterno, $apellido_materno, $telefono, $email, $password_hash, $usuario_id);
	} else {
		$stmt = $conn->prepare('UPDATE usuarios SET nombre = ?, apellido_paterno = ?, apellido_materno = ?, telefono = ?, email = ? WHERE id = ?');
		$stmt->bind_param('sssssi', $nombre, $apellido_paterno, $apellido_materno, $telefono, $email, $usuario_id);
	}

	if ($stmt->execute()) {
		echo json_encode(['success' => true, 'message' => 'Perfil actualizado correctamente']);
	} else {
		echo json_encode(['success' => false, 'message' => 'No se pudo actualizar el perfil']);
	}
} catch (Exception $e) {
	echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>
