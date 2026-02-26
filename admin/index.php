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

// Obtener información del usuario actual y sus áreas (si es asistente o supervisor)
$sql_usuario_actual = "SELECT id, rol FROM usuarios WHERE usuario = ?";
$stmt_usuario = $conn->prepare($sql_usuario_actual);
$stmt_usuario->bind_param("s", $_SESSION['usuario']);
$stmt_usuario->execute();
$usuario_actual = $stmt_usuario->get_result()->fetch_assoc();

// Filtro para asistentes y supervisores
$where_areas = "";
$areas_usuario = [];

// Supervisores: ven todos los reclamos de su(s) departamento(s)
// Asistentes: solo ven reclamos de sus áreas específicas (departamento + área)
// Dirección: solo ve reclamos derivados a su(s) área(s)
// Decanatura: ve todos los reclamos de su departamento
if ($usuario_actual && ($usuario_actual['rol'] === 'asistente' || $usuario_actual['rol'] === 'supervisor' || $usuario_actual['rol'] === 'direccion' || $usuario_actual['rol'] === 'decanatura')) {
    $sql_areas = "SELECT departamento, area FROM asistentes_areas WHERE usuario_id = ?";
    $stmt_areas = $conn->prepare($sql_areas);
    $stmt_areas->bind_param("i", $usuario_actual['id']);
    $stmt_areas->execute();
    $result_areas = $stmt_areas->get_result();
    
    while ($row_area = $result_areas->fetch_assoc()) {
        $areas_usuario[] = $row_area;
    }
    
    // Construir condición WHERE según el rol
    if (!empty($areas_usuario)) {
        $condiciones = [];
        
        if ($usuario_actual['rol'] === 'supervisor' || $usuario_actual['rol'] === 'decanatura') {
            // Supervisores y Decanatura: filtrar solo por departamento (ver todo el departamento)
            $departamentos_unicos = array_unique(array_column($areas_usuario, 'departamento'));
            foreach ($departamentos_unicos as $dept) {
                $dept_escaped = $conn->real_escape_string($dept);
                $condiciones[] = "departamento = '$dept_escaped'";
            }
        } else {
            // Asistentes: filtrar por departamento Y área específica
            foreach ($areas_usuario as $area) {
                $dept = $conn->real_escape_string($area['departamento']);
                $ar = $conn->real_escape_string($area['area']);
                $condiciones[] = "(departamento = '$dept' AND area = '$ar')";
            }
        }
        
        $where_areas = " AND (" . implode(" OR ", $condiciones) . ")";
    } else {
        // Si no tiene áreas asignadas, no ve ningún reclamo
        $where_areas = " AND 1=0";
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

// Datos para Usuarios (solo si es admin o superadmin)
$usuarios_array = [];
if ($_SESSION['rol'] === 'admin' || $_SESSION['rol'] === 'superadmin') {
    $sql_usuarios = "SELECT * FROM usuarios ORDER BY fecha_creacion DESC";
    $result_usuarios = $conn->query($sql_usuarios);
    while($row = $result_usuarios->fetch_assoc()) {
        $usuarios_array[] = $row;
    }
}

// Datos para Gestión de Roles (solo superadmin)
$usuarios_con_roles = [];
if ($_SESSION['rol'] === 'superadmin') {
    $sql_roles = "SELECT u.*, COUNT(aa.id) as areas_asignadas 
                  FROM usuarios u 
                  LEFT JOIN asistentes_areas aa ON u.id = aa.usuario_id 
                  GROUP BY u.id 
                  ORDER BY u.rol, u.nombre";
    $result_roles = $conn->query($sql_roles);
    while($row = $result_roles->fetch_assoc()) {
        $usuarios_con_roles[] = $row;
    }
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
        <a href="#" class="nav-item active" onclick="showSection('dashboard'); return false;">
            <i class="fas fa-home"></i>
            <span>Dashboard</span>
        </a>
        <a href="#" class="nav-item" onclick="showSection('reclamaciones'); return false;">
            <i class="fas fa-clipboard-list"></i>
            <span>Reclamaciones</span>
        </a>
        <?php if ($_SESSION['rol'] === 'admin' || $_SESSION['rol'] === 'superadmin'): ?>
        <a href="#" class="nav-item" onclick="showSection('usuarios'); return false;">
            <i class="fas fa-users"></i>
            <span>Usuarios</span>
        </a>
        <?php endif; ?>
        <?php if ($_SESSION['rol'] === 'superadmin'): ?>
        <a href="#" class="nav-item" onclick="showSection('roles'); return false;">
            <i class="fas fa-shield-alt"></i>
            <span>Gestión de Roles</span>
        </a>
        <?php endif; ?>
        <a href="#" class="nav-item" onclick="showSection('reportes'); return false;">
            <i class="fas fa-chart-bar"></i>
            <span>Reportes</span>
        </a>
        <hr style="border: none; border-top: 1px solid rgba(255,255,255,0.1); margin: 1rem 0;">
        <a href="#" class="nav-item" onclick="showSection('perfil'); return false;">
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
            <button class="sidebar-toggle" onclick="toggleSidebar()">
                <i class="fas fa-bars"></i>
            </button>
            <h1 class="page-title" id="pageTitle">Dashboard</h1>
        </div>
        <div class="header-right">
            <!-- User Menu Dropdown -->
            <div class="user-menu-container">
                <button class="user-menu-toggle" onclick="toggleUserMenu()" style="border: none; background: none; cursor: pointer; display: flex; align-items: center; gap: 1rem; padding: 0.5rem 1rem;">
                    <div class="user-avatar" style="width: 50px; height: 50px; border-radius: 50%; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); display: flex; align-items: center; justify-content: center; color: white; font-size: 1.5rem; flex-shrink: 0;">
                        <?php if (!empty($_SESSION['foto'])): ?>
                            <img src="<?php echo $_SESSION['foto']; ?>" alt="Avatar" style="width: 100%; height: 100%; border-radius: 50%; object-fit: cover;">
                        <?php else: ?>
                            <i class="fas fa-user"></i>
                        <?php endif; ?>
                    </div>
                    <div class="user-info-header" style="display: flex; flex-direction: column; gap: 0.2rem; text-align: left;">
                        <span class="user-name" id="header-user-name" style="font-size: 1rem; font-weight: 600; color: #003366;">
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
                        <span style="font-size: 0.85rem; color: #ff9800; font-weight: 500;" id="header-departamento">
                            <?php echo $area_mostrar; ?>
                        </span>
                        <?php endif; ?>
                        <span class="user-role" id="header-user-role" style="font-size: 0.85rem; color: #ff5252; font-weight: 500;">
                            <?php 
                            $rol_labels_header = [
                                'superadmin' => 'Superadmin',
                                'admin' => 'Admin',
                                'analista' => 'Analista',
                                'supervisor' => 'Supervisor',
                                'direccion' => 'Dirección',
                                'decanatura' => 'Decanatura',
                                'asistente' => 'Asistente'
                            ];
                            echo 'Rol: ' . ($rol_labels_header[$_SESSION['rol']] ?? ucfirst($_SESSION['rol']));
                            ?>
                        </span>
                    </div>
                    <i class="fas fa-chevron-down" style="color: white; margin-left: 0.5rem;"></i>
                </button>
                
                <!-- Dropdown Menu -->
                <div class="user-menu-dropdown" id="userMenuDropdown">
                    <a href="#" onclick="showSection('perfil'); return false;" class="dropdown-item">
                        <i class="fas fa-user-circle"></i>
                        <span>Mi Perfil</span>
                    </a>
                    <a href="#" onclick="mostrarAlerta('Función aún no disponible', 'info'); return false;" class="dropdown-item">
                        <i class="fas fa-cog"></i>
                        <span>Configuración</span>
                    </a>
                    <div class="dropdown-divider"></div>
                    <a href="#" onclick="mostrarAlerta('Función aún no disponible', 'info'); return false;" class="dropdown-item">
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
            <div class="col-lg-8" style="flex: 0 0 calc(65% - 0.75rem);">
                <div class="dashboard-card">
                    <div class="card-header">
                        <h3 class="card-title">
                            <i class="fas fa-chart-bar"></i> Descripción General de Reclamos
                        </h3>
                    </div>
                    <div class="card-body" style="padding: 1.25rem 1.5rem;">
                        <div id="legend-container-tipos" style="margin-bottom: 1rem;"></div>
                        <div style="position: relative; height: 320px;">
                            <canvas id="chartTiposReclamos"></canvas>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Gráfica de Estados -->
            <div class="col-lg-4" style="flex: 0 0 calc(35% - 0.75rem);">
                <div class="dashboard-card" style="height: 100%;">
                    <div class="card-header">
                        <h3 class="card-title">
                            <i class="fas fa-chart-pie"></i> Estados de Reclamos
                        </h3>
                    </div>
                    <div class="card-body" style="padding: 1.5rem; display: flex; align-items: center; justify-content: center;">
                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 2rem; width: 100%; align-items: center;">
                            <div style="display: flex; align-items: center; justify-content: center;">
                                <canvas id="chartEstados" width="200" height="200"></canvas>
                            </div>
                            <div style="display: flex; flex-direction: column; gap: 1rem;">
                                <div style="display: flex; align-items: center; justify-content: space-between;">
                                    <div style="display: flex; align-items: center; gap: 0.625rem;">
                                        <div style="width: 10px; height: 10px; border-radius: 50%; background-color: #28a745;"></div>
                                        <span style="color: #95a5a6; font-size: 0.8125rem; font-weight: 400;">Resueltos</span>
                                    </div>
                                    <span style="font-weight: 600; color: #fff; font-size: 0.9375rem; background-color: #28a745; padding: 0.25rem 0.625rem; border-radius: 50px; min-width: 32px; text-align: center;"><?php echo $estados_result['resueltos'] ?? 0; ?></span>
                                </div>
                                <div style="display: flex; align-items: center; justify-content: space-between;">
                                    <div style="display: flex; align-items: center; gap: 0.625rem;">
                                        <div style="width: 10px; height: 10px; border-radius: 50%; background-color: #ffbf00;"></div>
                                        <span style="color: #95a5a6; font-size: 0.8125rem; font-weight: 400;">En Revisión</span>
                                    </div>
                                    <span style="font-weight: 600; color: #fff; font-size: 0.9375rem; background-color: #ffbf00; padding: 0.25rem 0.625rem; border-radius: 50px; min-width: 32px; text-align: center;"><?php echo $estados_result['en_revision'] ?? 0; ?></span>
                                </div>
                                <div style="display: flex; align-items: center; justify-content: space-between;">
                                    <div style="display: flex; align-items: center; gap: 0.625rem;">
                                        <div style="width: 10px; height: 10px; border-radius: 50%; background-color: #dc3545;"></div>
                                        <span style="color: #95a5a6; font-size: 0.8125rem; font-weight: 400;">No Procede</span>
                                    </div>
                                    <span style="font-weight: 600; color: #fff; font-size: 0.9375rem; background-color: #dc3545; padding: 0.25rem 0.625rem; border-radius: 50px; min-width: 32px; text-align: center;"><?php echo $estados_result['no_procede'] ?? 0; ?></span>
                                </div>
                                <div style="display: flex; align-items: center; justify-content: space-between;">
                                    <div style="display: flex; align-items: center; gap: 0.625rem;">
                                        <div style="width: 10px; height: 10px; border-radius: 50%; background-color: #6c757d;"></div>
                                        <span style="color: #95a5a6; font-size: 0.8125rem; font-weight: 400;">Pendientes</span>
                                    </div>
                                    <span style="font-weight: 600; color: #fff; font-size: 0.9375rem; background-color: #6c757d; padding: 0.25rem 0.625rem; border-radius: 50px; min-width: 32px; text-align: center;"><?php echo $estados_result['pendientes'] ?? 0; ?></span>
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
                                        <button onclick="verDetalleReclamo(<?php echo $row['id']; ?>)" class="btn-action btn-view" title="Ver Detalle">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                        <?php if ($_SESSION['rol'] !== 'asistente'): ?>
                                        <button onclick="responderReclamo(<?php echo $row['id']; ?>)" class="btn-action btn-edit" title="Responder Reclamo">
                                            <i class="fas fa-reply"></i>
                                        </button>
                                        <?php endif; ?>
                                        <button onclick="derivarReclamo(<?php echo $row['id']; ?>)" class="btn-action btn-forward" title="Derivar Reclamo">
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

    <!-- SECTION: USUARIOS -->
    <div id="section-usuarios" class="content-section">
        <div class="dashboard-card">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-users"></i> Gestionar Usuarios
                </h3>
            </div>
            <div class="card-body">
                <?php if ($_SESSION['rol'] === 'admin' || $_SESSION['rol'] === 'superadmin'): ?>
                <form id="formCrearUsuario" class="dashboard-form" style="margin-bottom: 1.5rem;">
                    <div class="row-form" style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 1rem;">
                        <div class="form-group">
                            <label for="nuevo_usuario">Usuario</label>
                            <input type="text" id="nuevo_usuario" name="usuario" required>
                        </div>
                        <div class="form-group">
                            <label for="nuevo_nombre">Nombre</label>
                            <input type="text" id="nuevo_nombre" name="nombre" required>
                        </div>
                        <div class="form-group">
                            <label for="nuevo_apellido_paterno">Apellido Paterno</label>
                            <input type="text" id="nuevo_apellido_paterno" name="apellido_paterno" required>
                        </div>
                        <div class="form-group">
                            <label for="nuevo_apellido_materno">Apellido Materno</label>
                            <input type="text" id="nuevo_apellido_materno" name="apellido_materno">
                        </div>
                        <div class="form-group">
                            <label for="nuevo_dni">DNI</label>
                            <input type="text" id="nuevo_dni" name="dni" maxlength="8" required>
                        </div>
                        <div class="form-group">
                            <label for="nuevo_telefono">Teléfono</label>
                            <input type="text" id="nuevo_telefono" name="telefono" maxlength="9" required>
                        </div>
                        <div class="form-group">
                            <label for="nuevo_email">Email</label>
                            <input type="email" id="nuevo_email" name="email" required>
                        </div>
                        <div class="form-group">
                            <label for="nuevo_rol">Rol</label>
                            <select id="nuevo_rol" name="rol" required>
                                <option value="asistente">Asistente</option>
                                <option value="analista">Analista</option>
                                <option value="supervisor">Supervisor</option>
                                <option value="direccion">Dirección</option>
                                <option value="decanatura">Decanatura</option>
                                <option value="admin">Admin</option>
                                <?php if ($_SESSION['rol'] === 'superadmin'): ?>
                                <option value="superadmin">Superadmin</option>
                                <?php endif; ?>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="nuevo_password">Contraseña</label>
                            <div class="password-wrapper">
                                <input type="password" id="nuevo_password" name="password" minlength="6" required>
                                <button type="button" class="password-toggle" onclick="togglePassword('nuevo_password', 'nuevo_toggle_icon')" aria-label="Mostrar contraseña">
                                    <i class="fas fa-eye" id="nuevo_toggle_icon"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                    <div style="margin-top: 1rem;">
                        <button type="submit" class="btn-action btn-toggle">Crear Usuario</button>
                    </div>
                </form>

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
                                    <td><?php echo ucfirst($usuario['rol']); ?></td>
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
                                        <button class="btn-action btn-toggle<?php echo $es_activo ? '' : ' inactive'; ?>" onclick="toggleUsuario(<?php echo (int)$usuario['id']; ?>)">
                                            <?php echo $es_activo ? 'Desactivar' : 'Activar'; ?>
                                        </button>
                                        <button class="btn-action btn-toggle" onclick="eliminarUsuario(<?php echo (int)$usuario['id']; ?>)" style="background: #dc3545;">
                                            Eliminar
                                        </button>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="7" style="text-align: center;">No hay usuarios registrados.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
                <?php else: ?>
                <p>No tienes permisos para ver esta sección.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Modal Detalle Reclamo -->
    <div id="modalDetalleReclamo" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.6); z-index: 1000; justify-content: center; align-items: center; padding: 1rem;">
        <div style="background: white; border-radius: 12px; padding: 0; max-width: 700px; width: 100%; max-height: 90vh; overflow: hidden; box-shadow: 0 10px 40px rgba(0,0,0,0.2); display: flex; flex-direction: column;">
            <!-- Header Modal -->
            <div style="padding: 1.5rem 2rem; border-bottom: 2px solid #f0f0f0; display: flex; justify-content: space-between; align-items: center; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
                <h2 style="margin: 0; color: white; font-size: 1.5rem; display: flex; align-items: center; gap: 0.5rem;">
                    <i class="fas fa-file-alt" style="font-size: 1.3rem;"></i>
                    Detalle del Reclamo
                </h2>
                <button onclick="cerrarModalDetalle()" style="border: none; background: none; font-size: 1.8rem; cursor: pointer; color: white; padding: 0; width: 30px; height: 30px; display: flex; align-items: center; justify-content: center; transition: transform 0.2s;">&times;</button>
            </div>
            <!-- Content Modal -->
            <div id="contenidoModalDetalle" style="color: #555; flex: 1; overflow-y: auto; padding: 2rem;">
                <p>Cargando...</p>
            </div>
        </div>
    </div>

    <!-- Modal Responder Reclamo -->
    <div id="modalResponderReclamo" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.6); z-index: 1001; justify-content: center; align-items: center; padding: 1rem;">
        <div style="background: white; border-radius: 12px; padding: 0; max-width: 600px; width: 100%; box-shadow: 0 10px 40px rgba(0,0,0,0.2); display: flex; flex-direction: column;">
            <!-- Header -->
            <div style="padding: 1.5rem 2rem; border-bottom: 2px solid #f0f0f0; display: flex; justify-content: space-between; align-items: center; background: linear-gradient(135deg, #28a745 0%, #20c997 100%);">
                <h2 style="margin: 0; color: white; font-size: 1.5rem; display: flex; align-items: center; gap: 0.5rem;">
                    <i class="fas fa-reply" style="font-size: 1.3rem;"></i>
                    Responder Reclamo
                </h2>
                <button onclick="cerrarModalResponder()" style="border: none; background: none; font-size: 1.8rem; cursor: pointer; color: white; padding: 0;">&times;</button>
            </div>
            <!-- Content -->
            <div style="padding: 2rem;">
                <div style="margin-bottom: 1.5rem;">
                    <label style="display: block; font-weight: 600; color: #2c3e50; margin-bottom: 0.5rem;">Folio</label>
                    <p id="responderFolio" style="margin: 0; padding: 0.8rem; background: #f5f5f5; border-radius: 6px; color: #666;">-</p>
                </div>
                <div style="margin-bottom: 1.5rem;">
                    <label style="display: block; font-weight: 600; color: #2c3e50; margin-bottom: 0.5rem;">Escribe tu respuesta</label>
                    <textarea id="textareaRespuesta" style="width: 100%; padding: 0.8rem; border: 1px solid #ddd; border-radius: 6px; font-family: Arial; font-size: 0.95rem; min-height: 200px; resize: vertical;" placeholder="Ingresa la respuesta a la reclamación..."></textarea>
                </div>
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                    <button onclick="cerrarModalResponder()" style="padding: 0.8rem 1.5rem; background: #6c757d; color: white; border: none; border-radius: 6px; font-weight: 600; cursor: pointer;">
                        Cancelar
                    </button>
                    <button onclick="guardarRespuesta()" style="padding: 0.8rem 1.5rem; background: #28a745; color: white; border: none; border-radius: 6px; font-weight: 600; cursor: pointer;">
                        <i class="fas fa-save"></i> Guardar Respuesta
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Derivar Reclamo -->
    <div id="modalDerivarReclamo" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.6); z-index: 1001; justify-content: center; align-items: center; padding: 1rem;">
        <div style="background: white; border-radius: 12px; padding: 0; max-width: 650px; width: 100%; max-height: 90vh; overflow: hidden; box-shadow: 0 10px 40px rgba(0,0,0,0.2); display: flex; flex-direction: column;">
            <!-- Header -->
            <div style="padding: 1.5rem 2rem; border-bottom: 2px solid #f0f0f0; display: flex; justify-content: space-between; align-items: center; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
                <h2 style="margin: 0; color: white; font-size: 1.5rem; display: flex; align-items: center; gap: 0.5rem;">
                    <i class="fas fa-share" style="font-size: 1.3rem;"></i>
                    Derivar Reclamo
                </h2>
                <button onclick="cerrarModalDerivar()" style="border: none; background: none; font-size: 1.8rem; cursor: pointer; color: white; padding: 0;">&times;</button>
            </div>
            <!-- Content -->
            <div id="contenidoDerivar" style="flex: 1; overflow-y: auto; padding: 2rem;">
                <p>Cargando...</p>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <footer class="dashboard-footer">
        <p>Copyright © UPeU 2026. Todos los derechos son reservados</p>
    </footer>
