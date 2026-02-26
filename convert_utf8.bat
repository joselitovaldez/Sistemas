@echo off
chcp 65001 > nul

setlocal enabledelayedexpansion
set "files=c:\xampp\htdocs\reclamos\admin\index.php" "c:\xampp\htdocs\reclamos\admin\ajax_actualizar_perfil.php" "c:\xampp\htdocs\reclamos\admin\ajax_cambiar_password.php" "c:\xampp\htdocs\reclamos\config\agregar_campos.php"

for %%f in (%files%) do (
    if exist "%%f" (
        echo ✓ Convertiendo: %%~nf
        powershell -Command "^
            $bytes = [IO.File]::ReadAllBytes('%%f'); ^
            $text = [Text.Encoding]::GetEncoding(28591).GetString($bytes); ^
            $utf8 = New-Object Text.UTF8Encoding $false; ^
            [IO.File]::WriteAllText('%%f', $text, $utf8); ^
            Write-Host 'OK: %%~nf'"
    )
)

echo.
echo ✓ Conversión completada a UTF-8
pause
