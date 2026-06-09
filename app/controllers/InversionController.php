<?php
require_once ROOT_PATH . '/app/helpers/PDFGenerator.php';
require_once ROOT_PATH . '/app/helpers/NotificacionHelper.php';
class InversionController extends BaseController {

    public function listar() {
        $this->requirePermission('cobro.inversion');
        $stmt = $this->db->query("SELECT i.*, CONCAT_WS(' ', s.apellido1, s.apellido2, s.nombre1, s.nombre2) AS socio,
                                   p.nombre AS producto
                                   FROM inversiones i
                                   JOIN socios s ON i.id_socio = s.id_socio
                                   JOIN productos_financieros p ON i.id_producto = p.id_producto
                                   ORDER BY i.fecha_registro DESC");
        $inversiones = $stmt->fetchAll();
        $this->render('inversiones/listar', [
            'titulo' => 'Inversiones',
            'inversiones' => $inversiones,
        ]);
    }

    public function apertura() {
        $this->requirePermission('cobro.inversion');
        $errors = [];
        $productos = $this->db->query("SELECT id_producto, nombre, tasa_interes_anual, plazo_min_meses, plazo_max_meses, monto_min, monto_max FROM productos_financieros WHERE tipo = 'inversion' AND activo = TRUE ORDER BY nombre")->fetchAll();
        $socios = $this->db->query("SELECT s.id_socio, s.cedula, CONCAT_WS(' ', s.apellido1, s.apellido2, s.nombre1, s.nombre2) AS nombre, COALESCE(ci.saldo, 0) AS capital_inversion FROM socios s LEFT JOIN capital_inversion ci ON s.id_socio = ci.id_socio WHERE s.estado = 'activo' ORDER BY s.apellido1, s.nombre1")->fetchAll();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->validateCSRF();
            $idSocio = $_POST['id_socio'] ?? '';
            $idProducto = $_POST['id_producto'] ?? '';
            $monto = str_replace(',', '.', $_POST['monto'] ?? '0');
            $plazo = intval($_POST['plazo'] ?? 1);
            $destino = $_POST['destino_final'] ?? 'capital_inversion';

            if (empty($idSocio)) $errors['id_socio'] = 'Seleccione un socio';
            if (empty($idProducto)) $errors['id_producto'] = 'Seleccione un producto';
            if (!in_array($destino, ['capital_inversion', 'efectivo', 'transferencia'])) $destino = 'capital_inversion';

            $prod = null;
            foreach ($productos as $p) {
                if ($p['id_producto'] === $idProducto) { $prod = $p; break; }
            }
            if (!$prod) $errors['id_producto'] = 'Producto invalido';
            if (!is_numeric($monto) || $monto <= 0) $errors['monto'] = 'Monto invalido';
            if ($plazo < ($prod['plazo_min_meses'] ?? 1) || $plazo > ($prod['plazo_max_meses'] ?? 999)) $errors['plazo'] = 'Plazo fuera de rango';

            if (empty($errors)) {
                $tasa = $prod['tasa_interes_anual'];
                $factor = $tasa / 100 / 12;
                $rendimiento = $monto * $factor * $plazo;

                $fechaInicio = date('Y-m-d');
                $fechaVenc = new DateTime($fechaInicio);
                $fechaVenc->modify('+' . $plazo . ' months');

                $id = UUIDGenerator::generar();
                $this->db->beginTransaction();
                try {
                    $this->db->prepare("INSERT INTO inversiones
                        (id_inversion, id_socio, id_producto, monto, plazo_meses, tasa_interes, fecha_inicio, fecha_vencimiento, rendimiento_proyectado, destino_final)
                        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)")
                        ->execute([$id, $idSocio, $idProducto, $monto, $plazo, $tasa, $fechaInicio, $fechaVenc->format('Y-m-d'), round($rendimiento, 2), $destino]);

                    $hasCap = $this->db->query("SELECT COUNT(*) FROM capital_inversion WHERE id_socio = '$idSocio'")->fetchColumn();
                    if ($hasCap == 0) {
                        $this->db->prepare("INSERT INTO capital_inversion (id_capital_inversion, id_socio) VALUES (?, ?)")->execute([UUIDGenerator::generar(), $idSocio]);
                    }
                    $this->db->prepare("UPDATE capital_inversion SET saldo = saldo - ?, fecha_ultimo_movimiento = NOW() WHERE id_socio = ?")->execute([$monto, $idSocio]);

                    $idSesion = $this->db->query("SELECT id_sesion FROM sesiones_mensuales WHERE estado = 'abierta' LIMIT 1")->fetchColumn();
                    $idCobro = UUIDGenerator::generar();
                    $hash = hash('sha256', $idSocio . $id . 'inversion' . $monto . date('Y-m-d H:i:s'));
                    $this->db->prepare("INSERT INTO cobros (id_cobro, id_socio, id_sesion, tipo, id_referencia, monto, medio_pago, hash_integridad, usuario_registra) VALUES (?, ?, ?, 'inversion', ?, ?, 'efectivo', ?, ?)")
                        ->execute([$idCobro, $idSocio, $idSesion ?: null, $id, $monto, $hash, $_SESSION['usuario_id']]);

                    $this->historialInsert($idSocio, 'inversion_apertura', $monto, $id, $idSesion ?: null);
                    $this->db->commit();

                    $st = $this->db->prepare("SELECT CONCAT_WS(' ', apellido1, apellido2, nombre1, nombre2) AS nombre, cedula FROM socios WHERE id_socio = ?");
                    $st->execute([$idSocio]);
                    $soc = $st->fetch();
                    try { require_once ROOT_PATH . '/app/helpers/NotificacionHelper.php'; NotificacionHelper::crearInversion($idSocio, $soc['nombre'], $monto, 'apertura'); } catch (Exception $e) {}
                    PDFGenerator::generarContratoInversion([
                        'inversion' => $id,
                        'socio' => $soc['nombre'] ?? '',
                        'cedula' => $soc['cedula'] ?? '',
                        'monto' => $monto,
                        'producto' => $prod['nombre'],
                        'tasa' => $tasa,
                        'plazo' => $plazo,
                        'rendimiento' => round($rendimiento, 2),
                        'fecha_inicio' => $fechaInicio,
                        'fecha_vencimiento' => $fechaVenc->format('Y-m-d'),
                        'destino' => $destino,
                    ], 'contrato_inversion_' . substr($id, 0, 8));
                    $this->redirect('/inversion/listar');
                } catch (Exception $e) {
                    $this->db->rollBack();
                    $errors['general'] = $e->getMessage();
                }
            }
        }

        $this->render('inversiones/apertura', [
            'titulo' => 'Nueva inversion',
            'errors' => $errors,
            'productos' => $productos,
            'socios' => $socios,
        ]);
    }