</div>

<script>
function toggleSidebar() {
    document.querySelector('.sidebar').classList.toggle('collapsed');
    document.querySelector('.main-content').classList.toggle('expanded');
}

function toggleUserMenu() {
    const dropdown = document.getElementById('userMenuDropdown');
    dropdown.classList.toggle('show');
}

function showSection(sectionName) {
    document.querySelectorAll('.content-section').forEach(section => {
        section.classList.remove('active');
    });
    
    document.getElementById('section-' + sectionName).classList.add('active');
    
    document.querySelectorAll('.nav-item').forEach(item => {
        item.classList.remove('active');
    });
    
    const titles = {
        'dashboard': 'Dashboard',
        'reclamaciones': 'Gestionar Reclamaciones',
        'usuarios': 'Gestionar Usuarios',
        'reportes': 'Reportes y Estadísticas',
        'perfil': 'Mi Perfil',
        'roles': 'Gestión de Roles y Áreas'
    };
    document.getElementById('pageTitle').textContent = titles[sectionName];
}

function verDetalleReclamo(id) {
    fetch('ajax_ver_reclamo.php?id=' + id)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const r = data.reclamacion;
                let contenido = '<div style="display: grid; gap: 1.5rem;">';
                
                // Folio y Tipo
                contenido += '<div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem; padding-bottom: 1rem; border-bottom: 1px solid #e8e8e8;">';
                if (r.folio) {
                    contenido += `<div>
                        <label style="font-size: 0.8rem; color: #999; text-transform: uppercase; font-weight: 600; letter-spacing: 0.5px;">Folio</label>
                        <p style="margin: 0.5rem 0 0 0; font-family: monospace; font-weight: 600; font-size: 1.1rem; color: #667eea;">${r.folio}</p>
                    </div>`;
                }
                if (r.tipo_registro) {
                    contenido += `<div>
                        <label style="font-size: 0.8rem; color: #999; text-transform: uppercase; font-weight: 600; letter-spacing: 0.5px;">Tipo</label>
                        <p style="margin: 0.5rem 0 0 0;"><span class="badge badge-type" style="display: inline-block; padding: 0.4rem 0.8rem; background: #ffba57; color: white; border-radius: 20px; font-weight: 600; font-size: 0.85rem;">${r.tipo_registro}</span></p>
                    </div>`;
                }
                contenido += '</div>';
                
                // Datos Personales: Nombre, DNI, Teléfono
                if (r.nombres || r.dni_ce || r.telefono) {
                    contenido += '<div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 1.5rem;">';
                    if (r.nombres) {
                        let nombreCompleto = ((r.nombres || '') + ' ' + (r.apellido_paterno || '') + ' ' + (r.apellido_materno || '')).trim();
                        contenido += `<div>
                            <label style="font-size: 0.8rem; color: #999; text-transform: uppercase; font-weight: 600; letter-spacing: 0.5px;">Nombre Completo</label>
                            <p style="margin: 0.5rem 0 0 0; font-weight: 500; color: #2c3e50; font-size: 0.95rem;">${nombreCompleto}</p>
                        </div>`;
                    }
                    if (r.dni_ce) {
                        contenido += `<div>
                            <label style="font-size: 0.8rem; color: #999; text-transform: uppercase; font-weight: 600; letter-spacing: 0.5px;">DNI/CE</label>
                            <p style="margin: 0.5rem 0 0 0; font-weight: 500; color: #2c3e50; font-size: 0.95rem;">${r.dni_ce}</p>
                        </div>`;
                    }
                    if (r.telefono) {
                        contenido += `<div>
                            <label style="font-size: 0.8rem; color: #999; text-transform: uppercase; font-weight: 600; letter-spacing: 0.5px;">Teléfono</label>
                            <p style="margin: 0.5rem 0 0 0; font-weight: 500; color: #2c3e50; font-size: 0.95rem;"><a href="tel:${r.telefono}" style="color: #667eea; text-decoration: none;">${r.telefono}</a></p>
                        </div>`;
                    }
                    contenido += '</div>';
                }
                
                // Email y Domicilio
                if (r.email || r.domicilio) {
                    contenido += '<div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem;">';
                    if (r.email) {
                        contenido += `<div>
                            <label style="font-size: 0.8rem; color: #999; text-transform: uppercase; font-weight: 600; letter-spacing: 0.5px;">Email</label>
                            <p style="margin: 0.5rem 0 0 0;"><a href="mailto:${r.email}" style="color: #667eea; text-decoration: none; font-weight: 500;">${r.email}</a></p>
                        </div>`;
                    }
                    if (r.domicilio) {
                        contenido += `<div>
                            <label style="font-size: 0.8rem; color: #999; text-transform: uppercase; font-weight: 600; letter-spacing: 0.5px;">Domicilio</label>
                            <p style="margin: 0.5rem 0 0 0; color: #2c3e50; font-size: 0.95rem;">${r.domicilio}</p>
                        </div>`;
                    }
                    contenido += '</div>';
                }
                
                // Campus, Departamento y Área
                if (r.campus || r.departamento || r.area) {
                    contenido += '<div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 1.5rem; padding: 1rem; background: #f0f4f8; border-radius: 6px;">';
                    if (r.campus) {
                        contenido += `<div>
                            <label style="font-size: 0.8rem; color: #666; text-transform: uppercase; font-weight: 600; letter-spacing: 0.5px;">Campus</label>
                            <p style="margin: 0.5rem 0 0 0; color: #2c3e50; font-size: 0.95rem; font-weight: 500;">${r.campus}</p>
                        </div>`;
                    }
                    if (r.departamento) {
                        contenido += `<div>
                            <label style="font-size: 0.8rem; color: #666; text-transform: uppercase; font-weight: 600; letter-spacing: 0.5px;">Departamento</label>
                            <p style="margin: 0.5rem 0 0 0; color: #2c3e50; font-size: 0.95rem; font-weight: 500;">${r.departamento}</p>
                        </div>`;
                    }
                    if (r.area) {
                        contenido += `<div>
                            <label style="font-size: 0.8rem; color: #666; text-transform: uppercase; font-weight: 600; letter-spacing: 0.5px;">Área</label>
                            <p style="margin: 0.5rem 0 0 0; color: #2c3e50; font-size: 0.95rem; font-weight: 500;">${r.area}</p>
                        </div>`;
                    }
                    contenido += '</div>';
                }
                
                // Fecha Registro, Estado y Tipo de Bien (3 columnas)
                if (r.fecha_registro || r.estado || r.tipo_bien) {
                    contenido += '<div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 1.5rem;">';
                    if (r.fecha_registro) {
                        const fecha = new Date(r.fecha_registro).toLocaleDateString('es-PE') + ' ' + new Date(r.fecha_registro).toLocaleTimeString('es-PE');
                        contenido += `<div>
                            <label style="font-size: 0.8rem; color: #999; text-transform: uppercase; font-weight: 600; letter-spacing: 0.5px;">Fecha Registro</label>
                            <p style="margin: 0.5rem 0 0 0; color: #2c3e50; font-size: 0.95rem;">${fecha}</p>
                        </div>`;
                    }
                    if (r.estado) {
                        const badge = r.estado === 'Resuelto' ? 'badge-success' : r.estado === 'En revisión' ? 'badge-warning' : r.estado === 'No procede' ? 'badge-danger' : 'badge-info';
                        contenido += `<div>
                            <label style="font-size: 0.8rem; color: #999; text-transform: uppercase; font-weight: 600; letter-spacing: 0.5px;">Estado</label>
                            <p style="margin: 0.5rem 0 0 0;">
                                <span class="badge badge-status ${badge}">${r.estado}</span>
                            </p>
                        </div>`;
                    }
                    if (r.tipo_bien) {
                        contenido += `<div>
                            <label style="font-size: 0.8rem; color: #999; text-transform: uppercase; font-weight: 600; letter-spacing: 0.5px;">Tipo de Bien Contratado</label>
                            <p style="margin: 0.5rem 0 0 0; color: #2c3e50; font-size: 0.95rem;">${r.tipo_bien}</p>
                        </div>`;
                    }
                    contenido += '</div>';
                }
                
                // Descripción del Asunto
                if (r.descripcion_asunto) {
                    contenido += `<div>
                        <label style="font-size: 0.8rem; color: #999; text-transform: uppercase; font-weight: 600; letter-spacing: 0.5px; display: block; margin-bottom: 0.7rem;">Asunto/Descripción</label>
                        <div style="background: #f8f9fa; padding: 1.2rem; border-radius: 6px; border-left: 4px solid #667eea; white-space: pre-wrap; color: #2c3e50; font-size: 0.95rem; line-height: 1.5; max-height: 200px; overflow-y: auto;">${r.descripcion_asunto}</div>
                    </div>`;
                }
                
                // Detalle de Reclamación
                if (r.detalle_reclamacion) {
                    contenido += `<div>
                        <label style="font-size: 0.8rem; color: #999; text-transform: uppercase; font-weight: 600; letter-spacing: 0.5px; display: block; margin-bottom: 0.7rem;">Detalle de la Reclamación</label>
                        <div style="background: #f8f9fa; padding: 1.2rem; border-radius: 6px; border-left: 4px solid #ff9800; white-space: pre-wrap; color: #2c3e50; font-size: 0.95rem; line-height: 1.5; max-height: 250px; overflow-y: auto;">${r.detalle_reclamacion}</div>
                    </div>`;
                }
                
                // Pedido
                if (r.pedido) {
                    contenido += `<div>
                        <label style="font-size: 0.8rem; color: #999; text-transform: uppercase; font-weight: 600; letter-spacing: 0.5px;">Pedido</label>
                        <p style="margin: 0.5rem 0 0 0; background: #fff3cd; padding: 0.8rem; border-radius: 6px; color: #856404; font-size: 0.95rem;">${r.pedido}</p>
                    </div>`;
                }
                
                // Respuesta
                if (r.respuesta) {
                    contenido += `<div>
                        <label style="font-size: 0.8rem; color: #999; text-transform: uppercase; font-weight: 600; letter-spacing: 0.5px; display: block; margin-bottom: 0.7rem;">Respuesta</label>
                        <div style="background: #e8f5e9; padding: 1.2rem; border-radius: 6px; border-left: 4px solid #28a745; white-space: pre-wrap; color: #1b5e20; font-size: 0.95rem; line-height: 1.5;">${r.respuesta}</div>
                    </div>`;
                }
                
                // Fecha de Respuesta
                if (r.fecha_respuesta) {
                    const fechaResp = new Date(r.fecha_respuesta).toLocaleDateString('es-PE') + ' ' + new Date(r.fecha_respuesta).toLocaleTimeString('es-PE');
                    contenido += `<div>
                        <label style="font-size: 0.8rem; color: #999; text-transform: uppercase; font-weight: 600; letter-spacing: 0.5px;">Fecha de Respuesta</label>
                        <p style="margin: 0.5rem 0 0 0; color: #2c3e50; font-size: 0.95rem;">${fechaResp}</p>
                    </div>`;
                }
                
                // Botones de Acción
                contenido += `<div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; padding-top: 1.5rem; border-top: 2px solid #e8e8e8;">
                    <button onclick="responderReclamoModal(${r.id})" style="padding: 0.8rem 1.5rem; background: #28a745; color: white; border: none; border-radius: 6px; font-weight: 600; cursor: pointer; transition: background 0.2s;">
                        <i class="fas fa-reply"></i> Responder
                    </button>
                    <button onclick="derivarReclamoModal(${r.id})" style="padding: 0.8rem 1.5rem; background: #667eea; color: white; border: none; border-radius: 6px; font-weight: 600; cursor: pointer; transition: background 0.2s;">
                        <i class="fas fa-share"></i> Derivar
                    </button>
                </div>`;
                
                contenido += '</div>';
                document.getElementById('contenidoModalDetalle').innerHTML = contenido;
                document.getElementById('modalDetalleReclamo').style.display = 'flex';
            } else {
                mostrarAlerta('Error: ' + (data.message || 'Error al cargar detalle'), 'error');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            mostrarAlerta('Error de conexión: ' + error.message, 'error');
        });
}

