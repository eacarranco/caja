<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="<?= $csrfToken ?? '' ?>">
    <title><?= APP_NAME ?> - <?= $titulo ?? 'Sistema' ?></title>
    <link rel="shortcut icon" href="<?= $baseUrl ?>/public/assets/images/favicon.svg" type="image/x-icon">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/gh/zuramai/mazer@docs/demo/assets/compiled/css/app.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/gh/zuramai/mazer@docs/demo/assets/compiled/css/app-dark.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/gh/zuramai/mazer@docs/demo/assets/compiled/css/iconly.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link href="<?= $baseUrl ?>/public/assets/css/style.css" rel="stylesheet">
    <script>
    var BASE_URL = '<?= $baseUrl ?>';
    <?php if (!empty(PUSHER_APP_KEY)): ?>
    var PUSHER_KEY = '<?= PUSHER_APP_KEY ?>';
    var PUSHER_CLUSTER = '<?= PUSHER_APP_CLUSTER ?>';
    <?php endif; ?>
    </script>
    <?php if (!empty(PUSHER_APP_KEY)): ?>
    <script src="https://js.pusher.com/8.2/pusher.min.js"></script>
    <?php endif; ?>
</head>
<body>
    <script src="https://cdn.jsdelivr.net/gh/zuramai/mazer@docs/demo/assets/static/js/initTheme.js"></script>
    <div id="app">
    <?php
    $loggedIn = isset($_SESSION['usuario_id']) && isset($_SESSION['2fa_verified']) && $_SESSION['2fa_verified'];
    $uid = $_SESSION['usuario_id'] ?? null;
    $notifCount = 0;
    if ($loggedIn) {
        $ndb = Database::getInstance();
        $uid = $_SESSION['usuario_id'];
        $nstmt = $ndb->prepare("SELECT COUNT(*) FROM notificaciones WHERE (id_usuario = ? OR (id_usuario IS NULL AND id_socio IS NULL)) AND leída = FALSE");
        $nstmt->execute([$uid]);
        $notifCount = (int)$nstmt->fetchColumn();
        $cedula = $_SESSION['usuario_cedula'] ?? '';
        if ($cedula) {
            $nstmt = $ndb->prepare("SELECT id_socio FROM socios WHERE cédula = ?");
            $nstmt->execute([$cedula]);
            $idSocio = $nstmt->fetchColumn();
            if ($idSocio) {
                $nstmt = $ndb->prepare("SELECT COUNT(*) FROM notificaciones WHERE id_socio = ? AND leída = FALSE");
                $nstmt->execute([$idSocio]);
                $notifCount += (int)$nstmt->fetchColumn();
            }
        }
    }
    if ($loggedIn):
    ?>
    <div id="sidebar">
        <div class="sidebar-wrapper active">
            <div class="sidebar-header position-relative">
                <div class="d-flex justify-content-between align-items-center">
                    <div class="logo">
                        <a href="<?= $baseUrl ?>/dashboard">
                            <?php
                            $logoSrc = $baseUrl . '/public/assets/images/favicon.svg';
                            try {
                                $logoStmt = $ndb->prepare("SELECT valor FROM parámetros WHERE código = 'logo_sidebar'");
                                $logoStmt->execute();
                                $logoId = $logoStmt->fetchColumn();
                                if ($logoId) $logoSrc = $baseUrl . '/archivo/ver/' . $logoId;
                            } catch (Exception $e) {}
                            ?>
                            <img src="<?= $logoSrc ?>" alt="Logo" style="max-height:40px; width:auto">
                        </a>
                    </div>
                    <div class="theme-toggle d-flex gap-2 align-items-center mt-2">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="currentColor" class="bi bi-sun-fill" viewBox="0 0 16 16"><path d="M8 12a4 4 0 1 0 0-8 4 4 0 0 0 0 8zM8 0a.5.5 0 0 1 .5.5v2a.5.5 0 0 1-1 0v-2A.5.5 0 0 1 8 0zm0 13a.5.5 0 0 1 .5.5v2a.5.5 0 0 1-1 0v-2A.5.5 0 0 1 8 13zm8-5a.5.5 0 0 1-.5.5h-2a.5.5 0 0 1 0-1h2a.5.5 0 0 1 .5.5zM3 8a.5.5 0 0 1-.5.5h-2a.5.5 0 0 1 0-1h2A.5.5 0 0 1 3 8zm10.657-5.657a.5.5 0 0 1 0 .707l-1.414 1.415a.5.5 0 1 1-.707-.708l1.414-1.414a.5.5 0 0 1 .707 0zm-9.193 9.193a.5.5 0 0 1 0 .707L3.05 13.657a.5.5 0 0 1-.707-.707l1.414-1.414a.5.5 0 0 1 .707 0zm9.193 2.121a.5.5 0 0 1-.707 0l-1.414-1.414a.5.5 0 0 1 .707-.707l1.414 1.414a.5.5 0 0 1 0 .707zM4.464 4.465a.5.5 0 0 1-.707 0L2.343 3.05a.5.5 0 0 1 .707-.707l1.414 1.414a.5.5 0 0 1 0 .708z"/></svg>
                        <div class="form-check form-switch fs-6">
                            <input class="form-check-input me-0" type="checkbox" id="toggle-dark" style="cursor: pointer">
                            <label class="form-check-label"></label>
                        </div>
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="currentColor" class="bi bi-moon-stars-fill" viewBox="0 0 16 16"><path d="M6 .278a.768.768 0 0 1 .08.858 7.208 7.208 0 0 0-.878 3.46c0 4.021 3.278 7.277 7.318 7.277.527 0 1.04-.055 1.533-.16a.787.787 0 0 1 .81.316.733.733 0 0 1-.031.893A8.349 8.349 0 0 1 8.344 16C3.734 16 0 12.286 0 7.71 0 4.266 2.114 1.312 5.124.06A.752.752 0 0 1 6 .278z"/><path d="M10.794 3.148a.217.217 0 0 1 .412 0l.387 1.162c.173.518.579.924 1.097 1.097l1.162.387a.217.217 0 0 1 0 .412l-1.162.387a1.734 1.734 0 0 0-1.097 1.097l-.387 1.162a.217.217 0 0 1-.412 0l-.387-1.162A1.734 1.734 0 0 0 9.31 6.593l-1.162-.387a.217.217 0 0 1 0-.412l1.162-.387a1.734 1.734 0 0 0 1.097-1.097l.387-1.162zM13.863.099a.145.145 0 0 1 .274 0l.258.774c.115.346.386.617.732.732l.774.258a.145.145 0 0 1 0 .274l-.774.258a1.156 1.156 0 0 0-.732.732l-.258.774a.145.145 0 0 1-.274 0l-.258-.774a1.156 1.156 0 0 0-.732-.732l-.774-.258a.145.145 0 0 1 0-.274l.774-.258c.346-.115.617-.386.732-.732L13.863.1z"/></svg>
                    </div>
                    <div class="sidebar-toggler x">
                        <a href="#" class="sidebar-hide d-xl-none d-block"><i class="bi bi-x bi-middle"></i></a>
                    </div>
                </div>
            </div>
            <div class="sidebar-menu">
                <ul class="menu">
                    <li class="sidebar-title">Menu</li>
                    <?php
                    $currentUrl = $_GET['url'] ?? '';
                    function mazerActive($prefix) {
                        global $currentUrl;
                        return strpos($currentUrl, $prefix) === 0 ? 'active' : '';
                    }
                    function mazerHasSub($prefix) {
                        global $currentUrl;
                        return strpos($currentUrl, $prefix) === 0 ? 'active' : '';
                    }
                    ?>
                    <li class="sidebar-item <?= ($currentUrl === '' || $currentUrl === 'dashboard') ? 'active' : '' ?>">
                        <a href="<?= $baseUrl ?>/dashboard" class="sidebar-link">
                            <i class="bi bi-grid-fill"></i>
                            <span>Dashboard</span>
                        </a>
                    </li>
                    <?php if ($uid && RBAC::tienePermiso($uid, 'socio.consultar')): ?>
                    <li class="sidebar-item <?= mazerActive('socio') ?>">
                        <a href="<?= $baseUrl ?>/socio/listar" class="sidebar-link">
                            <i class="bi bi-people-fill"></i>
                            <span>Socios</span>
                        </a>
                    </li>
                    <?php endif; ?>
                    <?php if ($uid && RBAC::tienePermiso($uid, 'cobro.aporte')): ?>
                    <li class="sidebar-item <?= mazerActive('sesion') ?>">
                        <a href="<?= $baseUrl ?>/sesion/listar" class="sidebar-link">
                            <i class="bi bi-calendar-check-fill"></i>
                            <span>Sesiones</span>
                        </a>
                    </li>
                    <li class="sidebar-item <?= mazerActive('cobro') ?>">
                        <a href="<?= $baseUrl ?>/cobro/listar" class="sidebar-link">
                            <i class="bi bi-cash-coin"></i>
                            <span>Cobros</span>
                        </a>
                    </li>
                    <li class="sidebar-item <?= mazerActive('asistencia') ?>">
                        <a href="<?= $baseUrl ?>/asistencia/listar" class="sidebar-link">
                            <i class="bi bi-clipboard-check-fill"></i>
                            <span>Asistencias</span>
                        </a>
                    </li>
                    <li class="sidebar-item <?= mazerActive('retiro') ?>">
                        <a href="<?= $baseUrl ?>/retiro/listar" class="sidebar-link">
                            <i class="bi bi-cash-stack"></i>
                            <span>Retiros</span>
                        </a>
                    </li>
                    <?php endif; ?>
                    <?php if ($uid && RBAC::tienePermiso($uid, 'cobro.desembolso')): ?>
                    <li class="sidebar-item <?= mazerActive('credito') ?>">
                        <a href="<?= $baseUrl ?>/credito/listar" class="sidebar-link">
                            <i class="bi bi-bank"></i>
                            <span>Créditos</span>
                        </a>
                    </li>
                    <?php endif; ?>
                    <?php if ($uid && RBAC::tienePermiso($uid, 'cobro.inversión')): ?>
                    <li class="sidebar-item <?= mazerActive('inversion') ?>">
                        <a href="<?= $baseUrl ?>/inversion/listar" class="sidebar-link">
                            <i class="bi bi-piggy-bank-fill"></i>
                            <span>Inversiones</span>
                        </a>
                    </li>
                    <?php endif; ?>
                    <?php if ($uid && RBAC::tienePermiso($uid, 'param.financiero')): ?>
                    <li class="sidebar-item has-sub <?= mazerHasSub('producto') ?>">
                        <a href="#" class="sidebar-link">
                            <i class="bi bi-box-seam-fill"></i>
                            <span>Productos</span>
                        </a>
                        <ul class="submenu <?= mazerHasSub('producto') ? 'active' : '' ?>">
                            <li class="submenu-item <?= (strpos($currentUrl, 'producto/listar') === 0 || $currentUrl === 'producto') ? 'active' : '' ?>">
                                <a href="<?= $baseUrl ?>/producto/listar" class="submenu-link">Listar</a>
                            </li>
                            <li class="submenu-item <?= mazerActive('producto/registrar') ?>">
                                <a href="<?= $baseUrl ?>/producto/registrar" class="submenu-link">Nuevo</a>
                            </li>
                        </ul>
                    </li>
                    <?php endif; ?>
                    <?php if ($uid && RBAC::tienePermiso($uid, 'cálculo.intereses')): ?>
                    <li class="sidebar-item <?= mazerActive('calculo') ?>">
                        <a href="<?= $baseUrl ?>/calculo/simulador" class="sidebar-link">
                            <i class="bi bi-calculator-fill"></i>
                            <span>Cálculos</span>
                        </a>
                    </li>
                    <?php endif; ?>
                    <?php if ($uid && (RBAC::tienePermiso($uid, 'reporte.cobros') || RBAC::tienePermiso($uid, 'reporte.financiero') || RBAC::tienePermiso($uid, 'reporte.socios'))): ?>
                    <li class="sidebar-item <?= mazerActive('reporte') ?>">
                        <a href="<?= $baseUrl ?>/reporte/listar" class="sidebar-link">
                            <i class="bi bi-file-earmark-bar-graph-fill"></i>
                            <span>Reportes</span>
                        </a>
                    </li>
                    <?php endif; ?>
                    <?php if ($uid && RBAC::tienePermiso($uid, 'param.roles')): ?>
                    <li class="sidebar-title">Administración</li>
                    <?php $paramSubActive = (strpos($currentUrl, 'parametro') === 0 || strpos($currentUrl, 'imagen') === 0) ? 'active' : ''; ?>
                    <li class="sidebar-item has-sub <?= $paramSubActive ?>">
                        <a href="#" class="sidebar-link">
                            <i class="bi bi-gear-fill"></i>
                            <span>Configuración</span>
                        </a>
                        <ul class="submenu <?= $paramSubActive ?>">
                            <li class="submenu-item <?= mazerActive('parametro') ?>">
                                <a href="<?= $baseUrl ?>/parametro/listar" class="submenu-link">Parámetros</a>
                            </li>
                            <li class="submenu-item <?= mazerActive('imagen') ?>">
                                <a href="<?= $baseUrl ?>/imagen/index" class="submenu-link">Imagen corporativa</a>
                            </li>
                        </ul>
                    </li>
                    <li class="sidebar-item <?= mazerActive('usuario') ?>">
                        <a href="<?= $baseUrl ?>/usuario/listar" class="sidebar-link">
                            <i class="bi bi-people-fill"></i>
                            <span>Usuarios</span>
                        </a>
                    </li>
                    <li class="sidebar-item <?= mazerActive('rol') ?>">
                        <a href="<?= $baseUrl ?>/rol/listar" class="sidebar-link">
                            <i class="bi bi-shield-fill-check"></i>
                            <span>Roles</span>
                        </a>
                    </li>
                    <li class="sidebar-item <?= mazerActive('catalogo') ?>">
                        <a href="<?= $baseUrl ?>/catalogo/provincias" class="sidebar-link">
                            <i class="bi bi-journal-text"></i>
                            <span>Catálogos</span>
                        </a>
                    </li>
                    <?php endif; ?>
                    <li class="sidebar-title">General</li>
                    <li class="sidebar-item <?= mazerActive('multa') ?>">
                        <a href="<?= $baseUrl ?>/multa/listar" class="sidebar-link">
                            <i class="bi bi-exclamation-triangle-fill"></i>
                            <span>Multas</span>
                        </a>
                    </li>
                    <li class="sidebar-item <?= mazerActive('portal') ?>">
                        <a href="<?= $baseUrl ?>/portal" class="sidebar-link">
                            <i class="bi bi-person-circle"></i>
                            <span>Mi portal</span>
                        </a>
                    </li>
                    <li class="sidebar-item <?= mazerActive('password') ?>">
                        <a href="<?= $baseUrl ?>/password" class="sidebar-link">
                            <i class="bi bi-key-fill"></i>
                            <span>Contraseña</span>
                        </a>
                    </li>
                    <li class="sidebar-item">
                        <a href="<?= $baseUrl ?>/auth/logout" class="sidebar-link">
                            <i class="bi bi-box-arrow-right"></i>
                            <span>Salir</span>
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </div>
    <div id="main">
        <header class="mb-3 d-flex justify-content-between align-items-center">
            <a href="#" class="burger-btn d-block d-xl-none">
                <i class="bi bi-justify fs-3"></i>
            </a>
            <div class="d-flex align-items-center gap-3 ms-auto">
                <a href="<?= $baseUrl ?>/notificacion/listar" class="position-relative text-secondary" title="Notificaciones">
                    <i class="bi bi-bell-fill fs-5"></i>
                    <span id="notifBadge" class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger <?= $notifCount > 0 ? '' : 'd-none' ?>" style="font-size:10px"><?= min($notifCount, 99) ?></span>
                </a>
                <div class="dropdown">
                    <a href="#" class="dropdown-toggle text-decoration-none text-secondary d-flex align-items-center gap-2" data-bs-toggle="dropdown" aria-expanded="false">
                        <span class="avatar avatar-sm">
                            <span class="avatar-content bg-primary text-white fw-bold"><?= strtoupper(substr($_SESSION['usuario_nombres'] ?? 'U', 0, 1)) ?></span>
                        </span>
                        <span class="d-none d-md-inline"><?= $_SESSION['usuario_nombres'] . ' ' . ($_SESSION['usuario_apellidos'] ?? '') ?></span>
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <li><a class="dropdown-item" href="<?= $baseUrl ?>/portal"><i class="bi bi-person-circle me-2"></i>Mi portal</a></li>
                        <li><a class="dropdown-item" href="<?= $baseUrl ?>/password"><i class="bi bi-key me-2"></i>Contraseña</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item text-danger" href="<?= $baseUrl ?>/auth/logout"><i class="bi bi-box-arrow-right me-2"></i>Salir</a></li>
                    </ul>
                </div>
            </div>
        </header>
        <?php if (isset($subtitulo)): ?>
        <div class="page-heading">
            <p class="text-subtitle text-muted"><?= htmlspecialchars($subtitulo) ?></p>
        </div>
        <?php endif; ?>
        <div class="page-content">
        <?php
        $flashTypes = ['success' => 'success', 'error' => 'danger', 'warning' => 'warning', 'info' => 'info'];
        foreach ($flashTypes as $key => $bsClass):
            if (isset($_SESSION[$key]) && !empty($_SESSION[$key])):
        ?>
        <div class="alert alert-<?= $bsClass ?> alert-dismissible fade show" role="alert">
            <?= htmlspecialchars($_SESSION[$key]) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Cerrar"></button>
        </div>
        <?php
                unset($_SESSION[$key]);
            endif;
        endforeach;
        ?>
    <?php endif; ?>
