<?php
class PDFGenerator {

    public static function generarComprobante($data, $filename) {
        $dir = dirname(__DIR__, 2) . '/storage/documentos/';
        if (!is_dir($dir)) mkdir($dir, 0755, true);
        $html = self::layout(self::plantillaComprobante($data));
        file_put_contents($dir . $filename . '.html', $html);
        return $filename . '.html';
    }

    public static function generarActaCierre($sesion, $resumen, $filename) {
        $html = self::layout(self::plantillaActa($sesion, $resumen));
        $dir = dirname(__DIR__, 2) . '/storage/documentos/';
        if (!is_dir($dir)) mkdir($dir, 0755, true);
        file_put_contents($dir . $filename . '.html', $html);
        return $filename . '.html';
    }

    private static function layout($body) {
        $logo = BASE_URL . '/public/assets/images/favicon.svg';
        try {
            $stmt = Database::getInstance()->prepare("SELECT valor FROM parametros WHERE codigo = 'logo_sidebar'");
            $stmt->execute();
            $logoId = $stmt->fetchColumn();
            if ($logoId) $logo = BASE_URL . '/archivo/ver/' . $logoId;
        } catch (Exception $e) {}
        return '<!DOCTYPE html>
<html lang="es">
<head><meta charset="UTF-8">
<style>
    @page { margin: 2cm; }
    body { font-family: "Segoe UI", Arial, sans-serif; font-size: 12pt; color: #333; line-height: 1.5; }
    .header { text-align: center; border-bottom: 2px solid #1a3a5c; padding-bottom: 15px; margin-bottom: 20px; }
    .header img { max-height: 80px; }
    .header h2 { margin: 5px 0; color: #1a3a5c; font-size: 16pt; }
    .header small { color: #666; font-size: 9pt; }
    table { width: 100%; border-collapse: collapse; margin: 10px 0; }
    table th { background: #1a3a5c; color: #fff; padding: 6px 8px; text-align: left; font-size: 10pt; }
    table td { padding: 5px 8px; border-bottom: 1px solid #ddd; font-size: 10pt; }
    table tr:nth-child(even) td { background: #f5f5f5; }
    .total-row td { font-weight: bold; border-top: 2px solid #1a3a5c; }
    .footer { margin-top: 30px; padding-top: 15px; border-top: 1px solid #ccc; font-size: 9pt; color: #666; text-align: center; }
    .hash { font-family: monospace; font-size: 8pt; color: #999; word-break: break-all; }
    .firma { margin-top: 40px; }
    .firma table td { border: none; text-align: center; padding-top: 30px; }
    .firma table td div { border-top: 1px solid #333; width: 200px; margin: 0 auto; font-size: 9pt; }
    @media print { .no-print { display: none; } }
</style></head><body>
<div class="header">
    <img src="' . $logo . '" alt="Logo">
    <h2>' . APP_NAME . '</h2>
    <small>Sistema de Gestión Financiera</small>
</div>
' . $body . '
<div class="footer">
    <p>Documento generado electrónicamente el ' . date('d/m/Y H:i:s') . '</p>
    <p class="hash">Hash SHA-256: ' . hash('sha256', $body) . '</p>
</div>
<div class="no-print" style="text-align:center;margin-top:20px">
    <button onclick="window.print()" style="padding:10px 30px;font-size:14px;cursor:pointer">Imprimir / Guardar PDF</button>
</div>
</body></html>';
    }

    public static function generarConstancia($data, $filename) {
        $h = '<h3>Constancia de socio activo</h3>';
        $h .= '<p style="font-size:11pt;text-align:justify">';
        $h .= 'Por medio de la presente, ' . APP_NAME . ' certifica que el/la señor(a) <strong>' . htmlspecialchars($data['socio']) . '</strong>';
        $h .= ', portador(a) de la cedula de identidad N° <strong>' . htmlspecialchars($data['cedula'] ?? '') . '</strong>,';
        $h .= ' se encuentra registrado(a) como socio(a) activo(a) desde el <strong>' . $data['fecha_ingreso'] . '</strong>.';
        $h .= '</p><p style="font-size:10pt;color:#666">Estado actual: <strong>' . ucfirst($data['estado']) . '</strong></p>';
        $h .= '<div class="firma"><table><tr>';
        $h .= '<td><div>_________________________________<br>Presidente</div></td>';
        $h .= '<td><div>_________________________________<br>Secretario</div></td>';
        $h .= '</tr></table></div>';
        $html = self::layout($h);
        $dir = dirname(__DIR__, 2) . '/storage/documentos/';
        if (!is_dir($dir)) mkdir($dir, 0755, true);
        file_put_contents($dir . $filename . '.html', $html);
        return $filename . '.html';
    }

    public static function generarLibreDeuda($data, $filename) {
        $h = '<h3>Certificado de libre deuda</h3>';
        $h .= '<p style="font-size:11pt;text-align:justify">';
        $h .= 'Por medio de la presente, ' . APP_NAME . ' certifica que el/la señor(a) <strong>' . htmlspecialchars($data['socio']) . '</strong>';
        $h .= ', portador(a) de la cedula de identidad N° <strong>' . htmlspecialchars($data['cedula'] ?? '') . '</strong>,';
        $h .= ' se encuentra al día en sus obligaciones con la institución.</p>';
        $h .= '<p style="font-size:12pt;text-align:center;padding:15px;background:#e8f5e9;border-radius:5px">';
        $h .= $data['libre_deuda'] ? '✅ CERTIFICADO SIN DEUDA' : '❌ REGISTRA OBLIGACIONES PENDIENTES';
        $h .= '</p>';
        $h .= '<div class="firma"><table><tr>';
        $h .= '<td><div>_________________________________<br>Presidente</div></td>';
        $h .= '<td><div>_________________________________<br>Secretario</div></td>';
        $h .= '</tr></table></div>';
        $html = self::layout($h);
        $dir = dirname(__DIR__, 2) . '/storage/documentos/';
        if (!is_dir($dir)) mkdir($dir, 0755, true);
        file_put_contents($dir . $filename . '.html', $html);
        return $filename . '.html';
    }

    public static function generarContratoInversion($data, $filename) {
        $h = '<h3>Contrato de inversión</h3>';
        $h .= '<p style="font-size:11pt;text-align:justify">';
        $h .= 'Entre ' . APP_NAME . ', representada por su Presidente y el/la señor(a) <strong>' . htmlspecialchars($data['socio']) . '</strong>';
        $h .= ', portador(a) de la cedula N° <strong>' . htmlspecialchars($data['cedula']) . '</strong>,';
        $h .= ' se acuerda la apertura de una inversión por <strong>$ ' . number_format($data['monto'], 2) . '</strong>';
        $h .= ' en el producto <strong>' . htmlspecialchars($data['producto']) . '</strong>.';
        $h .= '</p>';
        $h .= '<table>';
        $h .= '<tr><td width="180"><strong>Monto:</strong></td><td>$ ' . number_format($data['monto'], 2) . '</td></tr>';
        $h .= '<tr><td><strong>Tasa interes anual:</strong></td><td>' . $data['tasa'] . '%</td></tr>';
        $h .= '<tr><td><strong>Plazo:</strong></td><td>' . $data['plazo'] . ' meses</td></tr>';
        $h .= '<tr><td><strong>Rendimiento proyectado:</strong></td><td>$ ' . number_format($data['rendimiento'], 2) . '</td></tr>';
        $h .= '<tr><td><strong>Fecha de inicio:</strong></td><td>' . $data['fecha_inicio'] . '</td></tr>';
        $h .= '<tr><td><strong>Fecha de vencimiento:</strong></td><td>' . $data['fecha_vencimiento'] . '</td></tr>';
        $destinoLabel = ['capital_inversion' => 'Reinversion (capital de inversion)', 'efectivo' => 'Efectivo', 'transferencia' => 'Transferencia'];
        $h .= '<tr><td><strong>Destino al vencimiento:</strong></td><td>' . ($destinoLabel[$data['destino'] ?? 'capital_inversion'] ?? $data['destino']) . '</td></tr>';
        $h .= '</table>';
        $h .= '<p style="font-size:10pt;text-align:justify;margin-top:20px">El socio acepta los terminos y condiciones establecidos en el reglamento interno de la institucion.</p>';
        $h .= '<div class="firma"><table><tr>';
        $h .= '<td><div>_________________________________<br>Presidente</div></td>';
        $h .= '<td><div>_________________________________<br>Inversionista</div></td>';
        $h .= '</tr></table></div>';
        $html = self::layout($h);
        $dir = dirname(__DIR__, 2) . '/storage/documentos/';
        if (!is_dir($dir)) mkdir($dir, 0755, true);
        file_put_contents($dir . $filename . '.html', $html);
        return $filename . '.html';
    }

    public static function generarEstadoCuenta($data, $filename) {
        $h = '<h3>Estado de cuenta</h3>';
        $h .= '<table>';
        $h .= '<tr><td width="150"><strong>Socio:</strong></td><td>' . htmlspecialchars($data['socio']) . '</td></tr>';
        $h .= '<tr><td><strong>Cédula:</strong></td><td>' . htmlspecialchars($data['cedula']) . '</td></tr>';
        $h .= '<tr><td><strong>Período:</strong></td><td>' . $data['periodo'] . '</td></tr>';
        $h .= '<tr><td><strong>Fecha:</strong></td><td>' . $data['fecha'] . '</td></tr>';
        $h .= '<tr><td><strong>Saldo actual:</strong></td><td style="font-size:13pt;font-weight:bold">$ ' . number_format($data['saldo_actual'], 2) . '</td></tr>';
        $h .= '</table>';
        $h .= '<h4>Movimientos</h4>';
        $h .= '<table><tr><th>Fecha</th><th>Concepto</th><th>Débito</th><th>Crédito</th></tr>';
        foreach ($data['movimientos'] as $m) {
            $h .= '<tr>';
            $h .= '<td>' . $m['fecha'] . '</td>';
            $h .= '<td>' . htmlspecialchars($m['concepto']) . '</td>';
            $h .= '<td>' . ($m['tipo'] === 'debito' ? '$ ' . number_format($m['monto'], 2) : '') . '</td>';
            $h .= '<td>' . ($m['tipo'] === 'credito' ? '$ ' . number_format($m['monto'], 2) : '') . '</td>';
            $h .= '</tr>';
        }
        $h .= '</table>';
        $h .= '<div class="firma"><table><tr>';
        $h .= '<td><div>_________________________________<br>Presidente</div></td>';
        $h .= '<td><div>_________________________________<br>Tesorero</div></td>';
        $h .= '</tr></table></div>';
        $html = self::layout($h);
        $dir = dirname(__DIR__, 2) . '/storage/documentos/';
        if (!is_dir($dir)) mkdir($dir, 0755, true);
        file_put_contents($dir . $filename . '.html', $html);
        return $filename . '.html';
    }

    private static function plantillaComprobante($data) {
        $h = '<h3>Comprobante de ' . htmlspecialchars($data['tipo'] ?? 'pago') . '</h3>';
        $h .= '<table>';
        $h .= '<tr><td width="120"><strong>Fecha:</strong></td><td>' . $data['fecha'] . '</td></tr>';
        $h .= '<tr><td><strong>Socio:</strong></td><td>' . htmlspecialchars($data['socio']) . '</td></tr>';
        $h .= '<tr><td><strong>Cédula:</strong></td><td>' . htmlspecialchars($data['cedula'] ?? '') . '</td></tr>';
        $h .= '<tr><td><strong>Concepto:</strong></td><td>' . htmlspecialchars($data['concepto']) . '</td></tr>';
        $h .= '<tr><td><strong>Sesión:</strong></td><td>' . htmlspecialchars($data['sesion'] ?? '') . '</td></tr>';
        $h .= '<tr><td><strong>Monto:</strong></td><td style="font-size:14pt;font-weight:bold">$ ' . number_format($data['monto'], 2) . '</td></tr>';
        $h .= '<tr><td><strong>Medio de pago:</strong></td><td>' . htmlspecialchars($data['medio_pago'] ?? '') . '</td></tr>';
        $h .= '</table>';
        $h .= '<div class="hash"><strong>Hash integridad:</strong> ' . $data['hash'] . '</div>';
        return $h;
    }

    private static function plantillaActa($sesion, $resumen) {
        $h = '<h3>Acta de cierre — Sesión #' . $sesion['numero_sesion'] . '</h3>';
        $h .= '<table>';
        $h .= '<tr><td width="150"><strong>Fecha de cierre:</strong></td><td>' . $sesion['fecha_cierre'] . '</td></tr>';
        $h .= '<tr><td><strong>Fecha de sesión:</strong></td><td>' . $sesion['fecha'] . '</td></tr>';
        $h .= '<tr><td><strong>Título:</strong></td><td>' . htmlspecialchars($sesion['titulo'] ?? 'Sesión #' . $sesion['numero_sesion']) . '</td></tr>';
        $h .= '<tr><td><strong>Total recaudado:</strong></td><td>$ ' . number_format($sesion['total_recaudado'], 2) . '</td></tr>';
        $h .= '<tr><td><strong>Total desembolsado:</strong></td><td>$ ' . number_format($sesion['total_desembolsado'], 2) . '</td></tr>';
        $h .= '<tr><td><strong>Saldo de caja:</strong></td><td>$ ' . number_format($sesion['saldo_caja'], 2) . '</td></tr>';
        $h .= '</table>';

        if (!empty($resumen)) {
            $h .= '<h4>Detalle de cobros</h4><table><tr><th>Tipo</th><th>Cantidad</th><th>Total</th></tr>';
            foreach ($resumen as $r) {
                $h .= '<tr><td>' . htmlspecialchars($r['tipo']) . '</td><td>' . $r['total'] . '</td><td>$ ' . number_format($r['suma'], 2) . '</td></tr>';
            }
            $h .= '</table>';
        }

        $h .= '<div class="firma"><table><tr>';
        $h .= '<td><div>_________________________________<br>Presidente</div></td>';
        $h .= '<td><div>_________________________________<br>Tesorero</div></td>';
        $h .= '<td><div>_________________________________<br>Secretario</div></td>';
        $h .= '</tr></table></div>';
        return $h;
    }
}