function cerrarModalDetalle() {
    document.getElementById('modalDetalleReclamo').style.display = 'none';
}

function responderReclamoModal(id) {
    // Obtener datos del reclamo para mostrar el folio
    fetch('ajax_ver_reclamo.php?id=' + id)
    .then(response => response.json())
    .then(data => {
        if (data.success && data.reclamacion) {
            const r = data.reclamacion;
            document.getElementById('responderFolio').textContent = r.folio || 'N/A';
            document.getElementById('textareaRespuesta').value = '';
            document.getElementById('textareaRespuesta').focus();
            
            // Guardar el ID del reclamo en el modal para usarlo al guardar
            document.getElementById('modalResponderReclamo').dataset.reclamoId = id;
            
            // Mostrar modal de respuesta
            document.getElementById('modalResponderReclamo').style.display = 'flex';
        }
    })
    .catch(error => {
        console.error('Error:', error);
        mostrarAlerta('Error al cargar el reclamo', 'error');
    });
}

function cerrarModalResponder() {
    document.getElementById('modalResponderReclamo').style.display = 'none';
    document.getElementById('textareaRespuesta').value = '';
}

function guardarRespuesta() {
    const id = document.getElementById('modalResponderReclamo').dataset.reclamoId;
    const respuesta = document.getElementById('textareaRespuesta').value.trim();
    
    if (!respuesta) {
        mostrarAlerta('La respuesta no puede estar vacía', 'error');
        return;
    }
    
    const formData = new FormData();
    formData.append('id', id);
    formData.append('respuesta', respuesta);
    
    fetch('ajax_guardar_respuesta.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            mostrarAlerta('Respuesta guardada correctamente', 'success');
            cerrarModalResponder();
            verDetalleReclamo(id); // Recargar el modal de detalle
        } else {
            mostrarAlerta(data.message || 'Error al guardar respuesta', 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        mostrarAlerta('Error al guardar respuesta', 'error');
    });
}

