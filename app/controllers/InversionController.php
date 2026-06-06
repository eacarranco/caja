<?php
require_once ROOT_PATH . '/app/helpers/PDFGenerator.php';
class InversionController extends BaseController {

    public function listar() {
        $this->requirePermission('cobro.inversión');
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
        $this->requirePermission('cobro.inversión');
        $errors = [];
        $productos = $this->db->query("SELECT id_producto, nombre, tasa_interés_anual, plazo_mín_meses, plazo_máx_meses, monto_mín, monto_máx FROM productos_financieros WHERE tipo = 'inversión' AND activo = TRUE ORDER BY nombre")->fetchAll();
        $socios = $this->db->query("SELECT id_socio, cédula, CONCAT_WS(' ', apellido1, apellido2, nombre1, nombre2) AS nombre FROM socios WHERE estado = 'activo' ORDER BY apellido1, nombre1")->fetchAll();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->validateCSRF();
            $idSocio = $_POST['id_socio'] ?? '';
            $idProducto = $_POST['id_producto'] ?? '';
            $monto = str_replace(',', '.', $_POST['monto'] ?? '0');
            $plazo = intval($_POST['plazo'] ?? 1);

            if (empty($idSocio)) $errors['id_socio'] = 'Seleccione un socio';
            if (empty($idProducto)) $errors['id_producto'] = 'Seleccione un producto';

            $prod = null;
            foreach ($productos as $p) {
                if ($p['id_producto'] === $idProducto) { $prod = $p; break; }
            }
            if (!$prod) $errors['id_producto'] = 'Producto inválido';
            if (!is_numeric($monto) || $monto <= 0) $errors['monto'] = 'Monto inválido';
            if ($plazo < ($prod['plazo_mín_meses'] ?? 1) || $plazo > ($prod['plazo_máx_meses'] ?? 999)) $errors['plazo'] = 'Plazo fuera de rango';

            if (empty($errors)) {
                $tasa = $prod['tasa_interés_anual'];
                $factor = $tasa / 100 / 12;
                $rendimiento = $monto * $factor * $plazo;

                $fechaInicio = date('Y-m-d');
                $fechaVenc = new DateTime($fechaInicio);
                $fechaVenc->modify('+' . $plazo . ' months');

                $id = UUIDGenerator::generar();
                $this->db->beginTransaction();
                try {
                    $this->db->prepare("INSERT INTO inversiones
                        (id_inversión, id_socio, id_producto, monto, plazo_meses, tasa_interés, fecha_inicio, fecha_vencimiento, rendimiento_proyectado)
                        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)")
                        ->execute([$id, $idSocio, $idProducto, $monto, $plazo, $tasa, $fechaInicio, $fechaVenc->format('Y-m-d'), round($rendimiento, 2)]);

                    $stmt = $this->db->prepare("SELECT COUNT(*) FROM cuentas_ahorro WHERE id_socio = ?");
                    $stmt->execute([$idSocio]);
                    if ($stmt->fetchColumn() == 0) {
                        $this->db->prepare("INSERT INTO cuentas_ahorro (id_cuenta_ahorro, id_socio) VALUES (?, ?)")->execute([UUIDGenerator::generar(), $idSocio]);
                    }
                    $this->db->prepare("UPDATE cuentas_ahorro SET saldo_disponible = saldo_disponible - ?, fecha_último_movimiento = NOW() WHERE id_socio = ?")->execute([$monto, $idSocio]);

                    $idSesion = $this->db->query("SELECT id_sesión FROM sesiones_mensuales WHERE estado = 'abierta' LIMIT 1")->fetchColumn();
                    $idCobro = UUIDGenerator::generar();
                    $hash = hash('sha256', $idSocio . $id . 'inversión' . $monto . date('Y-m-d H:i:s'));
                    $this->db->prepare("INSERT INTO cobros (id_cobro, id_socio, id_sesión, tipo, id_referencia, monto, medio_pago, hash_integridad, usuario_registra) VALUES (?, ?, ?, 'inversión', ?, ?, 'efectivo', ?, ?)")
                        ->execute([$idCobro, $idSocio, $idSesion ?: null, $id, $monto, $hash, $_SESSION['usuario_id']]);

                    $this->historialInsert($idSocio, 'inversión_apertura', $monto, $id, $idSesion ?: null);
                    $this->db->commit();

                    $st = $this->db->prepare("SELECT CONCAT_WS(' ', apellido1, apellido2, nombre1, nombre2) AS nombre, cédula FROM socios WHERE id_socio = ?");
                    $st->execute([$idSocio]);
                    $soc = $st->fetch();
                    PDFGenerator::generarContratoInversion([
                        'inversion' => $id,
                        'socio' => $soc['nombre'] ?? '',
                        'cedula' => $soc['cédula'] ?? '',
                        'monto' => $monto,
                        'producto' => $prod['nombre'],
                        'tasa' => $tasa,
                        'plazo' => $plazo,
                        'rendimiento' => round($rendimiento, 2),
                        'fecha_inicio' => $fechaInicio,
                        'fecha_vencimiento' => $fechaVenc->format('Y-m-d'),
                    ], 'contrato_inversion_' . substr($id, 0, 8));
                    $this->redirect('/inversion/listar');
                } catch (Exception $e) {
                    $this->db->rollBack();
                    $errors['general'] = $e->getMessage();
                }
            }
        }

