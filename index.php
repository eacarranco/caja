<?php
define('ROOT_PATH', __DIR__);

require_once ROOT_PATH . '/config/app.php';
require_once ROOT_PATH . '/config/database.php';
require_once ROOT_PATH . '/app/helpers/UUIDGenerator.php';
require_once ROOT_PATH . '/app/helpers/Auth.php';
require_once ROOT_PATH . '/app/helpers/RBAC.php';
require_once ROOT_PATH . '/app/helpers/Validator.php';
require_once ROOT_PATH . '/app/helpers/CedulaEcuador.php';
require_once ROOT_PATH . '/app/helpers/CSRFMiddleware.php';
require_once ROOT_PATH . '/app/helpers/FileManager.php';
require_once ROOT_PATH . '/config/pusher.php';

session_start();
header('Content-Type: text/html; charset=utf-8');

if (isset($_SESSION['usuario_id']) && isset($_SESSION['last_activity'])) {
    $inactive = time() - $_SESSION['last_activity'];
    if ($inactive > SESSION_TIMEOUT_MINUTES * 60) {
        session_destroy();
        session_start();
    }
}
$_SESSION['last_activity'] = time();

$url = $_GET['url'] ?? '';
$url = rtrim($url, '/');
$urlParts = explode('/', $url);

