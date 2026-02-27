<?php
require_once dirname(__FILE__) . '/../config/database.php';

// Generar folio único
function generarFolio() {
    global $conn;
    $sql = "SELECT COUNT(*) as total FROM reclamaciones WHERE DATE(fecha_registro) = CURDATE()";
    $result = $conn->query($sql);
    $row = $result->fetch_assoc();
    $numero = $row['total'] + 1;
    return 'UPeU-' . str_pad($numero, 6, '0', STR_PAD_LEFT);
}

// Validar email
function validarEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL);
}

// Validar DNI/CE
function validarDNI($dni) {
    return preg_match('/^[0-9]{8}$|^[A-Z]{2}[0-9]{6}$/', trim($dni));
}

// Sanitizar input
function sanitizar($data) {
    return htmlspecialchars(stripslashes(trim($data)));
}

// Subir archivo
function subirArchivo($file) {
    $directorio = dirname(__FILE__) . '/../uploads/';
    $extensiones_permitidas = array('pdf', 'jpg', 'jpeg', 'png', 'doc', 'docx');
    $tamaño_maximo = 5 * 1024 * 1024; // 5 MB
    
    if ($file['size'] > $tamaño_maximo) {
        return array('error' => 'El archivo es demasiado grande (máximo 5 MB)');
    }
    
    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    
    if (!in_array($ext, $extensiones_permitidas)) {
        return array('error' => 'Extensión de archivo no permitida');
    }
    
    $nombre_original = basename($file['name']);
    $nombre_original = str_replace(array("/", "\\"), '', $nombre_original);
    $nombre_archivo = time() . '_' . uniqid() . '.' . $ext;
    $ruta_completa = $directorio . $nombre_archivo;
    
    if (move_uploaded_file($file['tmp_name'], $ruta_completa)) {
        return array('success' => true, 'archivo' => $nombre_archivo, 'nombre_original' => $nombre_original);
    } else {
        return array('error' => 'Error al subir el archivo');
    }
}

