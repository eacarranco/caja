<?php
class DashboardController extends BaseController {

    public function index() {
        $this->requireAuth();

        $totalSocios = $this->db->query("SELECT COUNT(*) FROM socios")->fetchColumn();
        $sociosActivos = $this->db->query("SELECT COUNT(*) FROM socios WHERE estado = 'activo'")->fetchColumn();
        $sesionAbierta = $this->db->query("SELECT COUNT(*) FROM sesiones_mensuales WHERE estado = 'abierta'")->fetchColumn();
        $creditosPendientes = $this->db->query("SELECT COUNT(*) FROM `creditos` WHERE estado IN ('ingresado','pendiente')")->fetchColumn();
        $inversionesPendientes = $this->db->query("SELECT COUNT(*) FROM inversiones WHERE estado = 'pendiente'")->fetchColumn();

        $capitalCaja = $this->db->query("SELECT COALESCE(saldo_posterior, 0) FROM caja_movimientos ORDER BY fecha_registro DESC LIMIT 1")->fetchColumn() ?: 0;

        $stmt = $this->db->query("SELECT c.fecha_registro, CONCAT(s.apellido1, ' ', COALESCE(s.apellido2,''), ' ', s.nombre1, ' ', COALESCE(s.nombre2,'')) AS socio,
                                   c.tipo, c.monto
                                   FROM cobros c
                                   JOIN socios s ON c.id_socio = s.id_socio
                                   WHERE c.anulado = FALSE
                                   ORDER BY c.fecha_registro DESC LIMIT 5");
        $ultimosCobros = $stmt->fetchAll();

        $stmt2 = $this->db->query("SELECT id_sesion, numero_sesion, fecha_sesion, estado FROM sesiones_mensuales ORDER BY fecha_sesion DESC LIMIT 3");
        $ultimasSesiones = $stmt2->fetchAll();

        $permisos = RBAC::obtenerPermisosUsuario($_SESSION['usuario_id']);

        $cobrosPorMes = $this->db->query("SELECT DATE_FORMAT(fecha_registro, '%Y-%m') AS mes, SUM(monto) AS total FROM cobros WHERE anulado = FALSE AND fecha_registro >= DATE_SUB(CURDATE(), INTERVAL 6 MONTH) GROUP BY mes ORDER BY mes")->fetchAll();
        $chartLabels = []; $chartData = [];
        foreach ($cobrosPorMes as $r) { $chartLabels[] = $r['mes']; $chartData[] = (float)$r['total']; }

        $tipoCobros = $this->db->query("SELECT tipo, SUM(monto) AS total FROM cobros WHERE anulado = FALSE GROUP BY tipo ORDER BY total DESC LIMIT 5")->fetchAll();
        $chartTipoLabels = []; $chartTipoData = [];
        foreach ($tipoCobros as $r) { $chartTipoLabels[] = $r['tipo']; $chartTipoData[] = (float)$r['total']; }

        $this->render('dashboard/index', [
            'titulo' => 'Dashboard',
            'totalSocios' => $totalSocios,
            'sociosActivos' => $sociosActivos,
            'sesionAbierta' => $sesionAbierta,
            'creditosPendientes' => $creditosPendientes,
            'inversionesPendientes' => $inversionesPendientes,
            'capitalCaja' => $capitalCaja,
            'ultimosCobros' => $ultimosCobros,
            'ultimasSesiones' => $ultimasSesiones,
            'permisos' => $permisos,
            'chartLabels' => $chartLabels,
            'chartData' => $chartData,
            'chartTipoLabels' => $chartTipoLabels,
            'chartTipoData' => $chartTipoData,
        ]);
    }
}
