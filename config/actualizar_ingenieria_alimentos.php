<?php
/**
 * Script para actualizar "EP Ingeniería de Alimentos" a "EP Ingeniería en Industrias Alimentarias"
 * Ejecutar una sola vez para corregir los datos
 */

require_once 'database.php';

try {
    echo "<h3>Actualizando EP Ingeniería de Alimentos a EP Ingeniería en Industrias Alimentarias...</h3>";
    
    // Desactivar autocommit para usar transacción
    $conn->autocommit(FALSE);
    
    // 1. Actualizar en tabla departamentos_areas
    $sql1 = "UPDATE departamentos_areas 
             SET area = 'EP Ingeniería de Industrias Alimentarias' 
             WHERE area = 'EP Ingeniería de Alimentos'";
    if ($conn->query($sql1)) {
        echo "✅ Actualizado departamentos_areas: " . $conn->affected_rows . " registros<br>";
    } else {
        throw new Exception("Error en departamentos_areas: " . $conn->error);
    }
    
    // 2. Actualizar en tabla reclamaciones (area)
    $sql2 = "UPDATE reclamaciones 
             SET area = 'EP Ingeniería de Industrias Alimentarias' 
             WHERE area = 'EP Ingeniería de Alimentos'";
    if ($conn->query($sql2)) {
        echo "✅ Actualizado reclamaciones (area): " . $conn->affected_rows . " registros<br>";
    } else {
        throw new Exception("Error en reclamaciones area: " . $conn->error);
    }
    
    // Commit de la transacción
    $conn->commit();
    $conn->autocommit(TRUE);
    
    echo "<hr>";
    echo "<h3 style='color: green;'>✅ Actualización completada exitosamente</h3>";
    echo "<p>Todos los registros de 'EP Ingeniería de Alimentos' han sido actualizados a 'EP Ingeniería de Industrias Alimentarias'.</p>";
    
} catch (Exception $e) {
    // Rollback en caso de error
    $conn->rollback();
    $conn->autocommit(TRUE);
    echo "<h3 style='color: red;'>❌ Error: " . $e->getMessage() . "</h3>";
}

$conn->close();
?>
