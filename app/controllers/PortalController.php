<?php
class PortalController extends BaseController {

    public function index() {
        $this->requireAuth();
        $cedula = $_SESSION['usuario_cedula'] ?? '';

        $stmt = $this->db->prepare("SELECT * FROM socios WHERE cédula = ?");
        $stmt->execute([$cedula]);
        $socio = $stmt->fetch();

        if (!$socio) {
            $this->render('portal/index', [
                'titulo' => 'Mi portal',
                'socio' => null,
                'creditos' => [],
                'inversiones' => [],
                'cobros' => [],
                'cuenta' => null,
            ]);
            return;
        }

        $stmt = $this->db->prepare("SELECT * FROM cuentas_ahorro WHERE id_socio = ?");
        $stmt->execute([$socio['id_socio']]);
        $cuenta = $stmt->fetch();

        $stmt = $this->db->prepare("SELECT c.*, p.nombre AS producto FROM créditos c JOIN productos_financieros p ON c.id_producto = p.id_producto WHERE c.id_socio = ? ORDER BY c.fecha_solicitud DESC");
        $stmt->execute([$socio['id_socio']]);
        $creditos = $stmt->fetchAll();

        $stmt = $this->db->prepare("SELECT i.*, p.nombre AS producto FROM inversiones i JOIN productos_financieros p ON i.id_producto = p.id_producto WHERE i.id_socio = ? ORDER BY i.fecha_registro DESC");
        $stmt->execute([$socio['id_socio']]);
        $inversiones = $stmt->fetchAll();

        $stmt = $this->db->prepare("SELECT c.*, ses.número_sesión FROM cobros c LEFT JOIN sesiones_mensuales ses ON c.id_sesión = ses.id_sesión WHERE c.id_socio = ? AND c.anulado = FALSE ORDER BY c.fecha_registro DESC LIMIT 10");
        $stmt->execute([$socio['id_socio']]);
        $cobros = $stmt->fetchAll();

        $this->render('portal/index', [
            'titulo' => 'Mi portal',
            'socio' => $socio,
            'creditos' => $creditos,
            'inversiones' => $inversiones,
            'cobros' => $cobros,
            'cuenta' => $cuenta,
        ]);
    }

    public function historial() {
        $this->requireAuth();
        $cedula = $_SESSION['usuario_cedula'] ?? '';
        $stmt = $this->db->prepare("SELECT id_socio FROM socios WHERE cédula = ?");
        $stmt->execute([$cedula]);
        $socio = $stmt->fetch();
        if (!$socio) { $this->redirect('/portal'); return; }

        $stmt = $this->db->prepare("SELECT h.*, c.nombre1, c.apellido1 FROM historial_operaciones h JOIN socios c ON h.id_socio = c.id_socio WHERE h.id_socio = ? ORDER BY h.fecha_registro DESC LIMIT 100");
        $stmt->execute([$socio['id_socio']]);
        $historial = $stmt->fetchAll();

        $this->render('portal/historial', [
            'titulo' => 'Historial de operaciones',
            'historial' => $historial,
        ]);
    }

    public function solicitarRetiro() {
        $this->requireAuth();
        $cedula = $_SESSION['usuario_cedula'] ?? '';
        $stmt = $this->db->prepare("SELECT s.id_socio, c.saldo_disponible FROM socios s LEFT JOIN cuentas_ahorro c ON s.id_socio = c.id_socio WHERE s.cédula = ?");
        $stmt->execute([$cedula]);
        $socio = $stmt->fetch();
        if (!$socio) $this->redirect('/portal');

        $errors = [];
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->validateCSRF();
            $monto = str_replace(',', '.', $_POST['monto'] ?? '0');
            $motivo = trim($_POST['motivo'] ?? '');

            if (!is_numeric($monto) || $monto <= 0) $errors['monto'] = 'Monto inválido';
            elseif ($monto > ($socio['saldo_disponible'] ?? 0)) $errors['monto'] = 'Saldo disponible insuficiente: $' . number_format($socio['saldo_disponible'] ?? 0, 2);
            if (empty($motivo)) $errors['motivo'] = 'Indique el motivo del retiro';

            $pend = $this->db->prepare("SELECT COUNT(*) FROM solicitudes_retiro WHERE id_socio = ? AND estado = 'pendiente'");
            $pend->execute([$socio['id_socio']]);
            if ($pend->fetchColumn() > 0) $errors['general'] = 'Ya tiene una solicitud pendiente';

            if (empty($errors)) {
                $id = UUIDGenerator::generar();
                $this->db->prepare("INSERT INTO solicitudes_retiro (id_solicitud, id_socio, monto, motivo) VALUES (?, ?, ?, ?)")
                    ->execute([$id, $socio['id_socio'], $monto, $motivo]);
                NotificacionHelper::crearCobro($id, $cedula, $monto, 'Solicitud de retiro');
                $this->redirect('/portal');
            }
        }