function derivarReclamoModal(id) {
    // Cargar reclamo y construir modal
    fetch('ajax_ver_reclamo.php?id=' + id)
    .then(response => response.json())
    .then(data => {
        if (!data.success || !data.reclamacion) {
            mostrarAlerta('Error al cargar reclamo', 'error');
            return;
        }
        
        const r = data.reclamacion;
        
        // Cargar departamentos
        fetch('ajax_get_departamentos.php')
        .then(response => response.json())
        .then(deptData => {
            let html = `
                <div style="margin-bottom: 1.5rem; padding: 1.2rem; background: #f8f9fa; border-radius: 6px;">
                    <label style="font-weight: 600; color: #667eea; font-size: 0.9rem;">Resumen del Reclamo</label>
                    <p style="margin: 0.5rem 0 0 0; color: #2c3e50; font-size: 1rem;"><strong>Folio:</strong> ${r.folio || 'N/A'}</p>
                    <p style="margin: 0.3rem 0 0 0; color: #2c3e50; font-size: 0.95rem;"><strong>Tipo:</strong> ${r.tipo || 'N/A'}</p>
                    <p style="margin: 0.3rem 0 0 0; color: #2c3e50; font-size: 0.95rem;"><strong>Estado Actual:</strong> <span style="background: #667eea; color: white; padding: 0.3rem 0.6rem; border-radius: 4px; font-size: 0.85rem;">${r.estado}</span></p>
                </div>
                
                <div style="margin-bottom: 1.5rem;">
                    <label style="display: block; font-weight: 600; color: #2c3e50; margin-bottom: 0.5rem;">Departamento</label>
                    <select id="selectDepartamento" onchange="cargarAreas(this.value)" style="width: 100%; padding: 0.8rem; border: 1px solid #ddd; border-radius: 6px; font-size: 0.95rem;">
                        <option value="">-- Selecciona un departamento --</option>`;
            
            if (deptData.success && deptData.departamentos && deptData.departamentos.length > 0) {
                deptData.departamentos.forEach(dept => {
                    html += `<option value="${dept.id}">${dept.nombre}</option>`;
                });
            } else {
                html += `<option value="">No hay departamentos disponibles</option>`;
            }
            
            html += `</select>
                </div>
                
                <div style="margin-bottom: 1.5rem;">
                    <label style="display: block; font-weight: 600; color: #2c3e50; margin-bottom: 0.5rem;">Área</label>
                    <select id="selectArea" style="width: 100%; padding: 0.8rem; border: 1px solid #ddd; border-radius: 6px; font-size: 0.95rem;">
                        <option value="">-- Selecciona un área --</option>
                    </select>
                </div>
                
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                    <button onclick="cerrarModalDerivar()" style="padding: 0.8rem 1.5rem; background: #6c757d; color: white; border: none; border-radius: 6px; font-weight: 600; cursor: pointer;">
                        Cancelar
                    </button>
                    <button onclick="guardarDerivacion()" style="padding: 0.8rem 1.5rem; background: #667eea; color: white; border: none; border-radius: 6px; font-weight: 600; cursor: pointer;">
                        <i class="fas fa-share"></i> Derivar
                    </button>
                </div>`;
            
            document.getElementById('contenidoDerivar').innerHTML = html;
            document.getElementById('modalDerivarReclamo').dataset.reclamoId = id;
            document.getElementById('modalDerivarReclamo').style.display = 'flex';
        })
        .catch(error => {
            console.error('Error al cargar departamentos:', error);
            mostrarAlerta('Error al cargar departamentos: ' + error.message, 'error');
        });
    })
    .catch(error => {
        console.error('Error:', error);
        mostrarAlerta('Error: ' + error.message, 'error');
    });
}

