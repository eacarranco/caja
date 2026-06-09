<?php
require_once ROOT_PATH . '/app/helpers/NotificacionHelper.php';
class PortalController extends BaseController {

    public function index() {
        $this->requireAuth();
        $cedula = $_SESSION['usuario_cedula'] ?? '';

        $stmt = $this->db->prepare("SELECT * FROM socios WHERE cedula = ?");
        $stmt->execute([$cedula]);
        $socio = $stmt->fetch();

        if (!$socio) {
            $this->render('portal/index', [
                'titulo' => 'Inicio',
                'socio' => null,
                'cuenta' => null,
                'capital_inversion' => 0,
                'valores_pagar' => 0,
            ]);
            return;
        }
        $idSocio = $socio['id_socio'];

        $stmt = $this->db->prepare("SELECT * FROM cuentas_ahorro WHERE id_socio = ?");
        $stmt->execute([$idSocio]);
        $cuenta = $stmt->fetch();

        $stmt = $this->db->prepare("SELECT COALESCE(saldo, 0) FROM capital_inversion WHERE id_socio = ?");
        $stmt->execute([$idSocio]);
        $saldoCapitalInversion = floatval($stmt->fetchColumn());

        $stmt = $this->db->prepare("SELECT IFNULL(SUM(monto), 0) AS multas FROM multas WHERE id_socio = ? AND pagada = FALSE");
        $stmt->execute([$idSocio]);
        $multasRes = $stmt->fetch();

        $aporteMensual = floatval($this->db->query("SELECT valor FROM parametros WHERE codigo = 'aporte_obligatorio_mensual'")->fetchColumn() ?: 10);
        $valoresPagar = $aporteMensual + floatval($multasRes['multas'] ?? 0);

        $this->render('portal/index', [
            'titulo' => 'Inicio',
            'socio' => $socio,
            'cuenta' => $cuenta,
            'capital_inversion' => $saldoCapitalInversion,
            'valores_pagar' => $valoresPagar,
        ]);
    }

    public function historial() {
        $this->requireAuth();
        $cedula = $_SESSION['usuario_cedula'] ?? '';
        $stmt = $this->db->prepare("SELECT id_socio FROM socios WHERE cedula = ?");
        $stmt->execute([$cedula]);
        $socio = $stmt->fetch();
        if (!$socio) { $this->redirect('/portal'); return; }

        $stmt = $this->db->prepare("SELECT h.*, c.nombre1, c.apellido1 FROM historial_operaciones h JOIN socios c ON h.id_socio = c.id_socio WHERE h.id_socio = ? ORDER BY h.fecha_registro DESC LIMIT 100");
        $stmt->execute([$socio['id_socio']]);
        $historial = $stmt->fetchAll();

        $stmt = $this->db->prepare("SELECT saldo_obligatorio, saldo_excedente FROM cuentas_ahorro WHERE id_socio = ?");
        $stmt->execute([$socio['id_socio']]);
        $cuenta = $stmt->fetch();
        $currentBalance = floatval($cuenta['saldo_obligatorio'] ?? 0) + floatval($cuenta['saldo_excedente'] ?? 0);

        $balance = $currentBalance;
        $impactMap = [
            'aporte_obligatorio' => 1,
            'aporte_excedente' => 1,
            'interes_ganado' => 1,
            'inversion_retiro' => 1,
            'retiro_ahorro' => -1,
            'inversion_apertura' => -1,
        ];

        foreach ($historial as &$registro) {
            $tipo = $registro['tipo_operacion'];
            $monto = floatval($registro['monto'] ?? 0);
            $impact = $impactMap[$tipo] ?? 0;
            $registro['saldo_posterior'] = $balance;
            if ($impact === 1) {
                $registro['saldo_anterior'] = $balance - $monto;
            } elseif ($impact === -1) {
                $registro['saldo_anterior'] = $balance + $monto;
            } else {
                $registro['saldo_anterior'] = $balance;
            }
            $balance = $registro['saldo_anterior'];
        }
        unset($registro);

        $this->render('portal/historial', [
            'titulo' => 'Historial de operaciones',
            'historial' => $historial,
        ]);
    }

