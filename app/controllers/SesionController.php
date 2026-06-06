<?php
require_once ROOT_PATH . '/app/helpers/PDFGenerator.php';

class SesionController extends BaseController {

    public function listar() {
        $this->requirePermission('cobro.aporte');
        $stmt = $this->db->query("SELECT s.*, u.nombres AS usuario_cierre_nombre
                                   FROM sesiones_mensuales s
                                   LEFT JOIN usuarios u ON s.usuario_cierre = u.id_usuario
                                   ORDER BY s.fecha DESC");
        $sesiones = $stmt->fetchAll();
        $this->render('sesiones/listar', [
            'titulo' => 'Sesiones mensuales',
            'sesiones' => $sesiones,
        ]);
    }

    public function abrir() {
        $this->requirePermission('cobro.aporte');
        $errors = [];

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->validateCSRF();

            $stmt = $this->db->query("SELECT COUNT(*) FROM sesiones_mensuales WHERE estado = 'abierta'");
            if ($stmt->fetchColumn() > 0) {
                $errors['general'] = 'Ya existe una sesión abierta. Ciérrela antes de abrir otra.';
            }

            if (empty($errors)) {
                $stmt = $this->db->query("SELECT COALESCE(MAX(número_sesión), 0) + 1 FROM sesiones_mensuales");
                $num = $stmt->fetchColumn();

                $id = UUIDGenerator::generar();
                $fecha = $_POST['fecha'] ?? date('Y-m-d');
                $titulo = trim($_POST['título'] ?? '');

                $stmt = $this->db->prepare("INSERT INTO sesiones_mensuales (id_sesión, número_sesión, fecha, título) VALUES (?, ?, ?, ?)");
                $stmt->execute([$id, $num, $fecha, $titulo]);
                $this->redirect('/sesion/checkin/' . $id);
            }
        }

        $this->render('sesiones/abrir', [
            'titulo' => 'Abrir sesión mensual',
            'errors' => $errors,
        ]);
    }

    public function checkin($id) {
        $this->requirePermission('cobro.aporte');
        $stmt = $this->db->prepare("SELECT * FROM sesiones_mensuales WHERE id_sesión = ?");
        $stmt->execute([$id]);
        $sesion = $stmt->fetch();
        if (!$sesion) $this->redirect('/sesion/listar');
        if ($sesion['estado'] === 'cerrada') {
            $this->redirect('/sesion/listar');
        }

        $socios = $this->db->query("SELECT id_socio, cédula, CONCAT_WS(' ', apellido1, apellido2, nombre1, nombre2) AS nombre_completo
                                     FROM socios WHERE estado = 'activo' ORDER BY apellido1, apellido2, nombre1, nombre2")->fetchAll();

        $stmt = $this->db->prepare("SELECT * FROM asistencias WHERE id_sesión = ?");
        $stmt->execute([$id]);
        $asistencias = [];
        while ($row = $stmt->fetch()) {
            $asistencias[$row['id_socio']] = $row;
        }

        $cobros_stmt = $this->db->prepare("SELECT tipo, COUNT(*) AS total, SUM(monto) AS suma FROM cobros WHERE id_sesión = ? AND anulado = FALSE GROUP BY tipo");
        $cobros_stmt->execute([$id]);
        $resumen_cobros = $cobros_stmt->fetchAll();

        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['accion'])) {
            $this->validateCSRF();
            $accion = $_POST['accion'];

            if ($accion === 'asistencia') {
                $idSocio = $_POST['id_socio'] ?? '';
                $tipo = $_POST['tipo'] ?? 'falta';

                $stmt = $this->db->prepare("SELECT COUNT(*) FROM asistencias WHERE id_socio = ? AND id_sesión = ?");
                $stmt->execute([$idSocio, $id]);
                if ($stmt->fetchColumn() > 0) {
                    $stmt = $this->db->prepare("UPDATE asistencias SET tipo = ?, usuario_registra = ? WHERE id_socio = ? AND id_sesión = ?");
                    $stmt->execute([$tipo, $_SESSION['usuario_id'], $idSocio, $id]);
                } else {
                    $stmt = $this->db->prepare("INSERT INTO asistencias (id_asistencia, id_socio, id_sesión, tipo, usuario_registra) VALUES (?, ?, ?, ?, ?)");
                    $stmt->execute([UUIDGenerator::generar(), $idSocio, $id, $tipo, $_SESSION['usuario_id']]);
                }
                $this->redirect('/sesion/checkin/' . $id);
            }

            if ($accion === 'cierre') {
                $this->requirePermission('cobro.cierre_sesión');
                return $this->ejecutarCierre($sesion);
            }
        }

