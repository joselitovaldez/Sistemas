<?php
// Script para crear la base de datos y tablas
$host = 'localhost';
$user = 'root';
$pass = '';

$conn = new mysqli($host, $user, $pass);

if ($conn->connect_error) {
    die("Error de conexión: " . $conn->connect_error);
}

// Crear base de datos
$sql = "CREATE DATABASE IF NOT EXISTS upeu_reclamaciones";
if ($conn->query($sql) === TRUE) {
    echo "Base de datos creada exitosamente<br>";
} else {
    echo "Error al crear base de datos: " . $conn->error;
}

// Seleccionar la base de datos
$conn->select_db("upeu_reclamaciones");

// Tabla de reclamaciones
$tabla_reclamaciones = "CREATE TABLE IF NOT EXISTS reclamaciones (
    id INT PRIMARY KEY AUTO_INCREMENT,
    folio VARCHAR(20) UNIQUE NOT NULL,
    fecha_registro DATETIME DEFAULT CURRENT_TIMESTAMP,
    
    -- Datos del consumidor
    campus VARCHAR(100) NOT NULL,
    departamento VARCHAR(100) NOT NULL,
    area VARCHAR(100) NOT NULL,
    nombres VARCHAR(100) NOT NULL,
    apellido_paterno VARCHAR(100) NOT NULL,
    apellido_materno VARCHAR(100) NOT NULL,
    dni_ce VARCHAR(15) NOT NULL,
    email VARCHAR(100) NOT NULL,
    telefono VARCHAR(20) NOT NULL,
    domicilio TEXT NOT NULL,
    padre_madre VARCHAR(100),
    
    -- Bien contratado
    tipo_bien VARCHAR(100) NOT NULL,
    descripcion_asunto TEXT NOT NULL,
    
    -- Reclamación
    tipo_registro VARCHAR(100) NOT NULL,
    detalle_reclamacion TEXT NOT NULL,
    pedido TEXT,
    archivo_adjunto VARCHAR(255),
    archivo_adjunto_nombre VARCHAR(255),
    
    -- Estado
    estado ENUM('Pendiente', 'En revisión', 'Resuelto', 'No procede') DEFAULT 'Pendiente',
    respuesta TEXT,
    fecha_respuesta DATETIME,
    
    INDEX(email),
    INDEX(dni_ce),
    INDEX(estado),
    INDEX(fecha_registro)
)";

if ($conn->query($tabla_reclamaciones) === TRUE) {
    echo "Tabla de reclamaciones creada exitosamente<br>";
} else {
    echo "Error al crear tabla: " . $conn->error;
}

// Tabla de usuarios administradores
$tabla_usuarios = "CREATE TABLE IF NOT EXISTS usuarios (
    id INT PRIMARY KEY AUTO_INCREMENT,
    usuario VARCHAR(50) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    nombre VARCHAR(100) NOT NULL,
    apellido_paterno VARCHAR(100) NULL,
    apellido_materno VARCHAR(100) NULL,
    dni VARCHAR(8) UNIQUE NULL,
    email VARCHAR(100) NOT NULL,
    telefono VARCHAR(9) NULL,
    foto VARCHAR(255) NULL,
    rol ENUM('superadmin', 'asistente_admin', 'decano_upg', 'director_escuela_upg', 'director_area', 'asistente', 'auditor') DEFAULT 'asistente',
    estado ENUM('activo', 'inactivo') DEFAULT 'activo',
    fecha_creacion DATETIME DEFAULT CURRENT_TIMESTAMP,
    INDEX(usuario),
    INDEX(dni)
)";

if ($conn->query($tabla_usuarios) === TRUE) {
    echo "Tabla de usuarios creada exitosamente<br>";
} else {
    echo "Error al crear tabla usuarios: " . $conn->error;
}

// Insertar usuario admin por defecto
$admin_pass = password_hash('admin123', PASSWORD_BCRYPT);
$sql_admin = "INSERT IGNORE INTO usuarios (usuario, password, nombre, email, rol) 
              VALUES ('admin', '$admin_pass', 'Administrador', 'admin@upeu.edu.pe', 'superadmin')";

if ($conn->query($sql_admin) === TRUE) {
    echo "Usuario administrador creado exitosamente<br>";
} else {
    echo "Error al crear usuario: " . $conn->error;
}

// Tabla de áreas para asistentes
$tabla_areas = "CREATE TABLE IF NOT EXISTS asistentes_areas (
    id INT PRIMARY KEY AUTO_INCREMENT,
    usuario_id INT NOT NULL,
    departamento VARCHAR(100) NOT NULL,
    area VARCHAR(100) NOT NULL,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE,
    INDEX(usuario_id),
    INDEX(departamento),
    INDEX(area)
)";

if ($conn->query($tabla_areas) === TRUE) {
    echo "Tabla de áreas de asistentes creada exitosamente<br>";
} else {
    echo "Error al crear tabla de áreas: " . $conn->error;
}

// Actualizar roles existentes si es necesario
$actualizar_roles = "ALTER TABLE usuarios MODIFY rol ENUM('superadmin', 'asistente_admin', 'decano_upg', 'director_escuela_upg', 'director_area', 'asistente', 'auditor') DEFAULT 'asistente'";
$conn->query($actualizar_roles); // Ejecutar sin validación para evitar errores si ya está actualizado

// Agregar campo de asignación en reclamaciones
$agregar_asignado = "ALTER TABLE reclamaciones ADD COLUMN IF NOT EXISTS asignado_a INT, 
                     ADD COLUMN IF NOT EXISTS fecha_asignacion DATETIME,
                     ADD FOREIGN KEY IF NOT EXISTS (asignado_a) REFERENCES usuarios(id) ON DELETE SET NULL";
$conn->query($agregar_asignado); // Ejecutar sin validación

echo "<hr>";
echo "¡Setup completado! Puedes acceder al panel de administración.<br>";
echo "Usuario: <strong>admin</strong><br>";
echo "Contraseña: <strong>admin123</strong><br>";

$conn->close();
?>
