<?php
require_once ROOT_PATH . '/app/helpers/CajaHelper.php';

class CajaController extends BaseController {

    public function estadoCuenta() {
        $this->requireAuth();
        $desde = $_GET['desde'] ?? date('Y-m-d', strtotime('-30 days'));
        $hasta = $_GET['hasta'] ?? date('Y-m-d');
        $categoria = $_GET['categoria'] ?? '';

        $where = "WHERE DATE(m.fecha_registro) BETWEEN ? AND ?";
        $params = [$desde, $hasta];
        if ($categoria) { $where .= " AND m.categoria = ?"; $params[] = $categoria; }

        $movimientos = $this->db->prepare("SELECT m.*, ses.numero_sesion FROM caja_movimientos m LEFT JOIN sesiones_mensuales ses ON m.id_sesion = ses.id_sesion $where ORDER BY m.fecha_registro DESC");
        $movimientos->execute($params);
        $movimientos = $movimientos->fetchAll();

        $saldoActual = CajaHelper::obtenerSaldo();

        // Resumen por categoria
        $resumen = $this->db->prepare("SELECT categoria, tipo_movimiento, SUM(monto) AS total FROM caja_movimientos $where GROUP BY categoria, tipo_movimiento ORDER BY categoria");
        $resumen->execute($params);
        $resumen = $resumen->fetchAll();

        // Lista de categorias disponibles para el filtro
        $categorias = $this->db->query("SELECT DISTINCT categoria FROM caja_movimientos ORDER BY categoria")->fetchAll(PDO::FETCH_COLUMN);

        $this->render('caja/estadoCuenta', [
            'titulo' => 'Capital de Caja',
            'movimientos' => $movimientos,
            'saldoActual' => $saldoActual,
            'resumen' => $resumen,
            'categorias' => $categorias,
            'desde' => $desde,
            'hasta' => $hasta,
            'categoriaSel' => $categoria,
        ]);
    }

