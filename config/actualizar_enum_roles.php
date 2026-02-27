<?php
/**
 * Script para actualizar el ENUM del campo 'rol' en la tabla usuarios
 * Agregando los nuevos roles: analista, direccion, decanatura
 */

require_once 'database.php';

echo "=== Actualizando campo 'rol' en tabla usuarios ===\n\n";

// Primero verificamos el estado actual
$sql_describe = "DESCRIBE usuarios";
$result = $conn->query($sql_describe);
while ($row = $result->fetch_assoc()) {
    if ($row['Field'] === 'rol') {
        echo "Estado ACTUAL del campo 'rol':\n";
        echo "Type: " . $row['Type'] . "\n";
        echo "Null: " . $row['Null'] . "\n";
        echo "Default: " . $row['Default'] . "\n\n";
    }
}

// Modificar el ENUM para incluir todos los roles
$sql_alter = "ALTER TABLE usuarios 
              MODIFY COLUMN rol ENUM(
                  'superadmin',
                  'asistente_admin',
                  'decano_upg',
                  'director_escuela_upg',
                  'director_area',
                  'asistente',
                  'auditor'
              ) NULL DEFAULT 'asistente'";

echo "Ejecutando ALTER TABLE...\n";
if ($conn->query($sql_alter)) {
    echo "✓ Campo 'rol' actualizado exitosamente!\n\n";
    
    // Verificar el nuevo estado
    $result2 = $conn->query($sql_describe);
    while ($row = $result2->fetch_assoc()) {
        if ($row['Field'] === 'rol') {
            echo "NUEVO estado del campo 'rol':\n";
            echo "Type: " . $row['Type'] . "\n";
            echo "Null: " . $row['Null'] . "\n";
            echo "Default: " . $row['Default'] . "\n\n";
        }
    }
    
    echo "✓ Ahora puedes asignar los siguientes roles:\n";
    echo "  - superadmin\n";
    echo "  - asistente_admin\n";
    echo "  - decano_upg\n";
    echo "  - director_escuela_upg\n";
    echo "  - director_area\n";
    echo "  - asistente\n";
    echo "  - auditor\n";
} else {
    echo "✗ Error al actualizar: " . $conn->error . "\n";
}

$conn->close();
?>
