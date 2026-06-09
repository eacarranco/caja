<?php
require_once ROOT_PATH . '/app/helpers/NotificacionHelper.php';

class CobroController extends BaseController {

    private $tiposCobro = [
        'aporte_obligatorio' => 'Aporte obligatorio',
        'aporte_excedente' => 'Aporte excedente',
        'cuota_credito' => 'Cuota de crédito',
        'multa' => 'Multa',
        'inversion' => 'Inversión',
        'desembolso' => 'Desembolso',
        'interes' => 'Interés',
        'otro' => 'Otro',
    ];

    private $mediosPago = ['efectivo' => 'Efectivo', 'transferencia' => 'Transferencia', 'compensacion' => 'Compensación', 'digital' => 'Digital'];

    public function listar() {
        $this->requirePermission('cobro.aporte');
        $stmt = $this->db->query("SELECT c.*, s.cedula, CONCAT_WS(' ', s.apellido1, s.apellido2, s.nombre1, s.nombre2) AS socio,
                                   ses.numero_sesion, ses.fecha AS fecha_sesion
                                   FROM cobros c
                                   JOIN socios s ON c.id_socio = s.id_socio
                                   LEFT JOIN sesiones_mensuales ses ON c.id_sesion = ses.id_sesion
                                   WHERE c.anulado = FALSE
                                   ORDER BY c.fecha_registro DESC");
        $cobros = $stmt->fetchAll();
        $sesionAbierta = $this->db->query("SELECT id_sesion FROM sesiones_mensuales WHERE estado = 'abierta' LIMIT 1")->fetchColumn();
        $this->render('cobros/listar', [
            'titulo' => 'Cobros',
            'cobros' => $cobros,
            'tiposCobro' => $this->tiposCobro,
            'mediosPago' => $this->mediosPago,
            'sesionAbierta' => $sesionAbierta,
        ]);
    }

    public function registrar($idSesion = null) {
        $this->requirePermission('cobro.aporte');
        $errors = [];

        $stmt = $this->db->prepare("SELECT * FROM sesiones_mensuales WHERE id_sesion = ? AND estado = 'abierta'");
        $stmt->execute([$idSesion]);
        $sesion = $stmt->fetch();
        if (!$sesion) $this->redirect('/sesion/listar');

        $socios = $this->db->query("SELECT id_socio, cedula, CONCAT_WS(' ', apellido1, apellido2, nombre1, nombre2) AS nombre FROM socios WHERE estado = 'activo' ORDER BY apellido1, nombre1")->fetchAll();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->validateCSRF();

            $idSocio = $_POST['id_socio'] ?? '';
            $tipo = $_POST['tipo'] ?? '';
            $monto = str_replace(',', '.', $_POST['monto'] ?? '0');
            $medioPago = $_POST['medio_pago'] ?? 'efectivo';

            if (empty($idSocio)) $errors['id_socio'] = 'Seleccione un socio';
            if (!isset($this->tiposCobro[$tipo])) $errors['tipo'] = 'Tipo inválido';
            if (!is_numeric($monto) || $monto <= 0) $errors['monto'] = 'Monto inválido';

            if (empty($errors)) {
                $idCobro = UUIDGenerator::generar();

                $data = $idSocio . $idSesion . $tipo . $monto . $idCobro . date('Y-m-d H:i:s');
                $hash = hash('sha256', $data);

                $stmt = $this->db->prepare("INSERT INTO cobros
                    (id_cobro, id_socio, id_sesion, tipo, monto, medio_pago, hash_integridad, usuario_registra)
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
                $stmt->execute([$idCobro, $idSocio, $idSesion, $tipo, $monto, $medioPago, $hash, $_SESSION['usuario_id']]);

                $this->actualizarCuentaAhorro($idSocio, $tipo, $monto);

                if ($tipo === 'cuota_credito') {
                    $this->requirePermission('cobro.cuota_credito');
                    $this->aplicarPagoCuota($idSocio, $monto, $idCobro);
                }

                $histTipo = $this->mapearTipoHistorial($tipo);
                if ($histTipo) {
                    $this->historialInsert($idSocio, $histTipo, $monto, $idCobro, $idSesion);
                }

                $stmt = $this->db->prepare("SELECT CONCAT_WS(' ', apellido1, apellido2, nombre1, nombre2) FROM socios WHERE id_socio = ?");
                $stmt->execute([$idSocio]);
                $nombreSocio = $stmt->fetchColumn();
                NotificacionHelper::crearCobro($idSocio, $nombreSocio, $monto, $this->tiposCobro[$tipo]);

                $this->json(['mensaje' => 'Cobro registrado', 'id_cobro' => $idCobro]);
            }
            $this->json(['error' => implode(', ', $errors)], 400);
        }

        $this->render('cobros/registrar', [
            'titulo' => 'Registrar cobro — Sesión #' . $sesion['numero_sesion'],
            'sesion' => $sesion,
            'socios' => $socios,
            'tiposCobro' => array_diff_key($this->tiposCobro, ['desembolso' => 1, 'interes' => 1]),
            'mediosPago' => $this->mediosPago,
        ]);
    }

    public function anular($id) {
        $this->requirePermission('cobro.anular');
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->validateCSRF();
            $stmt = $this->db->prepare("UPDATE cobros SET anulado = TRUE, motivo_anulacion = ?, fecha_anulacion = NOW(), usuario_anula = ? WHERE id_cobro = ? AND anulado = FALSE");
            $stmt->execute([$_POST['motivo'] ?? '', $_SESSION['usuario_id'], $id]);
            $cobro = $this->db->prepare("SELECT id_socio, monto, tipo, id_sesion, id_referencia FROM cobros WHERE id_cobro = ?");
            $cobro->execute([$id]); $c = $cobro->fetch();
            if ($c) {
                if ($c['tipo'] === 'deposito_capital_inversion') {
                    $this->db->prepare("UPDATE capital_inversion SET saldo = saldo - ?, fecha_ultimo_movimiento = NOW() WHERE id_socio = ?")->execute([$c['monto'], $c['id_socio']]);
                    $this->historialInsert($c['id_socio'], 'anulacion', $c['monto'], $id, $c['id_sesion']);
                } elseif ($c['tipo'] === 'inversion' && !empty($c['id_referencia'])) {
                    $inv = $this->db->prepare("SELECT estado, destino_final FROM inversiones WHERE id_inversion = ?");
                    $inv->execute([$c['id_referencia']]);
                    $i = $inv->fetch();
                    $tipoHist = 'anulacion';
                    if ($i && $i['estado'] === 'activa') {
                        $this->db->prepare("UPDATE inversiones SET estado = 'cancelada' WHERE id_inversion = ?")->execute([$c['id_referencia']]);
                        if ($i['destino_final'] === 'capital_inversion') {
                            $this->db->prepare("UPDATE capital_inversion SET saldo = saldo + ?, fecha_ultimo_movimiento = NOW() WHERE id_socio = ?")->execute([$c['monto'], $c['id_socio']]);
                        }
                        $tipoHist = 'anulacion_inversion';
                    }
                    $this->historialInsert($c['id_socio'], $tipoHist, $c['monto'], $c['id_referencia'], $c['id_sesion']);
                } else {
                    $this->historialInsert($c['id_socio'], 'anulacion', $c['monto'], $id, $c['id_sesion']);
                }
            }
            $this->json(['mensaje' => 'Cobro anulado']);
        }
    }

