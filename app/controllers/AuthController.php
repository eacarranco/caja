<?php
require_once ROOT_PATH . '/app/helpers/EmailHelper.php';
class AuthController extends BaseController {

    public function index() {
        $this->login();
    }

    public function login() {
        if ($this->auth->isLoggedIn() && $this->auth->is2FAVerified()) {
            $this->redirect($this->auth->getDashboardURL());
        }

        $error = '';
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->validateCSRF();
            $cedula = trim($_POST['cedula'] ?? '');
            $password = $_POST['password'] ?? '';

            if (empty($cedula) || empty($password)) {
                $error = 'Todos los campos son obligatorios';
            } elseif ($this->auth->login($cedula, $password)) {
                if ($_SESSION['2fa_required']) {
                    $pin = $this->auth->generarPIN();
                    $this->enviarPINCorreo($pin);
                    $this->redirect('/login/2fa');
                }
                $_SESSION['2fa_verified'] = true;
                $this->redirect($this->auth->getDashboardURL());
            } else {
                $error = 'Credenciales incorrectas o usuario bloqueado';
            }
        }

        $this->render('auth/login', ['titulo' => 'Iniciar sesión', 'error' => $error]);
    }

    public function _2fa() {
        if (!$this->auth->isLoggedIn()) {
            $this->redirect('/login');
        }
        if ($this->auth->is2FAVerified()) {
            $this->redirect($this->auth->getDashboardURL());
        }

        $error = '';
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->validateCSRF();
            $pin = $_POST['pin'] ?? '';
            if ($this->auth->verificarPIN($pin)) {
                $this->redirect($this->auth->getDashboardURL());
            } else {
                $error = 'PIN incorrecto o expirado';
            }
        }

        $this->render('auth/2fa', ['titulo' => 'Verificación 2FA', 'error' => $error]);
    }

    public function reenviarPIN() {
        if (!$this->auth->isLoggedIn()) {
            $this->json(['error' => 'No autenticado'], 401);
        }
        if (!$this->auth->puedeReenviarPIN()) {
            $this->json(['error' => 'Has excedido el límite de reenvíos'], 429);
        }
        $pin = $this->auth->generarPIN();
        $this->enviarPINCorreo($pin);
        $this->json(['mensaje' => 'PIN reenviado']);
    }

    public function password() {
        $this->requireAuth();
        $errors = [];

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->validateCSRF();
            $actual = $_POST['actual'] ?? '';
            $nueva = $_POST['nueva'] ?? '';
            $confirmar = $_POST['confirmar'] ?? '';

            if (empty($actual)) $errors['actual'] = 'Ingrese su contraseña actual';
            if (strlen($nueva) < 6) $errors['nueva'] = 'Mínimo 6 caracteres';
            if ($nueva !== $confirmar) $errors['confirmar'] = 'Las contraseñas no coinciden';

            if (empty($errors)) {
                $stmt = $this->db->prepare("SELECT contraseña FROM usuarios WHERE id_usuario = ?");
                $stmt->execute([$_SESSION['usuario_id']]);
                $hash = $stmt->fetchColumn();

                if (!password_verify($actual, $hash)) {
                    $errors['actual'] = 'Contraseña actual incorrecta';
                } else {
                    $stmt = $this->db->prepare("UPDATE usuarios SET contraseña = ? WHERE id_usuario = ?");
                    $stmt->execute([password_hash($nueva, PASSWORD_BCRYPT), $_SESSION['usuario_id']]);
                    $errors['exito'] = 'Contraseña actualizada correctamente';
                }
            }
        }

        $this->render('auth/password', ['titulo' => 'Cambiar contraseña', 'errors' => $errors]);
    }

    public function logout() {
        $this->auth->logout();
    }

    private function enviarPINCorreo($pin) {
        $to = $_SESSION['usuario_correo'] ?? '';
        if (empty($to)) {
            $db = Database::getInstance();
            $stmt = $db->prepare("SELECT correo_electrónico FROM usuarios WHERE id_usuario = ?");
            $stmt->execute([$_SESSION['usuario_id']]);
            $to = $stmt->fetchColumn();
        }
        $nombre = $_SESSION['usuario_nombres'] . ' ' . ($_SESSION['usuario_apellidos'] ?? '');
        EmailHelper::enviarPIN($to, $nombre, $pin);
    }
}