function cargarAreas(departamentoId) {
    if (!departamentoId) {
        document.getElementById('selectArea').innerHTML = '<option value="">-- Selecciona un área --</option>';
        return;
    }
    
    const formData = new FormData();
    formData.append('departamento_id', departamentoId);
    
    fetch('ajax_get_areas.php', {
        method: 'POST',
        body: formData
    })
    .then(response => {
        if (!response.ok) throw new Error('Error al cargar áreas');
        return response.json();
    })
    .then(data => {
        console.log('Áreas cargadas:', data);
        let opciones = '<option value="">-- Selecciona un área --</option>';
        if (data.success && data.areas && data.areas.length > 0) {
            data.areas.forEach(area => {
                opciones += `<option value="${area.id}">${area.nombre}</option>`;
            });
        } else {
            opciones += '<option value="">No hay áreas disponibles</option>';
        }
        document.getElementById('selectArea').innerHTML = opciones;
    })
    .catch(error => {
        console.error('Error al cargar áreas:', error);
        document.getElementById('selectArea').innerHTML = '<option value="">Error al cargar áreas</option>';
        mostrarAlerta('Error: ' + error.message, 'error');
    });
}

function cerrarModalDerivar() {
    document.getElementById('modalDerivarReclamo').style.display = 'none';
}

