<?php
// Script para migrar la base de datos existente a los nuevos roles
$host = 'localhost';
$user = 'root';
$pass = '';
$db = 'upeu_reclamaciones';

$conn = new mysqli($host, $user, $pass, $db);

if ($conn->connect_error) {
    die("Error de conexión: " . $conn->connect_error);
}

echo "<h2>Migrando Base de Datos</h2>";
echo "<hr>";

// 1. Actualizar ENUM de roles
echo "1. Actualizando roles...<br>";
$sql_roles = "ALTER TABLE usuarios MODIFY rol ENUM('superadmin', 'asistente_admin', 'decano_upg', 'director_escuela_upg', 'director_area', 'asistente', 'auditor') DEFAULT 'asistente'";
if ($conn->query($sql_roles) === TRUE) {
    echo "✓ Roles actualizados correctamente<br>";
} else {
    echo "✗ Error al actualizar roles: " . $conn->error . "<br>";
}

// 2. Actualizar admin existente a superadmin
echo "<br>2. Actualizando administrador principal...<br>";
$sql_update_admin = "UPDATE usuarios SET rol = 'superadmin' WHERE usuario = 'admin'";
if ($conn->query($sql_update_admin) === TRUE) {
    echo "✓ Usuario admin actualizado a superadmin<br>";
} else {
    echo "✗ Error al actualizar admin: " . $conn->error . "<br>";
}

// 3. Actualizar operadores existentes a asistentes
echo "<br>3. Actualizando operadores a asistentes...<br>";
$sql_update_operadores = "UPDATE usuarios SET rol = 'asistente' WHERE rol = 'operador'";
if ($conn->query($sql_update_operadores) === TRUE) {
    echo "✓ Operadores actualizados a asistentes<br>";
} else {
    echo "✗ Error al actualizar operadores: " . $conn->error . "<br>";
}

// 4. Crear tabla de áreas para asistentes
echo "<br>4. Creando tabla de áreas...<br>";
$sql_areas = "CREATE TABLE IF NOT EXISTS asistentes_areas (
    id INT PRIMARY KEY AUTO_INCREMENT,
    usuario_id INT NOT NULL,
    departamento VARCHAR(100) NOT NULL,
    area VARCHAR(100) NOT NULL,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE,
    INDEX(usuario_id),
    INDEX(departamento),
    INDEX(area)
)";
if ($conn->query($sql_areas) === TRUE) {
    echo "✓ Tabla de áreas creada correctamente<br>";
} else {
    echo "✗ Error al crear tabla: " . $conn->error . "<br>";
}

// 5. Agregar campos de asignación a reclamaciones
echo "<br>5. Agregando campos de asignación a reclamaciones...<br>";
$sql_check_asignado = "SHOW COLUMNS FROM reclamaciones LIKE 'asignado_a'";
$result_check = $conn->query($sql_check_asignado);

if ($result_check->num_rows == 0) {
    $sql_asignado = "ALTER TABLE reclamaciones 
                     ADD COLUMN asignado_a INT,
                     ADD COLUMN fecha_asignacion DATETIME,
                     ADD CONSTRAINT fk_asignado FOREIGN KEY (asignado_a) REFERENCES usuarios(id) ON DELETE SET NULL";
    if ($conn->query($sql_asignado) === TRUE) {
        echo "✓ Campos de asignación agregados correctamente<br>";
    } else {
        echo "✗ Error al agregar campos: " . $conn->error . "<br>";
    }
} else {
    echo "✓ Campos de asignación ya existen<br>";
}

echo "<hr>";
echo "<h3 style='color: green;'>✓ Migración completada</h3>";
echo "<p><a href='../admin/login.php'>Ir al panel de administración</a></p>";

$conn->close();
?>