    public function cerrarVencidas() {
        $this->requirePermission('cobro.inversion');
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') $this->json(['error' => 'Metodo no permitido'], 405);
        $this->validateCSRF();

        $stmt = $this->db->query("SELECT i.id_inversion, i.id_socio, i.monto, i.rendimiento_proyectado, i.destino_final
                                   FROM inversiones i
                                   WHERE i.estado = 'activa' AND i.fecha_vencimiento <= CURDATE()");
        $vencidas = $stmt->fetchAll();
        $count = 0;
        foreach ($vencidas as $v) {
            $devolucion = $v['monto'] + ($v['rendimiento_proyectado'] ?? 0);
            try {
                $this->db->beginTransaction();
                $this->db->prepare("UPDATE inversiones SET estado = 'vencida' WHERE id_inversion = ?")->execute([$v['id_inversion']]);

                if ($v['destino_final'] === 'capital_inversion') {
                    $this->db->prepare("UPDATE capital_inversion SET saldo = saldo + ?, fecha_ultimo_movimiento = NOW() WHERE id_socio = ?")
                        ->execute([$devolucion, $v['id_socio']]);
                }

                $this->historialInsert($v['id_socio'], 'inversion_retiro', $devolucion, $v['id_inversion']);
                $this->db->commit();
                try { $st2 = $this->db->prepare("SELECT CONCAT_WS(' ', apellido1, apellido2, nombre1, nombre2) AS nombre FROM socios WHERE id_socio = ?"); $st2->execute([$v['id_socio']]); $nom = $st2->fetchColumn(); require_once ROOT_PATH . '/app/helpers/NotificacionHelper.php'; NotificacionHelper::crearRetornoInversion($v['id_socio'], $nom, $devolucion, $v['destino_final']); } catch (Exception $e) {}
                $count++;
            } catch (Exception $e) {
                $this->db->rollBack();
            }
        }
        $this->json(['mensaje' => "$count inversion(es) cerrada(s) automaticamente"]);
    }

    public function retirar($id) {
        $this->requirePermission('cobro.inversion');
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->validateCSRF();
            $stmt = $this->db->prepare("SELECT i.*, p.penalidad_retiro_anticipado FROM inversiones i JOIN productos_financieros p ON i.id_producto = p.id_producto WHERE i.id_inversion = ? AND i.estado = 'activa'");
            $stmt->execute([$id]);
            $inv = $stmt->fetch();
            if (!$inv) $this->json(['error' => 'Inversion no encontrada o no activa'], 400);

            $penalidad = $inv['penalidad_retiro_anticipado'] / 100 * $inv['monto'];
            $devolucion = $inv['monto'] - $penalidad;

            $this->db->beginTransaction();
            try {
                $this->db->prepare("UPDATE inversiones SET estado = 'retiro_anticipado' WHERE id_inversion = ?")->execute([$id]);

                if ($inv['destino_final'] === 'capital_inversion') {
                    $this->db->prepare("UPDATE capital_inversion SET saldo = saldo + ?, fecha_ultimo_movimiento = NOW() WHERE id_socio = ?")->execute([$devolucion, $inv['id_socio']]);
                }

                $this->historialInsert($inv['id_socio'], 'inversion_retiro', $devolucion, $id);
                $this->db->commit();
                $this->json(['mensaje' => 'Retiro procesado', 'devolucion' => round($devolucion, 2), 'penalidad' => round($penalidad, 2)]);
            } catch (Exception $e) {
                $this->db->rollBack();
                $this->json(['error' => $e->getMessage()], 500);
            }
        }
    }

