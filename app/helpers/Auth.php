<?php
class Auth {
    public function login($cedula, $password) {
        $db = Database::getInstance();
        $stmt = $db->prepare("SELECT * FROM usuarios WHERE cédula = ? AND activo = TRUE");
        $stmt->execute([$cedula]);
        $user = $stmt->fetch();

        if (!$user) return false;

        if ($user['bloqueado_hasta'] !== null) {
            $bloqueo = strtotime($user['bloqueado_hasta']);
            if (time() < $bloqueo) {
                return false;
            }
            $stmt = $db->prepare("UPDATE usuarios SET intentos_fallidos = 0, bloqueado_hasta = NULL WHERE id_usuario = ?");
            $stmt->execute([$user['id_usuario']]);
        }

        if (!password_verify($password, $user['contraseña'])) {
            $intentos = $user['intentos_fallidos'] + 1;
            if ($intentos >= MAX_LOGIN_ATTEMPTS) {
                $bloqueoHasta = date('Y-m-d H:i:s', time() + (BLOCK_MINUTES * 60));
                $stmt = $db->prepare("UPDATE usuarios SET intentos_fallidos = ?, bloqueado_hasta = ? WHERE id_usuario = ?");
                $stmt->execute([$intentos, $bloqueoHasta, $user['id_usuario']]);
            } else {
                $stmt = $db->prepare("UPDATE usuarios SET intentos_fallidos = ? WHERE id_usuario = ?");
                $stmt->execute([$intentos, $user['id_usuario']]);
            }
            return false;
        }

        $stmt = $db->prepare("UPDATE usuarios SET intentos_fallidos = 0, fecha_último_acceso = NOW() WHERE id_usuario = ?");
        $stmt->execute([$user['id_usuario']]);

        $_SESSION['usuario_id'] = $user['id_usuario'];
        $_SESSION['usuario_nombres'] = $user['nombres'];
        $_SESSION['usuario_apellidos'] = $user['apellidos'];
        $_SESSION['usuario_cedula'] = $user['cédula'];
        $_SESSION['2fa_required'] = $user['_2fa_obligatorio'];
        $_SESSION['2fa_verified'] = false;

        return true;
    }

    public function generarPIN() {
        $pin = '';
        for ($i = 0; $i < PIN_2FA_DIGITS; $i++) {
            $pin .= random_int(0, 9);
        }
        $_SESSION['2fa_pin'] = password_hash($pin, PASSWORD_BCRYPT);
        $_SESSION['2fa_pin_expira'] = time() + (PIN_2FA_EXPIRATION_MIN * 60);
        $_SESSION['2fa_intentos'] = 0;
        $_SESSION['2fa_reenvios'] = ($_SESSION['2fa_reenvios'] ?? 0) + 1;
        return $pin;
    }

    public function verificarPIN($pinIngresado) {
        if (!isset($_SESSION['2fa_pin']) || !isset($_SESSION['2fa_pin_expira'])) {
            return false;
        }
        if (time() > $_SESSION['2fa_pin_expira']) {
            return false;
        }
        if (!password_verify($pinIngresado, $_SESSION['2fa_pin'])) {
            $_SESSION['2fa_intentos'] = ($_SESSION['2fa_intentos'] ?? 0) + 1;
            if ($_SESSION['2fa_intentos'] >= MAX_LOGIN_ATTEMPTS) {
                $db = Database::getInstance();
                $stmt = $db->prepare("UPDATE usuarios SET bloqueado_hasta = ? WHERE id_usuario = ?");
                $bloqueo = date('Y-m-d H:i:s', time() + (BLOCK_MINUTES * 60));
                $stmt->execute([$bloqueo, $_SESSION['usuario_id']]);
                $this->logout();
            }
            return false;
        }
        $_SESSION['2fa_verified'] = true;
        unset($_SESSION['2fa_pin'], $_SESSION['2fa_pin_expira']);
        return true;
    }

    public function puedeReenviarPIN() {
        return ($_SESSION['2fa_reenvios'] ?? 0) < MAX_PIN_RESEND_HOUR;
    }

    public function isLoggedIn() {
        return isset($_SESSION['usuario_id']);
    }

    public function is2FAVerified() {
        return !isset($_SESSION['2fa_required']) || $_SESSION['2fa_required'] === false || $_SESSION['2fa_verified'] === true;
    }

    public function getDashboardURL() {
        $db = Database::getInstance();
        $stmt = $db->prepare("SELECT r.nombre FROM roles_usuarios ru JOIN roles r ON ru.id_rol = r.id_rol WHERE ru.id_usuario = ? LIMIT 1");
        $stmt->execute([$_SESSION['usuario_id']]);
        $rol = $stmt->fetchColumn();

        $rutas = [
            'Administrador Técnico' => '/dashboard',
            'Presidente' => '/dashboard',
            'Analista Financiero' => '/dashboard',
            'Tesorero' => '/dashboard',
            'Asistente de Tesorería' => '/dashboard',
            'Secretario/a' => '/dashboard',
            'Socio' => '/portal',
        ];
        return $rutas[$rol] ?? '/';
    }

    public function logout() {
        session_destroy();
        header('Location: ' . BASE_URL . '/login');
        exit;
    }
}
