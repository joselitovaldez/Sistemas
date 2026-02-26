# 📋 Sistema de Libro de Reclamaciones UPeU

Sistema web completo de Libro de Reclamaciones para la Universidad Peruana Unión, conforme al Código de Protección y Defensa del Consumidor.

## 🚀 Características

✅ **Formulario de Reclamaciones** con validación completa  
✅ **Consulta de Estado** mediante folio único  
✅ **Dashboard de Usuario** elegante con foto de perfil  
✅ **Panel de Administración** con gestión de reclamaciones  
✅ **Sistema de Usuarios** con roles (Admin/Operador)  
✅ **Reportes Detallados** por campus, departamento, tipo, estado  
✅ **Carga de Archivos** (PDF, DOC, JPG, PNG - máx 5MB)  
✅ **Cambio de Contraseña** seguro con validación  
✅ **Base de Datos MySQL** optimizada con índices  
✅ **Diseño Responsivo** con estilos modernos y gradientes  

## 📦 Requisitos

- PHP 7.4 o superior
- MySQL 5.7 o superior
- XAMPP, WAMP o servidor web similar
- Navegador moderno

## 🛠️ Instalación

### 1. Copiar Archivos

Coloca los archivos en la carpeta de tu servidor web:
```
c:\xampp\htdocs\reclamos\
```

### 2. Crear Base de Datos

Abre tu navegador e ingresa a:
```
http://localhost/reclamos/config/setup.php
```

Este script automáticamente:
- Crea la base de datos `upeu_reclamaciones`
- Crea las tablas necesarias
- Crea el usuario administrador

**Credenciales por defecto:**
- Usuario: `admin`
- Contraseña: `admin123`

### 3. Configurar Base de Datos (Opcional)

Si necesitas cambiar credenciales, edita `config/database.php`:

```php
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');          // Tu contraseña MySQL
define('DB_NAME', 'upeu_reclamaciones');
```

## 📝 Estructura del Proyecto

```
reclamos/
├── config/
│   ├── database.php          # Conexión a BD
│   └── setup.php             # Script de instalación
├── includes/
│   └── functions.php         # Funciones auxiliares
├── admin/
│   ├── login.php             # Login administrador
│   ├── index.php             # Dashboard
│   ├── reclamaciones.php     # Gestionar reclamaciones
│   ├── ver_reclamacion.php   # Ver detalle
│   ├── usuarios.php          # Gestionar usuarios
│   ├── reportes.php          # Reportes
│   └── logout.php            # Cerrar sesión
├── uploads/                  # Archivos subidos
├── css/
│   └── style.css             # Estilos
├── js/
│   └── script.js             # JavaScript
├── index.php                 # Formulario principal
├── procesar_reclamacion.php  # Procesar envío
├── confirmacion.php          # Confirmación
├── consultar_reclamacion.php # Consultar estado
←── descargar.php             # Descargar archivo
└── README.md                 # Este archivo
```

## 🌐 URLs Principales

| URL | Descripción |
|-----|-------------|
| `http://localhost/reclamos/` | Formulario de reclamación |
| `http://localhost/reclamos/consultar_reclamacion.php` | Consultar estado |
| `http://localhost/reclamos/mi_perfil.php` | Mi Perfil (Nuevo Dashboard) |
| `http://localhost/reclamos/admin/login.php` | Panel administrativo |

## 👤 Usuarios Iniciales

### Administrador
- **Usuario:** admin
- **Contraseña:** admin123
- **Rol:** Administrador (acceso total)

> ⚠️ **Importante:** Cambia la contraseña después de la primera sesión

## 🎨 Dashboard de Usuario (NUEVO)

Accede a `http://localhost/reclamos/mi_perfil.php` para:

