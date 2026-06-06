<?php
class ReporteController extends BaseController {

    public function listar() {
        $this->requirePermission('reporte.socios');
        $this->render('reportes/listar', ['titulo' => 'Reportes']);
    }

    public function socios() {
        $this->requirePermission('reporte.socios');
        $stmt = $this->db->query("SELECT s.cédula, CONCAT_WS(' ', s.apellido1, s.apellido2, s.nombre1, s.nombre2) AS nombre,
                                   s.correo_electrónico, s.teléfono, s.estado, s.fecha_ingreso,
                                   ca.saldo_obligatorio, ca.saldo_excedente, ca.saldo_disponible
                                   FROM socios s
                                   LEFT JOIN cuentas_ahorro ca ON s.id_socio = ca.id_socio
                                    ORDER BY s.apellido1, s.apellido2, s.nombre1, s.nombre2");
        $data = $stmt->fetchAll();

        if (isset($_GET['formato'])) {
            return $this->exportarCSV($data, ['Cédula', 'Nombre', 'Correo', 'Teléfono', 'Estado', 'Ingreso', 'Aporte Obligatorio', 'Aporte Excedente', 'Disponible'], 'socios');
        }

        $this->render('reportes/tabla', [
            'titulo' => 'Reporte de socios',
            'encabezados' => ['Cédula', 'Nombre', 'Correo', 'Teléfono', 'Estado', 'Ingreso', 'Aporte Obligatorio', 'Aporte Excedente', 'Disponible'],
            'filas' => $data,
            'campos' => ['cédula', 'nombre', 'correo_electrónico', 'teléfono', 'estado', 'fecha_ingreso', 'saldo_obligatorio', 'saldo_excedente', 'saldo_disponible'],
            'ruta_csv' => BASE_URL . '/reporte/socios?formato=csv',
        ]);
    }

    public function financiero() {
        $this->requirePermission('reporte.financiero');

        $activos = $this->db->query("SELECT COUNT(*) FROM socios WHERE estado = 'activo'")->fetchColumn();
        $totalAportes = $this->db->query("SELECT COALESCE(SUM(saldo_disponible), 0) FROM cuentas_ahorro")->fetchColumn();
        $totalCreditos = $this->db->query("SELECT COALESCE(SUM(monto_aprobado), 0) AS t FROM créditos WHERE estado IN ('aprobado','desembolsado')")->fetchColumn();
        $totalInversiones = $this->db->query("SELECT COALESCE(SUM(monto), 0) FROM inversiones WHERE estado = 'activa'")->fetchColumn();
        $totalCobros = $this->db->query("SELECT COALESCE(SUM(monto), 0) FROM cobros WHERE anulado = FALSE")->fetchColumn();

        $resumen = [
            'Socios activos' => $activos,
            'Total en cuentas de ahorro' => $totalAportes,
            'Total créditos activos' => $totalCreditos,
            'Total inversiones activas' => $totalInversiones,
            'Total cobros registrados' => $totalCobros,
        ];

        $this->render('reportes/financiero', [
            'titulo' => 'Reporte financiero',
            'resumen' => $resumen,
        ]);
    }

    public function morosidad() {
        $this->requirePermission('reporte.financiero');
        $stmt = $this->db->query("SELECT a.*, c.id_crédito, c.monto_aprobado, c.plazo_meses, c.tasa_interés,
                                  CONCAT_WS(' ', s.apellido1, s.apellido2, s.nombre1, s.nombre2) AS socio, s.cédula
                                  FROM amortizaciones a
                                  JOIN créditos c ON a.id_crédito = c.id_crédito
                                  JOIN socios s ON c.id_socio = s.id_socio
                                  WHERE a.estado = 'vencida' OR (a.estado = 'pendiente' AND a.fecha_vencimiento < CURDATE())
                                  ORDER BY a.fecha_vencimiento ASC");
        $cuotas = $stmt->fetchAll();

        $totalMoroso = 0;
        $sociosMorosos = [];
        foreach ($cuotas as $a) {
            $totalMoroso += $a['total'];
            $sociosMorosos[$a['id_crédito']] = $a['socio'];
        }

        $this->render('reportes/morosidad', [
            'titulo' => 'Reporte de morosidad',
            'cuotas' => $cuotas,
            'totalMoroso' => $totalMoroso,
            'sociosMorosos' => $sociosMorosos,
        ]);
    }

    public function historialOperaciones() {
        $this->requirePermission('reporte.financiero');
        $page = max(1, intval($_GET['p'] ?? 1));
        $porPagina = 50;
        $offset = ($page - 1) * $porPagina;
        $stmt = $this->db->prepare("SELECT h.*, CONCAT_WS(' ', s.apellido1, s.apellido2, s.nombre1, s.nombre2) AS socio
                                    FROM historial_operaciones h
                                    JOIN socios s ON h.id_socio = s.id_socio
                                    ORDER BY h.fecha_registro DESC LIMIT $porPagina OFFSET $offset");
        $stmt->execute();
        $historial = $stmt->fetchAll();
        $total = $this->db->query("SELECT COUNT(*) FROM historial_operaciones")->fetchColumn();
        $totalPaginas = ceil($total / $porPagina);

        $this->render('reportes/historial', [
            'titulo' => 'Historial de operaciones',
            'historial' => $historial,
            'page' => $page,
            'totalPaginas' => $totalPaginas,
        ]);
    }

    public function cobros() {
        $this->requirePermission('reporte.cobros');
        $stmt = $this->db->query("SELECT c.fecha_registro, CONCAT_WS(' ', s.apellido1, s.apellido2, s.nombre1, s.nombre2) AS socio,
                                   c.tipo, c.monto, c.medio_pago, ses.número_sesión,
                                   c.anulado, c.motivo_anulación
                                   FROM cobros c
                                   JOIN socios s ON c.id_socio = s.id_socio
                                   JOIN sesiones_mensuales ses ON c.id_sesión = ses.id_sesión
                                   ORDER BY c.fecha_registro DESC");
        $data = $stmt->fetchAll();

        if (isset($_GET['formato'])) {
            return $this->exportarCSV($data, ['Fecha', 'Socio', 'Tipo', 'Monto', 'Medio', 'Sesión', 'Anulado', 'Motivo'], 'cobros');
        }

        $this->render('reportes/tabla', [
            'titulo' => 'Reporte de cobros',
            'encabezados' => ['Fecha', 'Socio', 'Tipo', 'Monto', 'Medio de Pago', 'Sesión', 'Anulado', 'Motivo'],
            'filas' => $data,
            'campos' => ['fecha_registro', 'socio', 'tipo', 'monto', 'medio_pago', 'número_sesión', 'anulado', 'motivo_anulación'],
            'ruta_csv' => BASE_URL . '/reporte/cobros?formato=csv',
        ]);
    }

    private function exportarCSV($data, $headers, $nombre) {
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="' . $nombre . '_' . date('Y-m-d') . '.csv"');
        $output = fopen('php://output', 'w');
        fprintf($output, chr(0xEF) . chr(0xBB) . chr(0xBF));
        fputcsv($output, $headers);
        foreach ($data as $row) {
            $vals = [];
            foreach ($row as $v) {
                $vals[] = is_numeric($v) ? $v : html_entity_decode(strip_tags($v));
            }
            fputcsv($output, $vals);
        }
        fclose($output);
        exit;
    }
}