    public function depositar() {
        $this->requirePermission('cobro.inversion');
        $errors = [];
        $socios = $this->db->query("SELECT s.id_socio, s.cedula, CONCAT_WS(' ', s.apellido1, s.apellido2, s.nombre1, s.nombre2) AS nombre, COALESCE(ci.saldo, 0) AS capital_inversion FROM socios s LEFT JOIN capital_inversion ci ON s.id_socio = ci.id_socio WHERE s.estado = 'activo' ORDER BY s.apellido1, s.nombre1")->fetchAll();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->validateCSRF();
            $idSocio = $_POST['id_socio'] ?? '';
            $monto = str_replace(',', '.', $_POST['monto'] ?? '0');
            $medioPago = $_POST['medio_pago'] ?? 'efectivo';

            if (empty($idSocio)) $errors['id_socio'] = 'Seleccione un socio';
            if (!is_numeric($monto) || $monto <= 0) $errors['monto'] = 'Monto invalido';

            if (empty($errors)) {
                $this->db->beginTransaction();
                try {
                    $hasCap = $this->db->query("SELECT COUNT(*) FROM capital_inversion WHERE id_socio = '$idSocio'")->fetchColumn();
                    if ($hasCap == 0) {
                        $this->db->prepare("INSERT INTO capital_inversion (id_capital_inversion, id_socio) VALUES (?, ?)")->execute([UUIDGenerator::generar(), $idSocio]);
                    }
                    $this->db->prepare("UPDATE capital_inversion SET saldo = saldo + ?, fecha_ultimo_movimiento = NOW() WHERE id_socio = ?")->execute([$monto, $idSocio]);

                    $idSesion = $this->db->query("SELECT id_sesion FROM sesiones_mensuales WHERE estado = 'abierta' LIMIT 1")->fetchColumn();
                    $idCobro = UUIDGenerator::generar();
                    $hash = hash('sha256', $idSocio . $idCobro . 'deposito_capital_inversion' . $monto . date('Y-m-d H:i:s'));
                    $this->db->prepare("INSERT INTO cobros (id_cobro, id_socio, id_sesion, tipo, monto, medio_pago, hash_integridad, usuario_registra) VALUES (?, ?, ?, 'deposito_capital_inversion', ?, ?, ?, ?)")
                        ->execute([$idCobro, $idSocio, $idSesion ?: null, $monto, $medioPago, $hash, $_SESSION['usuario_id']]);

                    $this->historialInsert($idSocio, 'deposito_capital_inversion', $monto, $idCobro, $idSesion ?: null);
                    $this->db->commit();
                    try { $st = $this->db->prepare("SELECT CONCAT_WS(' ', apellido1, apellido2, nombre1, nombre2) AS nombre FROM socios WHERE id_socio = ?"); $st->execute([$idSocio]); $nom = $st->fetchColumn(); require_once ROOT_PATH . '/app/helpers/NotificacionHelper.php'; NotificacionHelper::crearDepositoCapital($idSocio, $nom, $monto); } catch (Exception $e) {}
                    $this->redirect('/inversion/listar');
                } catch (Exception $e) {
                    $this->db->rollBack();
                    $errors['general'] = $e->getMessage();
                }
            }
        }

        $this->render('inversiones/depositar', [
            'titulo' => 'Depositar a capital de inversion',
            'errors' => $errors,
            'socios' => $socios,
        ]);
    }
}