    public function solicitarRetiro() {
        $this->requireAuth();
        $cedula = $_SESSION['usuario_cedula'] ?? '';
        $stmt = $this->db->prepare("SELECT s.id_socio, c.saldo_disponible FROM socios s LEFT JOIN cuentas_ahorro c ON s.id_socio = c.id_socio WHERE s.cedula = ?");
        $stmt->execute([$cedula]);
        $socio = $stmt->fetch();
        if (!$socio) $this->redirect('/portal');

        $errors = [];
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->validateCSRF();
            $monto = str_replace(',', '.', $_POST['monto'] ?? '0');
            $motivo = trim($_POST['motivo'] ?? '');

            if (!is_numeric($monto) || $monto <= 0) $errors['monto'] = 'Monto inválido';
            elseif ($monto > ($socio['saldo_disponible'] ?? 0)) $errors['monto'] = 'Saldo disponible insuficiente: $' . number_format($socio['saldo_disponible'] ?? 0, 2);
            if (empty($motivo)) $errors['motivo'] = 'Indique el motivo del retiro';

            $pend = $this->db->prepare("SELECT COUNT(*) FROM solicitudes_retiro WHERE id_socio = ? AND estado = 'pendiente'");
            $pend->execute([$socio['id_socio']]);
            if ($pend->fetchColumn() > 0) $errors['general'] = 'Ya tiene una solicitud pendiente';

            if (empty($errors)) {
                $id = UUIDGenerator::generar();
                $this->db->prepare("INSERT INTO solicitudes_retiro (id_solicitud, id_socio, monto, motivo) VALUES (?, ?, ?, ?)")
                    ->execute([$id, $socio['id_socio'], $monto, $motivo]);
                NotificacionHelper::crearCobro($socio['id_socio'], $cedula, $monto, 'Solicitud de retiro');
                $this->redirect('/portal');
            }
        }

        $stmt = $this->db->prepare("SELECT * FROM solicitudes_retiro WHERE id_socio = ? ORDER BY fecha_solicitud DESC");
        $stmt->execute([$socio['id_socio']]);
        $solicitudes = $stmt->fetchAll();

