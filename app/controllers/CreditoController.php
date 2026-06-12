<?php
require_once ROOT_PATH . '/app/helpers/CalculadoraInteres.php';
require_once ROOT_PATH . '/app/helpers/NotificacionHelper.php';

class CreditoController extends BaseController {

    public function listar() {
        $this->requirePermission('cobro.desembolso');
        $page = max(1, intval($_GET['p'] ?? 1));
        $porPagina = 20;
        $offset = ($page - 1) * $porPagina;
        $total = $this->db->query("SELECT COUNT(*) FROM creditos")->fetchColumn();
        $totalPaginas = ceil($total / $porPagina);
        $stmt = $this->db->prepare("SELECT c.*, CONCAT(s.apellido1, ' ', COALESCE(s.apellido2,''), ' ', s.nombre1, ' ', COALESCE(s.nombre2,'')) AS socio,
                                   p.nombre AS producto, p.tipo AS productoTipo
                                   FROM creditos c
                                   JOIN socios s ON c.id_socio = s.id_socio
                                   JOIN productos_financieros p ON c.id_producto = p.id_producto
                                   ORDER BY c.fecha_solicitud DESC LIMIT $porPagina OFFSET $offset");
        $stmt->execute();
        $creditos = $stmt->fetchAll();
        $this->render('creditos/listar', [
            'titulo' => 'Créditos',
            'creditos' => $creditos,
            'page' => $page,
            'totalPaginas' => $totalPaginas,
        ]);
    }

    public function solicitar() {
        $this->requirePermission('cobro.desembolso');
        $errors = [];
        $productos = $this->db->query("SELECT id_producto, nombre, tasa_interes_anual, metodo_interes, plazo_min_meses, plazo_max_meses, monto_min, monto_max, requiere_garante FROM productos_financieros WHERE tipo = 'credito' AND activo = TRUE ORDER BY nombre")->fetchAll();
        $socios = $this->db->query("SELECT id_socio, cedula, CONCAT_WS(' ', apellido1, apellido2, nombre1, nombre2) AS nombre FROM socios WHERE estado = 'activo' ORDER BY apellido1, nombre1")->fetchAll();

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
            if ($plazo < ($prod['plazo_min_meses'] ?? 1) || $plazo > ($prod['plazo_max_meses'] ?? 999)) $errors['plazo'] = 'Plazo fuera de rango';

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
                    $stmt = $this->db->prepare("INSERT INTO `creditos`
                        (id_credito, id_socio, id_producto, monto_solicitado, plazo_meses, tasa_interes, metodo_interes, destino, estado)
                        VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'ingresado')");
                    $stmt->execute([$id, $idSocio, $idProducto, $monto, $plazo, $prod['tasa_interes_anual'], $prod['metodo_interes'], $destino]);

                    if (!empty($garantes)) {
                        $insG = $this->db->prepare("INSERT INTO garantes (id_garante, id_credito, id_socio, monto_garantizado) VALUES (?, ?, ?, ?)");
                        $n = count($garantes);
                        $montoBase = round($monto / $n, 2);
                        $suma = 0;
                        foreach ($garantes as $i => $g) {
                            $montoG = ($i === $n - 1) ? round($monto - $suma, 2) : $montoBase;
                            $insG->execute([UUIDGenerator::generar(), $id, $g, $montoG]);
                            $suma += $montoG;
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

    public function bandejaAprobados() {
        $this->requirePermission('credito.aprobar');
        $stmt = $this->db->query("SELECT c.*, CONCAT_WS(' ', s.apellido1, s.apellido2, s.nombre1, s.nombre2) AS socio,
                                   s.cedula, p.nombre AS producto, p.requiere_documento_firmado,
                                   p.es_emergente, p.condiciones_html
                                   FROM `creditos` c
                                   JOIN socios s ON c.id_socio = s.id_socio
                                   JOIN productos_financieros p ON c.id_producto = p.id_producto
                                   WHERE c.estado IN ('ingresado','pendiente','aprobado','legalizado')
                                   ORDER BY FIELD(c.estado,'ingresado','pendiente','aprobado','legalizado'), c.fecha_solicitud ASC");
        $creditos = $stmt->fetchAll();
        $this->render('creditos/bandejaAprobados', [
            'titulo' => 'Bandeja de creditos',
            'creditos' => $creditos,
        ]);
    }

    public function ver($id) {
        $this->requirePermission('cobro.desembolso');
        $stmt = $this->db->prepare("SELECT c.*, CONCAT_WS(' ', s.apellido1, s.apellido2, s.nombre1, s.nombre2) AS socio,
                                     s.cedula, p.nombre AS producto, p.tipo AS productoTipo,
                                     p.requiere_documento_firmado, p.es_emergente
                                     FROM `creditos` c
                                     JOIN socios s ON c.id_socio = s.id_socio
                                     JOIN productos_financieros p ON c.id_producto = p.id_producto
                                     WHERE c.id_credito = ?");
        $stmt->execute([$id]);
        $credito = $stmt->fetch();
        if (!$credito) $this->redirect('/credito/listar');

        $amortizaciones = [];
        if (in_array($credito['estado'], ['aprobado','legalizado','desembolsado'])) {
            $stmt = $this->db->prepare("SELECT * FROM amortizaciones WHERE id_credito = ? ORDER BY numero_cuota");
            $stmt->execute([$id]);
            $amortizaciones = $stmt->fetchAll();
        }

        $garantes = $this->db->prepare("SELECT g.*, CONCAT_WS(' ', s.apellido1, s.apellido2, s.nombre1, s.nombre2) AS nombre, s.cedula
                                        FROM garantes g JOIN socios s ON g.id_socio = s.id_socio
                                        WHERE g.id_credito = ?");
        $garantes->execute([$id]);
        $garantes = $garantes->fetchAll();

        $archivos = [];
        if ($credito['estado'] === 'legalizado') {
            $stmt = $this->db->prepare("SELECT * FROM archivos WHERE entidad_tipo = 'credito' AND entidad_id = ? ORDER BY fecha_subida DESC");
            $stmt->execute([$id]);
            $archivos = $stmt->fetchAll();
        }

        $this->render('creditos/ver', [
            'titulo' => 'Crédito #' . substr($id, 0, 8),
            'credito' => $credito,
            'amortizaciones' => $amortizaciones,
            'garantes' => $garantes,
            'archivos' => $archivos,
        ]);
    }

    public function aprobar($id) {
        $this->requirePermission('credito.aprobar');
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->validateCSRF();
            $stmt = $this->db->prepare("SELECT cr.*, p.requiere_documento_firmado FROM `creditos` cr
                                        JOIN productos_financieros p ON cr.id_producto = p.id_producto
                                        WHERE cr.id_credito = ? AND cr.estado IN ('ingresado','pendiente')");
            $stmt->execute([$id]);
            $credito = $stmt->fetch();
            if (!$credito) $this->json(['error' => 'No encontrado o ya procesado'], 400);

            $montoAprobado = str_replace(',', '.', $_POST['monto_aprobado'] ?? $credito['monto_solicitado']);

            $st = $this->db->prepare("SELECT CONCAT_WS(' ', apellido1, apellido2, nombre1, nombre2) AS nombre FROM socios WHERE id_socio = ?");
            $st->execute([$credito['id_socio']]);
            $socioNombre = $st->fetchColumn() ?: 'Socio';

            $this->db->beginTransaction();
            try {
                $this->db->prepare("UPDATE `creditos` SET estado = 'aprobado', monto_aprobado = ?, fecha_aprobacion = NOW(), usuario_aprueba = ? WHERE id_credito = ?")
                    ->execute([$montoAprobado, $_SESSION['usuario_id'], $id]);

                $cuotas = CalculadoraInteres::simular($montoAprobado, $credito['tasa_interes'], $credito['plazo_meses'], $credito['metodo_interes']);

                $ins = $this->db->prepare("INSERT INTO amortizaciones (id_amortizacion, id_credito, numero_cuota, fecha_vencimiento, capital, interes, total, saldo_restante) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
                $fechaInicio = new DateTime();
                foreach ($cuotas as $i => $c) {
                    $fv = clone $fechaInicio;
                    $fv->modify('+' . ($i + 1) . ' months');
                    $ins->execute([UUIDGenerator::generar(), $id, $c['numero'], $fv->format('Y-m-d'), $c['capital'], $c['interes'], $c['total'], $c['saldo']]);
                }
                $this->db->commit();
                NotificacionHelper::crear([
                    'tipo' => 'credito',
                    'titulo' => 'Credito aprobado',
                    'mensaje' => "Su credito de $$montoAprobado ha sido aprobado para legalización",
                    'id_socio' => $credito['id_socio'],
                    'enviar_pusher' => true,
                ]);
                $this->json(['mensaje' => 'Crédito aprobado', 'redirect' => BASE_URL . '/credito/bandejaAprobados']);
            } catch (Exception $e) {
                $this->db->rollBack();
                $this->json(['error' => $e->getMessage()], 500);
            }
        }
    }

    public function ponerEnEspera($id) {
        $this->requirePermission('credito.aprobar');
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->validateCSRF();
            $justificacion = trim($_POST['justificacion'] ?? '');
            if (empty($justificacion)) $this->json(['error' => 'Debe indicar el motivo de la espera'], 400);

            $stmt = $this->db->prepare("SELECT id_socio FROM `creditos` WHERE id_credito = ? AND estado = 'ingresado'");
            $stmt->execute([$id]);
            $credito = $stmt->fetch();
            if (!$credito) $this->json(['error' => 'No encontrado o ya procesado'], 400);

            $this->db->prepare("UPDATE `creditos` SET estado = 'pendiente', justificacion = ? WHERE id_credito = ?")
                ->execute([$justificacion, $id]);

            NotificacionHelper::crear([
                'tipo' => 'credito',
                'titulo' => 'Credito en espera',
                'mensaje' => "Su solicitud de credito ha sido puesta en espera: $justificacion",
                'id_socio' => $credito['id_socio'],
                'enviar_pusher' => true,
            ]);

            $this->json(['mensaje' => 'Crédito puesto en espera']);
        }
    }

    public function rechazar($id) {
        $this->requirePermission('credito.aprobar');
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->validateCSRF();
            $justificacion = trim($_POST['justificacion'] ?? '');
            if (empty($justificacion)) $this->json(['error' => 'Debe indicar la justificacion del rechazo'], 400);

            $stmt = $this->db->prepare("SELECT id_socio FROM `creditos` WHERE id_credito = ? AND estado IN ('ingresado','pendiente','aprobado')");
            $stmt->execute([$id]);
            $credito = $stmt->fetch();
            if (!$credito) $this->json(['error' => 'No encontrado o ya procesado'], 400);

            $this->db->prepare("UPDATE `creditos` SET estado = 'rechazado', justificacion = ?, fecha_aprobacion = NOW(), usuario_aprueba = ? WHERE id_credito = ?")
                ->execute([$justificacion, $_SESSION['usuario_id'], $id]);

            NotificacionHelper::crear([
                'tipo' => 'credito',
                'titulo' => 'Credito rechazado',
                'mensaje' => "Su solicitud de credito ha sido rechazada: $justificacion",
                'id_socio' => $credito['id_socio'],
                'enviar_pusher' => true,
            ]);

            $this->json(['mensaje' => 'Crédito rechazado']);
        }
    }

    public function generarSolicitudPdf($id) {
        $this->requirePermission('credito.aprobar');
        $stmt = $this->db->prepare("SELECT c.*, CONCAT_WS(' ', s.apellido1, s.apellido2, s.nombre1, s.nombre2) AS socio,
                                     s.cedula, p.nombre AS producto, p.tasa_interes_anual,
                                     p.metodo_interes, p.condiciones_html, p.requiere_documento_firmado
                                     FROM `creditos` c
                                     JOIN socios s ON c.id_socio = s.id_socio
                                     JOIN productos_financieros p ON c.id_producto = p.id_producto
                                     WHERE c.id_credito = ? AND c.estado = 'aprobado'");
        $stmt->execute([$id]);
        $credito = $stmt->fetch();
        if (!$credito) {
            http_response_code(404);
            echo "Crédito no encontrado o no está en etapa de legalización";
            exit;
        }

        require_once ROOT_PATH . '/app/helpers/CalculadoraInteres.php';
        $cuotas = CalculadoraInteres::simular($credito['monto_aprobado'], $credito['tasa_interes'], $credito['plazo_meses'], $credito['metodo_interes']);

        $totalPagar = array_sum(array_column($cuotas, 'total'));

        $this->render('documentos/solicitudCredito', [
            'credito' => $credito,
            'cuotas' => $cuotas,
            'totalPagar' => $totalPagar,
        ], 'layouts/blank');
    }

    public function subirActaFirmada($id) {
        $this->requirePermission('credito.aprobar');
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->validateCSRF();
            $stmt = $this->db->prepare("SELECT c.*, p.requiere_documento_firmado FROM `creditos` c
                                        JOIN productos_financieros p ON c.id_producto = p.id_producto
                                        WHERE c.id_credito = ? AND c.estado = 'aprobado'");
            $stmt->execute([$id]);
            $credito = $stmt->fetch();
            if (!$credito) $this->json(['error' => 'No encontrado o no está en legalización'], 400);
            if (empty($credito['requiere_documento_firmado'])) $this->json(['error' => 'Este producto no requiere documento firmado'], 400);

            if (empty($_FILES['archivo']) || $_FILES['archivo']['error'] !== UPLOAD_ERR_OK) {
                $this->json(['error' => 'Debe seleccionar un archivo PDF'], 400);
            }

            require_once ROOT_PATH . '/app/helpers/FileManager.php';
            $fm = new FileManager();
            $resultado = $fm->subir($_FILES['archivo'], 'credito', $id, $_SESSION['usuario_id'], ['pdf']);

            if (!$resultado['exito']) {
                $this->json(['error' => $resultado['mensaje']], 400);
            }

            $this->db->prepare("UPDATE `creditos` SET estado = 'legalizado' WHERE id_credito = ?")->execute([$id]);

            NotificacionHelper::crear([
                'tipo' => 'credito',
                'titulo' => 'Credito legalizado',
                'mensaje' => 'Su credito ha sido legalizado, pronto se realizara el desembolso',
                'id_socio' => $credito['id_socio'],
                'enviar_pusher' => true,
            ]);

            $this->json(['mensaje' => 'Documento subido. Crédito legalizado.', 'redirect' => BASE_URL . '/credito/ver/' . $id]);
        }
    }

    public function desembolsar($id) {
        $this->requirePermission('cobro.desembolso');
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->validateCSRF();
            $stmt = $this->db->prepare("SELECT c.*, p.requiere_documento_firmado FROM `creditos` c
                                        JOIN productos_financieros p ON c.id_producto = p.id_producto
                                        WHERE c.id_credito = ? AND c.estado IN ('aprobado','legalizado')");
            $stmt->execute([$id]);
            $credito = $stmt->fetch();
            if (!$credito) $this->json(['error' => 'No encontrado o no está listo para desembolso'], 400);

            if (!empty($credito['requiere_documento_firmado']) && $credito['estado'] !== 'legalizado') {
                $this->json(['error' => 'Debe subir el documento firmado antes del desembolso'], 400);
            }

            $st = $this->db->prepare("SELECT CONCAT_WS(' ', apellido1, apellido2, nombre1, nombre2) AS nombre FROM socios WHERE id_socio = ?");
            $st->execute([$credito['id_socio']]);
            $socioNombre = $st->fetchColumn() ?: 'Socio';

            $this->db->beginTransaction();
            try {
                $idCobro = UUIDGenerator::generar();
                $hash = hash('sha256', $credito['id_socio'] . $credito['id_credito'] . 'desembolso' . $credito['monto_aprobado'] . date('Y-m-d H:i:s'));

                $this->db->prepare("INSERT INTO cobros (id_cobro, id_socio, id_sesion, tipo, id_referencia, monto, medio_pago, hash_integridad, usuario_registra) VALUES (?, ?, ?, 'desembolso', ?, ?, 'efectivo', ?, ?)")
                    ->execute([$idCobro, $credito['id_socio'], null, $credito['id_credito'], $credito['monto_aprobado'], $hash, $_SESSION['usuario_id']]);

                $this->db->prepare("UPDATE `creditos` SET estado = 'desembolsado', fecha_desembolso = NOW() WHERE id_credito = ?")
                    ->execute([$id]);

                $this->historialInsert($credito['id_socio'], 'desembolso_credito', $credito['monto_aprobado'], $credito['id_credito']);
                $this->db->commit();

                try {
                    $stP = $this->db->prepare("SELECT p.nombre FROM productos_financieros p JOIN creditos c ON c.id_producto = p.id_producto WHERE c.id_credito = ?");
                    $stP->execute([$id]);
                    $prodNombre = $stP->fetchColumn() ?: 'Credito';
                    CajaHelper::registrar([
                        'tipo' => 'egreso',
                        'concepto' => "Desembolso credito - $socioNombre - $prodNombre - \${$credito['monto_aprobado']}",
                        'categoria' => 'desembolso',
                        'monto' => $credito['monto_aprobado'],
                        'id_socio' => $credito['id_socio'],
                        'id_referencia' => $id,
                    ]);
                } catch (Exception $e) {}

                NotificacionHelper::crear([
                    'tipo' => 'credito',
                    'titulo' => 'Credito desembolsado',
                    'mensaje' => "Su credito de $$credito[monto_aprobado] ha sido desembolsado",
                    'id_socio' => $credito['id_socio'],
                    'enviar_pusher' => true,
                ]);
                $this->json(['mensaje' => 'Desembolso registrado']);
            } catch (Exception $e) {
                $this->db->rollBack();
                $this->json(['error' => $e->getMessage()], 500);
            }
        }
    }

    public function calcularMora() {
        $this->requirePermission('cobro.desembolso');
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->json(['error' => 'Método no permitido'], 405);
        }
        $this->validateCSRF();

        $tasaMora = (float)($this->db->query("SELECT valor FROM parametros WHERE codigo = 'multa_mora_credito'")->fetchColumn() ?: 5);
        $tasaMora /= 100;

        $stmt = $this->db->query("SELECT a.id_amortizacion, a.id_credito, c.id_socio, a.total, a.fecha_vencimiento, c.monto_aprobado
                                  FROM amortizaciones a
                                  JOIN creditos c ON a.id_credito = c.id_credito
                                  WHERE a.estado = 'pendiente' AND a.fecha_vencimiento < CURDATE()");
        $vencidas = $stmt->fetchAll();
        $count = 0;

        $this->db->beginTransaction();
        try {
            foreach ($vencidas as $v) {
                $this->db->prepare("UPDATE amortizaciones SET estado = 'vencida' WHERE id_amortizacion = ?")->execute([$v['id_amortizacion']]);

                $dias = max(1, (new DateTime())->diff(new DateTime($v['fecha_vencimiento']))->days);
                $interesMora = round($v['total'] * $tasaMora * ($dias / 30), 2);

                if ($interesMora > 0) {
                    $this->db->prepare("INSERT INTO multas (id_multa, id_socio, tipo, monto, fecha_generacion) VALUES (?, ?, 'mora_credito', ?, NOW())")
                        ->execute([UUIDGenerator::generar(), $v['id_socio'], $interesMora]);
                }
                $count++;
                NotificacionHelper::crearCredito($v['id_socio'], 'mora', $v['total']);
            }
            $this->db->commit();
        } catch (Exception $e) {
            $this->db->rollBack();
            $this->json(['error' => $e->getMessage()], 500);
        }
        $this->json(['mensaje' => "$count cuota(s) marcada(s) como vencida(s)" . (($tasaMora > 0) ? ' con intereses moratorios' : '')]);
    }
}