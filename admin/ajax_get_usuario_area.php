<?php
header('Content-Type: application/json; charset=utf-8');
session_start();

require_once '../config/database.php';

try {
    $departamento = isset($_POST['departamento']) ? trim($_POST['departamento']) : '';
    $area = isset($_POST['area']) ? trim($_POST['area']) : '';
    
    if (!$departamento || !$area) {
        throw new Exception('Departamento y área son requeridos');
    }
    
    // Obtener los usuarios asignados al área especificada
    $query = "SELECT u.id, u.nombre, u.apellido_paterno, u.apellido_materno, u.email, u.telefono
              FROM usuarios u
              INNER JOIN asistentes_areas aa ON u.id = aa.usuario_id
              WHERE aa.departamento = ? AND aa.area = ? AND u.estado = 'activo'
              LIMIT 5";
    
    $stmt = $conn->prepare($query);
    if ($stmt === false) {
        throw new Exception('Error en preparar consulta: ' . $conn->error);
    }
    
    $stmt->bind_param('ss', $departamento, $area);
    
    if (!$stmt->execute()) {
        throw new Exception('Error al ejecutar consulta: ' . $stmt->error);
    }
    
    $result = $stmt->get_result();
    
    $usuarios = [];
    while ($row = $result->fetch_assoc()) {
        $usuarios[] = $row;
    }
    
    $stmt->close();
    
    echo json_encode([
        'success' => true,
        'usuarios' => $usuarios,
        'total' => count($usuarios)
    ]);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>