        $this->render('portal/retiro', [
            'titulo' => 'Solicitar retiro',
            'errors' => $errors,
            'saldo' => $socio['saldo_disponible'] ?? 0,
            'solicitudes' => $solicitudes,
        ]);
    }

    public function multas() {
        $this->requireAuth();
        $cedula = $_SESSION['usuario_cedula'] ?? '';
        $stmt = $this->db->prepare("SELECT id_socio FROM socios WHERE cedula = ?");
        $stmt->execute([$cedula]);
        $socio = $stmt->fetch();
        if (!$socio) { $this->redirect('/portal'); return; }

        $stmt = $this->db->prepare("SELECT * FROM multas WHERE id_socio = ? ORDER BY fecha_generacion DESC");
        $stmt->execute([$socio['id_socio']]);
        $multas = $stmt->fetchAll();

        $this->render('portal/multas', [
            'titulo' => 'Mis multas',
            'multas' => $multas,
        ]);
    }

    public function asistencias() {
        $this->requireAuth();
        $cedula = $_SESSION['usuario_cedula'] ?? '';
        $stmt = $this->db->prepare("SELECT id_socio FROM socios WHERE cedula = ?");
        $stmt->execute([$cedula]);
        $socio = $stmt->fetch();
        if (!$socio) { $this->redirect('/portal'); return; }

        $stmt = $this->db->prepare("SELECT a.*, ses.numero_sesion, ses.fecha AS fecha_sesión
                                    FROM asistencias a
                                    JOIN sesiones_mensuales ses ON a.id_sesion = ses.id_sesion
                                    WHERE a.id_socio = ?
                                    ORDER BY a.fecha_registro DESC");
        $stmt->execute([$socio['id_socio']]);
        $asistencias = $stmt->fetchAll();

        $this->render('portal/asistencias', [
            'titulo' => 'Mis asistencias',
            'asistencias' => $asistencias,
        ]);
    }

    public function notificaciones() {
        $this->requireAuth();
        $cedula = $_SESSION['usuario_cedula'] ?? '';
        $stmt = $this->db->prepare("SELECT id_socio FROM socios WHERE cedula = ?");
        $stmt->execute([$cedula]);
        $socio = $stmt->fetch();

        $notificaciones = [];
        if ($socio) {
            $stmt = $this->db->prepare("SELECT * FROM notificaciones WHERE id_socio = ? ORDER BY fecha_creacion DESC LIMIT 50");
            $stmt->execute([$socio['id_socio']]);
            $notificaciones = $stmt->fetchAll();
        }

        $stmt = $this->db->prepare("SELECT COUNT(*) FROM notificaciones WHERE id_usuario = ? AND leida = FALSE");
        $stmt->execute([$_SESSION['usuario_id']]);
        $noLeidas = $stmt->fetchColumn();

        $this->render('portal/notificaciones', [
            'titulo' => 'Notificaciones',
            'notificaciones' => $notificaciones,
            'noLeidas' => $noLeidas,
        ]);
    }

    public function pagar() {
        $this->requireAuth();
        $cedula = $_SESSION['usuario_cedula'] ?? '';
        $stmt = $this->db->prepare("SELECT id_socio FROM socios WHERE cedula = ?");
        $stmt->execute([$cedula]);
        $socio = $stmt->fetch();
        $pendientes = [];

        if ($socio) {
            $idSocio = $socio['id_socio'];

            $aporteMensual = floatval($this->db->query("SELECT valor FROM parametros WHERE codigo = 'aporte_obligatorio_mensual'")->fetchColumn() ?: 10);

            $stmt = $this->db->prepare("SELECT IFNULL(SUM(monto), 0) AS multas_pendientes FROM multas WHERE id_socio = ? AND pagada = FALSE");
            $stmt->execute([$idSocio]);
            $multas = $stmt->fetch();

            $pendientes = [
                'aporte_mensual' => $aporteMensual,
                'multas' => $multas['multas_pendientes'] ?? 0,
                'total' => $aporteMensual + floatval($multas['multas_pendientes'] ?? 0),
            ];
        }

        $this->render('portal/pagar', [
            'titulo' => 'Pagar',
            'pendientes' => $pendientes,
        ]);
    }

    public function solicitarCredito() {
        $this->requireAuth();
        $cedula = $_SESSION['usuario_cedula'] ?? '';
        $stmt = $this->db->prepare("SELECT s.*, c.saldo_obligatorio, c.saldo_excedente FROM socios s LEFT JOIN cuentas_ahorro c ON s.id_socio = c.id_socio WHERE s.cedula = ?");
        $stmt->execute([$cedula]);
        $socio = $stmt->fetch();
        if (!$socio) $this->redirect('/portal');

        $productos = $this->db->query("SELECT * FROM productos_financieros WHERE tipo = 'credito' AND activo = TRUE ORDER BY nombre")->fetchAll();

        $stmt = $this->db->prepare("SELECT COUNT(*) FROM creditos WHERE id_socio = ? AND estado IN ('ingresado','pendiente','aprobado','legalizado')");
        $stmt->execute([$socio['id_socio']]);
        $tieneSolicitudActiva = $stmt->fetchColumn() > 0;

        $errors = [];
        $exito = '';

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->validateCSRF();
            $idProducto = $_POST['id_producto'] ?? '';
            $monto = str_replace(',', '.', $_POST['monto'] ?? '0');
            $plazo = intval($_POST['plazo'] ?? 1);
            $acepta = !empty($_POST['acepta_condiciones']);

            $prod = null;
            foreach ($productos as $p) {
                if ($p['id_producto'] === $idProducto) { $prod = $p; break; }
            }

            if (!$prod) $errors['id_producto'] = 'Seleccione un producto';
            if (!is_numeric($monto) || $monto <= 0) $errors['monto'] = 'Monto inválido';
            if ($plazo < ($prod['plazo_min_meses'] ?? 1) || $plazo > ($prod['plazo_max_meses'] ?? 999)) $errors['plazo'] = 'Plazo fuera de rango';
            if ($monto < ($prod['monto_min'] ?? 0) || $monto > ($prod['monto_max'] ?? 999999)) $errors['monto'] = 'Monto fuera del rango del producto';
            if (!$acepta) $errors['acepta'] = 'Debe aceptar las condiciones';

            $fechaIngreso = new DateTime($socio['fecha_ingreso']);
            $hoy = new DateTime();
            $mesesActivo = $fechaIngreso->diff($hoy)->m + ($fechaIngreso->diff($hoy)->y * 12);
            $permanenciaReq = intval($prod['min_permanencia_meses'] ?? 0);
            if ($permanenciaReq > 0 && $mesesActivo < $permanenciaReq) {
                $errors['elegibilidad'] = "Requiere mínimo $permanenciaReq meses de permanencia activa (lleva $mesesActivo)";
            }
            $ahorroReq = floatval($prod['min_ahorro'] ?? 0);
            $ahorroTotal = floatval($socio['saldo_obligatorio'] ?? 0) + floatval($socio['saldo_excedente'] ?? 0);
            if ($ahorroReq > 0 && $ahorroTotal < $ahorroReq) {
                $errors['elegibilidad'] = ($errors['elegibilidad'] ?? '') . " Requiere mínimo $" . number_format($ahorroReq, 2) . " de ahorro (tiene $" . number_format($ahorroTotal, 2) . ")";
            }

            $stmt = $this->db->prepare("SELECT COUNT(*) FROM creditos WHERE id_socio = ? AND estado IN ('ingresado','pendiente','aprobado','legalizado')");
            $stmt->execute([$socio['id_socio']]);
            if ($stmt->fetchColumn() > 0) {
                $errors['general'] = 'Ya tiene una solicitud de crédito activa. Espere a que sea procesada.';
            }

            if (empty($errors)) {
                $id = UUIDGenerator::generar();
                $stmt = $this->db->prepare("INSERT INTO `creditos`
                    (id_credito, id_socio, id_producto, monto_solicitado, plazo_meses, tasa_interes, metodo_interes, destino, estado)
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'ingresado')");
                $stmt->execute([
                    $id, $socio['id_socio'], $idProducto, $monto, $plazo,
                    $prod['tasa_interes_anual'], $prod['metodo_interes'], $_POST['destino'] ?? ''
                ]);

                if (!empty($prod['requiere_garante']) && !empty($_POST['garantes'])) {
                    $insG = $this->db->prepare("INSERT INTO garantes (id_garante, id_credito, id_socio, monto_garantizado) VALUES (?, ?, ?, ?)");
                    $montoG = round($monto / count($_POST['garantes']), 2);
                    foreach ($_POST['garantes'] as $g) {
                        $insG->execute([UUIDGenerator::generar(), $id, $g, $montoG]);
                    }
                }

                NotificacionHelper::crear([
                    'tipo' => 'credito',
                    'titulo' => 'Nueva solicitud de credito',
                    'mensaje' => "El socio " . ($socio['nombre1'] ?? '') . " ha solicitado un credito de $$monto",
                    'enviar_pusher' => true,
                ]);

                $exito = 'Solicitud ingresada exitosamente. Recibirá notificación cuando sea procesada.';
            }
        }

        $stmt = $this->db->prepare("SELECT id_socio, cedula, CONCAT_WS(' ', apellido1, apellido2, nombre1, nombre2) AS nombre FROM socios WHERE estado = 'activo' AND id_socio != ? ORDER BY apellido1, nombre1");
        $stmt->execute([$socio['id_socio']]);
        $sociosActivos = $stmt->fetchAll();

        $this->render('portal/solicitarCredito', [
            'titulo' => 'Solicitar crédito',
            'productos' => $productos,
            'socio' => $socio,
            'sociosActivos' => $sociosActivos,
            'errors' => $errors,
            'exito' => $exito,
            'tieneSolicitudActiva' => $tieneSolicitudActiva,
        ]);
    }

    public function solicitarCertificado() {
        $this->requireAuth();
        $cedula = $_SESSION['usuario_cedula'] ?? '';
        $stmt = $this->db->prepare("SELECT id_socio FROM socios WHERE cedula = ?");
        $stmt->execute([$cedula]);
        $socio = $stmt->fetch();
        if (!$socio) $this->redirect('/portal');

        $idSocio = $socio['id_socio'];

        $stmt = $this->db->prepare("SELECT CONCAT_WS(' ', apellido1, apellido2, nombre1, nombre2) AS nombre FROM socios WHERE id_socio = ?");
        $stmt->execute([$idSocio]);
        $socioData = $stmt->fetch();

        $this->render('portal/certificaciones', [
            'titulo' => 'Certificaciones',
            'id_socio' => $idSocio,
            'socio_nombre' => $socioData['nombre'] ?? '',
        ]);
    }

    public function detalleAhorro() {
        $this->requireAuth();
        $cedula = $_SESSION['usuario_cedula'] ?? '';
        $stmt = $this->db->prepare("SELECT s.*, ca.saldo_obligatorio, ca.saldo_excedente FROM socios s LEFT JOIN cuentas_ahorro ca ON s.id_socio = ca.id_socio WHERE s.cedula = ?");
        $stmt->execute([$cedula]);
        $socio = $stmt->fetch();
        if (!$socio) $this->redirect('/portal');
        $idSocio = $socio['id_socio'];

        // Get current balances
        $saldoObligatorio = floatval($socio['saldo_obligatorio'] ?? 0);
        $saldoExcedente = floatval($socio['saldo_excedente'] ?? 0);

        // Get all historial_operaciones that affect savings account
        $stmt = $this->db->prepare("SELECT h.*, ses.numero_sesion, ses.titulo AS sesion_titulo, ses.fecha AS sesion_fecha
                                     FROM historial_operaciones h
                                     LEFT JOIN sesiones_mensuales ses ON h.id_sesion = ses.id_sesion
                                     WHERE h.id_socio = ?
                                     ORDER BY h.fecha_registro DESC");
        $stmt->execute([$idSocio]);
        $movimientos = $stmt->fetchAll();

        // Generate conceptos
        $conceptos = [
            'aporte_obligatorio' => 'Aporte obligatorio',
            'aporte_excedente' => 'Aporte excedente',
            'retiro_ahorro' => 'Retiro de ahorro',
            'desembolso_credito' => 'Desembolso de credito',
            'pago_cuota' => 'Pago de cuota de credito',
            'pago_multa' => 'Pago de multa',
            'inversion_apertura' => 'Inversion apertura',
            'inversion_retiro' => 'Retorno de inversion',
            'interes_ganado' => 'Interes ganado',
            'interes_pagado' => 'Interes pagado',
            'cierre_sesion' => 'Cierre de sesion',
            'anulacion' => 'Anulacion',
            'deposito_capital_inversion' => 'Deposito a capital de inversion',
            'retiro_capital_inversion' => 'Retiro de capital de inversion',
        ];

        $movs = [];
        foreach ($movimientos as $m) {
            $label = $conceptos[$m['tipo_operacion']] ?? $m['tipo_operacion'];
            if ($m['numero_sesion']) {
                $fechaSesion = $m['sesion_fecha'] ? date('d/m/Y', strtotime($m['sesion_fecha'])) : '';
                $label .= ' (Sesion #' . $m['numero_sesion'] . ' del ' . $fechaSesion . ')';
            }
            $m['concepto'] = $label;
            $movs[] = $m;
        }

        $this->render('portal/detalleAhorro', [
            'titulo' => 'Estado de cuenta',
            'movimientos' => $movs,
            'saldo_obligatorio' => $saldoObligatorio,
            'saldo_excedente' => $saldoExcedente,
        ]);
    }

    public function inversion() {
        $this->requireAuth();
        $cedula = $_SESSION['usuario_cedula'] ?? '';
        $stmt = $this->db->prepare("SELECT id_socio FROM socios WHERE cedula = ?");
        $stmt->execute([$cedula]);
        $idSocio = $stmt->fetchColumn();
        if (!$idSocio) $this->redirect('/portal');

        $capital = $this->db->prepare("SELECT * FROM capital_inversion WHERE id_socio = ?");
        $capital->execute([$idSocio]);
        $capitalRow = $capital->fetch();

        $stmt = $this->db->prepare("SELECT i.*, p.nombre AS producto FROM inversiones i JOIN productos_financieros p ON i.id_producto = p.id_producto WHERE i.id_socio = ? ORDER BY i.fecha_registro DESC");
        $stmt->execute([$idSocio]);
        $inversiones = $stmt->fetchAll();

        $productos = $this->db->query("SELECT id_producto, nombre, tasa_interes_anual, plazo_min_meses, plazo_max_meses, monto_min, monto_max FROM productos_financieros WHERE tipo = 'inversion' AND activo = TRUE ORDER BY nombre")->fetchAll();

        $this->render('portal/inversion', [
            'titulo' => 'Inversion',
            'capital' => $capitalRow,
            'inversiones' => $inversiones,
            'productos' => $productos,
        ]);
    }

    public function simularCredito() {
        $this->requireAuth();
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->json(['error' => 'Método no permitido'], 405);
        }
        $this->validateCSRF();

        $monto = str_replace(',', '.', $_POST['monto'] ?? '0');
        $tasa = str_replace(',', '.', $_POST['tasa'] ?? '0');
        $plazo = intval($_POST['plazo'] ?? 1);
        $metodo = $_POST['metodo'] ?? 'simple';

        if (!is_numeric($monto) || $monto <= 0 || !is_numeric($tasa) || $plazo <= 0) {
            $this->json(['error' => 'Parámetros inválidos'], 400);
        }

        require_once ROOT_PATH . '/app/helpers/CalculadoraInteres.php';
        try {
            $cuotas = CalculadoraInteres::simular($monto, $tasa, $plazo, $metodo);
            $this->json($cuotas);
        } catch (Exception $e) {
            $this->json(['error' => $e->getMessage()], 500);
        }
    }

    public function detalleCapitalInversion() {
        $this->requireAuth();
        $cedula = $_SESSION['usuario_cedula'] ?? '';
        $stmt = $this->db->prepare("SELECT id_socio FROM socios WHERE cedula = ?");
        $stmt->execute([$cedula]);
        $idSocio = $stmt->fetchColumn();
        if (!$idSocio) $this->redirect('/portal');

        $capital = $this->db->prepare("SELECT * FROM capital_inversion WHERE id_socio = ?");
        $capital->execute([$idSocio]);
        $capitalRow = $capital->fetch();

        $inv = $this->db->prepare("SELECT i.*, p.nombre AS producto FROM inversiones i JOIN productos_financieros p ON i.id_producto = p.id_producto WHERE i.id_socio = ? ORDER BY i.fecha_registro DESC");
        $inv->execute([$idSocio]);
        $inversiones = $inv->fetchAll();

        $historial = $this->db->prepare("SELECT * FROM historial_operaciones WHERE id_socio = ? AND (tipo_operacion = 'deposito_capital_inversion' OR tipo_operacion = 'inversion_apertura' OR tipo_operacion = 'inversion_retiro') ORDER BY fecha_registro DESC");
        $historial->execute([$idSocio]);
        $movimientos = $historial->fetchAll();

        $this->render('portal/detalleCapitalInversion', [
            'titulo' => 'Capital de inversion',
            'capital' => $capitalRow,
            'inversiones' => $inversiones,
            'movimientos' => $movimientos,
        ]);
    }

    public function password() {
        $this->requireAuth();
        $errors = [];
        $exito = '';

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->validateCSRF();
            $actual = $_POST['actual'] ?? '';
            $nueva = $_POST['nueva'] ?? '';
            $confirmar = $_POST['confirmar'] ?? '';

            $stmt = $this->db->prepare("SELECT contrasena FROM usuarios WHERE id_usuario = ?");
            $stmt->execute([$_SESSION['usuario_id']]);
            $hash = $stmt->fetchColumn();

            if (!password_verify($actual, $hash)) $errors['actual'] = 'Contraseña actual incorrecta';
            if (strlen($nueva) < 6) $errors['nueva'] = 'Mínimo 6 caracteres';
            if ($nueva !== $confirmar) $errors['confirmar'] = 'No coinciden';

            if (empty($errors)) {
                $this->db->prepare("UPDATE usuarios SET contrasena = ? WHERE id_usuario = ?")
                    ->execute([password_hash($nueva, PASSWORD_BCRYPT), $_SESSION['usuario_id']]);
                $exito = 'Contraseña actualizada';
            }
        }

        $this->render('portal/password', [
            'titulo' => 'Cambiar contrasena',
            'errors' => $errors,
            'exito' => $exito,
        ]);
    }
}
