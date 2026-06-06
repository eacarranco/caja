<?php
require_once 'app/helpers/Auth.php';
require_once 'app/helpers/RBAC.php';
require_once 'app/helpers/CSRFMiddleware.php';

class AuthMiddleware {
    public static function verificar() {
        $auth = new Auth();
        if (!$auth->isLoggedIn()) {
            header('Location: ' . BASE_URL . '/login');
            exit;
        }
        if (!$auth->is2FAVerified()) {
            header('Location: ' . BASE_URL . '/login/2fa');
            exit;
        }
        if (defined('SESSION_TIMEOUT_MINUTES') && SESSION_TIMEOUT_MINUTES > 0) {
            if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity'] > SESSION_TIMEOUT_MINUTES * 60)) {
                session_destroy();
                header('Location: ' . BASE_URL . '/login');
                exit;
            }
        }
        $_SESSION['last_activity'] = time();
    }
}
