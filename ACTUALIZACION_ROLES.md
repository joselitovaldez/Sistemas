# Sistema de Roles y Derivación de Reclamos - Actualización

## Cambios Implementados

### 1. Sistema de Roles Expandido
Se ha expandido el sistema de roles de 2 a 4 niveles:
- **Superadmin**: Control total del sistema
- **Admin**: Gestión de usuarios y reclamos
- **Supervisor**: Supervisión de áreas y asistentes
- **Asistente**: Atención de reclamos asignados

### 2. Vista de Detalle Integrada (SPA)
- El detalle de reclamos ahora se muestra en un modal dentro del dashboard
- Ya no redirige a páginas separadas
- Todos los archivos antiguos (ver_reclamacion.php) pueden ser eliminados

### 3. Nuevas Funcionalidades
- **Responder Reclamo**: Botón específico para gestionar y responder reclamos
- **Derivar Reclamo**: Sistema de asignación de reclamos a usuarios específicos
- **Áreas por Asistente**: Los asistentes pueden ser asignados a departamentos/áreas específicas

### 4. Base de Datos
Se agregaron:
- Campo `asignado_a` en tabla reclamaciones
- Campo `fecha_asignacion` en tabla reclamaciones
- Nueva tabla `asistentes_areas` para asignación de áreas

## Instrucciones de Instalación

### Paso 1: Ejecutar Migración de Base de Datos
Para actualizar una base de datos existente sin perder datos:

1. Abrir el navegador y visitar:
   ```
   http://localhost/reclamos/config/migrate.php
   ```

2. Este script automáticamente:
   - Actualizará los roles de usuarios
   - Convertirá 'admin' a 'superadmin'
   - Convertirá 'operador' a 'asistente'
   - Creará la tabla asistentes_areas
   - Agregará campos de asignación a reclamaciones

### Paso 2: Para Instalaciones Nuevas
Si está instalando el sistema por primera vez:

1. Ejecutar:
   ```
   http://localhost/reclamos/config/setup.php
   ```

2. Esto creará toda la estructura desde cero con los nuevos roles

## Uso del Sistema

### Botones en la Tabla de Reclamaciones
1. **Ver Detalle** (Ojo): Muestra toda la información del reclamo en un modal
2. **Responder Reclamo** (Flecha responder): Permite cambiar estado y agregar respuesta
3. **Derivar Reclamo** (Compartir): Asigna el reclamo a otro usuario

### Asignar Áreas a Asistentes
Para asignar asistentes a áreas específicas, agregar registros en la tabla `asistentes_areas`:

```sql
INSERT INTO asistentes_areas (usuario_id, departamento, area) 
VALUES (2, 'Académico', 'Registro y Matrícula');
```

## Archivos Creados/Modificados

### Archivos Nuevos:
- `admin/ajax_ver_reclamo.php` - Muestra detalle del reclamo en modal
- `admin/ajax_responder_reclamo.php` - Formulario para responder
- `admin/ajax_derivar_reclamo.php` - Formulario para derivar
- `admin/ajax_guardar_respuesta.php` - Guarda la respuesta
- `admin/ajax_guardar_derivacion.php` - Guarda la derivación
- `config/migrate.php` - Script de migración de base de datos

### Archivos Modificados:
- `admin/index.php` - Agregados modales y funciones JavaScript
- `css/admin-dashboard-rocker.css` - Estilos para modales y alertas
- `config/setup.php` - Actualizado para nuevos roles

### Archivos que pueden ser eliminados (ya no se usan):
- `admin/ver_reclamacion.php` (reemplazado por modal)
- `admin/reclamaciones.php` (integrado en index.php)
- `admin/usuarios.php` (procesamiento, pero mantener por ahora para el formulario)
- `admin/reportes.php` (integrado en index.php)

## Características Técnicas

### Modal System
- Overlay con blur effect
- Animaciones smooth (fadeIn, slideUp)
- Cierre por clic fuera o botón X
- Carga dinámica vía AJAX
- Loading spinner mientras carga

### Derivación de Reclamos
- Selección de usuario con categorías por rol
- Muestra área/departamento del usuario asignado
- Comentario opcional para el usuario
- Actualiza automáticamente el estado a "En revisión"

### Responder Reclamos
- Cambio de estado (Pendiente, En revisión, Resuelto, No procede)
- Campo de respuesta con contador de caracteres
- Guarda fecha de respuesta automáticamente
- Muestra historial de última respuesta

## Próximas Mejoras Sugeridas
- [ ] Notificaciones por email al derivar reclamos
- [ ] Dashboard diferenciado por rol
- [ ] Historial de derivaciones y cambios
- [ ] Filtros por usuario asignado
- [ ] Reportes por área y asistente
- [ ] Permisos granulares por rol

## Nuevas Actualizaciones (Versión 2.0)

### Login Rediseñado
- ✨ Nuevo diseño moderno con gradientes
- 🎨 Estilos consistentes con el dashboard Rocker
- 👁️ Toggle de contraseña con iconos Font Awesome
- 📱 Totalmente responsive
- ⚡ Animaciones suaves (fadeIn, shake)

### Sección Mi Perfil (Integrada en Dashboard)
- 🧑‍💼 Tarjeta de perfil con avatar e información
- 🔐 Cambio de contraseña integrado
- 💪 Indicador de fortaleza de contraseña
- 👁️ Toggle para ver/ocultar contraseña
- 🔒 Validaciones de seguridad

### Archivos Nuevos (Versión 2.0)
- `admin/ajax_cambiar_password.php` - Actualiza contraseña del usuario

### Archivos Modificados (Versión 2.0)
- `admin/login.php` - Rediseño completo con estilos Rocker
- `admin/index.php` - Agregados sección perfil y funciones JavaScript
- `css/admin-dashboard-rocker.css` - Estilos para login y perfil

## Datos de Acceso Predeterminados
```
Usuario: admin
Contraseña: admin123
Rol: superadmin
```

---

**Fecha de actualización**: <?php echo date('d/m/Y'); ?>
