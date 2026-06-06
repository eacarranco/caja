<?php
class BaseController {
    protected $db;
    protected $auth;
    protected $rbac;

    public function __construct() {
        $this->db = Database::getInstance();
        $this->auth = new Auth();
        $this->rbac = new RBAC();
    }

    protected function requireAuth() {
        if (!$this->auth->isLoggedIn()) {
            header('Location: ' . BASE_URL . '/login');
            exit;
        }
        if (!$this->auth->is2FAVerified()) {
            header('Location: ' . BASE_URL . '/login/2fa');
            exit;
        }
    }

    protected function requirePermission($codigo) {
        $this->requireAuth();
        if (!RBAC::tienePermiso($_SESSION['usuario_id'], $codigo)) {
            http_response_code(403);
            $this->render('errors/403', ['titulo' => 'Sin permiso']);
            exit;
        }
    }

    protected function render($view, $data = []) {
        extract($data);
        $baseUrl = BASE_URL;
        $csrfToken = CSRFMiddleware::generarToken();
        require_once 'app/views/layouts/header.php';
        require_once "app/views/$view.php";
        require_once 'app/views/layouts/footer.php';
    }

    protected function renderPartial($view, $data = []) {
        extract($data);
        require_once "app/views/$view.php";
    }

    protected function json($data, $code = 200) {
        http_response_code($code);
        header('Content-Type: application/json');
        echo json_encode($data, JSON_UNESCAPED_UNICODE);
        exit;
    }

    protected function validateCSRF($token = null) {
        if ($token === null) $token = $_POST['csrf_token'] ?? '';
        if (!CSRFMiddleware::validarToken($token)) {
            $this->json(['error' => 'CSRF inválido'], 403);
        }
    }

    protected function redirect($url) {
        header('Location: ' . BASE_URL . $url);
        exit;
    }

    protected function getParam($key, $default = null) {
        return $_GET[$key] ?? $default;
    }

    protected function postParam($key, $default = null) {
        return $_POST[$key] ?? $default;
    }

    protected function getPage() {
        return max(1, intval($this->getParam('page', 1)));
    }

    protected function historialInsert($idSocio, $tipoOperacion, $monto, $idReferencia = null, $idSesion = null) {
        $stmt = $this->db->prepare("INSERT INTO historial_operaciones
            (id_operación, id_socio, tipo_operación, monto, id_referencia, id_sesión, id_usuario_registra, ip_registro)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([
            UUIDGenerator::generar(),
            $idSocio,
            $tipoOperacion,
            $monto,
            $idReferencia,
            $idSesion,
            $_SESSION['usuario_id'] ?? null,
            $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1'
        ]);
    }

    protected function mapearTipoHistorial($tipoCobro) {
        $mapa = [
            'aporte_obligatorio' => 'aporte_obligatorio',
            'aporte_excedente' => 'aporte_excedente',
            'cuota_crédito' => 'pago_cuota',
            'multa' => 'pago_multa',
            'inversión' => 'inversión_apertura',
            'desembolso' => 'desembolso_crédito',
            'interés' => 'interés_pagado',
        ];
        return $mapa[$tipoCobro] ?? null;
    }
}