function guardarDerivacion() {
    const id = document.getElementById('modalDerivarReclamo').dataset.reclamoId;
    const areId = document.getElementById('selectArea').value;
    
    if (!areId) {
        mostrarAlerta('Debes seleccionar un área', 'error');
        return;
    }
    
    const formData = new FormData();
    formData.append('id', id);
    formData.append('area_destino', areId);
    
    fetch('ajax_derivar_reclamo.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            mostrarAlerta('Reclamo derivado correctamente', 'success');
            cerrarModalDerivar();
            cerrarModalDetalle();
            window.location.reload();
        } else {
            mostrarAlerta(data.message || 'Error al derivar reclamo', 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        mostrarAlerta('Error al derivar reclamo', 'error');
    });
}

function responderReclamo(id) {
    mostrarAlerta('Función aún en desarrollo', 'info');
}

function derivarReclamo(id) {
    mostrarAlerta('Función aún en desarrollo', 'info');
}

function mostrarAlerta(mensaje, tipo) {
    console.log(mensaje, tipo);
    alert(mensaje);
}

function togglePassword(inputId, iconId) {
    const input = document.getElementById(inputId);
    const icon = document.getElementById(iconId);
    if (!input || !icon) return;

    if (input.type === 'password') {
        input.type = 'text';
        icon.classList.remove('fa-eye');
        icon.classList.add('fa-eye-slash');
    } else {
        input.type = 'password';
        icon.classList.remove('fa-eye-slash');
        icon.classList.add('fa-eye');
    }
}

