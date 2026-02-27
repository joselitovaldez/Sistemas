<?php
// Script para agregar columna archivo_adjunto_nombre si no existe
$host = 'localhost';
$user = 'root';
$pass = '';
$db = 'upeu_reclamaciones';

$conn = new mysqli($host, $user, $pass, $db);

if ($conn->connect_error) {
    die("Error de conexion: " . $conn->connect_error);
}

echo "<h2>Agregar columna archivo_adjunto_nombre</h2>";
echo "<hr>";

$sql_check = "SHOW COLUMNS FROM reclamaciones LIKE 'archivo_adjunto_nombre'";
$result_check = $conn->query($sql_check);

if ($result_check && $result_check->num_rows == 0) {
    $sql_add = "ALTER TABLE reclamaciones ADD COLUMN archivo_adjunto_nombre VARCHAR(255) NULL AFTER archivo_adjunto";
    if ($conn->query($sql_add) === TRUE) {
        echo "✓ Columna archivo_adjunto_nombre agregada correctamente<br>";
    } else {
        echo "✗ Error al agregar columna: " . $conn->error . "<br>";
    }
} else {
    echo "✓ La columna archivo_adjunto_nombre ya existe<br>";
}

echo "<hr>";
echo "<p>Listo.</p>";

$conn->close();
?>
