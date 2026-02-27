<?php
header('Content-Type: text/html; charset=utf-8');
session_start();

// Verifica si el usuario está autenticado
if (!isset($_SESSION['usuario'])) {
    header('Location: login.php');
    exit();
}

require_once '../config/database.php';
require_once '../includes/functions.php';

// Obtener información del usuario actual y actualizar la sesión si es necesario
$sql_usuario_actual = "SELECT id, rol, nombre, apellido_paterno, apellido_materno, dni, email, telefono, foto FROM usuarios WHERE usuario = ?";
$stmt_usuario = $conn->prepare($sql_usuario_actual);
$stmt_usuario->bind_param("s", $_SESSION['usuario']);
$stmt_usuario->execute();
$usuario_actual = $stmt_usuario->get_result()->fetch_assoc();

// Actualizar datos de sesión si no existen o están vacíos
if ($usuario_actual) {
    if (!isset($_SESSION['nombre']) || $_SESSION['nombre'] === '') {
        $_SESSION['nombre'] = $usuario_actual['nombre'] ?? '';
    }
    if (!isset($_SESSION['apellido_paterno']) || $_SESSION['apellido_paterno'] === '') {
        $_SESSION['apellido_paterno'] = $usuario_actual['apellido_paterno'] ?? '';
    }
    if (!isset($_SESSION['apellido_materno']) || $_SESSION['apellido_materno'] === '') {
        $_SESSION['apellido_materno'] = $usuario_actual['apellido_materno'] ?? '';
    }
    if (!isset($_SESSION['dni']) || $_SESSION['dni'] === '') {
        $_SESSION['dni'] = $usuario_actual['dni'] ?? '';
    }
    if (!isset($_SESSION['email']) || $_SESSION['email'] === '') {
        $_SESSION['email'] = $usuario_actual['email'] ?? '';
    }
    if (!isset($_SESSION['telefono']) || $_SESSION['telefono'] === '') {
        $_SESSION['telefono'] = $usuario_actual['telefono'] ?? '';
    }
    if (!isset($_SESSION['foto']) || $_SESSION['foto'] === '') {
        $_SESSION['foto'] = $usuario_actual['foto'] ?? '';
    }
}

// Normalizar ruta de la foto para uso en admin
$foto_perfil = $_SESSION['foto'] ?? '';
if (!empty($foto_perfil)) {
    $foto_lower = strtolower($foto_perfil);
    if (strpos($foto_lower, 'http://') === 0 || strpos($foto_lower, 'https://') === 0 || strpos($foto_lower, '/') === 0) {
        // Mantener ruta absoluta o URL
    } elseif (strpos($foto_lower, '../') === 0) {
        // Mantener ruta relativa ya ajustada
    } elseif (strpos($foto_lower, 'uploads/') === 0) {
        $foto_perfil = '../' . $foto_perfil;
    } else {
        $foto_perfil = '../uploads/perfiles/' . $foto_perfil;
    }
}

// Sin filtros por rol
$where_areas = "";
$areas_usuario = [];

if ($usuario_actual) {
    $sql_areas = "SELECT departamento, area FROM asistentes_areas WHERE usuario_id = ?";
    $stmt_areas = $conn->prepare($sql_areas);
    $stmt_areas->bind_param("i", $usuario_actual['id']);
    $stmt_areas->execute();
    $result_areas = $stmt_areas->get_result();

    while ($row_area = $result_areas->fetch_assoc()) {
        $areas_usuario[] = $row_area;
    }
}

// Obtener estadísticas del Dashboard
$sql_total = "SELECT COUNT(*) as total FROM reclamaciones WHERE 1=1 $where_areas";
$result_total = $conn->query($sql_total);
$row_total = $result_total->fetch_assoc();

$sql_pendientes = "SELECT COUNT(*) as total FROM reclamaciones WHERE estado = 'En revisión' $where_areas";
$result_pendientes = $conn->query($sql_pendientes);
$row_pendientes = $result_pendientes->fetch_assoc();

$sql_resueltos = "SELECT COUNT(*) as total FROM reclamaciones WHERE estado = 'Resuelto' $where_areas";
$result_resueltos = $conn->query($sql_resueltos);
$row_resueltos = $result_resueltos->fetch_assoc();

$sql_pendientes_real = "SELECT COUNT(*) as total FROM reclamaciones WHERE estado = 'Pendiente' $where_areas";
$result_pendientes_real = $conn->query($sql_pendientes_real);
$row_pendientes_real = $result_pendientes_real->fetch_assoc();

$sql_no_procede = "SELECT COUNT(*) as total FROM reclamaciones WHERE estado = 'No procede' $where_areas";
$result_no_procede = $conn->query($sql_no_procede);
$row_no_procede = $result_no_procede->fetch_assoc();

$sql_hoy = "SELECT COUNT(*) as total FROM reclamaciones WHERE DATE(fecha_registro) = CURDATE() $where_areas";
$result_hoy = $conn->query($sql_hoy);
$row_hoy = $result_hoy->fetch_assoc();

// Datos para Reclamaciones
$sql_reclamaciones = "SELECT id, folio, fecha_registro, nombres, apellido_paterno, email, tipo_registro, estado, departamento, area 
                      FROM reclamaciones 
                      WHERE 1=1 $where_areas
                      ORDER BY fecha_registro DESC 
                      LIMIT 50";
$result_reclamaciones = $conn->query($sql_reclamaciones);

// Datos para Usuarios
$usuarios_array = [];
$sql_usuarios = "SELECT * FROM usuarios ORDER BY fecha_creacion DESC";
$result_usuarios = $conn->query($sql_usuarios);
while ($row = $result_usuarios->fetch_assoc()) {
    $usuarios_array[] = $row;
}

// Datos para Gestión de Roles
$usuarios_con_roles = [];
$sql_roles = "SELECT u.*, COUNT(aa.id) as areas_asignadas 
              FROM usuarios u 
              LEFT JOIN asistentes_areas aa ON u.id = aa.usuario_id 
              GROUP BY u.id 
              ORDER BY u.rol, u.nombre";
$result_roles = $conn->query($sql_roles);
while ($row = $result_roles->fetch_assoc()) {
    $usuarios_con_roles[] = $row;
}

