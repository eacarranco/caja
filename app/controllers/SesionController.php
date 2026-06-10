<?php
require_once ROOT_PATH . '/app/helpers/PDFGenerator.php';
require_once ROOT_PATH . '/app/helpers/NotificacionHelper.php';

class SesionController extends BaseController {

    public function listar() {
        $this->requirePermission('cobro.aporte');
        $abierta = $this->db->query("SELECT id_sesion FROM sesiones_mensuales WHERE estado = 'abierta' LIMIT 1")->fetchColumn();
        $stmt = $this->db->query("SELECT s.*, u.nombres AS usuario_cierre_nombre
                                   FROM sesiones_mensuales s
                                   LEFT JOIN usuarios u ON s.usuario_cierre = u.id_usuario
                                   ORDER BY s.fecha_sesion DESC");
        $sesiones = $stmt->fetchAll();
        $this->render('sesiones/listar', [
            'titulo' => 'Sesiones mensuales',
            'sesiones' => $sesiones,
            'hayAbierta' => !empty($abierta),
        ]);
    }

    public function reaperturar($id) {
        $this->requirePermission('cobro.cierre_sesion');
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') $this->redirect('/sesion/listar');
        $this->validateCSRF();
        $this->db->prepare("UPDATE sesiones_mensuales SET estado = 'abierta', fecha_cierre = NULL, usuario_cierre = NULL WHERE id_sesion = ? AND estado = 'cerrada'")->execute([$id]);
        $this->redirect('/sesion/checkin/' . $id);
    }

    public function abrir() {
        $this->requirePermission('cobro.aporte');
        $errors = [];

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->validateCSRF();

            $stmt = $this->db->query("SELECT COUNT(*) FROM sesiones_mensuales WHERE estado = 'abierta'");
            if ($stmt->fetchColumn() > 0) {
                $errors['general'] = 'Ya existe una sesión abierta. Ciérrela antes de abrir otra.';
            }

            if (empty($errors)) {
                $titulo = trim($_POST['titulo'] ?? '');
                if (empty($titulo)) {
                    $errors['titulo'] = 'El titulo es obligatorio';
                }
            }

            if (empty($errors)) {
                $stmt = $this->db->query("SELECT COALESCE(MAX(numero_sesion), 0) + 1 FROM sesiones_mensuales");
                $num = $stmt->fetchColumn();

                $id = UUIDGenerator::generar();
                $fechaSesion = $_POST['fecha_sesion'] ?? date('Y-m-d');
                $titulo = trim($_POST['titulo'] ?? '');

                $stmt = $this->db->prepare("INSERT INTO sesiones_mensuales (id_sesion, numero_sesion, fecha_sesion, titulo) VALUES (?, ?, ?, ?)");
                $stmt->execute([$id, $num, $fechaSesion, $titulo]);

                // Generar obligaciones para todos los socios activos
                $this->generarObligaciones($id, $fechaSesion);

                // Notificar a todos los socios via Pusher
                try {
                    require_once ROOT_PATH . '/app/helpers/PusherHelper.php';
                    $socios = $this->db->query("SELECT id_socio FROM socios WHERE estado = 'activo'")->fetchAll(PDO::FETCH_COLUMN);
                    foreach ($socios as $sid) {
                        PusherHelper::actualizarPortal($sid);
                    }
                } catch (Exception $e) {}

                $this->redirect('/sesion/checkin/' . $id);
            }
        }

        $this->render('sesiones/abrir', [
            'titulo' => 'Abrir sesión mensual',
            'errors' => $errors,
        ]);
    }

    public function editar($id) {
        $this->requirePermission('cobro.aporte');
        $stmt = $this->db->prepare("SELECT * FROM sesiones_mensuales WHERE id_sesion = ?");
        $stmt->execute([$id]);
        $sesion = $stmt->fetch();
        if (!$sesion) $this->redirect('/sesion/listar');

        $errors = [];

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->validateCSRF();
            $fechaSesion = $_POST['fecha_sesion'] ?? '';
            $titulo = trim($_POST['titulo'] ?? '');

            if (empty($fechaSesion)) $errors['fecha_sesion'] = 'La fecha es obligatoria';
            if (empty($titulo)) $errors['titulo'] = 'El titulo es obligatorio';

            if (empty($errors)) {
                $this->db->prepare("UPDATE sesiones_mensuales SET fecha_sesion = ?, titulo = ? WHERE id_sesion = ?")
                    ->execute([$fechaSesion, $titulo, $id]);
                $this->redirect('/sesion/listar');
            }
        }