    public function exportarCSV() {
        $this->requireAuth();
        $desde = $_GET['desde'] ?? date('Y-m-d', strtotime('-30 days'));
        $hasta = $_GET['hasta'] ?? date('Y-m-d');
        $categoria = $_GET['categoria'] ?? '';

        $where = "WHERE DATE(m.fecha_registro) BETWEEN ? AND ?";
        $params = [$desde, $hasta];
        if ($categoria) { $where .= " AND m.categoria = ?"; $params[] = $categoria; }

        $stmt = $this->db->prepare("SELECT m.fecha_registro, m.concepto, m.categoria, IF(m.tipo_movimiento='ingreso', m.monto, 0) AS ingreso, IF(m.tipo_movimiento='egreso', m.monto, 0) AS egreso, m.saldo_posterior FROM caja_movimientos m $where ORDER BY m.fecha_registro ASC");
        $stmt->execute($params);

        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="capital_caja_' . date('Ymd') . '.csv"');
        $output = fopen('php://output', 'w');
        fprintf($output, chr(0xEF) . chr(0xBB) . chr(0xBF)); // BOM UTF-8
        fputcsv($output, ['Fecha', 'Concepto', 'Categoria', 'Ingreso', 'Egreso', 'Saldo']);
        foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
            fputcsv($output, [$row['fecha_registro'], $row['concepto'], $row['categoria'], $row['ingreso'] ? number_format($row['ingreso'], 2) : '', $row['egreso'] ? number_format($row['egreso'], 2) : '', number_format($row['saldo_posterior'], 2)]);
        }
        fclose($output);
        exit;
    }

    public function exportarXLSX() {
        $this->requireAuth();
        $desde = $_GET['desde'] ?? date('Y-m-d', strtotime('-30 days'));
        $hasta = $_GET['hasta'] ?? date('Y-m-d');
        $categoria = $_GET['categoria'] ?? '';

        $where = "WHERE DATE(m.fecha_registro) BETWEEN ? AND ?";
        $params = [$desde, $hasta];
        if ($categoria) { $where .= " AND m.categoria = ?"; $params[] = $categoria; }

        $stmt = $this->db->prepare("SELECT m.*, ses.numero_sesion FROM caja_movimientos m LEFT JOIN sesiones_mensuales ses ON m.id_sesion = ses.id_sesion $where ORDER BY m.fecha_registro ASC");
        $stmt->execute($params);
        $rows = $stmt->fetchAll();

        header('Content-Type: application/vnd.ms-excel');
        header('Content-Disposition: attachment; filename="capital_caja_' . date('Ymd') . '.xls"');

        echo '<table border="1">';
        echo '<tr><th>Fecha</th><th>Concepto</th><th>Categoria</th><th>Ingreso</th><th>Egreso</th><th>Saldo</th></tr>';
        foreach ($rows as $r) {
            $ingreso = $r['tipo_movimiento'] === 'ingreso' ? number_format($r['monto'], 2) : '';
            $egreso = $r['tipo_movimiento'] === 'egreso' ? number_format($r['monto'], 2) : '';
            echo "<tr><td>{$r['fecha_registro']}</td><td>" . htmlspecialchars($r['concepto']) . "</td><td>{$r['categoria']}</td><td>$ingreso</td><td>$egreso</td><td>" . number_format($r['saldo_posterior'], 2) . "</td></tr>";
        }
        echo '</table>';
        exit;
    }

    public function exportarPDF() {
        $this->requireAuth();
        $desde = $_GET['desde'] ?? date('Y-m-d', strtotime('-30 days'));
        $hasta = $_GET['hasta'] ?? date('Y-m-d');
        $categoria = $_GET['categoria'] ?? '';

        $where = "WHERE DATE(m.fecha_registro) BETWEEN ? AND ?";
        $params = [$desde, $hasta];
        if ($categoria) { $where .= " AND m.categoria = ?"; $params[] = $categoria; }

        $stmt = $this->db->prepare("SELECT m.*, ses.numero_sesion FROM caja_movimientos m LEFT JOIN sesiones_mensuales ses ON m.id_sesion = ses.id_sesion $where ORDER BY m.fecha_registro ASC");
        $stmt->execute($params);
        $rows = $stmt->fetchAll();
        $saldo = CajaHelper::obtenerSaldo();

        $html = '<h2>Capital de Caja - Estado de Cuenta</h2>';
        $html .= '<p><strong>Periodo:</strong> ' . $desde . ' al ' . $hasta . '</p>';
        $html .= '<p><strong>Saldo actual:</strong> $' . number_format($saldo, 2) . '</p>';
        $html .= '<table border="1" cellpadding="6" cellspacing="0" style="width:100%;border-collapse:collapse;margin-top:15px">';
        $html .= '<tr style="background:#1a3a5c;color:#fff"><th>Fecha</th><th>Concepto</th><th>Categoria</th><th>Ingreso</th><th>Egreso</th><th>Saldo</th></tr>';
        foreach ($rows as $r) {
            $ingreso = $r['tipo_movimiento'] === 'ingreso' ? '$' . number_format($r['monto'], 2) : '';
            $egreso = $r['tipo_movimiento'] === 'egreso' ? '$' . number_format($r['monto'], 2) : '';
            $html .= '<tr>';
            $html .= '<td>' . $r['fecha_registro'] . '</td>';
            $html .= '<td>' . htmlspecialchars($r['concepto']) . '</td>';
            $html .= '<td>' . $r['categoria'] . '</td>';
            $html .= '<td style="text-align:right;color:green;font-weight:bold">' . $ingreso . '</td>';
            $html .= '<td style="text-align:right;color:red;font-weight:bold">' . $egreso . '</td>';
            $html .= '<td style="text-align:right">$' . number_format($r['saldo_posterior'], 2) . '</td>';
            $html .= '</tr>';
        }
        $html .= '</table>';
        $html .= '<p style="margin-top:20px;font-size:10pt;color:#666">Generado el ' . date('d/m/Y H:i:s') . '</p>';
        $html .= '<div class="no-print" style="text-align:center;margin-top:20px"><button onclick="window.print()" style="padding:10px 30px;font-size:14px;cursor:pointer">Imprimir / Guardar PDF</button></div>';

        echo '<!DOCTYPE html><html lang="es"><head><meta charset="UTF-8"><style>
            body{font-family:Arial,sans-serif;margin:2cm;font-size:12pt}
            table{width:100%;border-collapse:collapse}
            th,td{border:1px solid #888;padding:6px;text-align:left}
            th{background:#1a3a5c;color:#fff}
            .no-print{text-align:center;margin-top:20px}
            @media print{.no-print{display:none}}
        </style></head><body>' . $html . '</body></html>';
        exit;
    }
}
