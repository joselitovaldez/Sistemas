<?php
require_once 'includes/functions.php';

if (isset($_GET['archivo'])) {
    $archivo = basename($_GET['archivo']);
    $ruta = __DIR__ . '/uploads/' . $archivo;
    
    if (file_exists($ruta)) {
        // Detectar tipo de archivo
        $extension = strtolower(pathinfo($archivo, PATHINFO_EXTENSION));
        
        if ($extension === 'pdf') {
            // Mostrar PDF en el navegador
            header('Content-Type: application/pdf');
            header('Content-Disposition: inline; filename="' . $archivo . '"');
        } else {
            // Descargar otros archivos
            header('Content-Type: application/octet-stream');
            header('Content-Disposition: attachment; filename="' . $archivo . '"');
        }
        
        header('Content-Description: File Transfer');
        header('Expires: 0');
        header('Cache-Control: must-revalidate');
        header('Pragma: public');
        header('Content-Length: ' . filesize($ruta));
        readfile($ruta);
    } else {
        echo "Archivo no encontrado";
    }
} else {
    echo "Solicitud inválida";
}
?>