        $this->render('sesiones/form', [
            'titulo' => 'Editar sesion #' . $sesion['numero_sesion'],
            'sesion' => $sesion,
            'errors' => $errors,
        ]);
    }

    private function generarObligaciones($idSesion, $fechaCorte) {
        $socios = $this->db->query("SELECT id_socio, cedula FROM socios WHERE estado = 'activo'")->fetchAll();
        $aporteMensual = floatval($this->db->query("SELECT valor FROM parametros WHERE codigo = 'aporte_obligatorio_mensual'")->fetchColumn() ?: 10);
        $insertOblig = $this->db->prepare("INSERT INTO obligaciones_sesion (id_obligacion, id_sesion, id_socio, tipo, concepto, monto, id_referencia) VALUES (?, ?, ?, ?, ?, ?, ?)");

        foreach ($socios as $s) {
            $idSocio = $s['id_socio'];

            // 1. Cuota mensual obligatoria
            $insertOblig->execute([
                UUIDGenerator::generar(), $idSesion, $idSocio, 'cuota_mensual',
                "Cuota mensual - Sesion #" . $this->db->query("SELECT numero_sesion FROM sesiones_mensuales WHERE id_sesion = '$idSesion'")->fetchColumn() . " del " . date('d/m/Y', strtotime($fechaCorte)),
                $aporteMensual, null
            ]);

            // 2. Cuotas de credito vencidas (fecha_vencimiento <= fechaCorte)
            $cuotas = $this->db->prepare("SELECT a.id_amortizacion, a.numero_cuota, a.total, cr.id_credito, p.nombre AS producto
                                           FROM amortizaciones a
                                           JOIN creditos cr ON a.id_credito = cr.id_credito
                                           JOIN productos_financieros p ON cr.id_producto = p.id_producto
                                           WHERE cr.id_socio = ? AND a.estado IN ('pendiente','vencida') AND a.fecha_vencimiento <= ?");
            $cuotas->execute([$idSocio, $fechaCorte]);
            foreach ($cuotas as $c) {
                $insertOblig->execute([
                    UUIDGenerator::generar(), $idSesion, $idSocio, 'cuota_credito',
                    "Cuota #{$c['numero_cuota']} - {$c['producto']} (vence " . date('d/m/Y', strtotime($c['fecha_vencimiento'])) . ")",
                    $c['total'], $c['id_amortizacion']
                ]);
            }

            // 3. Multas no pagadas de sesiones anteriores
            $multas = $this->db->prepare("SELECT m.id_multa, m.tipo, m.monto, ses.numero_sesion AS multa_sesion, ses.fecha_sesion AS multa_fecha
                                           FROM multas m
                                           LEFT JOIN sesiones_mensuales ses ON m.id_sesion = ses.id_sesion
                                           WHERE m.id_socio = ? AND m.pagada = FALSE");
            $multas->execute([$idSocio]);
            foreach ($multas as $m) {
                $tipoMulta = str_replace('_', ' ', ucfirst($m['tipo']));
                $concepto = "Multa por {$tipoMulta}";
                if ($m['multa_sesion']) {
                    $fechaMulta = $m['multa_fecha'] ? date('d/m/Y', strtotime($m['multa_fecha'])) : '';
                    $concepto .= " - Sesion #{$m['multa_sesion']}" . ($fechaMulta ? " del {$fechaMulta}" : "");
                }
                $insertOblig->execute([
                    UUIDGenerator::generar(), $idSesion, $idSocio, 'multa',
                    $concepto,
                    $m['monto'], $m['id_multa']
                ]);
            }
        }
    }

    public function checkin($id) {
        $this->requirePermission('cobro.aporte');
        $stmt = $this->db->prepare("SELECT * FROM sesiones_mensuales WHERE id_sesion = ?");
        $stmt->execute([$id]);
        $sesion = $stmt->fetch();
        if (!$sesion) $this->redirect('/sesion/listar');
        if ($sesion['estado'] === 'cerrada') {
            $this->redirect('/sesion/listar');
        }

        $socios = $this->db->query("SELECT s.id_socio, s.cedula, CONCAT_WS(' ', s.apellido1, s.apellido2, s.nombre1, s.nombre2) AS nombre_completo
                                     FROM socios s WHERE s.estado = 'activo'
                                     ORDER BY s.apellido1, s.apellido2, s.nombre1, s.nombre2")->fetchAll();

        $asistencias = [];
        $stmt = $this->db->prepare("SELECT * FROM asistencias WHERE id_sesion = ?");
        $stmt->execute([$id]);
        while ($row = $stmt->fetch()) {
            $asistencias[$row['id_socio']] = $row;
        }

        // Obtener obligaciones de esta sesion con estado de pago
        $obligaciones = [];
        $stmt = $this->db->prepare("SELECT o.* FROM obligaciones_sesion o WHERE o.id_sesion = ? ORDER BY o.id_socio, o.tipo");
        $stmt->execute([$id]);
        foreach ($stmt->fetchAll() as $o) {
            $obligaciones[$o['id_socio']][] = $o;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['accion'])) {
            $this->validateCSRF();
            $accion = $_POST['accion'];

            if ($accion === 'asistencia') {
                $idSocio = $_POST['id_socio'] ?? '';
                $tipo = $_POST['tipo'] ?? 'falta';
                $stmt = $this->db->prepare("SELECT COUNT(*) FROM asistencias WHERE id_socio = ? AND id_sesion = ?");
                $stmt->execute([$idSocio, $id]);
                if ($stmt->fetchColumn() > 0) {
                    $stmt = $this->db->prepare("UPDATE asistencias SET tipo = ?, usuario_registra = ? WHERE id_socio = ? AND id_sesion = ?");
                    $stmt->execute([$tipo, $_SESSION['usuario_id'], $idSocio, $id]);
                } else {
                    $stmt = $this->db->prepare("INSERT INTO asistencias (id_asistencia, id_socio, id_sesion, tipo, usuario_registra) VALUES (?, ?, ?, ?, ?)");
                    $stmt->execute([UUIDGenerator::generar(), $idSocio, $id, $tipo, $_SESSION['usuario_id']]);
                }

                // Si la asistencia es a tiempo, eliminar multa existente por este concepto
                if ($tipo === 'a_tiempo') {
                    $tiposMulta = ['retraso_10min', 'retraso_30min', 'inasistencia'];
                    foreach ($tiposMulta as $tm) {
                        $this->db->prepare("DELETE FROM obligaciones_sesion WHERE id_sesion = ? AND id_socio = ? AND tipo = 'multa' AND id_referencia IN (SELECT id_multa FROM multas WHERE id_socio = ? AND id_sesion = ? AND tipo = ?)")
                            ->execute([$id, $idSocio, $idSocio, $id, $tm]);
                        $this->db->prepare("DELETE FROM multas WHERE id_socio = ? AND id_sesion = ? AND tipo = ?")
                            ->execute([$idSocio, $id, $tm]);
                    }
                }

                $this->redirect('/sesion/checkin/' . $id);
            }

            if ($accion === 'pagar_obligacion') {
                $idObligacion = $_POST['id_obligacion'] ?? '';
                $this->procesarPagoObligacion($idObligacion, $id);
                $this->redirect('/sesion/checkin/' . $id);
            }

            if ($accion === 'pagar_seleccion') {
                $ids = $_POST['obligaciones'] ?? [];
                if (!empty($ids)) {
                    foreach ($ids as $oid) {
                        $this->procesarPagoObligacion($oid, $id);
                    }
                }
                $this->redirect('/sesion/checkin/' . $id);
            }

            if ($accion === 'pagar_todo_socio') {
                $idSocio = $_POST['id_socio'] ?? '';
                $stmt = $this->db->prepare("SELECT id_obligacion FROM obligaciones_sesion WHERE id_sesion = ? AND id_socio = ? AND pagada = FALSE");
                $stmt->execute([$id, $idSocio]);
                foreach ($stmt->fetchAll(PDO::FETCH_COLUMN) as $oid) {
                    $this->procesarPagoObligacion($oid, $id);
                }
                $this->redirect('/sesion/checkin/' . $id);
            }

            if ($accion === 'cierre') {
                $this->requirePermission('cobro.cierre_sesion');
                return $this->ejecutarCierre($sesion);
            }
        }

        $this->render('sesiones/checkin', [
            'titulo' => 'Sesion #' . $sesion['numero_sesion'] . ' — ' . $sesion['fecha_sesion'],
            'sesion' => $sesion,
            'socios' => $socios,
            'asistencias' => $asistencias,
            'obligaciones' => $obligaciones,
        ]);
    }

    private function procesarPagoObligacion($idObligacion, $idSesion) {
        $stmt = $this->db->prepare("SELECT o.*, s.cedula FROM obligaciones_sesion o JOIN socios s ON o.id_socio = s.id_socio WHERE o.id_obligacion = ? AND o.pagada = FALSE");
        $stmt->execute([$idObligacion]);
        $o = $stmt->fetch();
        if (!$o) return;

        $tipoCobro = $o['tipo'] === 'cuota_mensual' ? 'aporte_obligatorio' : ($o['tipo'] === 'cuota_credito' ? 'cuota_credito' : ($o['tipo'] === 'multa' ? 'multa' : 'otro'));
        $idCobro = UUIDGenerator::generar();
        $hash = hash('sha256', $o['id_socio'] . $idCobro . $tipoCobro . $o['monto'] . date('Y-m-d H:i:s'));

        $this->db->beginTransaction();
        try {
            $this->db->prepare("INSERT INTO cobros (id_cobro, id_socio, id_sesion, tipo, id_referencia, monto, medio_pago, hash_integridad, usuario_registra) VALUES (?, ?, ?, ?, ?, ?, 'efectivo', ?, ?)")
                ->execute([$idCobro, $o['id_socio'], $idSesion, $tipoCobro, $o['id_referencia'], $o['monto'], $hash, $_SESSION['usuario_id']]);

            // Actualizar cuenta de ahorro
            if ($tipoCobro === 'aporte_obligatorio') {
                $this->db->prepare("UPDATE cuentas_ahorro SET saldo_obligatorio = saldo_obligatorio + ?, saldo_disponible = saldo_disponible + ?, fecha_ultimo_movimiento = NOW() WHERE id_socio = ?")
                    ->execute([$o['monto'], $o['monto'], $o['id_socio']]);
            }

            // Si es cuota de credito, marcar amortizacion como pagada
            if ($tipoCobro === 'cuota_credito' && $o['id_referencia']) {
                $this->db->prepare("UPDATE amortizaciones SET estado = 'pagada', id_cobro = ? WHERE id_amortizacion = ?")->execute([$idCobro, $o['id_referencia']]);
            }

            // Si es multa, marcar como pagada
            if ($tipoCobro === 'multa' && $o['id_referencia']) {
                $upd = $this->db->prepare("UPDATE multas SET pagada = TRUE WHERE id_multa = ?");
                $upd->execute([$o['id_referencia']]);
                if ($upd->rowCount() === 0) {
                    error_log("procesarPagoObligacion: multa no encontrada o ya pagada, id_referencia={$o['id_referencia']}, obligacion={$idObligacion}");
                }
            }

            // Marcar obligacion como pagada
            $this->db->prepare("UPDATE obligaciones_sesion SET pagada = TRUE, id_cobro = ? WHERE id_obligacion = ?")->execute([$idCobro, $idObligacion]);

            $this->historialInsert($o['id_socio'], $tipoCobro, $o['monto'], $idCobro, $idSesion);
            $this->db->commit();

            try {
                require_once ROOT_PATH . '/app/helpers/NotificacionHelper.php';
                $labelTipo = $o['tipo'] === 'cuota_mensual' ? 'Cuota mensual' : ($o['tipo'] === 'cuota_credito' ? 'Cuota de credito' : ($o['tipo'] === 'multa' ? 'Multa' : 'Pago'));
                NotificacionHelper::crear([
                    'id_socio' => $o['id_socio'],
                    'tipo' => 'cobro',
                    'titulo' => 'Pago registrado',
                    'mensaje' => "{$labelTipo} de \${$o['monto']} ha sido registrada en la sesion",
                    'enviar_pusher' => true,
                ]);
            } catch (Exception $e) {}
            try {
                require_once ROOT_PATH . '/app/helpers/PusherHelper.php';
                PusherHelper::actualizarPortal($o['id_socio']);
            } catch (Exception $e) {}
        } catch (Exception $e) {
            $this->db->rollBack();
            error_log("Error pagar obligacion: " . $e->getMessage());
        }
    }

    private function ejecutarCierre($sesion) {
        $id = $sesion['id_sesion'];
        $numSesion = $sesion['numero_sesion'];

        $stmt = $this->db->prepare("SELECT COUNT(*) FROM cobros WHERE id_sesion = ?");
        $stmt->execute([$id]);
        if ($stmt->fetchColumn() == 0) {
            $_SESSION['error'] = 'No hay cobros registrados en esta sesion';
            $this->redirect('/sesion/checkin/' . $id);
        }

        // Validar que todos los socios activos tengan asistencia registrada
        $totalSocios = $this->db->query("SELECT COUNT(*) FROM socios WHERE estado = 'activo'")->fetchColumn();
        $stmt = $this->db->prepare("SELECT COUNT(*) FROM asistencias WHERE id_sesion = ?");
        $stmt->execute([$id]);
        $asistidas = $stmt->fetchColumn();
        if ($asistidas < $totalSocios) {
            $_SESSION['error'] = "Debe registrar la asistencia de todos los socios activos antes de cerrar. Faltan " . ($totalSocios - $asistidas) . " socio(s).";
            $this->redirect('/sesion/checkin/' . $id);
        }

        $stmt = $this->db->prepare("SELECT COALESCE(SUM(monto), 0) FROM cobros WHERE id_sesion = ? AND anulado = FALSE AND tipo != 'desembolso'");
        $stmt->execute([$id]);
        $total_recaudado = $stmt->fetchColumn();

        $stmt = $this->db->prepare("SELECT COALESCE(SUM(monto), 0) FROM cobros WHERE id_sesion = ? AND anulado = FALSE AND tipo = 'desembolso'");
        $stmt->execute([$id]);
        $total_desembolsado = $stmt->fetchColumn();

        $saldo = $total_recaudado - $total_desembolsado;

        $acta = 'acta_sesion_' . $numSesion . '_' . date('Ymd') . '.pdf';

        $stmt = $this->db->prepare("SELECT tipo, COUNT(*) AS total, SUM(monto) AS suma FROM cobros WHERE id_sesion = ? AND anulado = FALSE GROUP BY tipo");
        $stmt->execute([$id]);
        $resumen = $stmt->fetchAll();

        // Generar multas y obligaciones
        $multasGeneradas = $this->generarMultasAsistencia($id, $sesion);

        $sesion['total_recaudado'] = $total_recaudado;
        $sesion['total_desembolsado'] = $total_desembolsado;
        $sesion['saldo_caja'] = $saldo;
        $sesion['fecha_cierre'] = date('Y-m-d H:i:s');
        $htmlFile = PDFGenerator::generarActaCierre($sesion, $resumen, pathinfo($acta, PATHINFO_FILENAME));

        $this->db->beginTransaction();
        try {
            $this->db->prepare("UPDATE sesiones_mensuales SET
                estado = 'cerrada', fecha_cierre = NOW(), usuario_cierre = ?,
                total_recaudado = ?, total_desembolsado = ?, saldo_caja = ?, acta_cierre_pdf = ?
                WHERE id_sesion = ? AND estado = 'abierta'")
                ->execute([$_SESSION['usuario_id'], $total_recaudado, $total_desembolsado, $saldo, $htmlFile, $id]);

            // Notificar a cada socio con multa y actualizar su portal
            foreach ($multasGeneradas as $m) {
                $tipoMulta = str_replace('_', ' ', ucfirst($m['tipo']));
                try {
                    NotificacionHelper::crear([
                        'id_socio' => $m['id_socio'],
                        'tipo' => 'multa',
                        'titulo' => 'Multa generada',
                        'mensaje' => "Se ha generado una multa por {$tipoMulta} de \${$m['monto']} en la sesion #{$numSesion}",
                        'enviar_pusher' => true,
                    ]);
                    require_once ROOT_PATH . '/app/helpers/PusherHelper.php';
                    PusherHelper::actualizarPortal($m['id_socio']);
                } catch (Exception $e) {}
            }

            // Notificar cierre solo a directiva (Presidente, Tesorero, Secretario, Asistente Tesoreria)
            $admins = $this->db->query("SELECT DISTINCT u.id_usuario FROM usuarios u JOIN roles_usuarios ru ON u.id_usuario = ru.id_usuario JOIN roles r ON ru.id_rol = r.id_rol WHERE r.nombre IN ('Presidente','Tesorero','Secretario/a','Asistente de Tesoreria')")->fetchAll(PDO::FETCH_COLUMN);
            foreach ($admins as $uid) {
                try {
                    NotificacionHelper::crear([
                        'id_usuario' => $uid,
                        'tipo' => 'sesion',
                        'titulo' => "Sesion #{$numSesion} cerrada",
                        'mensaje' => "La sesion #{$numSesion} ha sido cerrada. Total recaudado: \${$total_recaudado}",
                        'enviar_pusher' => true,
                    ]);
                } catch (Exception $e) {}
            }

            $this->db->commit();
        } catch (Exception $e) {
            $this->db->rollBack();
            $_SESSION['error'] = 'Error al cerrar la sesion: ' . $e->getMessage();
            $this->redirect('/sesion/checkin/' . $id);
        }
        $this->redirect('/sesion/listar');
    }

    private function generarMultasAsistencia($idSesion, $sesion) {
        $asistencias = $this->db->prepare("SELECT a.*, CONCAT_WS(' ', s.apellido1, s.apellido2, s.nombre1, s.nombre2) AS socio_nombre
                                            FROM asistencias a JOIN socios s ON a.id_socio = s.id_socio
                                            WHERE a.id_sesion = ?");
        $asistencias->execute([$idSesion]);

        $upsertMulta = $this->db->prepare("INSERT INTO multas (id_multa, id_socio, id_sesion, tipo, monto) VALUES (?, ?, ?, ?, ?)
            ON DUPLICATE KEY UPDATE monto = VALUES(monto)");
        $insertOblig = $this->db->prepare("INSERT IGNORE INTO obligaciones_sesion (id_obligacion, id_sesion, id_socio, tipo, concepto, monto, id_referencia) VALUES (?, ?, ?, 'multa', ?, ?, ?)");
        $updateOblig = $this->db->prepare("UPDATE obligaciones_sesion SET monto = ? WHERE id_referencia = ? AND tipo = 'multa' AND pagada = FALSE");
        $generadas = [];

        foreach ($asistencias as $a) {
            $monto = 0;
            $tipo = '';
            switch ($a['tipo']) {
                case 'retraso_10min': $monto = MULTA_RETRASO_10MIN; $tipo = 'retraso_10min'; break;
                case 'retraso_30min': $monto = MULTA_RETRASO_30MIN; $tipo = 'retraso_30min'; break;
                case 'falta': $monto = MULTA_INASISTENCIA; $tipo = 'inasistencia'; break;
                default: continue 2;
            }
            if ($monto > 0) {
                $idMulta = UUIDGenerator::generar();
                $upsertMulta->execute([$idMulta, $a['id_socio'], $idSesion, $tipo, $monto]);

                if ($upsertMulta->rowCount() == 1) {
                    // Nueva multa insertada
                    $concepto = "Multa por " . str_replace('_', ' ', ucfirst($tipo)) . " - Sesion #{$sesion['numero_sesion']} del " . date('d/m/Y', strtotime($sesion['fecha_sesion']));
                    $insertOblig->execute([UUIDGenerator::generar(), $idSesion, $a['id_socio'], $concepto, $monto, $idMulta]);
                } else {
                    // Multa existente actualizada -> buscar id_multa real y actualizar obligacion
                    $realId = $this->db->prepare("SELECT id_multa FROM multas WHERE id_socio = ? AND id_sesion = ? AND tipo = ?");
                    $realId->execute([$a['id_socio'], $idSesion, $tipo]);
                    $idMultaReal = $realId->fetchColumn();
                    if ($idMultaReal) {
                        $updateOblig->execute([$monto, $idMultaReal]);
                    }
                }
                $generadas[] = ['id_socio' => $a['id_socio'], 'tipo' => $tipo, 'monto' => $monto];
            }
        }
        return $generadas;
    }
}
