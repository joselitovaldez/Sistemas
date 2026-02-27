<?php
/**
 * Script para actualizar DEC a IDEC en la base de datos
 * Ejecutar una sola vez para corregir los datos
 */

require_once 'database.php';

try {
    echo "<h3>Actualizando registros de DEC a IDEC...</h3>";
    
    // Desactivar autocommit para usar transacción
    $conn->autocommit(FALSE);
    
    // 1. Actualizar en tabla departamentos_areas
    $sql1 = "UPDATE departamentos_areas SET departamento = 'IDEC', area = 'IDEC' WHERE departamento = 'DEC' AND area = 'DEC'";
    if ($conn->query($sql1)) {
        echo "✅ Actualizado departamentos_areas: " . $conn->affected_rows . " registros<br>";
    } else {
        throw new Exception("Error en departamentos_areas: " . $conn->error);
    }
    
    // 2. Actualizar en tabla reclamaciones (departamento)
    $sql2 = "UPDATE reclamaciones SET departamento = 'IDEC' WHERE departamento = 'DEC'";
    if ($conn->query($sql2)) {
        echo "✅ Actualizado reclamaciones (departamento): " . $conn->affected_rows . " registros<br>";
    } else {
        throw new Exception("Error en reclamaciones departamento: " . $conn->error);
    }
    
    // 3. Actualizar en tabla reclamaciones (area)
    $sql3 = "UPDATE reclamaciones SET area = 'IDEC' WHERE area = 'DEC'";
    if ($conn->query($sql3)) {
        echo "✅ Actualizado reclamaciones (area): " . $conn->affected_rows . " registros<br>";
    } else {
        throw new Exception("Error en reclamaciones area: " . $conn->error);
    }
    
    // Commit de la transacción
    $conn->commit();
    $conn->autocommit(TRUE);
    
    echo "<hr>";
    echo "<h3 style='color: green;'>✅ Actualización completada exitosamente</h3>";
    echo "<p>Todos los registros de DEC han sido actualizados a IDEC.</p>";
    
} catch (Exception $e) {
    // Rollback en caso de error
    $conn->rollback();
    $conn->autocommit(TRUE);
    echo "<h3 style='color: red;'>❌ Error: " . $e->getMessage() . "</h3>";
}

$conn->close();
?>