$routeMap = [
    '' => ['controller' => 'DashboardController', 'method' => 'index'],
    'dashboard' => ['controller' => 'DashboardController', 'method' => 'index'],
    'documento' => ['controller' => 'DocumentoController', 'method' => 'comprobante'],
    'documento/estadoCuenta' => ['controller' => 'DocumentoController', 'method' => 'estadoCuenta'],
    'documento/constanciaSocio' => ['controller' => 'DocumentoController', 'method' => 'constanciaSocio'],
    'documento/libreDeuda' => ['controller' => 'DocumentoController', 'method' => 'libreDeuda'],
    'documento/actaCierre' => ['controller' => 'DocumentoController', 'method' => 'actaCierre'],
    'notificacion' => ['controller' => 'NotificacionController', 'method' => 'listar'],
    'notificacion/contar' => ['controller' => 'NotificacionController', 'method' => 'contar'],
    'notificacion/reglas' => ['controller' => 'NotificacionController', 'method' => 'reglas'],
    'notificacion/reglas/guardar' => ['controller' => 'NotificacionController', 'method' => 'reglasGuardar'],
    'login' => ['controller' => 'AuthController', 'method' => 'login'],
    'logout' => ['controller' => 'AuthController', 'method' => 'logout'],
    'password' => ['controller' => 'AuthController', 'method' => 'password'],
    '2fa' => ['controller' => 'AuthController', 'method' => '_2fa'],
    'reenviarPIN' => ['controller' => 'AuthController', 'method' => 'reenviarPIN'],
    'socio' => ['controller' => 'SocioController', 'method' => 'listar'],
    'socio/registrar' => ['controller' => 'SocioController', 'method' => 'registrar'],
    'socio/editar' => ['controller' => 'SocioController', 'method' => 'editar'],
    'socio/ver' => ['controller' => 'SocioController', 'method' => 'ver'],
    'socio/cambiarEstado' => ['controller' => 'SocioController', 'method' => 'cambiarEstado'],
    'socio/subirDocumento' => ['controller' => 'SocioController', 'method' => 'subirDocumento'],
    'parametro' => ['controller' => 'ParametroController', 'method' => 'listar'],
    'usuario' => ['controller' => 'UsuarioController', 'method' => 'listar'],
    'rol' => ['controller' => 'RolController', 'method' => 'listar'],
    'catalogo' => ['controller' => 'CatalogoController', 'method' => 'provincias'],
    'catalogo/provincias' => ['controller' => 'CatalogoController', 'method' => 'provincias'],
    'catalogo/cantones' => ['controller' => 'CatalogoController', 'method' => 'cantones'],
    'catalogo/entidades' => ['controller' => 'CatalogoController', 'method' => 'entidades'],
    'catalogo/agregar' => ['controller' => 'CatalogoController', 'method' => 'agregar'],
    'catalogo/editar' => ['controller' => 'CatalogoController', 'method' => 'editar'],
    'catalogo/eliminar' => ['controller' => 'CatalogoController', 'method' => 'eliminar'],
    'imagen' => ['controller' => 'ImagenController', 'method' => 'index'],
    'imagen/subirImagenParam' => ['controller' => 'ImagenController', 'method' => 'subirImagenParam'],
    'producto' => ['controller' => 'ProductoController', 'method' => 'listar'],
    'producto/registrar' => ['controller' => 'ProductoController', 'method' => 'registrar'],
    'producto/editar' => ['controller' => 'ProductoController', 'method' => 'editar'],
    'producto/toggleEstado' => ['controller' => 'ProductoController', 'method' => 'toggleEstado'],
    'sesion' => ['controller' => 'SesionController', 'method' => 'listar'],
    'sesion/abrir' => ['controller' => 'SesionController', 'method' => 'abrir'],
    'sesion/checkin' => ['controller' => 'SesionController', 'method' => 'checkin'],
    'cobro' => ['controller' => 'CobroController', 'method' => 'listar'],
    'cobro/registrar' => ['controller' => 'CobroController', 'method' => 'registrar'],
    'cobro/historialSesion' => ['controller' => 'CobroController', 'method' => 'historialSesion'],
    'cobro/anular' => ['controller' => 'CobroController', 'method' => 'anular'],
    'calculo' => ['controller' => 'CalculoController', 'method' => 'simulador'],
    'calculo/simulador' => ['controller' => 'CalculoController', 'method' => 'simulador'],
    'calculo/generarTabla' => ['controller' => 'CalculoController', 'method' => 'generarTabla'],
    'calculo/excedentes' => ['controller' => 'CalculoController', 'method' => 'excedentes'],
    'calculo/aprobarExcedentes' => ['controller' => 'CalculoController', 'method' => 'aprobarExcedentes'],
    'calculo/interesesAhorro' => ['controller' => 'CalculoController', 'method' => 'interesesAhorro'],
    'reporte' => ['controller' => 'ReporteController', 'method' => 'listar'],
    'reporte/socios' => ['controller' => 'ReporteController', 'method' => 'socios'],
    'reporte/financiero' => ['controller' => 'ReporteController', 'method' => 'financiero'],
    'reporte/morosidad' => ['controller' => 'ReporteController', 'method' => 'morosidad'],
    'reporte/cobros' => ['controller' => 'ReporteController', 'method' => 'cobros'],
    'reporte/historialOperaciones' => ['controller' => 'ReporteController', 'method' => 'historialOperaciones'],
    'reporte/certificados' => ['controller' => 'ReporteController', 'method' => 'certificados'],
    'credito' => ['controller' => 'CreditoController', 'method' => 'listar'],
    'credito/solicitar' => ['controller' => 'CreditoController', 'method' => 'solicitar'],
    'credito/ver' => ['controller' => 'CreditoController', 'method' => 'ver'],
    'credito/aprobar' => ['controller' => 'CreditoController', 'method' => 'aprobar'],
    'credito/desembolsar' => ['controller' => 'CreditoController', 'method' => 'desembolsar'],
    'credito/rechazar' => ['controller' => 'CreditoController', 'method' => 'rechazar'],
    'credito/calcularMora' => ['controller' => 'CreditoController', 'method' => 'calcularMora'],
    'credito/bandejaAprobados' => ['controller' => 'CreditoController', 'method' => 'bandejaAprobados'],
    'credito/generarSolicitudPdf' => ['controller' => 'CreditoController', 'method' => 'generarSolicitudPdf'],
    'credito/subirActaFirmada' => ['controller' => 'CreditoController', 'method' => 'subirActaFirmada'],
    'credito/ponerEnEspera' => ['controller' => 'CreditoController', 'method' => 'ponerEnEspera'],
    'inversion' => ['controller' => 'InversionController', 'method' => 'listar'],
    'inversion/apertura' => ['controller' => 'InversionController', 'method' => 'apertura'],
    'inversion/retirar' => ['controller' => 'InversionController', 'method' => 'retirar'],
    'inversion/cerrarVencidas' => ['controller' => 'InversionController', 'method' => 'cerrarVencidas'],
    'inversion/depositar' => ['controller' => 'InversionController', 'method' => 'depositar'],
    'portal' => ['controller' => 'PortalController', 'method' => 'index'],
    'portal/historial' => ['controller' => 'PortalController', 'method' => 'historial'],
    'portal/multas' => ['controller' => 'PortalController', 'method' => 'multas'],
    'portal/asistencias' => ['controller' => 'PortalController', 'method' => 'asistencias'],
    'portal/notificaciones' => ['controller' => 'PortalController', 'method' => 'notificaciones'],
    'portal/password' => ['controller' => 'PortalController', 'method' => 'password'],
    'portal/solicitarRetiro' => ['controller' => 'PortalController', 'method' => 'solicitarRetiro'],
    'portal/pagar' => ['controller' => 'PortalController', 'method' => 'pagar'],
    'portal/solicitarCredito' => ['controller' => 'PortalController', 'method' => 'solicitarCredito'],
    'portal/simularCredito' => ['controller' => 'PortalController', 'method' => 'simularCredito'],

    'portal/inversion' => ['controller' => 'PortalController', 'method' => 'inversion'],
    'portal/detalleCapitalInversion' => ['controller' => 'PortalController', 'method' => 'detalleCapitalInversion'],
    'portal/detalleAhorro' => ['controller' => 'PortalController', 'method' => 'detalleAhorro'],
    'retiro' => ['controller' => 'RetiroController', 'method' => 'listar'],
    'retiro/aprobar' => ['controller' => 'RetiroController', 'method' => 'aprobar'],
    'retiro/rechazar' => ['controller' => 'RetiroController', 'method' => 'rechazar'],
    'asistencia' => ['controller' => 'AsistenciaController', 'method' => 'listar'],
    'asistencia/justificar' => ['controller' => 'AsistenciaController', 'method' => 'justificar'],
    'asistencia/aprobarJustificacion' => ['controller' => 'AsistenciaController', 'method' => 'aprobarJustificacion'],
    'multa' => ['controller' => 'MultaController', 'method' => 'listar'],
    'multa/ver' => ['controller' => 'MultaController', 'method' => 'ver'],
    'multa/justificar' => ['controller' => 'MultaController', 'method' => 'justificar'],
    'multa/aprobarJustificacion' => ['controller' => 'MultaController', 'method' => 'aprobarJustificacion'],
    'multa/marcarPagada' => ['controller' => 'MultaController', 'method' => 'marcarPagada'],
    'archivo/ver' => ['controller' => 'ArchivoController', 'method' => 'ver'],
    'archivo/descargar' => ['controller' => 'ArchivoController', 'method' => 'descargar'],
    'archivo/listarPorEntidad' => ['controller' => 'ArchivoController', 'method' => 'listarPorEntidad'],
    'archivo/eliminar' => ['controller' => 'ArchivoController', 'method' => 'eliminar'],
];

