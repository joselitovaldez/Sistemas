<?php
require_once 'database.php';

try {
    echo "Agregando campos a la tabla usuarios...<br>";
    
    // Agregar apellido_paterno
    $sql1 = "ALTER TABLE usuarios ADD COLUMN apellido_paterno VARCHAR(100) NULL";
    if ($conn->query($sql1)) {
        echo "✓ Campo 'apellido_paterno' agregado<br>";
    } else {
        if (strpos($conn->error, 'Duplicate column') === false) {
            echo "Error en apellido_paterno: " . $conn->error . "<br>";
        } else {
            echo "✓ Campo 'apellido_paterno' ya existe<br>";
        }
    }
    
    // Agregar apellido_materno
    $sql2 = "ALTER TABLE usuarios ADD COLUMN apellido_materno VARCHAR(100) NULL";
    if ($conn->query($sql2)) {
        echo "✓ Campo 'apellido_materno' agregado<br>";
    } else {
        if (strpos($conn->error, 'Duplicate column') === false) {
            echo "Error en apellido_materno: " . $conn->error . "<br>";
        } else {
            echo "✓ Campo 'apellido_materno' ya existe<br>";
        }
    }
    
    // Agregar foto
    $sql3 = "ALTER TABLE usuarios ADD COLUMN foto VARCHAR(255) NULL";
    if ($conn->query($sql3)) {
        echo "✓ Campo 'foto' agregado<br>";
    } else {
        if (strpos($conn->error, 'Duplicate column') === false) {
            echo "Error en foto: " . $conn->error . "<br>";
        } else {
            echo "✓ Campo 'foto' ya existe<br>";
        }
    }
    
    // Agregar telefono
    $sql4 = "ALTER TABLE usuarios ADD COLUMN telefono VARCHAR(20) NULL";
    if ($conn->query($sql4)) {
        echo "✓ Campo 'telefono' agregado<br>";
    } else {
        if (strpos($conn->error, 'Duplicate column') === false) {
            echo "Error en telefono: " . $conn->error . "<br>";
        } else {
            echo "✓ Campo 'telefono' ya existe<br>";
        }
    }
    
    echo "<br><strong>Migración completada. Puedes eliminar este archivo.</strong>";
    $conn->close();
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
?>
