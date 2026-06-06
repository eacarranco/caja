<?php
class CSRFMiddleware {
    public static function generarToken() {
        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['csrf_token'];
    }

    public static function validarToken($token) {
        if (empty($_SESSION['csrf_token']) || empty($token)) {
            return false;
        }
        return hash_equals($_SESSION['csrf_token'], $token);
    }

    public static function regenerarToken() {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }

    public static function campoHTML() {
        return '<input type="hidden" name="csrf_token" value="' . self::generarToken() . '">';
    }
}