async function crearUsuario(event) {
    event.preventDefault();
    const form = event.target;
    const formData = new FormData(form);

    try {
        const response = await fetch('ajax_crear_usuario.php', {
            method: 'POST',
            body: formData
        });
        const data = await response.json();
        if (data.success) {
            mostrarAlerta('Usuario creado correctamente', 'success');
            window.location.reload();
        } else {
            mostrarAlerta(data.message || 'No se pudo crear el usuario', 'error');
        }
    } catch (error) {
        mostrarAlerta('Error al crear usuario', 'error');
    }
}

async function toggleUsuario(usuarioId) {
    const formData = new FormData();
    formData.append('usuario_id', usuarioId);

    try {
        const response = await fetch('ajax_toggle_usuario.php', {
            method: 'POST',
            body: formData
        });
        const data = await response.json();
        if (data.success) {
            mostrarAlerta('Estado actualizado', 'success');
            window.location.reload();
        } else {
            mostrarAlerta(data.message || 'No se pudo actualizar el estado', 'error');
        }
    } catch (error) {
        mostrarAlerta('Error al actualizar estado', 'error');
    }
}

async function eliminarUsuario(usuarioId) {
    if (!confirm('¿Seguro que deseas eliminar este usuario?')) {
        return;
    }

    const formData = new FormData();
    formData.append('usuario_id', usuarioId);

    try {
        const response = await fetch('ajax_eliminar_usuario.php', {
            method: 'POST',
            body: formData
        });
        const data = await response.json();
        if (data.success) {
            mostrarAlerta('Usuario eliminado', 'success');
            window.location.reload();
        } else {
            mostrarAlerta(data.message || 'No se pudo eliminar el usuario', 'error');
        }
    } catch (error) {
        mostrarAlerta('Error al eliminar usuario', 'error');
    }
}

document.addEventListener('DOMContentLoaded', () => {
    const form = document.getElementById('formCrearUsuario');
    if (form) {
        form.addEventListener('submit', crearUsuario);
    }

    // Inicializar gráficas
    initCharts();
});

