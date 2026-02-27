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
	$dni = trim($_POST['dni'] ?? '');
	$telefono = trim($_POST['telefono'] ?? '');
	$email = trim($_POST['email'] ?? '');
	$password_nueva = $_POST['password_nueva'] ?? '';
	$foto_path = null;

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

	if ($dni !== '' && !preg_match('/^[0-9]{8}$/', $dni)) {
		echo json_encode(['success' => false, 'message' => 'DNI inválido']);
		exit();
	}

	if ($password_nueva !== '' && strlen($password_nueva) < 6) {
		echo json_encode(['success' => false, 'message' => 'La contraseña debe tener al menos 6 caracteres']);
		exit();
	}

	if (isset($_FILES['foto']) && $_FILES['foto']['size'] > 0) {
		$max_size = 2 * 1024 * 1024;
		if ($_FILES['foto']['size'] > $max_size) {
			echo json_encode(['success' => false, 'message' => 'La foto supera el tamaño máximo (2 MB)']);
			exit();
		}

		$allowed_ext = ['jpg', 'jpeg', 'png', 'gif'];
		$ext = strtolower(pathinfo($_FILES['foto']['name'], PATHINFO_EXTENSION));
		if (!in_array($ext, $allowed_ext, true)) {
			echo json_encode(['success' => false, 'message' => 'Formato de foto no permitido']);
			exit();
		}

		$upload_dir = __DIR__ . '/../uploads/perfiles/';
		if (!is_dir($upload_dir)) {
			mkdir($upload_dir, 0755, true);
		}

		$filename = 'perfil_' . $usuario_id . '_' . time() . '.' . $ext;
		$destino = $upload_dir . $filename;

		if (!move_uploaded_file($_FILES['foto']['tmp_name'], $destino)) {
			echo json_encode(['success' => false, 'message' => 'No se pudo subir la foto']);
			exit();
		}

		$foto_path = 'uploads/perfiles/' . $filename;
	}

	$password_hash = null;
	if ($password_nueva !== '') {
		$password_hash = password_hash($password_nueva, PASSWORD_BCRYPT);
	}

	if ($password_hash !== null && $foto_path !== null) {
		$stmt = $conn->prepare('UPDATE usuarios SET nombre = ?, apellido_paterno = ?, apellido_materno = ?, dni = ?, telefono = ?, email = ?, password = ?, foto = ? WHERE id = ?');
		$stmt->bind_param('ssssssssi', $nombre, $apellido_paterno, $apellido_materno, $dni, $telefono, $email, $password_hash, $foto_path, $usuario_id);
	} elseif ($password_hash !== null) {
		$stmt = $conn->prepare('UPDATE usuarios SET nombre = ?, apellido_paterno = ?, apellido_materno = ?, dni = ?, telefono = ?, email = ?, password = ? WHERE id = ?');
		$stmt->bind_param('sssssssi', $nombre, $apellido_paterno, $apellido_materno, $dni, $telefono, $email, $password_hash, $usuario_id);
	} elseif ($foto_path !== null) {
		$stmt = $conn->prepare('UPDATE usuarios SET nombre = ?, apellido_paterno = ?, apellido_materno = ?, dni = ?, telefono = ?, email = ?, foto = ? WHERE id = ?');
		$stmt->bind_param('sssssssi', $nombre, $apellido_paterno, $apellido_materno, $dni, $telefono, $email, $foto_path, $usuario_id);
	} else {
		$stmt = $conn->prepare('UPDATE usuarios SET nombre = ?, apellido_paterno = ?, apellido_materno = ?, dni = ?, telefono = ?, email = ? WHERE id = ?');
		$stmt->bind_param('ssssssi', $nombre, $apellido_paterno, $apellido_materno, $dni, $telefono, $email, $usuario_id);
	}

	if ($stmt->execute()) {
		// Actualizar las variables de sesión
		$_SESSION['nombre'] = $nombre;
		$_SESSION['apellido_paterno'] = $apellido_paterno;
		$_SESSION['apellido_materno'] = $apellido_materno;
		$_SESSION['dni'] = $dni;
		$_SESSION['telefono'] = $telefono;
		$_SESSION['email'] = $email;
		if ($foto_path !== null) {
			$_SESSION['foto'] = $foto_path;
		}
		
		echo json_encode(['success' => true, 'message' => 'Perfil actualizado correctamente']);
	} else {
		echo json_encode(['success' => false, 'message' => 'No se pudo actualizar el perfil']);
	}
} catch (Exception $e) {
	echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>