        $stmt = $this->db->prepare("SELECT * FROM solicitudes_retiro WHERE id_socio = ? ORDER BY fecha_solicitud DESC");
        $stmt->execute([$socio['id_socio']]);
        $solicitudes = $stmt->fetchAll();

        $this->render('portal/retiro', [
            'titulo' => 'Solicitar retiro',
            'errors' => $errors,
            'saldo' => $socio['saldo_disponible'] ?? 0,
            'solicitudes' => $solicitudes,
        ]);
    }

    public function multas() {
        $this->requireAuth();
        $cedula = $_SESSION['usuario_cedula'] ?? '';
        $stmt = $this->db->prepare("SELECT id_socio FROM socios WHERE cédula = ?");
        $stmt->execute([$cedula]);
        $socio = $stmt->fetch();
        if (!$socio) { $this->redirect('/portal'); return; }

        $stmt = $this->db->prepare("SELECT * FROM multas WHERE id_socio = ? ORDER BY fecha_generación DESC");
        $stmt->execute([$socio['id_socio']]);
        $multas = $stmt->fetchAll();

        $this->render('portal/multas', [
            'titulo' => 'Mis multas',
            'multas' => $multas,
        ]);
    }

    public function asistencias() {
        $this->requireAuth();
        $cedula = $_SESSION['usuario_cedula'] ?? '';
        $stmt = $this->db->prepare("SELECT id_socio FROM socios WHERE cédula = ?");
        $stmt->execute([$cedula]);
        $socio = $stmt->fetch();
        if (!$socio) { $this->redirect('/portal'); return; }

        $stmt = $this->db->prepare("SELECT a.*, ses.número_sesión, ses.fecha AS fecha_sesión
                                    FROM asistencias a
                                    JOIN sesiones_mensuales ses ON a.id_sesión = ses.id_sesión
                                    WHERE a.id_socio = ?
                                    ORDER BY a.fecha_registro DESC");
        $stmt->execute([$socio['id_socio']]);
        $asistencias = $stmt->fetchAll();

        $this->render('portal/asistencias', [
            'titulo' => 'Mis asistencias',
            'asistencias' => $asistencias,
        ]);
    }

    public function notificaciones() {
        $this->requireAuth();
        $cedula = $_SESSION['usuario_cedula'] ?? '';
        $stmt = $this->db->prepare("SELECT id_socio FROM socios WHERE cédula = ?");
        $stmt->execute([$cedula]);
        $socio = $stmt->fetch();

        $notificaciones = [];
        if ($socio) {
            $stmt = $this->db->prepare("SELECT * FROM notificaciones WHERE id_socio = ? ORDER BY fecha_creación DESC LIMIT 50");
            $stmt->execute([$socio['id_socio']]);
            $notificaciones = $stmt->fetchAll();
        }

        $stmt = $this->db->prepare("SELECT COUNT(*) FROM notificaciones WHERE id_usuario = ? AND leída = FALSE");
        $stmt->execute([$_SESSION['usuario_id']]);
        $noLeidas = $stmt->fetchColumn();

        $this->render('portal/notificaciones', [
            'titulo' => 'Notificaciones',
            'notificaciones' => $notificaciones,
            'noLeidas' => $noLeidas,
        ]);
    }

    public function password() {
        $this->requireAuth();
        $errors = [];
        $exito = '';

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->validateCSRF();
            $actual = $_POST['actual'] ?? '';
            $nueva = $_POST['nueva'] ?? '';
            $confirmar = $_POST['confirmar'] ?? '';

            $stmt = $this->db->prepare("SELECT contraseña FROM usuarios WHERE id_usuario = ?");
            $stmt->execute([$_SESSION['usuario_id']]);
            $hash = $stmt->fetchColumn();

            if (!password_verify($actual, $hash)) $errors['actual'] = 'Contraseña actual incorrecta';
            if (strlen($nueva) < 6) $errors['nueva'] = 'Mínimo 6 caracteres';
            if ($nueva !== $confirmar) $errors['confirmar'] = 'No coinciden';

            if (empty($errors)) {
                $this->db->prepare("UPDATE usuarios SET contraseña = ? WHERE id_usuario = ?")
                    ->execute([password_hash($nueva, PASSWORD_BCRYPT), $_SESSION['usuario_id']]);
                $exito = 'Contraseña actualizada';
            }
        }

        $this->render('portal/password', [
            'titulo' => 'Cambiar contraseña',
            'errors' => $errors,
            'exito' => $exito,
        ]);
    }
}
