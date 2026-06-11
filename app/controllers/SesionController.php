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
                                           WHERE m.id_socio = ?
                                           AND m.id_multa NOT IN (
                                               SELECT o.id_referencia FROM obligaciones_sesion o
                                               WHERE o.tipo = 'multa' AND o.pagada = TRUE AND o.id_referencia IS NOT NULL
                                           )
                                           AND m.estado = 'activa'");
            $multas->execute([$idSocio]);
            foreach ($multas as $m) {
                $tipoMulta = str_replace('_', ' ', ucfirst($m['tipo']));
                $concepto = "Multa por {$tipoMulta}";
                if ($m['multa_sesion']) {
                    $fechaMulta = $m['multa_fecha'] ? date('d/m/Y', strtotime($m['multa_fecha'])) : '';
                    $concepto .= " - Sesion #{$m['multa_sesion']}" . ($fechaMulta ? " del {$fechaMulta}" : "");
                }
                // Eliminar obligacion anterior impaga para esta multa (cada multa solo una vez)
                $this->db->prepare("DELETE FROM obligaciones_sesion WHERE id_referencia = ? AND tipo = 'multa' AND pagada = FALSE")->execute([$m['id_multa']]);
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

        // Búsqueda y paginación
        $buscar = trim($_GET['buscar'] ?? '');
        $pagina = max(1, intval($_GET['pagina'] ?? 1));
        $porPagina = 20;
        $offset = ($pagina - 1) * $porPagina;

        // Contar total de socios activos (para paginación)
        $whereSocio = "s.estado = 'activo'";
        $paramsCount = [];
        if ($buscar) {
            $whereSocio .= " AND (s.cedula LIKE ? OR CONCAT_WS(' ', s.apellido1, s.apellido2, s.nombre1, s.nombre2) LIKE ?)";
            $paramsCount[] = "%$buscar%";
            $paramsCount[] = "%$buscar%";
        }
        $totalSocios = $this->db->prepare("SELECT COUNT(*) FROM socios s WHERE $whereSocio");
        $totalSocios->execute($paramsCount);
        $totalPaginas = ceil($totalSocios->fetchColumn() / $porPagina);

        // Obtener socios de la página actual
        $paramsSocio = $paramsCount;
        $sqlSocio = "SELECT s.id_socio, s.cedula, CONCAT_WS(' ', s.apellido1, s.apellido2, s.nombre1, s.nombre2) AS nombre_completo
                     FROM socios s WHERE $whereSocio
                     ORDER BY s.apellido1, s.apellido2, s.nombre1, s.nombre2
                     LIMIT $porPagina OFFSET $offset";
        $socios = $this->db->prepare($sqlSocio);
        $socios->execute($paramsSocio);
        $socios = $socios->fetchAll();

        // Obtener IDs de socios para filtrar obligaciones
        $socioIds = array_column($socios, 'id_socio');

        $asistencias = [];
        $stmt = $this->db->prepare("SELECT * FROM asistencias WHERE id_sesion = ?");
        $stmt->execute([$id]);
        while ($row = $stmt->fetch()) {
            $asistencias[$row['id_socio']] = $row;
        }

        // Obtener obligaciones solo de los socios visibles
        $obligaciones = [];
        if (!empty($socioIds)) {
            $placeholders = implode(',', array_fill(0, count($socioIds), '?'));
            $stmt = $this->db->prepare("SELECT o.* FROM obligaciones_sesion o WHERE o.id_sesion = ? AND o.id_socio IN ($placeholders) ORDER BY o.id_socio, o.tipo");
            $stmt->execute(array_merge([$id], $socioIds));
            foreach ($stmt->fetchAll() as $o) {
                $obligaciones[$o['id_socio']][] = $o;
            }
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['accion'])) {
            $this->validateCSRF();
            $accion = $_POST['accion'];

            if ($accion === 'asistencia') {
                $idSocio = $_POST['id_socio'] ?? '';
                $tipo = $_POST['tipo'] ?? 'falta';
                $stmt = $this->db->prepare("SELECT COUNT(*) AS cnt, MAX(tipo) AS old_tipo FROM asistencias WHERE id_socio = ? AND id_sesion = ?");
                $stmt->execute([$idSocio, $id]);
                $row = $stmt->fetch();
                $existe = intval($row['cnt']) > 0;
                $oldTipo = $existe ? $row['old_tipo'] : null;

                if ($existe) {
                    $stmt = $this->db->prepare("UPDATE asistencias SET tipo = ?, usuario_registra = ? WHERE id_socio = ? AND id_sesion = ?");
                    $stmt->execute([$tipo, $_SESSION['usuario_id'], $idSocio, $id]);
                } else {
                    $stmt = $this->db->prepare("INSERT INTO asistencias (id_asistencia, id_socio, id_sesion, tipo, usuario_registra) VALUES (?, ?, ?, ?, ?)");
                    $stmt->execute([UUIDGenerator::generar(), $idSocio, $id, $tipo, $_SESSION['usuario_id']]);
                }

                // Eliminar multas del tipo anterior si existian
                $tiposEliminar = [];
                if ($oldTipo === 'retraso_10min') $tiposEliminar[] = 'retraso_10min';
                elseif ($oldTipo === 'retraso_30min') $tiposEliminar[] = 'retraso_30min';
                elseif ($oldTipo === 'falta') $tiposEliminar[] = 'inasistencia';

                if ($tipo === 'a_tiempo') {
                    $tiposEliminar = ['retraso_10min', 'retraso_30min', 'inasistencia'];
                }

                $multasEliminadas = false;
                foreach ($tiposEliminar as $tm) {
                    $idsMultas = $this->db->prepare("SELECT id_multa FROM multas WHERE id_socio = ? AND id_sesion = ? AND tipo = ?");
                    $idsMultas->execute([$idSocio, $id, $tm]);
                    $ids = $idsMultas->fetchAll(PDO::FETCH_COLUMN);
                    if (!empty($ids)) {
                        $multasEliminadas = true;
                        $ph = implode(',', array_fill(0, count($ids), '?'));
                        $this->db->prepare("DELETE FROM obligaciones_sesion WHERE id_sesion = ? AND id_socio = ? AND tipo = 'multa' AND id_referencia IN ($ph)")
                            ->execute(array_merge([$id, $idSocio], $ids));
                    }
                    $this->db->prepare("DELETE FROM multas WHERE id_socio = ? AND id_sesion = ? AND tipo = ?")
                        ->execute([$idSocio, $id, $tm]);
                }

                // Notificar correccion solo si se elimino efectivamente una multa
                if ($tipo === 'a_tiempo' && $multasEliminadas) {
                    $numSesion = $sesion['numero_sesion'];
                    $cedula = $this->db->prepare("SELECT cedula FROM socios WHERE id_socio = ?");
                    $cedula->execute([$idSocio]);
                    $cedulaVal = $cedula->fetchColumn() ?: '';
                    NotificacionHelper::crear([
                        'id_socio' => $idSocio,
                        'tipo' => 'asistencia',
                        'titulo' => 'Correccion de asistencia',
                        'mensaje' => "Correccion de asistencia - A tiempo en la sesion #{$numSesion} para el socio {$cedulaVal}. Multa eliminada.",
                        'enviar_pusher' => true,
                    ]);
                    require_once ROOT_PATH . '/app/helpers/PusherHelper.php';
                    PusherHelper::actualizarPortal($idSocio);
                }

                // Generar multa inmediatamente segun el tipo de asistencia seleccionado
                $montoMulta = 0;
                $tipoMulta = '';
                switch ($tipo) {
                    case 'retraso_10min': $montoMulta = MULTA_RETRASO_10MIN; $tipoMulta = 'retraso_10min'; break;
                    case 'retraso_30min': $montoMulta = MULTA_RETRASO_30MIN; $tipoMulta = 'retraso_30min'; break;
                    case 'falta': $montoMulta = MULTA_INASISTENCIA; $tipoMulta = 'inasistencia'; break;
                }

                if ($montoMulta > 0) {
                    $idMulta = UUIDGenerator::generar();
                    $upsert = $this->db->prepare("INSERT INTO multas (id_multa, id_socio, id_sesion, tipo, monto) VALUES (?, ?, ?, ?, ?)
                        ON DUPLICATE KEY UPDATE monto = VALUES(monto)");
                    $upsert->execute([$idMulta, $idSocio, $id, $tipoMulta, $montoMulta]);
                    $numSesion = $sesion['numero_sesion'];

                    if ($upsert->rowCount() == 1) {
                        // Nueva multa — crear obligacion y notificar
                        $concepto = "Multa por " . str_replace('_', ' ', ucfirst($tipoMulta)) . " - Sesion #{$numSesion} del " . date('d/m/Y', strtotime($sesion['fecha_sesion']));
                        $this->db->prepare("INSERT INTO obligaciones_sesion (id_obligacion, id_sesion, id_socio, tipo, concepto, monto, id_referencia) VALUES (?, ?, ?, 'multa', ?, ?, ?)")
                            ->execute([UUIDGenerator::generar(), $id, $idSocio, $concepto, $montoMulta, $idMulta]);

                        $cedula = $this->db->prepare("SELECT cedula FROM socios WHERE id_socio = ?");
                        $cedula->execute([$idSocio]);
                        $cedulaVal = $cedula->fetchColumn() ?: '';
                        $tipoLabel = str_replace('_', ' ', ucfirst($tipoMulta));
                        NotificacionHelper::crear([
                            'id_socio' => $idSocio,
                            'tipo' => 'multa',
                            'titulo' => 'Multa generada',
                            'mensaje' => "Se ha generado una multa por {$tipoLabel} de \${$montoMulta} en la sesion #{$numSesion} para el socio {$cedulaVal}",
                            'enviar_pusher' => true,
                        ]);
                        require_once ROOT_PATH . '/app/helpers/PusherHelper.php';
                        PusherHelper::actualizarPortal($idSocio);
                    } else {
                        // Multa existente actualizada
                        $realId = $this->db->prepare("SELECT id_multa FROM multas WHERE id_socio = ? AND id_sesion = ? AND tipo = ?");
                        $realId->execute([$idSocio, $id, $tipoMulta]);
                        $idMultaReal = $realId->fetchColumn();
                        if ($idMultaReal) {
                            $this->db->prepare("UPDATE obligaciones_sesion SET monto = ? WHERE id_referencia = ? AND tipo = 'multa' AND pagada = FALSE")
                                ->execute([$montoMulta, $idMultaReal]);
                        }
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
                error_log("pagar_seleccion: ids count=" . count($ids) . " sesion=$id");
                if (!empty($ids)) {
                    foreach ($ids as $oid) {
                        error_log("pagar_seleccion: procesando oid=$oid");
                        $this->procesarPagoObligacion($oid, $id);
                    }
                } else {
                    error_log("pagar_seleccion: no hay ids seleccionados");
                    $_SESSION['error'] = 'No se seleccionaron obligaciones';
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
            'buscar' => $buscar,
            'pagina' => $pagina,
            'totalPaginas' => $totalPaginas,
        ]);
    }

    public function obligacionesJSON($idSesion, $idSocio) {
        $this->requireAuth();
        $stmt = $this->db->prepare("SELECT o.* FROM obligaciones_sesion o WHERE o.id_sesion = ? AND o.id_socio = ? AND o.pagada = FALSE ORDER BY o.tipo");
        $stmt->execute([$idSesion, $idSocio]);
        $this->json($stmt->fetchAll());
    }

    private function procesarPagoObligacion($idObligacion, $idSesion) {
        $stmt = $this->db->prepare("SELECT o.*, s.cedula FROM obligaciones_sesion o JOIN socios s ON o.id_socio = s.id_socio WHERE o.id_obligacion = ? AND o.pagada = FALSE");
        $stmt->execute([$idObligacion]);
        $o = $stmt->fetch();
        if (!$o) {
            error_log("procesarPagoObligacion: obligacion no encontrada o ya pagada, id=$idObligacion");
            $_SESSION['error'] = 'La obligacion no existe o ya fue pagada';
            return;
        }

        $tipoCobro = $o['tipo'] === 'cuota_mensual' ? 'aporte_obligatorio' : ($o['tipo'] === 'cuota_credito' ? 'cuota_credito' : ($o['tipo'] === 'multa' ? 'multa' : 'otro'));
        $tipoHistorial = $this->mapearTipoHistorial($tipoCobro);
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

            // Si es multa, marcar como pagada (el estado real esta en obligaciones_sesion)
            // Marcar TODAS las obligaciones para esta multa como pagadas (evita duplicados entre sesiones)
            if ($tipoCobro === 'multa' && $o['id_referencia']) {
                $this->db->prepare("UPDATE obligaciones_sesion SET pagada = TRUE, id_cobro = ? WHERE id_referencia = ? AND tipo = 'multa' AND pagada = FALSE")->execute([$idCobro, $o['id_referencia']]);
            } else {
                // Marcar solo esta obligacion como pagada
                $this->db->prepare("UPDATE obligaciones_sesion SET pagada = TRUE, id_cobro = ? WHERE id_obligacion = ?")->execute([$idCobro, $idObligacion]);
            }

            $this->historialInsert($o['id_socio'], $tipoHistorial, $o['monto'], $idCobro, $idSesion);
            $this->db->commit();

            try {
                require_once ROOT_PATH . '/app/helpers/NotificacionHelper.php';
                $labelTipo = $o['tipo'] === 'cuota_mensual' ? 'Cuota mensual' : ($o['tipo'] === 'cuota_credito' ? 'Cuota de credito' : ($o['tipo'] === 'multa' ? 'Multa' : 'Pago'));
                $numSesionPago = $this->db->query("SELECT numero_sesion FROM sesiones_mensuales WHERE id_sesion = '$idSesion'")->fetchColumn();
                $fechaPago = date('d/m/Y');
                if ($o['tipo'] === 'multa') {
                    $conceptoOriginal = $o['concepto']; // ej: "Multa por Retraso 10min - Sesion #1 del 11/06/2026"
                    $mensajeNotif = "{$conceptoOriginal} ha sido pagada en Sesion #{$numSesionPago} del {$fechaPago}";
                } else {
                    $mensajeNotif = "{$labelTipo} de \${$o['monto']} ha sido registrada en Sesion #{$numSesionPago} del {$fechaPago}";
                }
                NotificacionHelper::crear([
                    'id_socio' => $o['id_socio'],
                    'tipo' => 'cobro',
                    'titulo' => 'Pago registrado',
                    'mensaje' => $mensajeNotif,
                    'enviar_pusher' => true,
                ]);
            } catch (Exception $e) {}
            try {
                require_once ROOT_PATH . '/app/helpers/PusherHelper.php';
                PusherHelper::actualizarPortal($o['id_socio']);
            } catch (Exception $e) {}
            // Registrar en Caja
            try {
                $numSesionPago = $this->db->query("SELECT numero_sesion FROM sesiones_mensuales WHERE id_sesion = '$idSesion'")->fetchColumn();
                $conceptoCaja = $o['tipo'] === 'multa'
                    ? "{$o['concepto']} - pagada en Sesion #{$numSesionPago}"
                    : "{$labelTipo} - {$o['cedula']} - Sesion #{$numSesionPago}";
                CajaHelper::registrar([
                    'tipo' => 'ingreso',
                    'concepto' => $conceptoCaja,
                    'categoria' => $tipoCobro,
                    'monto' => $o['monto'],
                    'id_socio' => $o['id_socio'],
                    'id_sesion' => $idSesion,
                    'id_referencia' => $idCobro,
                ]);
            } catch (Exception $e) {}
        } catch (Exception $e) {
            $this->db->rollBack();
            error_log("Error pagar obligacion: " . $e->getMessage());
            $_SESSION['error'] = 'Error al procesar pago: ' . $e->getMessage();
        }
    }

    private function ejecutarCierre($sesion) {
        $id = $sesion['id_sesion'];
        $numSesion = $sesion['numero_sesion'];

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
        $multasGeneradas = [];

        // Multa por cuota impaga (socios que no pagaron su cuota mensual)
        $montoMultaCuota = floatval($this->db->query("SELECT valor FROM parametros WHERE codigo = 'multa_cuota_impaga'")->fetchColumn() ?: 2);
        if ($montoMultaCuota > 0) {
            // 1. Eliminar multas cuota_impaga existentes de socios que YA pagaron (limpia si se reabrio y pago)
            $pagaron = $this->db->prepare("SELECT DISTINCT o.id_socio FROM obligaciones_sesion o WHERE o.id_sesion = ? AND o.tipo = 'cuota_mensual' AND o.pagada = TRUE");
            $pagaron->execute([$id]);
            $idsPagaron = $pagaron->fetchAll(PDO::FETCH_COLUMN);
            if (!empty($idsPagaron)) {
                $ph = implode(',', array_fill(0, count($idsPagaron), '?'));
                // Obtener IDs de multas cuota_impaga de estos socios en esta sesion
                $idsMultas = $this->db->prepare("SELECT id_multa FROM multas WHERE id_sesion = ? AND tipo = 'cuota_impaga' AND id_socio IN ($ph)");
                $idsMultas->execute(array_merge([$id], $idsPagaron));
                $idsMultasArr = $idsMultas->fetchAll(PDO::FETCH_COLUMN);
                if (!empty($idsMultasArr)) {
                    $ph2 = implode(',', array_fill(0, count($idsMultasArr), '?'));
                    $this->db->prepare("DELETE FROM obligaciones_sesion WHERE id_referencia IN ($ph2) AND tipo = 'multa' AND pagada = FALSE")->execute($idsMultasArr);
                    // Solo eliminar multas sin obligaciones pagadas
                    $this->db->prepare("DELETE FROM multas WHERE id_multa IN ($ph2) AND id_multa NOT IN (SELECT id_referencia FROM obligaciones_sesion WHERE id_referencia IN ($ph2) AND tipo = 'multa' AND pagada = TRUE)")->execute(array_merge($idsMultasArr, $idsMultasArr));
                }
            }
            // 2. Crear multas para socios que NO pagaron
            $sociosSinPago = $this->db->prepare("SELECT s.id_socio FROM socios s WHERE s.estado = 'activo' AND s.id_socio NOT IN (SELECT o.id_socio FROM obligaciones_sesion o WHERE o.id_sesion = ? AND o.tipo = 'cuota_mensual' AND o.pagada = TRUE)");
            $sociosSinPago->execute([$id]);
            foreach ($sociosSinPago as $sp) {
                // Verificar si ya existe multa cuota_impaga para evitar duplicados
                $existe = $this->db->prepare("SELECT id_multa FROM multas WHERE id_sesion = ? AND id_socio = ? AND tipo = 'cuota_impaga'");
                $existe->execute([$id, $sp['id_socio']]);
                $row = $existe->fetch();
                if ($row) {
                    $idMulta = $row['id_multa'];
                } else {
                    $idMulta = UUIDGenerator::generar();
                    $this->db->prepare("INSERT INTO multas (id_multa, id_socio, id_sesion, tipo, monto) VALUES (?, ?, ?, 'cuota_impaga', ?)")->execute([$idMulta, $sp['id_socio'], $id, $montoMultaCuota]);
                    $multasGeneradas[] = ['id_socio' => $sp['id_socio'], 'tipo' => 'cuota_impaga', 'monto' => $montoMultaCuota];
                }
                $concepto = "Multa por cuota impaga - Sesion #{$numSesion} del " . date('d/m/Y', strtotime($sesion['fecha_sesion']));
                $this->db->prepare("DELETE FROM obligaciones_sesion WHERE id_referencia = ? AND tipo = 'multa' AND pagada = FALSE")->execute([$idMulta]);
                $this->db->prepare("INSERT INTO obligaciones_sesion (id_obligacion, id_sesion, id_socio, tipo, concepto, monto, id_referencia) VALUES (?, ?, ?, 'multa', ?, ?, ?)")->execute([UUIDGenerator::generar(), $id, $sp['id_socio'], $concepto, $montoMultaCuota, $idMulta]);
            }
        }

        $sesion['total_recaudado'] = $total_recaudado;
        $sesion['total_desembolsado'] = $total_desembolsado;
        $sesion['saldo_caja'] = $saldo;
        $sesion['fecha_cierre'] = date('Y-m-d H:i:s');
        $htmlFile = PDFGenerator::generarActaCierre($sesion, $resumen, pathinfo($acta, PATHINFO_FILENAME));

        $this->db->beginTransaction();
        try {
            $upd = $this->db->prepare("UPDATE sesiones_mensuales SET
                estado = 'cerrada', fecha_cierre = NOW(), usuario_cierre = ?,
                total_recaudado = ?, total_desembolsado = ?, saldo_caja = ?, acta_cierre_pdf = ?
                WHERE id_sesion = ? AND estado = 'abierta'");
            $upd->execute([$_SESSION['usuario_id'], $total_recaudado, $total_desembolsado, $saldo, $htmlFile, $id]);
            if ($upd->rowCount() === 0) {
                $this->db->rollBack();
                $this->redirect('/sesion/listar');
            }

            // Notificar a cada socio con multa y actualizar su portal
            foreach ($multasGeneradas as $m) {
                $tipoMulta = str_replace('_', ' ', ucfirst($m['tipo']));
                $cedula = $this->db->prepare("SELECT cedula FROM socios WHERE id_socio = ?");
                $cedula->execute([$m['id_socio']]);
                $cedulaVal = $cedula->fetchColumn() ?: '';
                try {
                    NotificacionHelper::crear([
                        'id_socio' => $m['id_socio'],
                        'tipo' => 'multa',
                        'titulo' => 'Multa generada',
                        'mensaje' => "Se ha generado una multa por {$tipoMulta} de \${$m['monto']} en la sesion #{$numSesion} para el socio {$cedulaVal}",
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
                    $generadas[] = ['id_socio' => $a['id_socio'], 'tipo' => $tipo, 'monto' => $monto];
                } else {
                    // Multa existente actualizada -> buscar id_multa real y actualizar obligacion
                    $realId = $this->db->prepare("SELECT id_multa FROM multas WHERE id_socio = ? AND id_sesion = ? AND tipo = ?");
                    $realId->execute([$a['id_socio'], $idSesion, $tipo]);
                    $idMultaReal = $realId->fetchColumn();
                    if ($idMultaReal) {
                        $updateOblig->execute([$monto, $idMultaReal]);
                    }
                }
            }
        }
        return $generadas;
    }
}
