<?php
require_once 'includes/functions.php';

if (isset($_GET['archivo'])) {
    $archivo = basename($_GET['archivo']);
    $ruta = __DIR__ . '/uploads/' . $archivo;
    
    if (file_exists($ruta)) {
        $nombre_descarga = $archivo;
        if (isset($conn)) {
            $stmt = $conn->prepare("SELECT archivo_adjunto_nombre FROM reclamaciones WHERE archivo_adjunto = ? LIMIT 1");
            if ($stmt) {
                $stmt->bind_param('s', $archivo);
                if ($stmt->execute()) {
                    $result = $stmt->get_result();
                    $row = $result->fetch_assoc();
                    if (!empty($row['archivo_adjunto_nombre'])) {
                        $nombre_descarga = $row['archivo_adjunto_nombre'];
                    }
                }
                $stmt->close();
            }
        }
        $nombre_descarga = basename($nombre_descarga);
        $nombre_descarga = str_replace(array("\r", "\n"), '', $nombre_descarga);
        $nombre_encoded = rawurlencode($nombre_descarga);
        // Detectar tipo de archivo
        $extension = strtolower(pathinfo($archivo, PATHINFO_EXTENSION));
        
        if ($extension === 'pdf') {
            // Mostrar PDF en el navegador
            header('Content-Type: application/pdf');
            header('Content-Disposition: inline; filename="' . $nombre_descarga . '"; filename*=UTF-8\'\'' . $nombre_encoded);
        } else {
            // Descargar otros archivos
            header('Content-Type: application/octet-stream');
            header('Content-Disposition: attachment; filename="' . $nombre_descarga . '"; filename*=UTF-8\'\'' . $nombre_encoded);
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
