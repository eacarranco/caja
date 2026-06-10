<?php
require_once ROOT_PATH . '/app/helpers/PDFGenerator.php';

class DocumentoController extends BaseController {

    public function comprobante($idCobro = null) {
        $this->requireAuth();
        if (!$idCobro) { $this->redirect('/cobro'); return; }
        $stmt = $this->db->prepare("SELECT c.*, CONCAT_WS(' ', s.apellido1, s.apellido2, s.nombre1, s.nombre2) AS socio_nombre,
                                     s.cedula, ses.numero_sesion
                                     FROM cobros c
                                     JOIN socios s ON c.id_socio = s.id_socio
                                     LEFT JOIN sesiones_mensuales ses ON c.id_sesion = ses.id_sesion
                                     WHERE c.id_cobro = ?");
        $stmt->execute([$idCobro]);
        $c = $stmt->fetch();
        if (!$c) { http_response_code(404); exit; }

        $data = [
            'fecha' => $c['fecha_registro'],
            'socio' => $c['socio_nombre'],
            'cedula' => $c['cedula'],
            'concepto' => ucfirst(str_replace('_', ' ', $c['tipo'])),
            'sesion' => $c['numero_sesion'] ? 'Sesión #' . $c['numero_sesion'] : '-',
            'monto' => $c['monto'],
            'medio_pago' => $c['medio_pago'],
            'tipo' => $c['tipo'],
            'hash' => $c['hash_integridad'],
            'anulado' => !empty($c['anulado']),
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
            'cedula' => $s['cedula'],
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

        $vencidas = $this->db->prepare("SELECT COUNT(*) FROM amortizaciones a JOIN creditos c ON a.id_credito = c.id_credito WHERE c.id_socio = ? AND a.estado IN ('pendiente','vencida')");
        $vencidas->execute([$idSocio]);
        $tieneDeuda = $vencidas->fetchColumn() > 0;

        $multas = $this->db->prepare("SELECT COUNT(*) FROM multas WHERE id_socio = ? AND pagada = FALSE");
        $multas->execute([$idSocio]);
        $tieneMultas = $multas->fetchColumn() > 0;

        $data = [
            'fecha' => date('Y-m-d'),
            'socio' => $s['apellido1'] . ' ' . ($s['apellido2'] ?? '') . ' ' . $s['nombre1'] . ' ' . ($s['nombre2'] ?? ''),
            'cedula' => $s['cedula'],
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

        $stmt = $this->db->prepare("SELECT fecha_registro AS fecha, tipo_operacion AS concepto, monto,
                                     CASE WHEN tipo_operacion IN ('aporte_obligatorio','aporte_excedente','interes_ganado') THEN 'credito' ELSE 'debito' END AS tipo
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
            'cedula' => $s['cedula'],
            'saldo_actual' => $saldo,
            'movimientos' => $movs,
        ];
        $filename = PDFGenerator::generarEstadoCuenta($data, 'estado_cuenta_' . substr($idSocio, 0, 8));
        header('Location: ' . BASE_URL . '/storage/documentos/' . $filename);
        exit;
    }

    public function actaCierre($idSesion) {
        $this->requireAuth();
        $stmt = $this->db->prepare("SELECT * FROM sesiones_mensuales WHERE id_sesion = ?");
        $stmt->execute([$idSesion]);
        $sesion = $stmt->fetch();
        if (!$sesion || $sesion['estado'] !== 'cerrada') { http_response_code(404); exit; }

        $stmt = $this->db->prepare("SELECT tipo, COUNT(*) AS total, SUM(monto) AS suma FROM cobros WHERE id_sesion = ? AND anulado = FALSE GROUP BY tipo");
        $stmt->execute([$idSesion]);
        $resumen = $stmt->fetchAll();

        $filename = PDFGenerator::generarActaCierre($sesion, $resumen, 'acta_sesion_' . $sesion['numero_sesion']);
        header('Location: ' . BASE_URL . '/storage/documentos/' . $filename);
        exit;
    }

    public function comprobanteSesion($idSesion) {
        $this->requireAuth();
        $sesion = $this->db->prepare("SELECT * FROM sesiones_mensuales WHERE id_sesion = ?");
        $sesion->execute([$idSesion]);
        $s = $sesion->fetch();
        if (!$s) { http_response_code(404); exit; }

        $cobros = $this->db->prepare("SELECT c.*, CONCAT_WS(' ', s.apellido1, s.apellido2, s.nombre1, s.nombre2) AS socio_nombre, s.cedula
                                       FROM cobros c JOIN socios s ON c.id_socio = s.id_socio
                                       WHERE c.id_sesion = ? AND c.anulado = FALSE
                                       ORDER BY s.apellido1, c.fecha_registro");
        $cobros->execute([$idSesion]);
        $items = $cobros->fetchAll();

        $html = '<h2>Comprobante de cobro</h2>';
        $html .= '<p><strong>Sesion:</strong> #' . $s['numero_sesion'] . ' — ' . htmlspecialchars($s['titulo'] ?? '') . '</p>';
        $html .= '<p><strong>Fecha:</strong> ' . $s['fecha_sesion'] . '</p>';
        $html .= '<table border="1" cellpadding="6" cellspacing="0" style="width:100%;border-collapse:collapse;margin-top:15px">';
        $html .= '<tr style="background:#1a3a5c;color:#fff">
                    <th>#</th><th>Socio</th><th>Cedula</th><th>Concepto</th><th>Monto</th>
                  </tr>';
        $total = 0;
        $num = 1;
        foreach ($items as $c) {
            $tipoLabel = ucfirst(str_replace('_', ' ', $c['tipo']));
            $html .= '<tr>
                        <td>' . $num++ . '</td>
                        <td>' . htmlspecialchars($c['socio_nombre']) . '</td>
                        <td>' . $c['cedula'] . '</td>
                        <td>' . $tipoLabel . '</td>
                        <td style="text-align:right">$' . number_format($c['monto'], 2) . '</td>
                      </tr>';
            $total += floatval($c['monto']);
        }
        $html .= '<tr style="font-weight:bold;background:#f0f0f0">
                    <td colspan="4" style="text-align:right">TOTAL COBRADO:</td>
                    <td style="text-align:right">$' . number_format($total, 2) . '</td>
                  </tr>';
        $html .= '</table>';
        $html .= '<p style="margin-top:20px;font-size:10pt;color:#666">Documento generado el ' . date('d/m/Y H:i:s') . '</p>';
        $html .= '<div class="no-print" style="text-align:center;margin-top:20px">
                    <button onclick="window.print()" style="padding:10px 30px;font-size:14px;cursor:pointer">Imprimir</button>
                  </div>';

        $htmlCompleto = '<!DOCTYPE html><html lang="es"><head><meta charset="UTF-8"><style>
            body{font-family:Arial,sans-serif;margin:2cm;font-size:12pt}
            table{width:100%;border-collapse:collapse}
            th,td{border:1px solid #999;padding:6px;text-align:left}
            th{background:#1a3a5c;color:#fff}
            .no-print{text-align:center;margin-top:20px}
            @media print{.no-print{display:none}}
        </style></head><body>' . $html . '</body></html>';

        file_put_contents(ROOT_PATH . '/storage/documentos/comprobante_sesion_' . $s['numero_sesion'] . '.html', $htmlCompleto);
        header('Location: ' . BASE_URL . '/storage/documentos/comprobante_sesion_' . $s['numero_sesion'] . '.html');
        exit;
    }

    public function comprobanteSocio($idSesion, $idSocio) {
        $this->requireAuth();
        $sesion = $this->db->prepare("SELECT * FROM sesiones_mensuales WHERE id_sesion = ?");
        $sesion->execute([$idSesion]);
        $s = $sesion->fetch();
        if (!$s) { http_response_code(404); exit; }

        $socio = $this->db->prepare("SELECT CONCAT_WS(' ', apellido1, apellido2, nombre1, nombre2) AS nombre, cedula FROM socios WHERE id_socio = ?");
        $socio->execute([$idSocio]);
        $soc = $socio->fetch();
        if (!$soc) { http_response_code(404); exit; }

        // Get all obligations for this socio in this session
        $obligaciones = $this->db->prepare("SELECT * FROM obligaciones_sesion WHERE id_sesion = ? AND id_socio = ? ORDER BY tipo");
        $obligaciones->execute([$idSesion, $idSocio]);
        $items = $obligaciones->fetchAll();

        $totalPagado = 0;
        $totalPendiente = 0;

        $html = '<h2>Comprobante de pago</h2>';
        $html .= '<p><strong>Sesion:</strong> #' . $s['numero_sesion'] . ' — ' . htmlspecialchars($s['titulo'] ?? '') . '</p>';
        $html .= '<p><strong>Fecha:</strong> ' . $s['fecha_sesion'] . '</p>';
        $html .= '<p><strong>Socio:</strong> ' . htmlspecialchars($soc['nombre']) . ' — <strong>Cedula:</strong> ' . $soc['cedula'] . '</p>';

        $html .= '<table border="1" cellpadding="6" cellspacing="0" style="width:100%;border-collapse:collapse;margin-top:15px">';
        $html .= '<tr style="background:#1a3a5c;color:#fff">
                    <th>Concepto</th><th>Monto</th><th>Estado</th>
                  </tr>';
        foreach ($items as $o) {
            $pagada = !empty($o['pagada']);
            $estado = $pagada ? '<span style="color:green;font-weight:bold">COBRADO</span>' : '<span style="color:red;font-weight:bold">PENDIENTE</span>';
            if ($pagada) $totalPagado += floatval($o['monto']);
            else $totalPendiente += floatval($o['monto']);
            $html .= '<tr>
                        <td>' . htmlspecialchars($o['concepto']) . '</td>
                        <td style="text-align:right">$' . number_format($o['monto'], 2) . '</td>
                        <td>' . $estado . '</td>
                      </tr>';
        }
        $html .= '<tr style="font-weight:bold;background:#e8f5e9">
                    <td>TOTAL COBRADO</td>
                    <td style="text-align:right">$' . number_format($totalPagado, 2) . '</td>
                    <td><span style="color:green">✓</span></td>
                  </tr>';
        $html .= '<tr style="font-weight:bold;background:#fbe9e7">
                    <td>TOTAL PENDIENTE</td>
                    <td style="text-align:right">$' . number_format($totalPendiente, 2) . '</td>
                    <td><span style="color:red">✗</span></td>
                  </tr>';
        $html .= '</table>';
        $html .= '<p style="margin-top:20px;font-size:10pt;color:#666">Documento generado el ' . date('d/m/Y H:i:s') . '</p>';
        $html .= '<div class="no-print" style="text-align:center;margin-top:20px">
                    <button onclick="window.print()" style="padding:10px 30px;font-size:14px;cursor:pointer">Imprimir</button>
                  </div>';

        $htmlCompleto = '<!DOCTYPE html><html lang="es"><head><meta charset="UTF-8"><style>
            body{font-family:Arial,sans-serif;margin:2cm;font-size:12pt}
            table{width:100%;border-collapse:collapse}
            th,td{border:1px solid #999;padding:8px;text-align:left}
            th{background:#1a3a5c;color:#fff}
            .no-print{text-align:center;margin-top:20px}
            @media print{.no-print{display:none}}
        </style></head><body>' . $html . '</body></html>';

        $filename = 'comprobante_socio_' . substr($idSocio, 0, 8) . '_sesion_' . $s['numero_sesion'] . '.html';
        file_put_contents(ROOT_PATH . '/storage/documentos/' . $filename, $htmlCompleto);
        header('Location: ' . BASE_URL . '/storage/documentos/' . $filename);
        exit;
    }

    public function actaCierre($idSesion) {