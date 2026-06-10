<?php
require_once ROOT_PATH . '/app/helpers/NotificacionHelper.php';
require_once ROOT_PATH . '/app/helpers/PusherHelper.php';

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
                                   ses.numero_sesion, ses.fecha_sesion
                                   FROM cobros c
                                   JOIN socios s ON c.id_socio = s.id_socio
                                   LEFT JOIN sesiones_mensuales ses ON c.id_sesion = ses.id_sesion
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
                // Sync obligaciones
                try {
                    $mapOblig = ['aporte_obligatorio' => 'cuota_mensual', 'cuota_credito' => 'cuota_credito', 'multa' => 'multa'];
                    $tipoOblig = $mapOblig[$tipo] ?? null;
                    if ($tipoOblig && $idSesion) {
                        $this->db->prepare("UPDATE obligaciones_sesion SET pagada = TRUE, id_cobro = ? WHERE id_socio = ? AND id_sesion = ? AND tipo = ? AND pagada = FALSE LIMIT 1")
                            ->execute([$idCobro, $idSocio, $idSesion, $tipoOblig]);
                    }
                } catch (Exception $e) {}
                try { PusherHelper::actualizarPortal($idSocio); } catch (Exception $e) {}

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
                $motivo = $_POST['motivo'] ?? 'Sin motivo';

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

                } elseif ($c['tipo'] === 'aporte_obligatorio') {
                    $this->db->prepare("UPDATE cuentas_ahorro SET saldo_obligatorio = GREATEST(saldo_obligatorio - ?, 0), saldo_disponible = GREATEST(saldo_disponible - ?, 0), fecha_ultimo_movimiento = NOW() WHERE id_socio = ?")->execute([$c['monto'], $c['monto'], $c['id_socio']]);
                    $this->historialInsert($c['id_socio'], 'anulacion', $c['monto'], $id, $c['id_sesion']);

                } elseif ($c['tipo'] === 'aporte_excedente') {
                    $this->db->prepare("UPDATE cuentas_ahorro SET saldo_excedente = GREATEST(saldo_excedente - ?, 0), saldo_disponible = GREATEST(saldo_disponible - ?, 0), fecha_ultimo_movimiento = NOW() WHERE id_socio = ?")->execute([$c['monto'], $c['monto'], $c['id_socio']]);
                    $this->historialInsert($c['id_socio'], 'anulacion', $c['monto'], $id, $c['id_sesion']);

                } elseif ($c['tipo'] === 'cuota_credito' && !empty($c['id_referencia'])) {
                    $amort = $this->db->prepare("SELECT estado FROM amortizaciones WHERE id_amortizacion = ?");
                    $amort->execute([$c['id_referencia']]);
                    if ($amort->fetchColumn() === 'pagada') {
                        $this->db->prepare("UPDATE amortizaciones SET estado = 'pendiente', id_cobro = NULL WHERE id_amortizacion = ?")->execute([$c['id_referencia']]);
                    }
                    $this->historialInsert($c['id_socio'], 'anulacion', $c['monto'], $id, $c['id_sesion']);

                } elseif ($c['tipo'] === 'multa' && !empty($c['id_referencia'])) {
                    $this->db->prepare("UPDATE multas SET pagada = FALSE WHERE id_multa = ?")->execute([$c['id_referencia']]);
                    $this->historialInsert($c['id_socio'], 'anulacion', $c['monto'], $id, $c['id_sesion']);

                } else {
                    $this->historialInsert($c['id_socio'], 'anulacion', $c['monto'], $id, $c['id_sesion']);
                }

                // Revertir obligacion si estaba marcada como pagada
                try {
                    $this->db->prepare("UPDATE obligaciones_sesion SET pagada = FALSE, id_cobro = NULL WHERE id_cobro = ?")->execute([$id]);
                } catch (Exception $e) {}

                // Notificacion portal + Pusher
                try {
                    $stSoc = $this->db->prepare("SELECT CONCAT_WS(' ', apellido1, apellido2, nombre1, nombre2) AS nombre, correo_electronico FROM socios WHERE id_socio = ?");
                    $stSoc->execute([$c['id_socio']]);
                    $socData = $stSoc->fetch();
                    $nombreSocio = $socData['nombre'] ?? 'Socio';
                    $correoSocio = $socData['correo_electronico'] ?? '';

                    NotificacionHelper::crear([
                        'id_socio' => $c['id_socio'],
                        'tipo' => 'anulacion',
                        'titulo' => 'Cobro anulado',
                        'mensaje' => "Se ha anulado un cobro de $$c[monto] ($c[tipo]). Motivo: $motivo",
                        'enviar_pusher' => true,
                    ]);

                    if ($correoSocio) {
                        require_once ROOT_PATH . '/app/helpers/EmailHelper.php';
                        EmailHelper::enviarNotificacion($correoSocio, $nombreSocio, 'Cobro anulado', "Se ha anulado un cobro por \$$c[monto]. Motivo: $motivo");
                    }
                } catch (Exception $e) {
                    error_log("Error notificacion anulacion: " . $e->getMessage());
                }

                try { PusherHelper::actualizarPortal($c['id_socio']); } catch (Exception $e) {}
            }
            $this->json(['mensaje' => 'Cobro anulado. Se ha notificado al socio.']);
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