✨ **Perfil Elegante** con diseño moderno gradiente  
📸 **Foto de Perfil** - Sube tu foto (JPG, PNG, GIF - máx 2MB)  
🔐 **Cambiar Contraseña** - Actualiza tu contraseña de forma segura  
📊 **Mi Resumen** - Estadísticas de tus reclamaciones  
📋 **Mis Reclamaciones** - Historial de tus reclamos  

### Características del Dashboard:
- Interfaz responsiva y moderna
- Foto de perfil redonda con validación
- Tabs interactivos para diferente información
- Cambio de contraseña con validación
- Diseño con degradados y animaciones
- Panel lateral "sticky" para fácil acceso

## 📋 Funcionalidades del Formulario

### 1. Información del Consumidor
- Campus (selección)
- Departamento (selección)
- Área/Subárea (selección)
- Nombres, Apellidos
- DNI/CE (validado)
- Email (validado)
- Teléfono (validado)
- Domicilio
- Padre/Madre (opcional si es menor)

### 2. Identificación del Bien Contratado
- Tipo de Bien
- Descripción/Asunto

### 3. Detalle de la Reclamación
- Tipo de Registro (Reclamo, Queja, Sugerencia)
- Detalle
- Pedido (qué esperas que se haga)
- Archivo Adjunto (opcional)

## 🔧 Panel Administrativo

### Dashboard
- Vista general de estadísticas
- Total de reclamaciones
- Pendientes vs Resueltas
- Últimas reclamaciones

### Gestionar Reclamaciones
- Filtrar por estado, tipo
- Paginación
- Ver detalles completos
- Actualizar estado
- Agregar respuesta

### Usuarios
- Crear nuevos usuarios
- Asignar roles (Admin/Operador)
- Activar/Desactivar

### Reportes
- Estadísticas por rango de fechas
- Información por campus
- Información por departamento
- Porcentaje de resolución

## 📧 Integración con Email (Opcional)

Para enviar correos automáticos, agrega en `procesar_reclamacion.php`:

```php
// Enviar correo al usuario
$to = $datos['email'];
$subject = "Reclamación registrada - Folio: " . $resultado['folio'];
$message = "Tu folio es: " . $resultado['folio'];
mail($to, $subject, $message);
```

## 📄 Campos de la Base de Datos

### Tabla: reclamaciones
- Información personal del reclamante
- Datos del bien/servicio
- Detalle de la reclamación
- Estado y respuesta
- Metadatos (fechas, folio)

### Tabla: usuarios
- Datos de administradores/operadores
- Roles y permisos
- Control de acceso

## 🔒 Seguridad

✅ Contraseñas hasheadas con bcrypt  
✅ Validación en cliente y servidor  
✅ Prevención SQL Injection (prepared statements)  
✅ Sanitización de inputs  
✅ Máximo tamaño de archivo: 5MB  
✅ Sesiones seguras

## 🐛 Solución de Problemas

### Error de conexión a BD
1. Verifica que MySQL esté iniciado
2. Comprueba credenciales en `config/database.php`
3. Asegúrate que la BD existe o ejecuta setup.php

### No cargan las imágenes/CSS
1. Verifica las rutas en los archivos
2. Asegúrate que la carpeta `uploads` existe y tiene permisos

### "usuario no encontrado" en login
1. Verifica que setup.php se ejecutó exitosamente
2. Revisa que la tabla usuarios tiene datos
3. Intenta crear un nuevo usuario en la página de usuarios

## 📞 Soporte

Para reportar problemas o sugerencias, contacta al equipo de TI de UPeU.

## 📋 Comprobación de Cumplimiento Normativo

Este sistema cumple con:
- ✅ Código de Protección y Defensa del Consumidor
- ✅ Almacenamiento mínimo de 60 días
- ✅ Respuesta en máximo 30 días hábiles
- ✅ Acceso público al libro de reclamaciones

---

**Última actualización:** 23 de febrero de 2026  
**Versión:** 1.0  
**Universidad Peruana Unión** - Todos los derechos reservados