function initCharts() {
    // Plugin personalizado para leyenda HTML con bordes
    const getOrCreateLegendList = (chart, id) => {
        const legendContainer = document.getElementById(id);
        let listContainer = legendContainer.querySelector('ul');

        if (!listContainer) {
            listContainer = document.createElement('ul');
            listContainer.style.display = 'flex';
            listContainer.style.flexDirection = 'row';
            listContainer.style.margin = '0';
            listContainer.style.padding = '0';
            listContainer.style.listStyle = 'none';
            listContainer.style.gap = '10px';
            listContainer.style.flexWrap = 'wrap';

            legendContainer.appendChild(listContainer);
        }

        return listContainer;
    };

    const htmlLegendPlugin = {
        id: 'htmlLegend',
        afterUpdate(chart, args, options) {
            const ul = getOrCreateLegendList(chart, options.containerID);

            // Remove old legend items
            while (ul.firstChild) {
                ul.firstChild.remove();
            }

            // Reuse the built-in legendItems generator
            const items = chart.options.plugins.legend.labels.generateLabels(chart);

            items.forEach(item => {
                const li = document.createElement('li');
                li.style.alignItems = 'center';
                li.style.cursor = 'pointer';
                li.style.display = 'flex';
                li.style.flexDirection = 'row';
                li.style.border = '1px solid #e2e8f0';
                li.style.borderRadius = '10px';
                li.style.padding = '6px 12px';
                li.style.background = '#ffffff';
                li.style.transition = 'all 0.2s ease';

                li.onmouseover = () => {
                    li.style.background = '#f7fafc';
                    li.style.boxShadow = '0 2px 4px rgba(0,0,0,0.08)';
                };

                li.onmouseout = () => {
                    li.style.background = '#ffffff';
                    li.style.boxShadow = 'none';
                };

                li.onclick = () => {
                    const {type} = chart.config;
                    if (type === 'pie' || type === 'doughnut') {
                        chart.toggleDataVisibility(item.index);
                    } else {
                        chart.setDatasetVisibility(item.datasetIndex, !chart.isDatasetVisible(item.datasetIndex));
                    }
                    chart.update();
                };

                // Color box
                const boxSpan = document.createElement('span');
                boxSpan.style.background = item.fillStyle;
                boxSpan.style.borderRadius = '20%';
                boxSpan.style.display = 'inline-block';
                boxSpan.style.height = '10px';
                boxSpan.style.marginRight = '0px';
                boxSpan.style.width = '10px';

                // Text
                const textContainer = document.createElement('span');
                textContainer.style.color = '#2c3e50';
                textContainer.style.fontSize = '13px';
                textContainer.style.fontFamily = 'Arial';
                textContainer.style.textDecoration = item.hidden ? 'line-through' : '';

                const text = document.createTextNode(item.text);
                textContainer.appendChild(text);

                li.appendChild(boxSpan);
                li.appendChild(textContainer);
                ul.appendChild(li);
            });
        }
    };

    // Gráfica de Tipos de Reclamos (Barras)
    const ctxTipos = document.getElementById('chartTiposReclamos');
    if (ctxTipos) {
        const datosPorMes = <?php echo json_encode($datos_por_mes); ?>;
        const meses = ['Ene', 'Feb', 'Mar', 'Abr', 'May', 'Jun', 'Jul', 'Ago', 'Sep', 'Oct', 'Nov', 'Dic'];
        
        const dataReclamos = [];
        const dataQuejas = [];
        const dataSugerencias = [];
        
        for (let i = 1; i <= 12; i++) {
            const mes = datosPorMes[i] || { reclamos: 0, quejas: 0, sugerencias: 0 };
            dataReclamos.push(parseInt(mes.reclamos) || 0);
            dataQuejas.push(parseInt(mes.quejas) || 0);
            dataSugerencias.push(parseInt(mes.sugerencias) || 0);
        }

        new Chart(ctxTipos, {
            type: 'bar',
            data: {
                labels: meses,
                datasets: [
                    {
                        label: 'Reclamos',
                        data: dataReclamos,
                        backgroundColor: '#4680ff',
                        borderColor: '#4680ff',
                        borderWidth: 0,
                        borderRadius: {
                            topLeft: 8,
                            topRight: 8,
                            bottomLeft: 0,
                            bottomRight: 0
                        },
                        barPercentage: 0.55,
                        categoryPercentage: 0.8
                    },
                    {
                        label: 'Quejas',
                        data: dataQuejas,
                        backgroundColor: '#ffba57',
                        borderColor: '#ffba57',
                        borderWidth: 0,
                        borderRadius: {
                            topLeft: 8,
                            topRight: 8,
                            bottomLeft: 0,
                            bottomRight: 0
                        },
                        barPercentage: 0.55,
                        categoryPercentage: 0.8
                    },
                    {
                        label: 'Sugerencias',
                        data: dataSugerencias,
                        backgroundColor: '#00d0ff',
                        borderColor: '#00d0ff',
                        borderWidth: 0,
                        borderRadius: {
                            topLeft: 8,
                            topRight: 8,
                            bottomLeft: 0,
                            bottomRight: 0
                        },
                        barPercentage: 0.55,
                        categoryPercentage: 0.8
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                interaction: {
                    mode: 'nearest',
                    intersect: true
                },
                plugins: {
                    htmlLegend: {
                        containerID: 'legend-container-tipos',
                    },
                    legend: {
                        display: false
                    },
                    tooltip: {
                        backgroundColor: 'rgba(0, 0, 0, 0.8)',
                        titleColor: '#fff',
                        bodyColor: '#fff',
                        borderColor: 'rgba(255, 255, 255, 0.1)',
                        borderWidth: 1,
                        padding: 12,
                        displayColors: true,
                        cornerRadius: 6,
                        titleFont: {
                            size: 13,
                            weight: '600'
                        },
                        bodyFont: {
                            size: 12
                        }
                    }
                },
                scales: {
                    x: {
                        grid: {
                            display: false,
                            drawBorder: false
                        },
                        ticks: {
                            font: {
                                size: 11,
                                family: 'Arial'
                            },
                            color: '#a0aec0',
                            padding: 5
                        }
                    },
                    y: {
                        beginAtZero: true,
                        ticks: {
                            stepSize: 1,
                            font: {
                                size: 11,
                                family: 'Arial'
                            },
                            color: '#a0aec0',
                            padding: 10
                        },
                        grid: {
                            color: '#f7fafc',
                            drawBorder: false,
                            lineWidth: 1
                        }
                    }
                }
            },
            plugins: [htmlLegendPlugin]
        });
    }

    // Gráfica de Estados (Pie)
    const ctxEstados = document.getElementById('chartEstados');
    if (ctxEstados) {
        const estadosData = <?php echo json_encode($estados_result); ?>;
        
        new Chart(ctxEstados, {
            type: 'doughnut',
            data: {
                labels: ['Resueltos', 'En Revisión', 'No Procede', 'Pendientes'],
                datasets: [{
                    data: [
                        parseInt(estadosData.resueltos) || 0,
                        parseInt(estadosData.en_revision) || 0,
                        parseInt(estadosData.no_procede) || 0,
                        parseInt(estadosData.pendientes) || 0
                    ],
                    backgroundColor: [
                        '#28a745',
                        '#ffbf00',
                        '#dc3545',
                        '#6c757d'
                    ],
                    borderWidth: 0,
                    hoverOffset: 4,
                    spacing: 2
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: true,
                plugins: {
                    legend: {
                        display: false
                    },
                    tooltip: {
                        backgroundColor: 'rgba(0, 0, 0, 0.8)',
                        titleColor: '#fff',
                        bodyColor: '#fff',
                        borderColor: 'rgba(255, 255, 255, 0.1)',
                        borderWidth: 1,
                        padding: 10,
                        displayColors: true,
                        cornerRadius: 4,
                        callbacks: {
                            label: function(context) {
                                return context.parsed;
                            }
                        }
                    }
                },
                cutout: '82%'
            }
        });
    }
}
</script>

</body>
</html>
