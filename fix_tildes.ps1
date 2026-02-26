# Script para reparar encoding de tildes
$archivos = @(
    "c:\xampp\htdocs\reclamos\admin\index.php",
    "c:\xampp\htdocs\reclamos\admin\ajax_actualizar_perfil.php",
    "c:\xampp\htdocs\reclamos\admin\ajax_cambiar_password.php",
    "c:\xampp\htdocs\reclamos\config\agregar_campos.php"
)

foreach ($archivo in $archivos) {
    if (Test-Path $archivo) {
        Write-Host "Procesando: $archivo"
        
        # Leer el contenido con encoding UTF-8
        $contenido = Get-Content $archivo -Raw -Encoding UTF8
        
        # Reemplazos de caracteres mal codificados
        $reemplazos = @{
            'Ã¡' = 'á'
            'Ã©' = 'é'
            'Ã­' = 'í'
            'Ã³' = 'ó'
            'Ãº' = 'ú'
            'Ã ' = 'à'
            'Ã"' = 'À'
            'Ã‰' = 'É'
            'Ã"' = 'Ó'
            'Ã"' = 'Ú'
            'Ã±' = 'ñ'
            'Ã¸' = 'ø'
            'Ã§' = 'ç'
        }
        
        foreach ($viejo in $reemplazos.Keys) {
            $contenido = $contenido -replace [regex]::Escape($viejo), $reemplazos[$viejo]
        }
        
        # Guardar con encoding UTF-8 sin BOM
        $encoding = New-Object System.Text.UTF8Encoding $false
        [System.IO.File]::WriteAllText($archivo, $contenido, $encoding)
        
        Write-Host "✓ $archivo - Corregido"
    } else {
        Write-Host "✗ $archivo - No encontrado"
    }
}

Write-Host "`n✓ Todos los archivos han sido reparados"