// Datos para Reportes
$fecha_inicio = date('Y-m-01');
$fecha_fin = date('Y-m-t');
$sql_stats = "SELECT 
    COUNT(*) as total,
    SUM(CASE WHEN estado='Resuelto' THEN 1 ELSE 0 END) as resueltos,
    SUM(CASE WHEN estado='Pendiente' THEN 1 ELSE 0 END) as pendientes,
    SUM(CASE WHEN estado='En revisión' THEN 1 ELSE 0 END) as en_revision,
    SUM(CASE WHEN estado='No procede' THEN 1 ELSE 0 END) as no_procede,
    SUM(CASE WHEN tipo_registro='Reclamo' THEN 1 ELSE 0 END) as reclamos,
    SUM(CASE WHEN tipo_registro='Queja' THEN 1 ELSE 0 END) as quejas,
    SUM(CASE WHEN tipo_registro='Sugerencia' THEN 1 ELSE 0 END) as sugerencias
FROM reclamaciones 
WHERE DATE(fecha_registro) BETWEEN '$fecha_inicio' AND '$fecha_fin' $where_areas";
$stats = $conn->query($sql_stats)->fetch_assoc();

// Datos por mes para el año actual
$ano_actual = date('Y');
$sql_por_mes = "SELECT 
    MONTH(fecha_registro) as mes,
    SUM(CASE WHEN tipo_registro='Reclamo' THEN 1 ELSE 0 END) as reclamos,
    SUM(CASE WHEN tipo_registro='Queja' THEN 1 ELSE 0 END) as quejas,
    SUM(CASE WHEN tipo_registro='Sugerencia' THEN 1 ELSE 0 END) as sugerencias
FROM reclamaciones 
WHERE YEAR(fecha_registro) = $ano_actual $where_areas
GROUP BY MONTH(fecha_registro)
ORDER BY MONTH(fecha_registro)";
$result_por_mes = $conn->query($sql_por_mes);

$datos_por_mes = array_fill(1, 12, ['reclamos' => 0, 'quejas' => 0, 'sugerencias' => 0]);
while ($row = $result_por_mes->fetch_assoc()) {
    $datos_por_mes[$row['mes']] = [
        'reclamos' => $row['reclamos'],
        'quejas' => $row['quejas'],
        'sugerencias' => $row['sugerencias']
    ];
}

$sql_campus = "SELECT campus, COUNT(*) as cantidad FROM reclamaciones 
WHERE DATE(fecha_registro) BETWEEN '$fecha_inicio' AND '$fecha_fin' $where_areas
GROUP BY campus";
$campus_result = $conn->query($sql_campus);

// Estadísticas de estados para el mes actual
$sql_estados = "SELECT 
    SUM(CASE WHEN estado='Resuelto' THEN 1 ELSE 0 END) as resueltos,
    SUM(CASE WHEN estado='En revisión' THEN 1 ELSE 0 END) as en_revision,
    SUM(CASE WHEN estado='No procede' THEN 1 ELSE 0 END) as no_procede,
    SUM(CASE WHEN estado='Pendiente' THEN 1 ELSE 0 END) as pendientes
FROM reclamaciones
WHERE DATE(fecha_registro) BETWEEN '$fecha_inicio' AND '$fecha_fin' $where_areas";
$estados_result = $conn->query($sql_estados)->fetch_assoc();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel de Administración - UPeU</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <!-- Font changed to Arial -->
    <link rel="stylesheet" href="../css/admin-dashboard-rocker.css">
    <link rel="stylesheet" href="../css/admin-dashboard.css">
    <link rel="stylesheet" href="../css/admin-reclamaciones.css">
    <link rel="stylesheet" href="../css/admin-reportes.css">
    <link rel="stylesheet" href="../css/admin-usuarios.css">
    <link rel="stylesheet" href="../css/admin-index-inline.css">
    <link rel="stylesheet" href="../css/admin-reportes-inline.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body class="rocker-dashboard">

<!-- Sidebar -->
<div class="sidebar">
    <div class="sidebar-header">
        <i class="fas fa-book-open"></i>
        <span class="sidebar-title">UPeU Reclamos</span>
    </div>
    
    <nav class="sidebar-nav">
        <a href="#" class="nav-item active" data-section="dashboard">
            <i class="fas fa-home"></i>
            <span>Dashboard</span>
        </a>
        <a href="#" class="nav-item" data-section="reclamaciones">
            <i class="fas fa-clipboard-list"></i>
            <span>Reclamaciones</span>
        </a>
        <a href="#" class="nav-item" data-section="usuarios">
            <i class="fas fa-users"></i>
            <span>Usuarios</span>
        </a>
        <a href="#" class="nav-item" data-section="roles">
            <i class="fas fa-shield-alt"></i>
            <span>Gestión de Roles</span>
        </a>
        <a href="#" class="nav-item" data-section="reportes">
            <i class="fas fa-chart-bar"></i>
            <span>Reportes</span>
        </a>
        <hr class="sidebar-divider">
        <a href="#" class="nav-item" data-section="perfil">
            <i class="fas fa-user-circle"></i>
            <span>Mi Perfil</span>
        </a>
    </nav>
</div>

