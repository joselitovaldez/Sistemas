<?php
header('Content-Type: application/json; charset=utf-8');
session_start();

require_once '../config/database.php';

try {
    // Consultar departamentos únicos de la tabla departamentos_areas
    $query = "SELECT DISTINCT departamento as id, departamento as nombre 
              FROM departamentos_areas 
              WHERE activo = 1 
              ORDER BY departamento ASC";
    
    $result = $conn->query($query);
    
    if ($result === false) {
        throw new Exception('Error en la consulta: ' . $conn->error);
    }
    
    $departamentos = [];
    while ($row = $result->fetch_assoc()) {
        $departamentos[] = $row;
    }
    
    echo json_encode([
        'success' => true,
        'departamentos' => $departamentos
    ]);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>
