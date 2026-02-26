<?php
// Script para agregar el campo DNI a la tabla usuarios

require_once 'database.php';

try {
    // Verificar si el campo DNI ya existe
    $sql_check = "SHOW COLUMNS FROM usuarios LIKE 'dni'";
    $result = $conn->query($sql_check);
    
    if ($result && $result->num_rows == 0) {
        // Agregar el campo DNI
        $sql = "ALTER TABLE usuarios ADD COLUMN dni VARCHAR(15) UNIQUE AFTER email";
        
        if ($conn->query($sql) === TRUE) {
            echo json_encode([
                'success' => true,
                'message' => 'Campo DNI agregado exitosamente a la tabla usuarios'
            ]);
        } else {
            throw new Exception('Error al agregar el campo: ' . $conn->error);
        }
    } else {
        echo json_encode([
            'success' => true,
            'message' => 'El campo DNI ya existe en la tabla usuarios'
        ]);
    }
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>
