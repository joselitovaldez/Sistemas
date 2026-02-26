<?php
// Script para agregar el campo teléfono a la tabla usuarios y actualizar DNI

require_once 'database.php';

try {
    // Verificar si el campo teléfono ya existe
    $sql_check_tel = "SHOW COLUMNS FROM usuarios LIKE 'telefono'";
    $result_tel = $conn->query($sql_check_tel);
    
    // Verificar si el campo DNI existe y su tipo
    $sql_check_dni = "SHOW COLUMNS FROM usuarios LIKE 'dni'";
    $result_dni = $conn->query($sql_check_dni);
    
    $messages = [];
    
    // Agregar telefono si no existe
    if ($result_tel && $result_tel->num_rows == 0) {
        $sql_tel = "ALTER TABLE usuarios ADD COLUMN telefono VARCHAR(9) NULL AFTER dni";
        if ($conn->query($sql_tel) === TRUE) {
            $messages[] = "Campo teléfono agregado exitosamente";
        } else {
            throw new Exception('Error al agregar teléfono: ' . $conn->error);
        }
    } else {
        $messages[] = "El campo teléfono ya existe";
    }
    
    // Actualizar tipo de DNI si es necesario
    if ($result_dni && $result_dni->num_rows > 0) {
        $row = $result_dni->fetch_assoc();
        if ($row['Type'] != 'varchar(8)') {
            $sql_dni = "ALTER TABLE usuarios MODIFY COLUMN dni VARCHAR(8) UNIQUE NULL";
            if ($conn->query($sql_dni) === TRUE) {
                $messages[] = "Campo DNI actualizado a VARCHAR(8)";
            } else {
                throw new Exception('Error al actualizar DNI: ' . $conn->error);
            }
        } else {
            $messages[] = "El campo DNI ya está correctamente configurado";
        }
    }
    
    echo json_encode([
        'success' => true,
        'messages' => $messages
    ]);
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>