// Guardar reclamación
function guardarReclamacion($datos) {
    global $conn;
    
    $folio = generarFolio();
    
    $stmt = $conn->prepare("
        INSERT INTO reclamaciones (
            folio, campus, departamento, area, nombres, 
            apellido_paterno, apellido_materno, dni_ce, email, 
            telefono, domicilio, padre_madre, tipo_bien, 
            descripcion_asunto, tipo_registro, detalle_reclamacion, 
            pedido, archivo_adjunto, archivo_adjunto_nombre
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
    ");
    
    $stmt->bind_param(
        "sssssssssssssssssss",
        $folio,
        $datos['campus'],
        $datos['departamento'],
        $datos['area'],
        $datos['nombres'],
        $datos['apellido_paterno'],
        $datos['apellido_materno'],
        $datos['dni_ce'],
        $datos['email'],
        $datos['telefono'],
        $datos['domicilio'],
        $datos['padre_madre'],
        $datos['tipo_bien'],
        $datos['descripcion_asunto'],
        $datos['tipo_registro'],
        $datos['detalle_reclamacion'],
        $datos['pedido'],
        $datos['archivo_adjunto'],
        $datos['archivo_adjunto_nombre']
    );
    
    if ($stmt->execute()) {
        return array('success' => true, 'folio' => $folio);
    } else {
        return array('error' => $stmt->error);
    }
}

// Obtener reclamación por folio
function obtenerReclamacion($folio) {
    global $conn;
    $stmt = $conn->prepare("SELECT * FROM reclamaciones WHERE folio = ?");
    $stmt->bind_param("s", $folio);
    $stmt->execute();
    $result = $stmt->get_result();
    return $result->fetch_assoc();
}

// Listar opciones de campus
function getCampus() {
    return array(
        'Lima' => 'Campus Lima',
        'Juliaca' => 'Campus Juliaca',
        'Tarapoto' => 'Campus Tarapoto'
    );
}

// Listar opciones de departamentos
function getDepartamentos() {
    return array(
        'Rectorado' => 'Rectorado',
        'Vicerrectorado Académico' => 'Vicerrectorado Académico',
        'Vicerrectorado de Bienestar Universitario' => 'Vicerrectorado de Bienestar Universitario',
        'Secretaría General' => 'Secretaría General',
        'Imagen Institucional' => 'Imagen Institucional',
        'Asesoría Legal' => 'Asesoría Legal',
        'Auditoría Interna' => 'Auditoría Interna',
        'Dirección de Planificación y Gestión de la Calidad' => 'Dirección de Planificación y Gestión de la Calidad',
        'Dirección General de Investigación' => 'Dirección General de Investigación',
        'Dirección de Cooperación y Desarrollo' => 'Dirección de Cooperación y Desarrollo',
        'Dirección de Misión' => 'Dirección de Misión',
        'IDEC' => 'IDEC',
        'Educación a Distancia UPeU' => 'Educación a Distancia UPeU',
        'Vicerrectorado Administrativo' => 'Vicerrectorado Administrativo',
        'Dirección de Marketing' => 'Dirección de Marketing',
        'Dirección Comercial' => 'Dirección Comercial',
        'Dirección de Tecnologías de Información' => 'Dirección de Tecnologías de Información',
        'Dirección del Talento Humano' => 'Dirección del Talento Humano',
        'Dirección Financiero Contable' => 'Dirección Financiero Contable',
        'Dirección de Infraestructura' => 'Dirección de Infraestructura',
        'Dirección General de Campus' => 'Dirección General de Campus',
        'Centro de Idiomas' => 'Centro de Idiomas',
        'Centro de Investigación Adventista (Centro de Investigación White)' => 'Centro de Investigación Adventista (Centro de Investigación White)',
        'Centro de Recursos del Aprendizaje e Investigación' => 'Centro de Recursos del Aprendizaje e Investigación',
        'Comunicaciones' => 'Comunicaciones',
        'Conservatorio de Música' => 'Conservatorio de Música',
        'Gestión Comercial' => 'Gestión Comercial',
        'Dirección de Operaciones de Campus' => 'Dirección de Operaciones de Campus',
        'Dirección de Servicios Generales' => 'Dirección de Servicios Generales',
        'Dirección de Activos y Adquisiciones' => 'Dirección de Activos y Adquisiciones',
        'Facultad de Ciencias de la Salud' => 'Facultad de Ciencias de la Salud',
        'Facultad de Ciencias Empresariales' => 'Facultad de Ciencias Empresariales',
        'Facultad de Ciencias Humanas y Educación' => 'Facultad de Ciencias Humanas y Educación',
        'Facultad de Ingeniería y Arquitectura' => 'Facultad de Ingeniería y Arquitectura',
        'Facultad de Teología' => 'Facultad de Teología',
        'Escuela de Posgrado' => 'Escuela de Posgrado',
        'Unión' => 'Unión'
    );
}

// Listar opciones de áreas por departamento
function getAreasPorDepartamento() {
    return array(
        'Rectorado' => array(
            'Rectorado'
        ),
        'Vicerrectorado Académico' => array(
            'Dirección Académico-Universitario',
            'Vicerrectorado Académico'
        ),
        'Vicerrectorado de Bienestar Universitario' => array(
            'Residencia Ana Stahl',
            'Residencia Fernando Stahl',
            'Vicerrectorado de Bienestar Universitario'
        ),
        'Secretaría General' => array(
            'Secretaría General'
        ),
        'Imagen Institucional' => array(
            'Imagen Institucional'
        ),
        'Asesoría Legal' => array(
            'Asesoría Legal'
        ),
        'Auditoría Interna' => array(
            'Auditoría Interna'
        ),
        'Dirección de Planificación y Gestión de la Calidad' => array(
            'Dirección de Planificación y Gestión de la Calidad'
        ),
        'Dirección General de Investigación' => array(
            'Dirección General de Investigación'
        ),
        'Dirección de Cooperación y Desarrollo' => array(
            'Dirección de Cooperación y Desarrollo'
        ),
        'Dirección de Misión' => array(
            'Dirección de Misión'
        ),
        'IDEC' => array(
            'IDEC'
        ),
        'Educación a Distancia UPeU' => array(
            'Educación a Distancia UPeU'
        ),
        'Vicerrectorado Administrativo' => array(
            'Vicerrectorado Administrativo'
        ),
        'Dirección de Marketing' => array(
            'Dirección de Marketing'
        ),
        'Dirección Comercial' => array(
            'Dirección Comercial'
        ),
        'Dirección de Tecnologías de Información' => array(
            'Dirección de Tecnologías de Información'
        ),
        'Dirección del Talento Humano' => array(
            'Dirección del Talento Humano'
        ),
        'Dirección Financiero Contable' => array(
            'Dirección Financiero Contable'
        ),
        'Dirección de Infraestructura' => array(
            'Dirección de Infraestructura'
        ),
        'Dirección General de Campus' => array(
            'Dirección General de Campus'
        ),
        'Centro de Idiomas' => array(
            'Centro de Idiomas'
        ),
        'Centro de Investigación Adventista (Centro de Investigación White)' => array(
            'Centro de Investigación Adventista (Centro de Investigación White)'
        ),
        'Centro de Recursos del Aprendizaje e Investigación' => array(
            'Centro de Recursos del Aprendizaje e Investigación'
        ),
        'Comunicaciones' => array(
            'Comunicaciones'
        ),
        'Conservatorio de Música' => array(
            'Conservatorio de Música'
        ),
        'Gestión Comercial' => array(
            'Gestión Comercial'
        ),
        'Dirección de Operaciones de Campus' => array(
            'Dirección de Operaciones de Campus'
        ),
        'Dirección de Servicios Generales' => array(
            'Dirección de Servicios Generales',
            'Control Patrimonial',
            'Lavandería',
            'Limpieza',
            'Mantenimiento',
            'Piscina',
            'Recursos Naturales',
            'Servicio de Alimentación'
        ),
        'Dirección de Activos y Adquisiciones' => array(
            'Dirección de Activos y Adquisiciones'
        ),
        'Facultad de Ciencias de la Salud' => array(
            'Consultorio Médico Unión',
            'EP Enfermería',
            'EP Medicina',
            'EP Nutrición Humana',
            'EP Psicología',
            'Facultad de Ciencias de la Salud'
        ),
        'Facultad de Ciencias Empresariales' => array(
            'EP Administración',
            'EP Contabilidad y Gestión Tributaria',
            'EP Marketing y Negocios Internacionales',
            'EP Administración - [a Distancia]',
            'EP Contabilidad y Gestión Tributaria - [a Distancia]',
            'Facultad de Ciencias Empresariales'
        ),
        'Facultad de Ciencias Humanas y Educación' => array(
            'Facultad de Ciencias Humanas y Educación',
            'EP Ciencias de la Comunicación',
            'Educación Talleres para la Vida',
            'EP Derecho',
            'EP Educación',
            'EP Educación - [a Distancia]'
        ),
        'Facultad de Ingeniería y Arquitectura' => array(
            'EP Arquitectura',
            'EP Ingeniería Ambiental',
            'EP Ingeniería Civil',
            'EP Ingeniería de Industrias Alimentarias',
            'EP Ingeniería de Sistemas',
            'Facultad de Ingeniería y Arquitectura'
        ),
        'Facultad de Teología' => array(
            'Educación Religiosa',
            'EP Teología',
            'Facultad de Teología'
        ),
        'Escuela de Posgrado' => array(
            'Escuela General de Posgrado',
            'Unidad de Posgrado de Administración',
            'Unidad de Posgrado de Ciencias de la Salud',
            'Unidad de Posgrado de Educación',
            'Unidad de Posgrado de Ingeniería',
            'Unidad de Posgrado de Salud Pública',
            'Unidad de Posgrado Psicología',
            'Unidad de Posgrado Teología - SALT'
        ),
        'Unión' => array(
            'Unión'
        )
    );
}

// Listar opciones de áreas (consolidado)
function getAreas() {
    $areas = array();
    foreach (getAreasPorDepartamento() as $lista) {
        foreach ($lista as $area) {
            $areas[$area] = $area;
        }
    }
    return $areas;
}

// Listar tipos de bien
function getTiposBien() {
    return array(
        'Servicio Educativo' => 'Servicio Educativo',
        'Servicio de Administración' => 'Servicio de Administración',
        'Infraestructura' => 'Infraestructura',
        'Otro' => 'Otro'
    );
}

// Listar tipos de registro
function getTiposRegistro() {
    return array(
        'Reclamo' => 'Reclamo',
        'Queja' => 'Queja',
        'Sugerencia' => 'Sugerencia'
    );
}

?>
