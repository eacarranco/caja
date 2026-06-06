<?php
class PermissionMiddleware {
    public static function verificar($codigo) {
        AuthMiddleware::verificar();
        if (!RBAC::tienePermiso($_SESSION['usuario_id'], $codigo)) {
            http_response_code(403);
            require_once 'app/views/errors/403.php';
            exit;
        }
    }
}