<!-- Main Content -->
<div class="main-content">
    <!-- Top Header -->
    <div class="top-header">
        <div class="header-left">
            <button class="sidebar-toggle" data-action="toggle-sidebar">
                <i class="fas fa-bars"></i>
            </button>
            <h1 class="page-title" id="pageTitle">Dashboard</h1>
        </div>
        <div class="header-right">
            <!-- User Menu Dropdown -->
            <div class="user-menu-container">
                <button class="user-menu-toggle user-menu-toggle-btn" data-action="toggle-user-menu">
                    <div class="user-avatar user-avatar-gradient">
                        <?php if (!empty($_SESSION['foto'])): ?>
                            <img src="<?php echo $_SESSION['foto']; ?>" alt="Avatar" class="user-avatar-img">
                        <?php else: ?>
                            <i class="fas fa-user"></i>
                        <?php endif; ?>
                    </div>
                    <div class="user-info-header">
                        <span class="user-name header-user-name" id="header-user-name">
                            <?php echo $_SESSION['nombre'] . ' ' . ($_SESSION['apellido_paterno'] ?? '') . ' ' . ($_SESSION['apellido_materno'] ?? ''); ?>
                        </span>
                        <?php 
                        // Mostrar área si existe
                        $area_mostrar = '';
                        if (!empty($areas_usuario)) {
                            if ($areas_usuario[0]['area'] !== '*') {
                                $area_mostrar = $areas_usuario[0]['area'];
                            }
                        }
                        ?>
                        <?php if (!empty($area_mostrar)): ?>
                        <span class="user-departamento" id="header-departamento">
                            <?php echo $area_mostrar; ?>
                        </span>
                        <?php endif; ?>
                        <span class="user-role header-user-role" id="header-user-role">
                            <?php 
                            $rol_labels_header = [
                                'superadmin' => 'Superadmin',
                                'asistente_admin' => 'Asistente Admin',
                                'decano_upg' => 'Decano/ UPG',
                                'director_escuela_upg' => 'Director de Escuela / UPG',
                                'director_area' => 'Director de Area',
                                'asistente' => 'Asistente',
                                'auditor' => 'Auditor'
                            ];
                            echo 'Rol: ' . ($rol_labels_header[$_SESSION['rol']] ?? ucfirst($_SESSION['rol']));
                            ?>
                        </span>
                    </div>
                    <i class="fas fa-chevron-down user-menu-chevron"></i>
                </button>
                
                <!-- Dropdown Menu -->
                <div class="user-menu-dropdown" id="userMenuDropdown">
                    <a href="#" class="dropdown-item" data-section="perfil">
                        <i class="fas fa-user-circle"></i>
                        <span>Mi Perfil</span>
                    </a>
                    <a href="#" class="dropdown-item" data-action="alert" data-message="Función aún no disponible" data-type="info">
                        <i class="fas fa-cog"></i>
                        <span>Configuración</span>
                    </a>
                    <div class="dropdown-divider"></div>
                    <a href="#" class="dropdown-item" data-action="alert" data-message="Función aún no disponible" data-type="info">
                        <i class="fas fa-chart-bar"></i>
                        <span>Reportes</span>
                    </a>
                    <div class="dropdown-divider"></div>
                    <a href="logout.php" class="dropdown-item logout-item">
                        <i class="fas fa-sign-out-alt"></i>
                        <span>Cerrar Sesión</span>
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- SECTION: DASHBOARD -->
    <div id="section-dashboard" class="content-section active">
        <!-- Stats Cards -->
        <div class="stats-grid">
            <div class="stat-card info">
                <div class="stat-content">
                    <div class="stat-info">
                        <p class="stat-label">Registrados Hoy</p>
                        <h2 class="stat-number"><?php echo $row_hoy['total']; ?></h2>
                        <span class="stat-change neutral">
                            <i class="fas fa-calendar-day"></i> Hoy
                        </span>
                    </div>
                    <div class="stat-icon">
                        <i class="fas fa-calendar-check"></i>
                    </div>
                </div>
            </div>

            <div class="stat-card secondary">
                <div class="stat-content">
                    <div class="stat-info">
                        <p class="stat-label">Pendientes</p>
                        <h2 class="stat-number"><?php echo $row_pendientes_real['total']; ?></h2>
                        <span class="stat-change neutral">
                            <i class="fas fa-clock"></i> Sin atender
                        </span>
                    </div>
                    <div class="stat-icon">
                        <i class="fas fa-pause-circle"></i>
                    </div>
                </div>
            </div>

            <div class="stat-card warning">
                <div class="stat-content">
                    <div class="stat-info">
                        <p class="stat-label">En Revisión</p>
                        <h2 class="stat-number"><?php echo $row_pendientes['total']; ?></h2>
                        <span class="stat-change warning">
                            <i class="fas fa-clock"></i> En Revisión
                        </span>
                    </div>
                    <div class="stat-icon">
                        <i class="fas fa-hourglass-half"></i>
                    </div>
                </div>
            </div>

            <div class="stat-card success">
                <div class="stat-content">
                    <div class="stat-info">
                        <p class="stat-label">Resueltos</p>
                        <h2 class="stat-number"><?php echo $row_resueltos['total']; ?></h2>
                        <span class="stat-change positive">
                            <i class="fas fa-check-circle"></i> Completados
                        </span>
                    </div>
                    <div class="stat-icon">
                        <i class="fas fa-check-circle"></i>
                    </div>
                </div>
            </div>

            <div class="stat-card danger">
                <div class="stat-content">
                    <div class="stat-info">
                        <p class="stat-label">No Procede</p>
                        <h2 class="stat-number"><?php echo $row_no_procede['total']; ?></h2>
                        <span class="stat-change negative">
                            <i class="fas fa-times-circle"></i> Rechazados
                        </span>
                    </div>
                    <div class="stat-icon">
                        <i class="fas fa-ban"></i>
                    </div>
                </div>
            </div>

            <div class="stat-card primary">
                <div class="stat-content">
                    <div class="stat-info">
                        <p class="stat-label">Total de Reclamos</p>
                        <h2 class="stat-number"><?php echo $row_total['total']; ?></h2>
                        <span class="stat-change positive">
                            <i class="fas fa-arrow-up"></i> +2.5%
                        </span>
                    </div>
                    <div class="stat-icon">
                        <i class="fas fa-clipboard-list"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- Charts Section -->
        <div class="stats-row">
            <!-- Gráfica de Tipos de Reclamos -->
            <div class="col-lg-8 stats-col stats-col-65">
                <div class="dashboard-card">
                    <div class="card-header">
                        <h3 class="card-title">
                            <i class="fas fa-chart-bar"></i> Descripción General de Reclamos
                        </h3>
                    </div>
                    <div class="card-body chart-card-body">
                        <div id="legend-container-tipos" class="chart-legend-container"></div>
                        <div class="chart-canvas-wrapper">
                            <canvas id="chartTiposReclamos"></canvas>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Gráfica de Estados -->
            <div class="col-lg-4 stats-col stats-col-35">
                <div class="dashboard-card full-height-card">
                    <div class="card-header">
                        <h3 class="card-title">
                            <i class="fas fa-chart-pie"></i> Estados de Reclamos
                        </h3>
                    </div>
                    <div class="card-body chart-estados-body">
                        <div class="chart-estados-grid">
                            <div class="chart-estados-canvas-wrap">
                                <canvas id="chartEstados" width="200" height="200"></canvas>
                            </div>
                            <div class="chart-estados-list">
                                <div class="chart-estados-row">
                                    <div class="chart-estados-label">
                                        <span class="chart-dot chart-dot-success"></span>
                                        <span class="chart-estados-text">Resueltos</span>
                                    </div>
                                    <span class="chart-estados-count chart-count-success"><?php echo $estados_result['resueltos'] ?? 0; ?></span>
                                </div>
                                <div class="chart-estados-row">
                                    <div class="chart-estados-label">
                                        <span class="chart-dot chart-dot-warning"></span>
                                        <span class="chart-estados-text">En Revisión</span>
                                    </div>
                                    <span class="chart-estados-count chart-count-warning"><?php echo $estados_result['en_revision'] ?? 0; ?></span>
                                </div>
                                <div class="chart-estados-row">
                                    <div class="chart-estados-label">
                                        <span class="chart-dot chart-dot-danger"></span>
                                        <span class="chart-estados-text">No Procede</span>
                                    </div>
                                    <span class="chart-estados-count chart-count-danger"><?php echo $estados_result['no_procede'] ?? 0; ?></span>
                                </div>
                                <div class="chart-estados-row">
                                    <div class="chart-estados-label">
                                        <span class="chart-dot chart-dot-muted"></span>
                                        <span class="chart-estados-text">Pendientes</span>
                                    </div>
                                    <span class="chart-estados-count chart-count-muted"><?php echo $estados_result['pendientes'] ?? 0; ?></span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- SECTION: RECLAMACIONES -->
    <div id="section-reclamaciones" class="content-section">
        <div class="dashboard-card">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-clipboard-list"></i> Gestionar Reclamaciones
                </h3>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="modern-table">
                        <thead>
                            <tr>
                                <th>Folio</th>
                                <th>Fecha</th>
                                <th>Nombre</th>
                                <th>Email</th>
                                <th>Tipo</th>
                                <th>Área</th>
                                <th>Estado</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $result_reclamaciones->data_seek(0);
                            while($row = $result_reclamaciones->fetch_assoc()): 
                            ?>
                            <tr>
                                <td><strong class="folio-number"><?php echo $row['folio']; ?></strong></td>
                                <td><?php echo date('d/m/Y h:i A', strtotime($row['fecha_registro'])); ?></td>
                                <td><?php echo ($row['nombres'] ?? '') . ' ' . ($row['apellido_paterno'] ?? ''); ?></td>
                                <td><?php echo $row['email']; ?></td>
                                <td>
                                    <span class="badge badge-type"><?php echo $row['tipo_registro']; ?></span>
                                </td>
                                <td>
                                    <?php if (!empty($row['area'])): ?>
                                        <span class="badge badge-area"><?php echo htmlspecialchars($row['area']); ?></span>
                                    <?php else: ?>
                                        <span class="text-muted">Sin asignar</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <span class="badge badge-status badge-<?php 
                                        if ($row['estado'] === 'Resuelto') echo 'success';
                                        elseif ($row['estado'] === 'En revisión') echo 'warning';
                                        elseif ($row['estado'] === 'No procede') echo 'danger';
                                        else echo 'info';
                                    ?>">
                                        <?php echo $row['estado']; ?>
                                    </span>
                                </td>
                                <td>
                                    <div class="action-buttons">
                                        <button class="btn-action btn-view" title="Ver Detalle" data-action="ver-detalle" data-id="<?php echo (int)$row['id']; ?>">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                        <button class="btn-action btn-edit" title="Responder Reclamo" data-action="responder-reclamo" data-id="<?php echo (int)$row['id']; ?>">
                                            <i class="fas fa-reply"></i>
                                        </button>
                                        <button class="btn-action btn-forward" title="Derivar Reclamo" data-action="derivar-reclamo" data-id="<?php echo (int)$row['id']; ?>">
                                            <i class="fas fa-share"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- SECTION: MI PERFIL -->
    <div id="section-perfil" class="content-section">
        <div class="dashboard-card">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-user-circle"></i> Mi Perfil
                </h3>
            </div>
            <div class="card-body">
                <div class="perfil-container">
                    <!-- Foto de Perfil -->
                    <div class="perfil-foto-section">
                        <div class="perfil-foto">
                            <?php if (!empty($foto_perfil)): ?>
                                <img src="<?php echo htmlspecialchars($foto_perfil, ENT_QUOTES, 'UTF-8'); ?>" alt="Foto de perfil" class="perfil-img">
                            <?php else: ?>
                                <div class="perfil-avatar-placeholder">
                                    <i class="fas fa-user"></i>
                                </div>
                            <?php endif; ?>
                        </div>
                        <h3 class="perfil-nombre">
                            <?php echo $_SESSION['nombre'] . ' ' . ($_SESSION['apellido_paterno'] ?? '') . ' ' . ($_SESSION['apellido_materno'] ?? ''); ?>
                        </h3>
                        <?php
                        $areas_texto = '';
                        if (!empty($areas_usuario)) {
                            $lista_areas = [];
                            foreach ($areas_usuario as $area_item) {
                                $texto_area = '<div class="perfil-area-item">';
                                $texto_area .= '<span class="perfil-area-depto">' . htmlspecialchars($area_item['departamento'], ENT_QUOTES, 'UTF-8') . '</span>';
                                if ($area_item['area'] !== '*') {
                                    $texto_area .= '<span class="perfil-area-sub">' . htmlspecialchars($area_item['area'], ENT_QUOTES, 'UTF-8') . '</span>';
                                }
                                $texto_area .= '</div>';
                                $lista_areas[] = $texto_area;
                            }
                            $areas_texto = implode('', $lista_areas);
                        }
                        ?>
                        <?php if ($areas_texto !== ''): ?>
                            <p class="perfil-area">
                                <?php echo $areas_texto; ?>
                            </p>
                        <?php endif; ?>
                    </div>

                    <!-- Información del Perfil -->
                    <div class="perfil-info-section">
                        <h4 class="perfil-section-title">
                            <i class="fas fa-info-circle"></i> Información Personal
                        </h4>
                        
                        <div class="perfil-info-grid">
                            <div class="perfil-info-item">
                                <label class="perfil-label">
                                    <i class="fas fa-user"></i> Nombres Completos
                                </label>
                                <p class="perfil-value">
                                    <?php echo $_SESSION['nombre'] . ' ' . ($_SESSION['apellido_paterno'] ?? '') . ' ' . ($_SESSION['apellido_materno'] ?? ''); ?>
                                </p>
                            </div>

                            <div class="perfil-info-item">
                                <label class="perfil-label">
                                    <i class="fas fa-id-card"></i> DNI
                                </label>
                                <p class="perfil-value">
                                    <?php echo $_SESSION['dni'] ?? 'No registrado'; ?>
                                </p>
                            </div>

                            <div class="perfil-info-item">
                                <label class="perfil-label">
                                    <i class="fas fa-envelope"></i> Correo Electrónico
                                </label>
                                <p class="perfil-value">
                                    <?php echo $_SESSION['email'] ?? 'No registrado'; ?>
                                </p>
                            </div>

                            <div class="perfil-info-item">
                                <label class="perfil-label">
                                    <i class="fas fa-phone"></i> Teléfono
                                </label>
                                <p class="perfil-value">
                                    <?php echo $_SESSION['telefono'] ?? 'No registrado'; ?>
                                </p>
                            </div>

                            <div class="perfil-info-item">
                                <label class="perfil-label">
                                    <i class="fas fa-user-tag"></i> Usuario
                                </label>
                                <p class="perfil-value">
                                    <?php echo $_SESSION['usuario'] ?? 'No registrado'; ?>
                                </p>
                            </div>

                            <div class="perfil-info-item">
                                <label class="perfil-label">
                                    <i class="fas fa-shield-alt"></i> Rol
                                </label>
                                <p class="perfil-value">
                                    <?php echo $rol_labels_perfil[$_SESSION['rol']] ?? ucfirst($_SESSION['rol']); ?>
                                </p>
                            </div>

                        </div>

                        <div class="perfil-actions">
                            <button type="button" class="btn btn-primary" data-action="open-perfil-modal">
                                <i class="fas fa-edit"></i> Editar Perfil
                            </button>
                            <button type="button" class="btn btn-warning" data-action="open-password-modal" data-user-id="<?php echo (int)$_SESSION['id']; ?>" data-user-name="<?php echo htmlspecialchars($_SESSION['nombre'] . ' ' . ($_SESSION['apellido_paterno'] ?? '') . ' ' . ($_SESSION['apellido_materno'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>">
                                <i class="fas fa-key"></i> Cambiar Contraseña
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- SECTION: USUARIOS -->
    <div id="section-usuarios" class="content-section">
        <div class="dashboard-card">
            <div class="card-header card-header-with-action">
                <h3 class="card-title">
                    <i class="fas fa-users"></i> Gestionar Usuarios
                </h3>
                <button type="button" class="btn btn-primary" data-action="open-crear-usuario">
                    <i class="fas fa-plus"></i> Agregar Usuario
                </button>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="modern-table">
                        <thead>
                            <tr>
                                <th>Usuario</th>
                                <th>Nombre</th>
                                <th>DNI</th>
                                <th>Email</th>
                                <th>Rol</th>
                                <th>Estado</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($usuarios_array)): ?>
                                <?php foreach ($usuarios_array as $usuario): ?>
                                <tr>
                                    <td><?php echo $usuario['usuario']; ?></td>
                                    <td><?php echo $usuario['nombre'] . ' ' . ($usuario['apellido_paterno'] ?? ''); ?></td>
                                    <td><?php echo $usuario['dni']; ?></td>
                                    <td><?php echo $usuario['email']; ?></td>
                                    <td>
                                        <?php
                                        $rol_labels_table = [
                                            'superadmin' => 'Superadmin',
                                            'asistente_admin' => 'Asistente Admin',
                                            'decano_upg' => 'Decano/ UPG',
                                            'director_escuela_upg' => 'Director de Escuela / UPG',
                                            'director_area' => 'Director de Area',
                                            'asistente' => 'Asistente',
                                            'auditor' => 'Auditor'
                                        ];
                                        echo $rol_labels_table[$usuario['rol']] ?? $usuario['rol'];
                                        ?>
                                    </td>
                                    <td>
                                        <?php
                                        $estado = $usuario['estado'] ?? 'activo';
                                        $estado_norm = strtolower(trim((string)$estado));
                                        $es_activo = ($estado_norm === '1' || $estado_norm === 'activo');
                                        ?>
                                        <span class="badge badge-status badge-<?php echo $es_activo ? 'success' : 'danger'; ?>">
                                            <?php echo $es_activo ? 'Activo' : 'Inactivo'; ?>
                                        </span>
                                    </td>
                                    <td>
                                        <div class="action-buttons">
                                            <button class="btn-action btn-edit" title="Editar Usuario" data-action="editar-usuario" data-id="<?php echo (int)$usuario['id']; ?>">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <button class="btn-action btn-password" title="Cambiar Contraseña" data-action="cambiar-password-usuario" data-id="<?php echo (int)$usuario['id']; ?>" data-nombre="<?php echo htmlspecialchars($usuario['nombre'] . ' ' . ($usuario['apellido_paterno'] ?? '')); ?>">
                                                <i class="fas fa-key"></i>
                                            </button>
                                            <button class="btn-action <?php echo $es_activo ? 'btn-warning' : 'btn-success'; ?>" title="<?php echo $es_activo ? 'Desactivar Usuario' : 'Activar Usuario'; ?>" data-action="toggle-usuario" data-id="<?php echo (int)$usuario['id']; ?>">
                                                <i class="fas fa-<?php echo $es_activo ? 'ban' : 'check'; ?>"></i>
                                            </button>
                                            <button class="btn-action btn-delete" title="Eliminar Usuario" data-action="eliminar-usuario" data-id="<?php echo (int)$usuario['id']; ?>">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="7" class="table-empty">No hay usuarios registrados.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- SECTION: GESTIÓN DE ROLES -->
    <div id="section-roles" class="content-section">
        <div class="dashboard-card">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-shield-alt"></i> Gestión de Roles y Áreas
                </h3>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="modern-table">
                        <thead>
                            <tr>
                                <th>Usuario</th>
                                <th>Nombre</th>
                                <th>Email</th>
                                <th>Rol Actual</th>
                                <th>Áreas Asignadas</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($usuarios_con_roles)): ?>
                                <?php foreach ($usuarios_con_roles as $usuario): ?>
                                <tr>
                                    <td><?php echo $usuario['usuario']; ?></td>
                                    <td><?php echo $usuario['nombre'] . ' ' . ($usuario['apellido_paterno'] ?? ''); ?></td>
                                    <td><?php echo $usuario['email']; ?></td>
                                    <td>
                                        <span class="badge badge-primary">
                                            <?php
                                            $rol_labels_roles = [
                                                'superadmin' => 'Superadmin',
                                                'asistente_admin' => 'Asistente Admin',
                                                'decano_upg' => 'Decano/ UPG',
                                                'director_escuela_upg' => 'Director de Escuela / UPG',
                                                'director_area' => 'Director de Area',
                                                'asistente' => 'Asistente',
                                                'auditor' => 'Auditor'
                                            ];
                                            echo $rol_labels_roles[$usuario['rol']] ?? $usuario['rol'];
                                            ?>
                                        </span>
                                    </td>
                                    <td>
                                        <span class="badge badge-info">
                                            <?php echo $usuario['areas_asignadas']; ?> área(s)
                                        </span>
                                    </td>
                                    <td>
                                        <button class="btn btn-primary btn-sm" data-action="alert" data-message="Función en desarrollo" data-type="info">
                                            <i class="fas fa-edit"></i> Cambiar Rol
                                        </button>
                                        <button class="btn btn-success btn-sm" data-action="alert" data-message="Función en desarrollo" data-type="info">
                                            <i class="fas fa-building"></i> Asignar Áreas
                                        </button>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="6" class="table-empty">No hay usuarios registrados.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- SECTION: REPORTES -->
    <div id="section-reportes" class="content-section">
        <div class="dashboard-card">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-chart-bar"></i> Reportes y Estadísticas
                </h3>
            </div>
            <div class="card-body">
                <div class="reportes-summary">
                    <div class="reporte-stat-card">
                        <i class="fas fa-clipboard-list reporte-icon icon-primary"></i>
                        <div class="reporte-info">
                            <h4>Total del Mes</h4>
                            <p class="reporte-number"><?php echo $stats['total'] ?? 0; ?></p>
                        </div>
                    </div>
                    <div class="reporte-stat-card">
                        <i class="fas fa-exclamation-circle reporte-icon icon-warning"></i>
                        <div class="reporte-info">
                            <h4>Reclamos</h4>
                            <p class="reporte-number"><?php echo $stats['reclamos'] ?? 0; ?></p>
                        </div>
                    </div>
                    <div class="reporte-stat-card">
                        <i class="fas fa-comment-dots reporte-icon icon-info"></i>
                        <div class="reporte-info">
                            <h4>Quejas</h4>
                            <p class="reporte-number"><?php echo $stats['quejas'] ?? 0; ?></p>
                        </div>
                    </div>
                    <div class="reporte-stat-card">
                        <i class="fas fa-lightbulb reporte-icon icon-success"></i>
                        <div class="reporte-info">
                            <h4>Sugerencias</h4>
                            <p class="reporte-number"><?php echo $stats['sugerencias'] ?? 0; ?></p>
                        </div>
                    </div>
                </div>

                <div class="reportes-actions">
                    <button class="btn btn-primary" data-action="alert" data-message="Función de exportación en desarrollo" data-type="info">
                        <i class="fas fa-file-excel"></i> Exportar a Excel
                    </button>
                    <button class="btn btn-danger" data-action="alert" data-message="Función de exportación en desarrollo" data-type="info">
                        <i class="fas fa-file-pdf"></i> Exportar a PDF
                    </button>
                    <button class="btn btn-info" data-action="alert" data-message="Función de impresión en desarrollo" data-type="info">
                        <i class="fas fa-print"></i> Imprimir Reporte
                    </button>
                </div>

                <div class="reportes-filtros">
                    <h4>
                        <i class="fas fa-filter"></i> Filtros de Reporte
                    </h4>
                    <div class="filtros-grid">
                        <div class="form-group">
                            <label>Fecha Inicio</label>
                            <input type="date" class="form-control" value="<?php echo $fecha_inicio; ?>">
                        </div>
                        <div class="form-group">
                            <label>Fecha Fin</label>
                            <input type="date" class="form-control" value="<?php echo $fecha_fin; ?>">
                        </div>
                        <div class="form-group">
                            <label>Estado</label>
                            <select class="form-control">
                                <option value="">Todos</option>
                                <option value="Pendiente">Pendiente</option>
                                <option value="En revisión">En Revisión</option>
                                <option value="Resuelto">Resuelto</option>
                                <option value="No procede">No Procede</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Tipo</label>
                            <select class="form-control">
                                <option value="">Todos</option>
                                <option value="Reclamo">Reclamo</option>
                                <option value="Queja">Queja</option>
                                <option value="Sugerencia">Sugerencia</option>
                            </select>
                        </div>
                    </div>
                    <button class="btn btn-primary" data-action="alert" data-message="Función de búsqueda en desarrollo" data-type="info">
                        <i class="fas fa-search"></i> Aplicar Filtros
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Detalle Reclamo -->
    <div id="modalDetalleReclamo" class="modal-overlay">
        <div class="modal-container">
            <!-- Header Modal -->
            <div class="modal-header-gradient">
                <h2>
                    <i class="fas fa-file-alt"></i>
                    Detalle del Reclamo
                </h2>
                <button type="button" class="modal-close-btn" data-action="close-detalle">&times;</button>
            </div>
            <!-- Content Modal -->
            <div id="contenidoModalDetalle" class="modal-content">
                <p>Cargando...</p>
            </div>
        </div>
    </div>

    <!-- Modal Crear Usuario -->
    <div id="modalCrearUsuario" class="modal-overlay">
        <div class="modal-container">
            <!-- Header Modal -->
            <div class="modal-header-gradient">
                <h2>
                    <i class="fas fa-user-plus"></i>
                    Nuevo Usuario
                </h2>
                <button type="button" class="modal-close-btn" data-action="close-crear-usuario">&times;</button>
            </div>
            <!-- Content Modal -->
            <div class="modal-content">
                <form id="formCrearUsuario" class="dashboard-form">
                    <div class="row-form user-form-grid">
                        <div class="form-group">
                            <label for="nuevo_nombre">Nombre</label>
                            <input type="text" id="nuevo_nombre" name="nombre" placeholder="Ej: Juan" required>
                        </div>
                        <div class="form-group">
                            <label for="nuevo_apellido_paterno">Apellido Paterno</label>
                            <input type="text" id="nuevo_apellido_paterno" name="apellido_paterno" placeholder="Ej: Pérez" required>
                        </div>
                        <div class="form-group">
                            <label for="nuevo_apellido_materno">Apellido Materno</label>
                            <input type="text" id="nuevo_apellido_materno" name="apellido_materno" placeholder="Ej: García">
                        </div>
                        <div class="form-group">
                            <label for="nuevo_dni">DNI</label>
                            <input type="text" id="nuevo_dni" name="dni" maxlength="8" placeholder="Ej: 12345678" required>
                        </div>
                        <div class="form-group">
                            <label for="nuevo_telefono">Teléfono</label>
                            <input type="text" id="nuevo_telefono" name="telefono" maxlength="9" placeholder="Ej: 987654321" required>
                        </div>
                        <div class="form-group">
                            <label for="nuevo_email">Email</label>
                            <input type="email" id="nuevo_email" name="email" placeholder="Ej: usuario@upeu.edu.pe" required>
                        </div>
                        <div class="form-group">
                            <label for="nuevo_rol">Rol</label>
                            <select id="nuevo_rol" name="rol" required>
                                <option value="">Selecciona un rol</option>
                                <option value="superadmin">Superadmin</option>
                                <option value="asistente_admin">Asistente Admin</option>
                                <option value="decano_upg">Decano/ UPG</option>
                                <option value="director_escuela_upg">Director de Escuela / UPG</option>
                                <option value="director_area">Director de Area</option>
                                <option value="asistente">Asistente</option>
                                <option value="auditor">Auditor</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="nuevo_usuario">Usuario</label>
                            <input type="text" id="nuevo_usuario" name="usuario" placeholder="Ej: juan.perez" required>
                        </div>
                        <div class="form-group">
                            <label for="nuevo_password">Contraseña</label>
                            <div class="password-wrapper">
                                <input type="password" id="nuevo_password" name="password" minlength="6" placeholder="Mínimo 6 caracteres" required>
                                <button type="button" class="password-toggle" data-action="toggle-password" data-target="nuevo_password" data-icon="nuevo_toggle_icon" aria-label="Mostrar contraseña">
                                    <i class="fas fa-eye" id="nuevo_toggle_icon"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                    <div class="user-modal-actions">
                        <button type="button" class="btn btn-secondary" data-action="close-crear-usuario">
                            <i class="fa-regular fa-circle-xmark"></i> Cancelar
                        </button>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> Guardar Usuario
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal Responder Reclamo -->
    <div id="modalResponderReclamo" class="modal-responder-overlay">
        <div class="modal-responder-container">
            <!-- Header -->
            <div class="modal-responder-header">
                <h2 class="modal-responder-title">
                    <i class="fas fa-reply modal-responder-icon"></i>
                    Responder Reclamo
                </h2>
                <button type="button" class="modal-responder-close" data-action="close-responder">&times;</button>
            </div>
            <!-- Content -->
            <div class="modal-responder-content">
                <div class="responder-group responder-group-folio">
                    <label class="responder-label">Folio</label>
                    <p id="responderFolio" class="responder-folio">-</p>
                </div>
                <div class="responder-group">
                    <label class="responder-label">Escribe tu respuesta</label>
                    <textarea id="textareaRespuesta" class="responder-textarea" placeholder="Ingresa la respuesta a la reclamación..."></textarea>
                </div>
                <div class="responder-actions">
                    <button type="button" class="btn btn-secondary" data-action="close-responder">
                        <i class="fa-regular fa-circle-xmark"></i> Cancelar
                    </button>
                    <button type="button" class="btn btn-success" data-action="save-respuesta">
                        <i class="fas fa-save"></i> Guardar Respuesta
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Derivar Reclamo -->
    <div id="modalDerivarReclamo" class="modal-derivar-overlay">
        <div class="modal-derivar-container">
            <!-- Header -->
            <div class="modal-derivar-header">
                <h2 class="modal-derivar-title">
                    <i class="fas fa-share modal-derivar-title-icon"></i>
                    Derivar Reclamo
                </h2>
                <button type="button" class="modal-derivar-close" data-action="close-derivar">&times;</button>
            </div>
            <!-- Content -->
            <div id="contenidoDerivar" class="modal-derivar-content">
                <p>Cargando...</p>
            </div>
        </div>
    </div>

    <!-- Modal Editar Usuario -->
    <div id="modalEditarUsuario" class="modal-overlay">
        <div class="modal-container">
            <!-- Header Modal -->
            <div class="modal-header-gradient">
                <h2>
                    <i class="fas fa-user-edit"></i>
                    Editar Usuario
                </h2>
                <button type="button" class="modal-close-btn" data-action="close-editar-usuario">&times;</button>
            </div>
            <!-- Content Modal -->
            <div class="modal-content">
                <form id="formEditarUsuario" class="dashboard-form">
                    <input type="hidden" id="editar_usuario_id" name="usuario_id" value="">
                    <div class="row-form user-form-grid">
                        <div class="form-group">
                            <label for="editar_nombre">Nombre</label>
                            <input type="text" id="editar_nombre" name="nombre" placeholder="Ej: Juan" required>
                        </div>
                        <div class="form-group">
                            <label for="editar_apellido_paterno">Apellido Paterno</label>
                            <input type="text" id="editar_apellido_paterno" name="apellido_paterno" placeholder="Ej: Pérez" required>
                        </div>
                        <div class="form-group">
                            <label for="editar_apellido_materno">Apellido Materno</label>
                            <input type="text" id="editar_apellido_materno" name="apellido_materno" placeholder="Ej: García">
                        </div>
                        <div class="form-group">
                            <label for="editar_dni">DNI</label>
                            <input type="text" id="editar_dni" name="dni" maxlength="8" placeholder="Ej: 12345678" required>
                        </div>
                        <div class="form-group">
                            <label for="editar_telefono">Teléfono</label>
                            <input type="text" id="editar_telefono" name="telefono" maxlength="9" placeholder="Ej: 987654321" required>
                        </div>
                        <div class="form-group">
                            <label for="editar_email">Email</label>
                            <input type="email" id="editar_email" name="email" placeholder="Ej: usuario@upeu.edu.pe" required>
                        </div>
                        <div class="form-group">
                            <label for="editar_rol">Rol</label>
                            <select id="editar_rol" name="rol" required>
                                <option value="">Selecciona un rol</option>
                                <option value="superadmin">Superadmin</option>
                                <option value="asistente_admin">Asistente Admin</option>
                                <option value="decano_upg">Decano/ UPG</option>
                                <option value="director_escuela_upg">Director de Escuela / UPG</option>
                                <option value="director_area">Director de Area</option>
                                <option value="asistente">Asistente</option>
                                <option value="auditor">Auditor</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="editar_usuario">Usuario</label>
                            <input type="text" id="editar_usuario" name="usuario" placeholder="Ej: juan.perez" required disabled>
                        </div>
                    </div>
                    <div class="user-modal-actions">
                        <button type="button" class="btn btn-secondary" data-action="close-editar-usuario">
                            <i class="fa-regular fa-circle-xmark"></i> Cancelar
                        </button>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> Actualizar Usuario
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal Editar Perfil -->
    <div id="modalEditarPerfil" class="modal-overlay">
        <div class="modal-container">
            <div class="modal-header-gradient">
                <h2>
                    <i class="fas fa-user-edit"></i>
                    Editar Perfil
                </h2>
                <button type="button" class="modal-close-btn" data-action="close-perfil-modal">&times;</button>
            </div>
            <div class="modal-content">
                <form id="formEditarPerfil" class="dashboard-form" enctype="multipart/form-data">
                    <div class="perfil-foto-preview">
                        <div class="perfil-foto-preview-img">
                            <?php if (!empty($foto_perfil)): ?>
                                <img src="<?php echo htmlspecialchars($foto_perfil, ENT_QUOTES, 'UTF-8'); ?>" alt="Foto de perfil" id="perfil_foto_preview">
                            <?php else: ?>
                                <div class="perfil-foto-placeholder" id="perfil_foto_placeholder">
                                    <i class="fas fa-user"></i>
                                </div>
                                <img src="" alt="Foto de perfil" id="perfil_foto_preview" style="display: none;">
                            <?php endif; ?>
                            <button type="button" class="perfil-foto-preview-edit" data-action="open-perfil-foto" aria-label="Cambiar foto de perfil">
                                <i class="fas fa-camera"></i>
                            </button>
                        </div>
                        <div class="perfil-info-heading">
                            Informacion personal
                        </div>
                        <input type="file" id="perfil_foto" name="foto" class="perfil-foto-input" accept="image/jpeg,image/png,image/gif">
                    </div>
                    <div class="perfil-form-grid">
                        <div class="form-group">
                            <label for="perfil_nombre">Nombre</label>
                            <input type="text" id="perfil_nombre" name="nombre" value="<?php echo htmlspecialchars($_SESSION['nombre'] ?? '', ENT_QUOTES, 'UTF-8'); ?>" required>
                        </div>
                        <div class="form-group">
                            <label for="perfil_apellido_paterno">Apellido Paterno</label>
                            <input type="text" id="perfil_apellido_paterno" name="apellido_paterno" value="<?php echo htmlspecialchars($_SESSION['apellido_paterno'] ?? '', ENT_QUOTES, 'UTF-8'); ?>" required>
                        </div>
                        <div class="form-group">
                            <label for="perfil_apellido_materno">Apellido Materno</label>
                            <input type="text" id="perfil_apellido_materno" name="apellido_materno" value="<?php echo htmlspecialchars($_SESSION['apellido_materno'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                        </div>
                        <div class="form-group">
                            <label for="perfil_dni">DNI</label>
                            <input type="text" id="perfil_dni" name="dni" maxlength="8" value="<?php echo htmlspecialchars($_SESSION['dni'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                        </div>
                        <div class="form-group">
                            <label for="perfil_telefono">Teléfono</label>
                            <input type="text" id="perfil_telefono" name="telefono" maxlength="9" value="<?php echo htmlspecialchars($_SESSION['telefono'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                        </div>
                        <div class="form-group">
                            <label for="perfil_email">Correo Electrónico</label>
                            <input type="email" id="perfil_email" name="email" value="<?php echo htmlspecialchars($_SESSION['email'] ?? '', ENT_QUOTES, 'UTF-8'); ?>" required>
                        </div>
                        <div class="form-group">
                            <label for="perfil_usuario">Usuario</label>
                            <input type="text" id="perfil_usuario" value="<?php echo htmlspecialchars($_SESSION['usuario'] ?? '', ENT_QUOTES, 'UTF-8'); ?>" disabled>
                        </div>
                        <div class="form-group">
                            <label for="perfil_rol">Rol</label>
                            <input type="text" id="perfil_rol" value="<?php echo htmlspecialchars($rol_labels_perfil[$_SESSION['rol']] ?? ucfirst($_SESSION['rol']), ENT_QUOTES, 'UTF-8'); ?>" disabled>
                        </div>
                    </div>
                    <div class="user-modal-actions">
                        <button type="button" class="btn btn-secondary" data-action="close-perfil-modal">
                            <i class="fa-regular fa-circle-xmark"></i> Cancelar
                        </button>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> Guardar Cambios
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal Cambiar Contraseña -->
    <div id="modalCambiarPassword" class="modal-overlay">
        <div class="modal-container modal-password">
            <!-- Header -->
            <div class="modal-header-gradient">
                <h2 class="modal-header-title">
                    <i class="fas fa-key"></i>
                    Cambiar Contraseña
                </h2>
                <button type="button" class="modal-close" data-action="close-password-modal">&times;</button>
            </div>
            <!-- Content -->
            <div class="modal-content">
                <div class="password-form">
                    <input type="hidden" id="passwordUserId" value="">
                    
                    <div class="form-group">
                        <label for="nuevaPassword">Nueva Contraseña</label>
                        <div class="password-input-wrapper">
                            <input type="password" 
                                   id="nuevaPassword" 
                                   class="form-control" 
                                   placeholder="Mínimo 6 caracteres"
                                autocomplete="new-password"
                                required
                                minlength="6">
                            <button type="button" class="toggle-password" data-target="nuevaPassword">
                                <i class="fas fa-eye"></i>
                            </button>
                        </div>
                        <div class="password-strength">
                            <div class="strength-bar">
                                <div class="strength-bar-fill"></div>
                            </div>
                            <span class="strength-text"></span>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="confirmarPassword">Confirmar Contraseña</label>
                        <div class="password-input-wrapper">
                            <input type="password" 
                                   id="confirmarPassword" 
                                   class="form-control" 
                                   placeholder="Repite la contraseña"
                                autocomplete="new-password"
                                required
                                minlength="6">
                            <button type="button" class="toggle-password" data-target="confirmarPassword">
                                <i class="fas fa-eye"></i>
                            </button>
                        </div>
                        <div class="password-match-message"></div>
                    </div>

                    <div class="usuario-info">
                        <i class="fas fa-user-circle"></i>
                        <span>Usuario: <strong id="passwordUserName"></strong></span>
                    </div>
                </div>
            </div>
            <!-- Footer -->
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-action="close-password-modal">
                    <i class="fa-regular fa-circle-xmark"></i> Cancelar
                </button>
                <button type="button" class="btn btn-primary" data-action="save-password">
                    <i class="fas fa-save"></i> Guardar
                </button>
            </div>
        </div>
    </div>

    <!-- Modal Alerta Moderno -->
    <div id="modalAlerta" class="modal-overlay modal-alerta-overlay">
        <div class="modal-container modal-alerta">
            <div class="modal-alerta-header modal-alerta-success">
                <i class="fas fa-check-circle modal-alerta-icon"></i>
            </div>
            <div class="modal-alerta-content">
                <h3 id="modalAlertaTitulo" class="modal-alerta-titulo">Éxito</h3>
                <p id="modalAlertaMensaje" class="modal-alerta-mensaje">Operación realizada correctamente</p>
            </div>
            <div class="modal-alerta-footer">
                <button type="button" class="btn btn-primary" id="modalAlertaBtn" data-action="cerrar-alerta">
                    <i class="fas fa-check"></i> Aceptar
                </button>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <footer class="dashboard-footer">
        <p>Copyright © UPeU 2026. Todos los derechos son reservados</p>
    </footer>
</div>

<script type="application/json" id="datos-por-mes-data"><?php echo json_encode($datos_por_mes); ?></script>
<script type="application/json" id="estados-data"><?php echo json_encode($estados_result); ?></script>
<script src="../js/admin-index.js"></script>

</body>
</html>