        $this->render('inversiones/apertura', [
            'titulo' => 'Nueva inversión',
            'errors' => $errors,
            'productos' => $productos,
            'socios' => $socios,
        ]);
    }

    public function cerrarVencidas() {
        $this->requirePermission('cobro.inversión');
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') $this->json(['error' => 'Método no permitido'], 405);
        $this->validateCSRF();

        $stmt = $this->db->query("SELECT i.id_inversión, i.id_socio, i.monto, i.rendimiento_proyectado
                                   FROM inversiones i
                                   WHERE i.estado = 'activa' AND i.fecha_vencimiento <= CURDATE()");
        $vencidas = $stmt->fetchAll();
        $count = 0;
        foreach ($vencidas as $v) {
            $devolucion = $v['monto'] + ($v['rendimiento_proyectado'] ?? 0);
            try {
                $this->db->beginTransaction();
                $this->db->prepare("UPDATE inversiones SET estado = 'vencida' WHERE id_inversión = ?")->execute([$v['id_inversión']]);
                $this->db->prepare("UPDATE cuentas_ahorro SET saldo_disponible = saldo_disponible + ?, fecha_último_movimiento = NOW() WHERE id_socio = ?")
                    ->execute([$devolucion, $v['id_socio']]);
                $this->historialInsert($v['id_socio'], 'inversión_retiro', $devolucion, $v['id_inversión']);
                $this->db->commit();
                $count++;
            } catch (Exception $e) {
                $this->db->rollBack();
            }
        }
        $this->json(['mensaje' => "$count inversión(es) cerrada(s) automáticamente"]);
    }

    public function retirar($id) {
        $this->requirePermission('cobro.inversión');
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->validateCSRF();
            $stmt = $this->db->prepare("SELECT i.*, p.penalidad_retiro_anticipado FROM inversiones i JOIN productos_financieros p ON i.id_producto = p.id_producto WHERE i.id_inversión = ? AND i.estado = 'activa'");
            $stmt->execute([$id]);
            $inv = $stmt->fetch();
            if (!$inv) $this->json(['error' => 'Inversión no encontrada o no activa'], 400);

            $penalidad = $inv['penalidad_retiro_anticipado'] / 100 * $inv['monto'];
            $devolucion = $inv['monto'] - $penalidad;

            $this->db->beginTransaction();
            try {
                $this->db->prepare("UPDATE inversiones SET estado = 'retiro_anticipado' WHERE id_inversión = ?")->execute([$id]);
                $this->db->prepare("UPDATE cuentas_ahorro SET saldo_disponible = saldo_disponible + ?, fecha_último_movimiento = NOW() WHERE id_socio = ?")->execute([$devolucion, $inv['id_socio']]);
                $this->historialInsert($inv['id_socio'], 'inversión_retiro', $devolucion, $id);
                $this->db->commit();
                $this->json(['mensaje' => 'Retiro procesado', 'devolución' => round($devolucion, 2), 'penalidad' => round($penalidad, 2)]);
            } catch (Exception $e) {
                $this->db->rollBack();
                $this->json(['error' => $e->getMessage()], 500);
            }
        }
    }
}
