<?php
/**
 * Script para crear tabla de departamentos y áreas
 * Ejecutar una sola vez para inicializar la estructura
 */

require_once 'database.php';

try {
    // Crear tabla de departamentos_areas
    $sql = "CREATE TABLE IF NOT EXISTS departamentos_areas (
        id INT PRIMARY KEY AUTO_INCREMENT,
        departamento VARCHAR(255) NOT NULL,
        area VARCHAR(255) NOT NULL,
        activo TINYINT(1) DEFAULT 1,
        fecha_creacion DATETIME DEFAULT CURRENT_TIMESTAMP,
        INDEX(departamento),
        INDEX(area),
        UNIQUE KEY unique_dept_area (departamento, area)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
    
    if ($conn->query($sql) === TRUE) {
        echo "✅ Tabla departamentos_areas creada exitosamente<br>";
    } else {
        throw new Exception("Error al crear tabla: " . $conn->error);
    }
    
    // Verificar si la tabla está vacía
    $countSql = "SELECT COUNT(*) as total FROM departamentos_areas";
    $result = $conn->query($countSql);
    $row = $result->fetch_assoc();
    
    if ($row['total'] > 0) {
        echo "⚠️ La tabla ya contiene " . $row['total'] . " registros. No se insertarán datos duplicados.<br>";
    } else {
        echo "Insertando datos de departamentos y áreas...<br>";
        
        // Insertar datos
        $datos = [
            // Rectorado
            ['Rectorado', 'Rectorado'],
            
            // Vicerrectorado Académico
            ['Vicerrectorado Académico', 'Dirección Académico-Universitario'],
            ['Vicerrectorado Académico', 'Vicerrectorado Académico'],
            
            // Vicerrectorado de Bienestar Universitario
            ['Vicerrectorado de Bienestar Universitario', 'Residencia Ana Stahl'],
            ['Vicerrectorado de Bienestar Universitario', 'Residencia Fernando Stahl'],
            ['Vicerrectorado de Bienestar Universitario', 'Vicerrectorado de Bienestar Universitario'],
            
            // Secretaría General
            ['Secretaría General', 'Secretaría General'],
            ['Secretaría General', 'Imagen Institucional'],
            
            // Asesoría Legal
            ['Asesoría Legal', 'Asesoría Legal'],
            
            // Auditoría Interna
            ['Auditoría Interna', 'Auditoría Interna'],
            
            // Dirección de Planificación y Gestión de la Calidad
            ['Dirección de Planificación y Gestión de la Calidad', 'Dirección de Planificación y Gestión de la Calidad'],
            
            // Dirección General de Investigación
            ['Dirección General de Investigación', 'Dirección General de Investigación'],
            
            // Dirección de Cooperación y Desarrollo
            ['Dirección de Cooperación y Desarrollo', 'Dirección de Cooperación y Desarrollo'],
            
            // Dirección de Misión
            ['Dirección de Misión', 'Dirección de Misión'],
            
            // DEC
            ['DEC', 'DEC'],
            
            // Educación a Distancia UPeU
            ['Educación a Distancia UPeU', 'Educación a Distancia UPeU'],
            
            // Vicerrectorado Administrativo
            ['Vicerrectorado Administrativo', 'Vicerrectorado Administrativo'],
            
            // Dirección de Marketing
            ['Dirección de Marketing', 'Dirección de Marketing'],
            
            // Dirección Comercial
            ['Dirección Comercial', 'Dirección Comercial'],
            
            // Dirección de Tecnologías de Información
            ['Dirección de Tecnologías de Información', 'Dirección de Tecnologías de Información'],
            
            // Dirección del Talento Humano
            ['Dirección del Talento Humano', 'Dirección del Talento Humano'],
            
            // Dirección Financiero Contable
            ['Dirección Financiero Contable', 'Dirección Financiero Contable'],
            
            // Dirección de Infraestructura
            ['Dirección de Infraestructura', 'Dirección de Infraestructura'],
            
            // Dirección General de Campus
            ['Dirección General de Campus', 'Dirección General de Campus'],
            
            // Centro de Idiomas
            ['Centro de Idiomas', 'Centro de Idiomas'],
            
            // Centro de Investigación Adventista
            ['Centro de Investigación Adventista (Centro de Investigación White)', 'Centro de Investigación Adventista (Centro de Investigación White)'],
            
            // Centro de Recursos del Aprendizaje e Investigación
            ['Centro de Recursos del Aprendizaje e Investigación', 'Centro de Recursos del Aprendizaje e Investigación'],
            
            // Comunicaciones
            ['Comunicaciones', 'Comunicaciones'],
            
            // Conservatorio de Música
            ['Conservatorio de Música', 'Conservatorio de Música'],
            
            // Gestión Comercial
            ['Gestión Comercial', 'Gestión Comercial'],
            
            // Dirección de Operaciones de Campus
            ['Dirección de Operaciones de Campus', 'Dirección de Operaciones de Campus'],
            
            // Dirección de Servicios Generales - Múltiples áreas
            ['Dirección de Servicios Generales', 'Dirección de Servicios Generales'],
            ['Dirección de Servicios Generales', 'Control Patrimonial'],
            ['Dirección de Servicios Generales', 'Lavandería'],
            ['Dirección de Servicios Generales', 'Limpieza'],
            ['Dirección de Servicios Generales', 'Mantenimiento'],
            ['Dirección de Servicios Generales', 'Piscina'],
            ['Dirección de Servicios Generales', 'Recursos Naturales'],
            ['Dirección de Servicios Generales', 'Servicio de Alimentación'],
            
            // Dirección de Activos y Adquisiciones
            ['Dirección de Activos y Adquisiciones', 'Dirección de Activos y Adquisiciones'],
            
            // Facultad de Ciencias de la Salud
            ['Facultad de Ciencias de la Salud', 'Consultorio Médico Unión'],
            ['Facultad de Ciencias de la Salud', 'EP Enfermería'],
            ['Facultad de Ciencias de la Salud', 'EP Medicina'],
            ['Facultad de Ciencias de la Salud', 'EP Nutrición Humana'],
            ['Facultad de Ciencias de la Salud', 'EP Psicología'],
            ['Facultad de Ciencias de la Salud', 'Facultad de Ciencias de la Salud'],
            
            // Facultad de Ciencias Empresariales
            ['Facultad de Ciencias Empresariales', 'EP Administración'],
            ['Facultad de Ciencias Empresariales', 'EP Contabilidad'],
            ['Facultad de Ciencias Empresariales', 'EP Marketing y Negocios Internacionales'],
            ['Facultad de Ciencias Empresariales', 'Facultad de Ciencias Empresariales'],
            ['Facultad de Ciencias Empresariales', 'Administración a Distancia'],
            ['Facultad de Ciencias Empresariales', 'Contabilidad Distancia'],
            
            // Facultad de Ciencias Humanas y Educación
            ['Facultad de Ciencias Humanas y Educación', 'Facultad de Ciencias Humanas y Educación'],
            ['Facultad de Ciencias Humanas y Educación', 'Educación para la Vida'],
            ['Facultad de Ciencias Humanas y Educación', 'EP Ciencias de la Comunicación'],
            ['Facultad de Ciencias Humanas y Educación', 'EP Derecho'],
            ['Facultad de Ciencias Humanas y Educación', 'EP Educación'],
            ['Facultad de Ciencias Humanas y Educación', 'EP Educación a Distancia'],
            
            // Facultad de Ingeniería y Arquitectura
            ['Facultad de Ingeniería y Arquitectura', 'EP Arquitectura'],
            ['Facultad de Ingeniería y Arquitectura', 'EP Ingeniería Ambiental'],
            ['Facultad de Ingeniería y Arquitectura', 'EP Ingeniería Civil'],
            ['Facultad de Ingeniería y Arquitectura', 'EP Ingeniería de Alimentos'],
            ['Facultad de Ingeniería y Arquitectura', 'EP Ingeniería de Sistemas'],
            ['Facultad de Ingeniería y Arquitectura', 'Facultad de Ingeniería y Arquitectura'],
            
            // Facultad de Teología
            ['Facultad de Teología', 'Educación Religiosa'],
            ['Facultad de Teología', 'EP Teología'],
            ['Facultad de Teología', 'Facultad de Teología'],
            
            // Escuela de Posgrado
            ['Escuela de Posgrado', 'Escuela General de Posgrado'],
            ['Escuela de Posgrado', 'Unidad de Posgrado de Administración'],
            ['Escuela de Posgrado', 'Unidad de Posgrado de Ciencias de la Salud'],
            ['Escuela de Posgrado', 'Unidad de Posgrado de Educación'],
            ['Escuela de Posgrado', 'Unidad de Posgrado de Ingeniería'],
            ['Escuela de Posgrado', 'Unidad de Posgrado de Salud Pública'],
            ['Escuela de Posgrado', 'Unidad de Posgrado Psicología'],
            ['Escuela de Posgrado', 'Unidad de Posgrado Teología - SALT'],
            
            // Unión
            ['Unión', 'Unión']
        ];
        
        $insertSql = "INSERT INTO departamentos_areas (departamento, area) VALUES (?, ?)";
        $stmt = $conn->prepare($insertSql);
        
        if (!$stmt) {
            throw new Exception("Error al preparar: " . $conn->error);
        }
        
        $insertados = 0;
        $errores = 0;
        
        foreach ($datos as $fila) {
            $stmt->bind_param("ss", $fila[0], $fila[1]);
            if ($stmt->execute()) {
                $insertados++;
            } else {
                $errores++;
                echo "⚠️ Error al insertar: " . $fila[0] . " - " . $fila[1] . "<br>";
            }
        }
        
        $stmt->close();
        
        echo "✅ Se insertaron $insertados departamentos/áreas<br>";
        if ($errores > 0) {
            echo "⚠️ Se produjeron $errores errores<br>";
        }
    }
    
    echo "<hr>";
    echo "✅ Setup de departamentos completado exitosamente<br>";
    echo "Ahora puedes acceder al sistema y verás los departamentos y áreas disponibles.";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage();
}

$conn->close();
?>