    public function historialSesion($idSesion) {
        $this->requirePermission('cobro.aporte');
        $stmt = $this->db->prepare("SELECT c.*, s.cedula, CONCAT_WS(' ', s.apellido1, s.apellido2, s.nombre1, s.nombre2) AS socio
                                     FROM cobros c JOIN socios s ON c.id_socio = s.id_socio
                                     WHERE c.id_sesion = ? ORDER BY c.fecha_registro");
        $stmt->execute([$idSesion]);
        $cobros = $stmt->fetchAll();
        $this->render('cobros/historial_sesion', [
            'titulo' => 'Cobros de la sesión',
            'cobros' => $cobros,
            'tiposCobro' => $this->tiposCobro,
            'mediosPago' => $this->mediosPago,
        ]);
    }

    private function aplicarPagoCuota($idSocio, $monto, $idCobro) {
        $stmt = $this->db->prepare("SELECT a.id_amortizacion, a.id_credito, a.total
                                    FROM amortizaciones a
                                    JOIN creditos c ON a.id_credito = c.id_credito
                                    WHERE c.id_socio = ? AND a.estado IN ('pendiente','vencida')
                                    ORDER BY a.fecha_vencimiento ASC LIMIT 1");
        $stmt->execute([$idSocio]);
        $cuota = $stmt->fetch();
        if ($cuota) {
            $totalCuota = (float)$cuota['total'];
            $montoPagado = (float)$monto;
            if ($montoPagado < $totalCuota) {
                $_SESSION['error'] = "El monto ($" . number_format($montoPagado, 2) . ") no cubre el total de la cuota ($" . number_format($totalCuota, 2) . ")";
                return;
            }
            $this->db->prepare("UPDATE amortizaciones SET estado = 'pagada', id_cobro = ? WHERE id_amortizacion = ?")
                ->execute([$idCobro, $cuota['id_amortizacion']]);
            $vuelto = $montoPagado - $totalCuota;
            if ($vuelto > 0) {
                $_SESSION['info'] = "Cuota pagada. Vuelto: $" . number_format($vuelto, 2);
            }
        }
    }

    private function actualizarCuentaAhorro($idSocio, $tipo, $monto) {
        if (in_array($tipo, ['aporte_obligatorio', 'aporte_excedente'])) {
            $col = $tipo === 'aporte_obligatorio' ? 'saldo_obligatorio' : 'saldo_excedente';

            $stmt = $this->db->prepare("SELECT COUNT(*) FROM cuentas_ahorro WHERE id_socio = ?");
            $stmt->execute([$idSocio]);
            if ($stmt->fetchColumn() == 0) {
                $this->db->prepare("INSERT INTO cuentas_ahorro (id_cuenta_ahorro, id_socio) VALUES (?, ?)")
                    ->execute([UUIDGenerator::generar(), $idSocio]);
            }

            $this->db->prepare("UPDATE cuentas_ahorro SET $col = $col + ?, saldo_disponible = saldo_disponible + ?, fecha_ultimo_movimiento = NOW() WHERE id_socio = ?")
                ->execute([$monto, $monto, $idSocio]);
        }
    }
}
