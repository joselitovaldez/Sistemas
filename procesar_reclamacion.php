<?php
require_once 'includes/functions.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    // Validar checkbox de privacidad
    if (!isset($_POST['privacidad'])) {
        header('Location: index.php?error=Debes aceptar la política de privacidad');
        exit();
    }
    
    // Preparar datos
    $datos = array(
        'campus' => sanitizar($_POST['campus'] ?? ''),
        'departamento' => sanitizar($_POST['departamento'] ?? ''),
        'area' => sanitizar($_POST['area'] ?? ''),
        'nombres' => sanitizar($_POST['nombres'] ?? ''),
        'apellido_paterno' => sanitizar($_POST['apellido_paterno'] ?? ''),
        'apellido_materno' => sanitizar($_POST['apellido_materno'] ?? ''),
        'dni_ce' => sanitizar($_POST['dni_ce'] ?? ''),
        'email' => sanitizar($_POST['email'] ?? ''),
        'telefono' => sanitizar($_POST['telefono'] ?? ''),
        'domicilio' => sanitizar($_POST['domicilio'] ?? ''),
        'padre_madre' => sanitizar($_POST['padre_madre'] ?? ''),
        'tipo_bien' => sanitizar($_POST['tipo_bien'] ?? ''),
        'descripcion_asunto' => sanitizar($_POST['descripcion_asunto'] ?? ''),
        'tipo_registro' => sanitizar($_POST['tipo_registro'] ?? ''),
        'detalle_reclamacion' => sanitizar($_POST['detalle_reclamacion'] ?? ''),
        'pedido' => sanitizar($_POST['pedido'] ?? ''),
        'archivo_adjunto' => NULL
    );
    
    // Validar campos requeridos
    if (empty($datos['campus']) || empty($datos['departamento']) || empty($datos['area']) || 
        empty($datos['nombres']) || empty($datos['apellido_paterno']) || empty($datos['apellido_materno']) ||
        empty($datos['dni_ce']) || empty($datos['email']) || empty($datos['telefono']) || 
        empty($datos['domicilio']) || empty($datos['tipo_bien']) || empty($datos['descripcion_asunto']) ||
        empty($datos['tipo_registro']) || empty($datos['detalle_reclamacion'])) {
        header('Location: index.php?error=Completa todos los campos requeridos');
        exit();
    }
    
    // Validar DNI
    if (!validarDNI($datos['dni_ce'])) {
        header('Location: index.php?error=DNI inválido');
        exit();
    }
    
    // Validar email
    if (!validarEmail($datos['email'])) {
        header('Location: index.php?error=Email inválido');
        exit();
    }
    
    // Procesar archivo si existe
    if (isset($_FILES['archivo']) && $_FILES['archivo']['size'] > 0) {
        $resultado_archivo = subirArchivo($_FILES['archivo']);
        if (isset($resultado_archivo['error'])) {
            header('Location: index.php?error=' . urlencode($resultado_archivo['error']));
            exit();
        }
        $datos['archivo_adjunto'] = $resultado_archivo['archivo'];
    }
    
    // Guardar reclamación
    $resultado = guardarReclamacion($datos);
    
    if ($resultado['success']) {
        // Guardar folio en sesión para mostrar
        session_start();
        $_SESSION['folio_generado'] = $resultado['folio'];
        header('Location: confirmacion.php');
        exit();
    } else {
        header('Location: index.php?error=' . urlencode($resultado['error']));
        exit();
    }
} else {
    header('Location: index.php');
    exit();
}
?>
