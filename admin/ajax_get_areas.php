<?php
header('Content-Type: application/json; charset=utf-8');
session_start();

require_once '../config/database.php';

try {
    // Obtener el ID o nombre del departamento
    $departamento_id = isset($_POST['departamento_id']) ? trim($_POST['departamento_id']) : '';
    
    if (!$departamento_id) {
        throw new Exception('Departamento no especificado');
    }
    
    // Consultar áreas del departamento de la tabla departamentos_areas
    $query = "SELECT DISTINCT area as id, area as nombre FROM departamentos_areas WHERE departamento = ? AND activo = 1 ORDER BY area ASC";
    
    $stmt = $conn->prepare($query);
    if ($stmt === false) {
        throw new Exception('Error en preparar consulta: ' . $conn->error);
    }
    
    $stmt->bind_param('s', $departamento_id);
    
    if (!$stmt->execute()) {
        throw new Exception('Error al ejecutar consulta: ' . $stmt->error);
    }
    
    $result = $stmt->get_result();
    
    $areas = [];
    while ($row = $result->fetch_assoc()) {
        $areas[] = $row;
    }
    
    $stmt->close();
    
    echo json_encode([
        'success' => true,
        'areas' => $areas
    ]);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>
