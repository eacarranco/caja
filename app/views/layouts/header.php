<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="<?= $csrfToken ?? '' ?>">
    <title><?= APP_NAME ?> - <?= $titulo ?? 'Sistema' ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link href="<?= $baseUrl ?>/public/assets/css/style.css" rel="stylesheet">
    <?php if (!empty(PUSHER_APP_KEY)): ?>
    <script src="https://js.pusher.com/8.2/pusher.min.js"></script>
    <?php endif; ?>
</head>
<body>
    <?php
    $loggedIn = isset($_SESSION['usuario_id']) && isset($_SESSION['2fa_verified']) && $_SESSION['2fa_verified'];
    $notifCount = 0;
    if ($loggedIn) {
        $ndb = Database::getInstance();
        $nstmt = $ndb->prepare("SELECT COUNT(*) FROM notificaciones WHERE (id_usuario = ? OR id_usuario IS NULL) AND leída = FALSE");
        $nstmt->execute([$_SESSION['usuario_id']]);
        $notifCount = $nstmt->fetchColumn();
    }
    ?>
    <?php if ($loggedIn): ?>
    <button class="sidebar-toggle" id="sidebarToggle" aria-label="Menú"><i class="bi bi-list"></i></button>
    <div class="sidebar-overlay" id="sidebarOverlay"></div>
    <div class="sidebar" id="sidebar">
        <div class="logo d-flex align-items-center gap-2">
            <div class="flex-grow-1">
                <h5 class="mb-0"><?= APP_NAME ?></h5>
                <small class="text-muted"><?= $_SESSION['usuario_nombres'] . ' ' . $_SESSION['usuario_apellidos'] ?></small>
            </div>
            <a href="<?= $baseUrl ?>/notificacion/listar" class="text-light position-relative" title="Notificaciones">
                <i class="bi bi-bell fs-5"></i>
                <?php if ($notifCount > 0): ?>
                <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger" style="font-size:8px"><?= min($notifCount, 99) ?></span>
                <?php endif; ?>
            </a>
        </div>
        <?php
        $uid = $_SESSION['usuario_id'] ?? null;
        ?><nav class="nav flex-column">
            <a class="nav-link" href="<?= $baseUrl ?>/dashboard"><i class="bi bi-house-door"></i> Dashboard</a>
            <a class="nav-link" href="<?= $baseUrl ?>/portal"><i class="bi bi-person"></i> Mi portal</a>
            <?php if ($uid && RBAC::tienePermiso($uid, 'socio.consultar')): ?>
            <a class="nav-link" href="<?= $baseUrl ?>/socio/listar"><i class="bi bi-people"></i> Socios</a>
            <?php endif; ?>
            <?php if ($uid && RBAC::tienePermiso($uid, 'param.financiero')): ?>
            <a class="nav-link" href="<?= $baseUrl ?>/producto/listar"><i class="bi bi-box-seam"></i> Productos</a>
            <?php endif; ?>
            <?php if ($uid && RBAC::tienePermiso($uid, 'cobro.desembolso')): ?>
            <a class="nav-link" href="<?= $baseUrl ?>/credito/listar"><i class="bi bi-bank"></i> Créditos</a>
            <?php endif; ?>
            <?php if ($uid && RBAC::tienePermiso($uid, 'cobro.inversión')): ?>
            <a class="nav-link" href="<?= $baseUrl ?>/inversion/listar"><i class="bi bi-piggy-bank"></i> Inversiones</a>
            <?php endif; ?>
            <?php if ($uid && RBAC::tienePermiso($uid, 'cobro.aporte')): ?>
            <a class="nav-link" href="<?= $baseUrl ?>/sesion/listar"><i class="bi bi-calendar-check"></i> Sesiones</a>
            <a class="nav-link" href="<?= $baseUrl ?>/cobro/listar"><i class="bi bi-cash-coin"></i> Cobros</a>
            <a class="nav-link" href="<?= $baseUrl ?>/asistencia/listar"><i class="bi bi-clipboard-check"></i> Asistencias</a>
            <a class="nav-link" href="<?= $baseUrl ?>/retiro/listar"><i class="bi bi-cash-stack"></i> Retiros</a>
            <?php endif; ?>
            <?php if ($uid && RBAC::tienePermiso($uid, 'cálculo.intereses')): ?>
            <a class="nav-link" href="<?= $baseUrl ?>/calculo/simulador"><i class="bi bi-calculator"></i> Cálculos</a>
            <?php endif; ?>
            <?php if ($uid && (RBAC::tienePermiso($uid, 'reporte.cobros') || RBAC::tienePermiso($uid, 'reporte.financiero'))): ?>
            <a class="nav-link" href="<?= $baseUrl ?>/reporte/listar"><i class="bi bi-file-earmark-bar-graph"></i> Reportes</a>
            <?php endif; ?>
            <?php if ($uid && RBAC::tienePermiso($uid, 'param.roles')): ?>
            <div class="nav-link"><i class="bi bi-gear"></i> Parametrización</div>
            <a class="nav-link ps-4 small" href="<?= $baseUrl ?>/parametro/listar">Parámetros</a>
            <a class="nav-link ps-4 small" href="<?= $baseUrl ?>/usuario/listar">Usuarios</a>
            <a class="nav-link ps-4 small" href="<?= $baseUrl ?>/rol/listar">Roles y permisos</a>
            <a class="nav-link ps-4 small" href="<?= $baseUrl ?>/catalogo/provincias">Catálogos</a>
            <a class="nav-link ps-4 small" href="<?= $baseUrl ?>/imagen/index">Imagen corporativa</a>
            <?php endif; ?>
            <hr class="text-secondary">
            <a class="nav-link" href="<?= $baseUrl ?>/multa/listar"><i class="bi bi-exclamation-triangle"></i> Multas</a>
            <a class="nav-link" href="<?= $baseUrl ?>/password"><i class="bi bi-key"></i> Cambiar contraseña</a>
            <a class="nav-link" href="<?= $baseUrl ?>/auth/logout"><i class="bi bi-box-arrow-left"></i> Cerrar sesión</a>
        </nav>
    </div>
    <?php endif; ?>
    <div class="<?= (isset($_SESSION['usuario_id']) && isset($_SESSION['2fa_verified']) && $_SESSION['2fa_verified']) ? 'main-content' : '' ?>">
        <div class="toast-container"></div>
