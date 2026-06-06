<?php
require_once ROOT_PATH . '/app/helpers/CalculadoraInteres.php';
require_once ROOT_PATH . '/app/helpers/NotificacionHelper.php';

class CreditoController extends BaseController {

    public function listar() {
        $this->requirePermission('cobro.desembolso');
        $stmt = $this->db->query("SELECT c.*, CONCAT(s.apellido1, ' ', COALESCE(s.apellido2,''), ' ', s.nombre1, ' ', COALESCE(s.nombre2,'')) AS socio,
                                   p.nombre AS producto, p.tipo AS productoTipo
                                   FROM créditos c
                                   JOIN socios s ON c.id_socio = s.id_socio
                                   JOIN productos_financieros p ON c.id_producto = p.id_producto
                                   ORDER BY c.fecha_solicitud DESC");
        $creditos = $stmt->fetchAll();
        $this->render('creditos/listar', [
            'titulo' => 'Créditos',
            'creditos' => $creditos,
        ]);
    }

    public function solicitar() {
        $this->requirePermission('cobro.desembolso');
        $errors = [];
        $productos = $this->db->query("SELECT id_producto, nombre, tasa_interés_anual, método_interés, plazo_mín_meses, plazo_máx_meses, monto_mín, monto_máx, requiere_garante FROM productos_financieros WHERE tipo = 'crédito' AND activo = TRUE ORDER BY nombre")->fetchAll();
        $socios = $this->db->query("SELECT id_socio, cédula, CONCAT_WS(' ', apellido1, apellido2, nombre1, nombre2) AS nombre FROM socios WHERE estado = 'activo' ORDER BY apellido1, nombre1")->fetchAll();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->validateCSRF();
            $idSocio = $_POST['id_socio'] ?? '';
            $idProducto = $_POST['id_producto'] ?? '';
            $monto = str_replace(',', '.', $_POST['monto'] ?? '0');
            $plazo = intval($_POST['plazo'] ?? 1);
            $destino = trim($_POST['destino'] ?? '');
            $garantes = $_POST['garantes'] ?? [];

            if (empty($idSocio)) $errors['id_socio'] = 'Seleccione un socio';
            if (empty($idProducto)) $errors['id_producto'] = 'Seleccione un producto';

            $prod = null;
            foreach ($productos as $p) {
                if ($p['id_producto'] === $idProducto) { $prod = $p; break; }
            }
            if (!$prod) $errors['id_producto'] = 'Producto inválido';
            if (!is_numeric($monto) || $monto <= 0) $errors['monto'] = 'Monto inválido';
            if ($plazo < ($prod['plazo_mín_meses'] ?? 1) || $plazo > ($prod['plazo_máx_meses'] ?? 999)) $errors['plazo'] = 'Plazo fuera de rango';

            if ($prod && $prod['requiere_garante'] && empty($garantes)) {
                $errors['garantes'] = 'Se requiere al menos un garante';
            }
            if (!empty($garantes) && in_array($idSocio, $garantes)) {
                $errors['garantes'] = 'El socio no puede ser su propio garante';
            }

            if (empty($errors)) {
                $id = UUIDGenerator::generar();
                $this->db->beginTransaction();
                try {
                    $stmt = $this->db->prepare("INSERT INTO créditos
                        (id_crédito, id_socio, id_producto, monto_solicitado, plazo_meses, tasa_interés, método_interés, destino)
                        VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
                    $stmt->execute([$id, $idSocio, $idProducto, $monto, $plazo, $prod['tasa_interés_anual'], $prod['método_interés'], $destino]);

                    if (!empty($garantes)) {
                        $insG = $this->db->prepare("INSERT INTO garantes (id_garante, id_credito, id_socio, monto_garantizado) VALUES (?, ?, ?, ?)");
                        $montoG = round($monto / count($garantes), 2);
                        foreach ($garantes as $g) {
                            $insG->execute([UUIDGenerator::generar(), $id, $g, $montoG]);
                        }
                    }
                    $this->db->commit();
                    $this->redirect('/credito/listar');
                } catch (Exception $e) {
                    $this->db->rollBack();
                    $errors['general'] = $e->getMessage();
                }
            }
        }

        $this->render('creditos/solicitar', [
            'titulo' => 'Nueva solicitud de crédito',
            'errors' => $errors,
            'productos' => $productos,
            'socios' => $socios,
        ]);
    }

    public function ver($id) {
        $this->requirePermission('cobro.desembolso');
        $stmt = $this->db->prepare("SELECT c.*, CONCAT_WS(' ', s.apellido1, s.apellido2, s.nombre1, s.nombre2) AS socio,
                                     s.cédula, p.nombre AS producto, p.tipo AS productoTipo
                                     FROM créditos c
                                     JOIN socios s ON c.id_socio = s.id_socio
                                     JOIN productos_financieros p ON c.id_producto = p.id_producto
                                     WHERE c.id_crédito = ?");
        $stmt->execute([$id]);
        $credito = $stmt->fetch();
        if (!$credito) $this->redirect('/credito/listar');

        $amortizaciones = [];
        if (in_array($credito['estado'], ['aprobado','desembolsado'])) {
            $stmt = $this->db->prepare("SELECT * FROM amortizaciones WHERE id_crédito = ? ORDER BY número_cuota");
            $stmt->execute([$id]);
            $amortizaciones = $stmt->fetchAll();
        }

        $garantes = $this->db->prepare("SELECT g.*, CONCAT_WS(' ', s.apellido1, s.apellido2, s.nombre1, s.nombre2) AS nombre, s.cédula
                                        FROM garantes g JOIN socios s ON g.id_socio = s.id_socio
                                        WHERE g.id_credito = ?");
        $garantes->execute([$id]);
        $garantes = $garantes->fetchAll();

        $this->render('creditos/ver', [
            'titulo' => 'Crédito #' . substr($id, 0, 8),
            'credito' => $credito,
            'amortizaciones' => $amortizaciones,
            'garantes' => $garantes,
        ]);
    }

    public function aprobar($id) {
        $this->requirePermission('cobro.desembolso');
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->validateCSRF();
            $stmt = $this->db->prepare("SELECT cr.* FROM créditos cr WHERE cr.id_crédito = ? AND cr.estado = 'pendiente'");
            $stmt->execute([$id]);
            $credito = $stmt->fetch();
            if (!$credito) $this->json(['error' => 'No encontrado o ya procesado'], 400);

            $montoAprobado = str_replace(',', '.', $_POST['monto_aprobado'] ?? $credito['monto_solicitado']);

            $st = $this->db->prepare("SELECT CONCAT_WS(' ', apellido1, apellido2, nombre1, nombre2) AS nombre FROM socios WHERE id_socio = ?");
            $st->execute([$credito['id_socio']]);
            $socioNombre = $st->fetchColumn() ?: 'Socio';

            $this->db->beginTransaction();
            try {
                $this->db->prepare("UPDATE créditos SET estado = 'aprobado', monto_aprobado = ?, fecha_aprobación = NOW(), usuario_aprueba = ? WHERE id_crédito = ?")
                    ->execute([$montoAprobado, $_SESSION['usuario_id'], $id]);

                $cuotas = CalculadoraInteres::simular($montoAprobado, $credito['tasa_interés'], $credito['plazo_meses'], $credito['método_interés']);

                $ins = $this->db->prepare("INSERT INTO amortizaciones (id_amortización, id_crédito, número_cuota, fecha_vencimiento, capital, interés, total, saldo_restante) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
                $fechaInicio = new DateTime();
                foreach ($cuotas as $i => $c) {
                    $fv = clone $fechaInicio;
                    $fv->modify('+' . ($i + 1) . ' months');
                    $ins->execute([UUIDGenerator::generar(), $id, $c['número'], $fv->format('Y-m-d'), $c['capital'], $c['interés'], $c['total'], $c['saldo']]);
                }
                $this->db->commit();
                NotificacionHelper::crearCredito($socioNombre, 'aprobado', $montoAprobado);
                $this->json(['mensaje' => 'Crédito aprobado y tabla generada']);
            } catch (Exception $e) {
                $this->db->rollBack();
                $this->json(['error' => $e->getMessage()], 500);
            }
        }
    }

    public function desembolsar($id) {
        $this->requirePermission('cobro.desembolso');
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->validateCSRF();
            $stmt = $this->db->prepare("SELECT cr.* FROM créditos cr WHERE cr.id_crédito = ? AND cr.estado = 'aprobado'");
            $stmt->execute([$id]);
            $credito = $stmt->fetch();
            if (!$credito) $this->json(['error' => 'No encontrado o no está aprobado'], 400);

            $st = $this->db->prepare("SELECT CONCAT_WS(' ', apellido1, apellido2, nombre1, nombre2) AS nombre FROM socios WHERE id_socio = ?");
            $st->execute([$credito['id_socio']]);
            $socioNombre = $st->fetchColumn() ?: 'Socio';

            $this->db->beginTransaction();
            try {
                $idCobro = UUIDGenerator::generar();
                $hash = hash('sha256', $credito['id_socio'] . $credito['id_crédito'] . 'desembolso' . $credito['monto_aprobado'] . date('Y-m-d H:i:s'));

                $this->db->prepare("INSERT INTO cobros (id_cobro, id_socio, id_sesión, tipo, id_referencia, monto, medio_pago, hash_integridad, usuario_registra) VALUES (?, ?, ?, 'desembolso', ?, ?, 'efectivo', ?, ?)")
                    ->execute([$idCobro, $credito['id_socio'], null, $credito['id_crédito'], $credito['monto_aprobado'], $hash, $_SESSION['usuario_id']]);

                $this->db->prepare("UPDATE créditos SET estado = 'desembolsado', fecha_desembolso = NOW() WHERE id_crédito = ?")
                    ->execute([$id]);

                $this->historialInsert($credito['id_socio'], 'desembolso_crédito', $credito['monto_aprobado'], $credito['id_crédito']);
                $this->db->commit();
                NotificacionHelper::crearCredito($socioNombre, 'desembolsado', $credito['monto_aprobado']);
                $this->json(['mensaje' => 'Desembolso registrado']);
            } catch (Exception $e) {
                $this->db->rollBack();
                $this->json(['error' => $e->getMessage()], 500);
            }
        }
    }

    public function rechazar($id) {
        $this->requirePermission('cobro.desembolso');
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->validateCSRF();
            $this->db->prepare("UPDATE créditos SET estado = 'rechazado' WHERE id_crédito = ? AND estado = 'pendiente'")->execute([$id]);
            $this->json(['mensaje' => 'Crédito rechazado']);
        }
    }

    public function calcularMora() {
        $this->requirePermission('cobro.desembolso');
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->json(['error' => 'Método no permitido'], 405);
        }
        $this->validateCSRF();

        $tasaMora = (float)($this->db->query("SELECT valor FROM parámetros WHERE código = 'multa_mora_crédito'")->fetchColumn() ?: 5);
        $tasaMora /= 100;

        $stmt = $this->db->query("SELECT a.id_amortización, a.id_crédito, c.id_socio, a.total, a.fecha_vencimiento, c.monto_aprobado
                                  FROM amortizaciones a
                                  JOIN créditos c ON a.id_crédito = c.id_crédito
                                  WHERE a.estado = 'pendiente' AND a.fecha_vencimiento < CURDATE()");
        $vencidas = $stmt->fetchAll();
        $count = 0;
        foreach ($vencidas as $v) {
            $this->db->prepare("UPDATE amortizaciones SET estado = 'vencida' WHERE id_amortización = ?")->execute([$v['id_amortización']]);

            $dias = max(1, (new DateTime())->diff(new DateTime($v['fecha_vencimiento']))->days);
            $interesMora = round($v['total'] * $tasaMora * ($dias / 30), 2);

            if ($interesMora > 0) {
                $this->db->prepare("INSERT INTO multas (id_multa, id_socio, tipo, monto, fecha_generación) VALUES (?, ?, 'mora_crédito', ?, NOW())")
                    ->execute([UUIDGenerator::generar(), $v['id_socio'], $interesMora]);
            }

            $count++;
            NotificacionHelper::crearCredito($v['id_socio'], 'mora', $v['total']);
        }
        $this->json(['mensaje' => "$count cuota(s) marcada(s) como vencida(s)" . (($tasaMora > 0) ? ' con intereses moratorios' : '')]);
    }
}