        $this->render('sesiones/checkin', [
            'titulo' => 'Sesión #' . $sesion['número_sesión'] . ' — ' . $sesion['fecha'],
            'sesion' => $sesion,
            'socios' => $socios,
            'asistencias' => $asistencias,
            'resumen_cobros' => $resumen_cobros,
        ]);
    }

    private function ejecutarCierre($sesion) {
        $id = $sesion['id_sesión'];

        $stmt = $this->db->prepare("SELECT COUNT(*) FROM cobros WHERE id_sesión = ?");
        $stmt->execute([$id]);
        if ($stmt->fetchColumn() == 0) {
            $_SESSION['error'] = 'No hay cobros registrados en esta sesión';
            $this->redirect('/sesion/checkin/' . $id);
        }

        $stmt = $this->db->prepare("SELECT COALESCE(SUM(monto), 0) FROM cobros WHERE id_sesión = ? AND anulado = FALSE AND tipo != 'desembolso'");
        $stmt->execute([$id]);
        $total_recaudado = $stmt->fetchColumn();

        $stmt = $this->db->prepare("SELECT COALESCE(SUM(monto), 0) FROM cobros WHERE id_sesión = ? AND anulado = FALSE AND tipo = 'desembolso'");
        $stmt->execute([$id]);
        $total_desembolsado = $stmt->fetchColumn();

        $saldo = $total_recaudado - $total_desembolsado;

        $acta = 'acta_sesion_' . $sesion['número_sesión'] . '_' . date('Ymd') . '.pdf';

        $stmt = $this->db->prepare("SELECT tipo, COUNT(*) AS total, SUM(monto) AS suma FROM cobros WHERE id_sesión = ? AND anulado = FALSE GROUP BY tipo");
        $stmt->execute([$id]);
        $resumen = $stmt->fetchAll();

        $this->generarMultasAsistencia($id);

        $sesion['total_recaudado'] = $total_recaudado;
        $sesion['total_desembolsado'] = $total_desembolsado;
        $sesion['saldo_caja'] = $saldo;
        $sesion['fecha_cierre'] = date('Y-m-d H:i:s');
        $htmlFile = PDFGenerator::generarActaCierre($sesion, $resumen, pathinfo($acta, PATHINFO_FILENAME));

        $stmt = $this->db->prepare("UPDATE sesiones_mensuales SET
            estado = 'cerrada', fecha_cierre = NOW(), usuario_cierre = ?,
            total_recaudado = ?, total_desembolsado = ?, saldo_caja = ?, acta_cierre_pdf = ?
            WHERE id_sesión = ? AND estado = 'abierta'");
        $stmt->execute([$_SESSION['usuario_id'], $total_recaudado, $total_desembolsado, $saldo, $htmlFile, $id]);

        NotificacionHelper::crearSesion($sesion['número_sesión'], 'cerrada');
        $this->redirect('/sesion/listar');
    }

    private function generarMultasAsistencia($idSesion) {
        $asistencias = $this->db->prepare("SELECT a.*, CONCAT_WS(' ', s.apellido1, s.apellido2, s.nombre1, s.nombre2) AS socio
                                            FROM asistencias a JOIN socios s ON a.id_socio = s.id_socio
                                            WHERE a.id_sesión = ?");
        $asistencias->execute([$idSesion]);
        $insertMulta = $this->db->prepare("INSERT IGNORE INTO multas (id_multa, id_socio, id_sesión, tipo, monto) VALUES (?, ?, ?, ?, ?)");

        foreach ($asistencias as $a) {
            $monto = 0;
            $tipo = '';
            switch ($a['tipo']) {
                case 'retraso_10min': $monto = MULTA_RETRASO_10MIN; $tipo = 'retraso_10min'; break;
                case 'retraso_30min': $monto = MULTA_RETRASO_30MIN; $tipo = 'retraso_30min'; break;
                case 'falta': $monto = MULTA_INASISTENCIA; $tipo = 'inasistencia'; break;
                default: continue 2;
            }
            if ($monto > 0) {
                $insertMulta->execute([UUIDGenerator::generar(), $a['id_socio'], $idSesion, $tipo, $monto]);
            }
        }
    }
}