$controllerName = 'AuthController';
$methodName = 'index';
$params = [];

if ($url !== '') {
    $routeKey = $urlParts[0] . (isset($urlParts[1]) ? '/' . $urlParts[1] : '');
    if (isset($routeMap[$routeKey])) {
        $controllerName = $routeMap[$routeKey]['controller'];
        $methodName = $routeMap[$routeKey]['method'];
        $params = array_slice($urlParts, 2);
    } elseif (isset($routeMap[$urlParts[0]])) {
        $controllerName = $routeMap[$urlParts[0]]['controller'];
        $methodName = $urlParts[1] ?? $routeMap[$urlParts[0]]['method'];
        $params = array_slice($urlParts, 2);
    } else {
        $controllerName = ucfirst($urlParts[0]) . 'Controller';
        $methodName = $urlParts[1] ?? 'index';
        $params = array_slice($urlParts, 2);
    }
} elseif (isset($routeMap[''])) {
    $controllerName = $routeMap['']['controller'];
    $methodName = $routeMap['']['method'];
}

$controllerFile = ROOT_PATH . '/app/controllers/' . $controllerName . '.php';
try {
    if (file_exists($controllerFile)) {
        require_once ROOT_PATH . '/app/controllers/BaseController.php';
        require_once $controllerFile;
        $controller = new $controllerName();
        if (method_exists($controller, $methodName)) {
            $controller->$methodName(...$params);
        } else {
            http_response_code(404);
            require_once ROOT_PATH . '/app/views/errors/404.php';
        }
    } else {
        http_response_code(404);
        require_once ROOT_PATH . '/app/views/errors/404.php';
    }
} catch (Throwable $e) {
    http_response_code(500);
    if (DEBUG) {
        echo "<h2>Error interno</h2><p><strong>" . htmlspecialchars($e->getMessage()) . "</strong></p>";
        echo "<pre>" . htmlspecialchars($e->getTraceAsString()) . "</pre>";
    } else {
        require_once ROOT_PATH . '/app/views/errors/500.php';
    }
}
