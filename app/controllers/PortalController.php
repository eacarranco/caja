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
                'titulo' => 'Inicio',
                'socio' => null,
                'creditos' => [],
                'inversiones' => [],
                'cobros' => [],
                'cuenta' => null,
                'pendientes' => [],
            ]);
            return;
        }
        $idSocio = $socio['id_socio'];

        $stmt = $this->db->prepare("SELECT * FROM cuentas_ahorro WHERE id_socio = ?");
        $stmt->execute([$idSocio]);
        $cuenta = $stmt->fetch();

        $stmt = $this->db->prepare("SELECT c.*, p.nombre AS producto FROM créditos c JOIN productos_financieros p ON c.id_producto = p.id_producto WHERE c.id_socio = ? ORDER BY c.fecha_solicitud DESC");
        $stmt->execute([$idSocio]);
        $creditos = $stmt->fetchAll();

        $stmt = $this->db->prepare("SELECT i.*, p.nombre AS producto FROM inversiones i JOIN productos_financieros p ON i.id_producto = p.id_producto WHERE i.id_socio = ? ORDER BY i.fecha_registro DESC");
        $stmt->execute([$idSocio]);
        $inversiones = $stmt->fetchAll();

        $stmt = $this->db->prepare("SELECT c.*, ses.número_sesión FROM cobros c LEFT JOIN sesiones_mensuales ses ON c.id_sesión = ses.id_sesión WHERE c.id_socio = ? AND c.anulado = FALSE ORDER BY c.fecha_registro DESC LIMIT 10");
        $stmt->execute([$idSocio]);
        $cobros = $stmt->fetchAll();

        $stmt = $this->db->prepare("SELECT saldo_obligatorio, saldo_excedente FROM cuentas_ahorro WHERE id_socio = ?");
        $stmt->execute([$idSocio]);
        $cuentaRes = $stmt->fetch();

        $stmt = $this->db->prepare("SELECT IFNULL(SUM(monto), 0) AS multas FROM multas WHERE id_socio = ? AND pagada = FALSE");
        $stmt->execute([$idSocio]);
        $multasRes = $stmt->fetch();

        $stmt = $this->db->prepare("SELECT IFNULL(SUM(a.total), 0) AS cuotas_credito FROM amortizaciones a JOIN créditos cr ON a.id_crédito = cr.id_crédito WHERE cr.id_socio = ? AND a.estado != 'pagada'");
        $stmt->execute([$idSocio]);
        $creditosRes = $stmt->fetch();

        $pendientes = [
            'aporte_obligatorio' => $cuentaRes['saldo_obligatorio'] ?? 0,
            'aporte_excedente' => $cuentaRes['saldo_excedente'] ?? 0,
            'multas' => $multasRes['multas'] ?? 0,
            'cuotas_credito' => $creditosRes['cuotas_credito'] ?? 0,
        ];

        $this->render('portal/index', [
            'titulo' => 'Inicio',
            'socio' => $socio,
            'creditos' => $creditos,
            'inversiones' => $inversiones,
            'cobros' => $cobros,
            'cuenta' => $cuenta,
            'pendientes' => $pendientes,
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

    public function pagar() {
        $this->requireAuth();
        $cedula = $_SESSION['usuario_cedula'] ?? '';
        $stmt = $this->db->prepare("SELECT id_socio FROM socios WHERE cédula = ?");
        $stmt->execute([$cedula]);
        $socio = $stmt->fetch();
        $pendientes = [];

        if ($socio) {
            $idSocio = $socio['id_socio'];

            $stmt = $this->db->prepare("SELECT saldo_obligatorio, saldo_excedente FROM cuentas_ahorro WHERE id_socio = ?");
            $stmt->execute([$idSocio]);
            $cuenta = $stmt->fetch();

            $stmt = $this->db->prepare("SELECT IFNULL(SUM(monto), 0) AS multas_pendientes FROM multas WHERE id_socio = ? AND pagada = FALSE");
            $stmt->execute([$idSocio]);
            $multas = $stmt->fetch();

            $stmt = $this->db->prepare("SELECT IFNULL(SUM(a.total), 0) AS cuotas_pendientes
                                        FROM amortizaciones a
                                        JOIN créditos cr ON a.id_crédito = cr.id_crédito
                                        WHERE cr.id_socio = ? AND a.estado != 'pagada'");
            $stmt->execute([$idSocio]);
            $creditos = $stmt->fetch();

            $pendientes = [
                'aporte_obligatorio' => $cuenta['saldo_obligatorio'] ?? 0,
                'aporte_excedente' => $cuenta['saldo_excedente'] ?? 0,
                'multas' => $multas['multas_pendientes'] ?? 0,
                'cuotas_credito' => $creditos['cuotas_pendientes'] ?? 0,
            ];
        }

        $this->render('portal/pagar', [
            'titulo' => 'Pagar',
            'pendientes' => $pendientes,
        ]);
    }

    public function solicitarCredito() {
        $this->redirect('/portal');
    }

    public function solicitarCertificado() {
        $this->requireAuth();
        $cedula = $_SESSION['usuario_cedula'] ?? '';
        $stmt = $this->db->prepare("SELECT id_socio FROM socios WHERE cédula = ?");
        $stmt->execute([$cedula]);
        $socio = $stmt->fetch();
        if (!$socio) $this->redirect('/portal');

        $idSocio = $socio['id_socio'];

        $stmt = $this->db->prepare("SELECT CONCAT_WS(' ', apellido1, apellido2, nombre1, nombre2) AS nombre FROM socios WHERE id_socio = ?");
        $stmt->execute([$idSocio]);
        $socioData = $stmt->fetch();

        $this->render('portal/certificaciones', [
            'titulo' => 'Certificaciones',
            'id_socio' => $idSocio,
            'socio_nombre' => $socioData['nombre'] ?? '',
        ]);
    }

    public function inversion() {
        $this->redirect('/portal');
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
