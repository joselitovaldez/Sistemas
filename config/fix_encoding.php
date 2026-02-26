<?php
// Script para corregir encoding en archivos PHP

$archivos = [
    __DIR__ . '/../admin/index.php',
    __DIR__ . '/../admin/ajax_actualizar_perfil.php',
    __DIR__ . '/../admin/ajax_cambiar_password.php',
    __DIR__ . '/agregar_campos.php'
];

$reemplazos = [
    'Ã¡' => 'á',
    'Ã©' => 'é',
    'Ã­' => 'í',
    'Ã³' => 'ó',
    'Ãº' => 'ú',
    'Ã ' => 'à',
    'Ã‰' => 'É',
    'Ã"' => 'Ó',
    'Ã"' => 'Ú',
    'Ã±' => 'ñ',
    'Ã§' => 'ç',
];

foreach ($archivos as $archivo) {
    if (file_exists($archivo)) {
        echo "<p>Procesando: $archivo</p>";
        
        $contenido = file_get_contents($archivo);
        
        foreach ($reemplazos as $viejo => $nuevo) {
            $contenido = str_replace($viejo, $nuevo, $contenido);
        }
        
        // Guardar en UTF-8 sin BOM
        file_put_contents($archivo, $contenido);
        
        echo "<p>✓ $archivo - Corregido</p>";
    } else {
        echo "<p>✗ $archivo - No encontrado</p>";
    }
}

echo "<p><strong>✓ Reparación completada. Recarga la página.</strong></p>";
?>
