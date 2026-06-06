<?php
require_once ROOT_PATH . '/app/helpers/PDFGenerator.php';

class DocumentoController extends BaseController {

    public function comprobante($idCobro = null) {
        $this->requireAuth();
        if (!$idCobro) { $this->redirect('/cobro'); return; }
        $stmt = $this->db->prepare("SELECT c.*, CONCAT_WS(' ', s.apellido1, s.apellido2, s.nombre1, s.nombre2) AS socio_nombre,
                                     s.cédula, ses.número_sesión
                                     FROM cobros c
                                     JOIN socios s ON c.id_socio = s.id_socio
                                     LEFT JOIN sesiones_mensuales ses ON c.id_sesión = ses.id_sesión
                                     WHERE c.id_cobro = ?");
        $stmt->execute([$idCobro]);
        $c = $stmt->fetch();
        if (!$c) { http_response_code(404); exit; }

        $data = [
            'fecha' => $c['fecha_registro'],
            'socio' => $c['socio_nombre'],
            'cedula' => $c['cédula'],
            'concepto' => ucfirst(str_replace('_', ' ', $c['tipo'])),
            'sesion' => $c['número_sesión'] ? 'Sesión #' . $c['número_sesión'] : '-',
            'monto' => $c['monto'],
            'medio_pago' => $c['medio_pago'],
            'tipo' => $c['tipo'],
            'hash' => $c['hash_integridad'],
        ];
        $filename = PDFGenerator::generarComprobante($data, 'comprobante_' . substr($idCobro, 0, 8));
        header('Location: ' . BASE_URL . '/storage/documentos/' . $filename);
        exit;
    }

    public function constanciaSocio($idSocio) {
        $this->requireAuth();
        $stmt = $this->db->prepare("SELECT * FROM socios WHERE id_socio = ?");
        $stmt->execute([$idSocio]);
        $s = $stmt->fetch();
        if (!$s) { http_response_code(404); exit; }

        $data = [
            'fecha' => date('Y-m-d'),
            'socio' => $s['apellido1'] . ' ' . ($s['apellido2'] ?? '') . ' ' . $s['nombre1'] . ' ' . ($s['nombre2'] ?? ''),
            'cedula' => $s['cédula'],
            'estado' => $s['estado'],
            'fecha_ingreso' => $s['fecha_ingreso'],
        ];
        $filename = PDFGenerator::generarConstancia($data, 'constancia_' . substr($idSocio, 0, 8));
        header('Location: ' . BASE_URL . '/storage/documentos/' . $filename);
        exit;
    }

    public function libreDeuda($idSocio) {
        $this->requireAuth();
        $stmt = $this->db->prepare("SELECT * FROM socios WHERE id_socio = ?");
        $stmt->execute([$idSocio]);
        $s = $stmt->fetch();
        if (!$s) { http_response_code(404); exit; }

        $vencidas = $this->db->prepare("SELECT COUNT(*) FROM amortizaciones a JOIN créditos c ON a.id_crédito = c.id_crédito WHERE c.id_socio = ? AND a.estado IN ('pendiente','vencida')");
        $vencidas->execute([$idSocio]);
        $tieneDeuda = $vencidas->fetchColumn() > 0;

        $multas = $this->db->prepare("SELECT COUNT(*) FROM multas WHERE id_socio = ? AND pagada = FALSE");
        $multas->execute([$idSocio]);
        $tieneMultas = $multas->fetchColumn() > 0;

        $data = [
            'fecha' => date('Y-m-d'),
            'socio' => $s['apellido1'] . ' ' . ($s['apellido2'] ?? '') . ' ' . $s['nombre1'] . ' ' . ($s['nombre2'] ?? ''),
            'cedula' => $s['cédula'],
            'libre_deuda' => !$tieneDeuda && !$tieneMultas,
        ];
        $filename = PDFGenerator::generarLibreDeuda($data, 'libre_deuda_' . substr($idSocio, 0, 8));
        header('Location: ' . BASE_URL . '/storage/documentos/' . $filename);
        exit;
    }

    public function estadoCuenta($idSocio) {
        $this->requireAuth();
        $stmt = $this->db->prepare("SELECT * FROM socios WHERE id_socio = ?");
        $stmt->execute([$idSocio]);
        $s = $stmt->fetch();
        if (!$s) { http_response_code(404); exit; }

        $stmt = $this->db->prepare("SELECT saldo_disponible FROM cuentas_ahorro WHERE id_socio = ?");
        $stmt->execute([$idSocio]);
        $saldo = $stmt->fetchColumn() ?: 0;

        $stmt = $this->db->prepare("SELECT fecha_registro AS fecha, tipo_operación AS concepto, monto,
                                     CASE WHEN tipo_operación IN ('aporte_obligatorio','aporte_excedente','interés_ganado') THEN 'credito' ELSE 'debito' END AS tipo
                                     FROM historial_operaciones WHERE id_socio = ? ORDER BY fecha_registro ASC");
        $stmt->execute([$idSocio]);
        $movs = $stmt->fetchAll();
        $movs = array_map(function($m) {
            $m['fecha'] = substr($m['fecha'], 0, 10);
            $m['monto'] = (float)$m['monto'];
            return $m;
        }, $movs);

        $data = [
            'fecha' => date('Y-m-d'),
            'periodo' => 'Desde el inicio hasta ' . date('Y-m-d'),
            'socio' => $s['apellido1'] . ' ' . ($s['apellido2'] ?? '') . ' ' . $s['nombre1'] . ' ' . ($s['nombre2'] ?? ''),
            'cedula' => $s['cédula'],
            'saldo_actual' => $saldo,
            'movimientos' => $movs,
        ];
        $filename = PDFGenerator::generarEstadoCuenta($data, 'estado_cuenta_' . substr($idSocio, 0, 8));
        header('Location: ' . BASE_URL . '/storage/documentos/' . $filename);
        exit;
    }

    public function actaCierre($idSesion) {
        $this->requireAuth();
        $stmt = $this->db->prepare("SELECT * FROM sesiones_mensuales WHERE id_sesión = ?");
        $stmt->execute([$idSesion]);
        $sesion = $stmt->fetch();
        if (!$sesion || $sesion['estado'] !== 'cerrada') { http_response_code(404); exit; }

        $stmt = $this->db->prepare("SELECT tipo, COUNT(*) AS total, SUM(monto) AS suma FROM cobros WHERE id_sesión = ? AND anulado = FALSE GROUP BY tipo");
        $stmt->execute([$idSesion]);
        $resumen = $stmt->fetchAll();

        $filename = PDFGenerator::generarActaCierre($sesion, $resumen, 'acta_sesion_' . $sesion['número_sesión']);
        header('Location: ' . BASE_URL . '/storage/documentos/' . $filename);
        exit;
    }
}
