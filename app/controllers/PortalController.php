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
        if (!$cuenta) {
            $this->db->prepare("INSERT INTO cuentas_ahorro (id_cuenta_ahorro, id_socio) VALUES (?, ?)")
                ->execute([UUIDGenerator::generar(), $idSocio]);
            $cuenta = ['saldo_obligatorio' => 0, 'saldo_excedente' => 0, 'saldo_disponible' => 0];
        }

        $stmt = $this->db->prepare("SELECT COALESCE(saldo, 0) FROM capital_inversion WHERE id_socio = ?");
        $stmt->execute([$idSocio]);
        $capDisponible = floatval($stmt->fetchColumn());
        $stmt = $this->db->prepare("SELECT COALESCE(SUM(monto), 0) FROM inversiones WHERE id_socio = ? AND estado IN ('activa','vencida')");
        $stmt->execute([$idSocio]);
        $capInvertido = floatval($stmt->fetchColumn());
        $saldoCapitalInversion = $capDisponible + $capInvertido;

        $stmt = $this->db->prepare("SELECT COALESCE(SUM(monto), 0) FROM obligaciones_sesion WHERE id_socio = ? AND pagada = FALSE");
        $stmt->execute([$idSocio]);
        $valoresPagar = floatval($stmt->fetchColumn());

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
                NotificacionHelper::crear([
                    'id_socio' => $socio['id_socio'],
                    'tipo' => 'retiro',
                    'titulo' => 'Solicitud de retiro',
                    'mensaje' => "Has solicitado un retiro de \${$monto}. Espera la aprobacion.",
                    'enviar_pusher' => true,
                ]);
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

        $stmt = $this->db->prepare("SELECT m.*, (SELECT COUNT(*) FROM obligaciones_sesion WHERE id_referencia = m.id_multa AND tipo = 'multa' AND pagada = TRUE) AS pagada FROM multas m WHERE m.id_socio = ? ORDER BY m.fecha_generacion DESC");
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

        $stmt = $this->db->prepare("SELECT a.*, ses.numero_sesion, ses.fecha_sesion
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
            $stmt = $this->db->prepare("SELECT * FROM notificaciones WHERE id_socio = ? AND buzon = 'entrada' ORDER BY fecha_creacion DESC LIMIT 50");
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
        $idSocio = $stmt->fetchColumn();

        $obligaciones = [];
        $totalPendiente = 0;

        if ($idSocio) {
            $stmt = $this->db->prepare("SELECT o.*, ses.numero_sesion, ses.fecha_sesion
                                         FROM obligaciones_sesion o
                                         JOIN sesiones_mensuales ses ON o.id_sesion = ses.id_sesion
                                         WHERE o.id_socio = ? AND o.pagada = FALSE
                                         ORDER BY ses.fecha_sesion DESC, o.tipo");
            $stmt->execute([$idSocio]);
            $obligaciones = $stmt->fetchAll();
            $totalPendiente = array_sum(array_map(function($o) { return floatval($o['monto']); }, $obligaciones));
        }

        $this->render('portal/pagar', [
            'titulo' => 'Pagar',
            'obligaciones' => $obligaciones,
            'totalPendiente' => $totalPendiente,
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
            if (!empty($prod['requiere_garante']) && empty($_POST['garantes'])) {
                $errors['garantes'] = 'Debe seleccionar al menos un garante';
            }

            $fechaIngreso = new DateTime($socio['fecha_ingreso']);
            $hoy = new DateTime();
            $mesesActivo = $fechaIngreso->diff($hoy)->m + ($fechaIngreso->diff($hoy)->y * 12);

            $destCarMin = intval($prod['min_destino_caracteres'] ?? 0);
            $destinoText = trim($_POST['destino'] ?? '');
            if ($destCarMin > 0 && mb_strlen($destinoText) < $destCarMin) {
                $errors['destino'] = "El destino debe tener al menos $destCarMin caracteres";
            }

            $permVal = intval($prod['min_permanencia_valor'] ?? 0);
            $permUnidad = $prod['min_permanencia_unidad'] ?? 'meses';
            if ($permVal > 0) {
                $mesesReq = $permVal;
                if ($permUnidad === 'dias') $mesesReq = max(1, round($permVal / 30));
                if ($permUnidad === 'anios') $mesesReq = $permVal * 12;
                if ($mesesActivo < $mesesReq) {
                    $errors['elegibilidad'] = "Requiere minimo $permVal " . ($permUnidad === 'dias' ? 'dias' : ($permUnidad === 'anios' ? 'anios' : 'meses')) . " de permanencia (lleva $mesesActivo meses)";
                }
            }
            $ahorroReq = floatval($prod['min_ahorro'] ?? 0);
            $ahorroReqUnidad = $prod['min_ahorro_unidad'] ?? 'dolares';
            $ahorroTotal = floatval($socio['saldo_obligatorio'] ?? 0) + floatval($socio['saldo_excedente'] ?? 0);
            if ($ahorroReq > 0) {
                $ahorroNecesario = $ahorroReq;
                $labelAhorro = '$' . number_format($ahorroReq, 2);
                if ($ahorroReqUnidad === 'porcentaje') {
                    $ahorroNecesario = round($monto * $ahorroReq / 100, 2);
                    $labelAhorro = $ahorroReq . '% del credito ($' . number_format($ahorroNecesario, 2) . ')';
                }
                if ($ahorroTotal < $ahorroNecesario) {
                    $errors['elegibilidad'] = ($errors['elegibilidad'] ?? '') . " Requiere minimo " . $labelAhorro . " de ahorro (tiene $" . number_format($ahorroTotal, 2) . ")";
                }
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

    public function activarCuenta() {
        $error = '';
        $exito = '';

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->validateCSRF();
            $token = $_POST['token'] ?? '';
            $password = $_POST['password'] ?? '';
            $confirmar = $_POST['confirmar'] ?? '';

            if (empty($token)) $error = 'Token inválido';
            elseif (strlen($password) < 6) $error = 'Mínimo 6 caracteres';
            elseif ($password !== $confirmar) $error = 'Las contraseñas no coinciden';
            else {
                $tokenHash = hash('sha256', $token);
                $stmt = $this->db->prepare("SELECT id_usuario FROM usuarios WHERE token_activacion = ? AND token_activacion_expira > NOW() AND activo = TRUE");
                $stmt->execute([$tokenHash]);
                $idUsuario = $stmt->fetchColumn();

                if (!$idUsuario) $error = 'El enlace ha expirado o es inválido. Contacta al administrador.';
                else {
                    $stmt = $this->db->prepare("UPDATE usuarios SET contrasena = ?, token_activacion = NULL, token_activacion_expira = NULL, fecha_contrasena = NOW() WHERE id_usuario = ?");
                    $stmt->execute([password_hash($password, PASSWORD_BCRYPT), $idUsuario]);
                    $exito = 'Cuenta activada correctamente. Ahora puedes iniciar sesión con tu cédula y nueva contraseña.';
                }
            }
        }

        $token = $_GET['token'] ?? '';
        if (empty($token) && empty($_POST)) $error = 'Enlace inválido. Usa el enlace de tu correo de bienvenida.';

        $this->render('portal/activarCuenta', [
            'titulo' => 'Activar cuenta',
            'token' => $token,
            'error' => $error,
            'exito' => $exito,
        ], 'layouts/blank');
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

        // Get historial_operaciones that affect savings account only (no multas, no anulaciones)
        $stmt = $this->db->prepare("SELECT h.*, ses.numero_sesion, ses.titulo AS sesion_titulo, ses.fecha_sesion AS sesion_fecha
                                     FROM historial_operaciones h
                                     LEFT JOIN sesiones_mensuales ses ON h.id_sesion = ses.id_sesion
                                     WHERE h.id_socio = ?
                                      AND h.tipo_operacion IN ('aporte_obligatorio','aporte_excedente','interes_ganado')
                                     ORDER BY h.fecha_registro DESC");
        $stmt->execute([$idSocio]);
        $movimientos = $stmt->fetchAll();

        // Generate conceptos (solo tipos que afectan ahorro)
        $conceptos = [
            'aporte_obligatorio' => 'Aporte obligatorio',
            'aporte_excedente' => 'Aporte excedente',
            'retiro_ahorro' => 'Retiro de ahorro',
            'interes_ganado' => 'Interes ganado',
        ];

        // Calculate running balance from current balance working backwards
        $currentBalance = $saldoObligatorio + $saldoExcedente;
        $movs = [];
        $runningBalance = $currentBalance;
        foreach ($movimientos as $m) {
            $esDebito = in_array($m['tipo_operacion'], ['retiro_ahorro']);
            $monto = floatval($m['monto']);
            $m['saldo_posterior'] = $runningBalance;
            $runningBalance -= $esDebito ? -$monto : $monto;
            $m['saldo_anterior'] = $runningBalance;
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
        $stmt = $this->db->prepare("SELECT s.*, c.saldo_obligatorio, c.saldo_excedente FROM socios s LEFT JOIN cuentas_ahorro c ON s.id_socio = c.id_socio WHERE s.cedula = ?");
        $stmt->execute([$cedula]);
        $socio = $stmt->fetch();
        if (!$socio) $this->redirect('/portal');
        $idSocio = $socio['id_socio'];

        $capital = $this->db->prepare("SELECT * FROM capital_inversion WHERE id_socio = ?");
        $capital->execute([$idSocio]);
        $capitalRow = $capital->fetch();
        if (!$capitalRow) {
            $this->db->prepare("INSERT INTO capital_inversion (id_capital_inversion, id_socio) VALUES (?, ?)")
                ->execute([UUIDGenerator::generar(), $idSocio]);
            $capitalRow = ['saldo' => 0];
        }
        $saldoCapital = floatval($capitalRow['saldo'] ?? 0);

        $stmt = $this->db->prepare("SELECT i.*, p.nombre AS producto FROM inversiones i JOIN productos_financieros p ON i.id_producto = p.id_producto WHERE i.id_socio = ? ORDER BY i.fecha_registro DESC");
        $stmt->execute([$idSocio]);
        $inversiones = $stmt->fetchAll();

        $productos = $this->db->query("SELECT id_producto, nombre, tasa_interes_anual, metodo_interes, plazo_min_meses, plazo_max_meses, monto_min, monto_max, condiciones_html, min_permanencia_meses, min_ahorro, min_ahorro_unidad, requiere_garante, penalidad_retiro_anticipado FROM productos_financieros WHERE tipo = 'inversion' AND activo = TRUE ORDER BY nombre")->fetchAll();

        $errors = [];

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->validateCSRF();
            $idProducto = $_POST['id_producto'] ?? '';
            $monto = str_replace(',', '.', $_POST['monto'] ?? '0');
            $plazo = intval($_POST['plazo'] ?? 1);
            $destino = $_POST['destino_final'] ?? 'capital_inversion';

            if (!in_array($destino, ['capital_inversion', 'efectivo', 'transferencia'])) $destino = 'capital_inversion';
            if (empty($idProducto)) $errors['id_producto'] = 'Seleccione un producto';

            $prod = null;
            foreach ($productos as $p) {
                if ($p['id_producto'] === $idProducto) { $prod = $p; break; }
            }
            if (!$prod) $errors['id_producto'] = 'Producto invalido';
            if ($prod) {
                if (!is_numeric($monto) || $monto <= 0) $errors['monto'] = 'Monto invalido';
                elseif ($monto < floatval($prod['monto_min'] ?? 0)) $errors['monto'] = 'Monto minimo: $' . number_format($prod['monto_min'], 2);
                elseif ($monto > floatval($prod['monto_max'] ?? 0)) $errors['monto'] = 'Monto maximo: $' . number_format($prod['monto_max'], 2);
                if ($monto > $saldoCapital) $errors['monto'] = 'Saldo insuficiente en capital de inversion. Disponible: $' . number_format($saldoCapital, 2);
                if ($plazo < ($prod['plazo_min_meses'] ?? 1) || $plazo > ($prod['plazo_max_meses'] ?? 999)) $errors['plazo'] = 'Plazo fuera de rango';
            }

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
                        (id_inversion, id_socio, id_producto, monto, plazo_meses, tasa_interes, fecha_inicio, fecha_vencimiento, rendimiento_proyectado, destino_final, estado)
                        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'pendiente')")
                        ->execute([$id, $idSocio, $idProducto, $monto, $plazo, $tasa, $fechaInicio, $fechaVenc->format('Y-m-d'), round($rendimiento, 2), $destino]);

                    $this->historialInsert($idSocio, 'inversion_apertura', $monto, $id);
                    $this->db->commit();

                    $st = $this->db->prepare("SELECT CONCAT_WS(' ', apellido1, apellido2, nombre1, nombre2) AS nombre FROM socios WHERE id_socio = ?");
                    $st->execute([$idSocio]);
                    $nom = $st->fetchColumn();
                    try { require_once ROOT_PATH . '/app/helpers/NotificacionHelper.php'; NotificacionHelper::crearInversion($idSocio, $nom, $monto, 'solicitada'); } catch (Exception $e) {}
                    try { require_once ROOT_PATH . '/app/helpers/PusherHelper.php'; PusherHelper::actualizarPortal($idSocio); } catch (Exception $e) {}

                    $this->redirect('/portal/inversion?ok=1');
                } catch (Exception $e) {
                    $this->db->rollBack();
                    $errors['general'] = $e->getMessage();
                }
            }
        }

        $this->render('portal/inversion', [
            'titulo' => 'Inversion',
            'capital' => $capitalRow,
            'inversiones' => $inversiones,
            'productos' => $productos,
            'errors' => $errors,
            'saldoCapital' => $saldoCapital,
            'socio' => $socio,
        ]);
    }

    public function simularInversion() {
        $this->requireAuth();
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->json(['error' => 'Método no permitido'], 405);
        }
        $this->validateCSRF();

        $monto = str_replace(',', '.', $_POST['monto'] ?? '0');
        $tasa = str_replace(',', '.', $_POST['tasa'] ?? '0');
        $plazo = intval($_POST['plazo'] ?? 1);

        if (!is_numeric($monto) || $monto <= 0 || !is_numeric($tasa) || $plazo <= 0) {
            $this->json(['error' => 'Parámetros inválidos'], 400);
        }

        $factor = floatval($tasa) / 100 / 12;
        $rendimiento = floatval($monto) * $factor * $plazo;
        $total = floatval($monto) + $rendimiento;

        $this->json([
            'monto' => round(floatval($monto), 2),
            'tasa' => round(floatval($tasa), 2),
            'plazo' => $plazo,
            'rendimiento' => round($rendimiento, 2),
            'total' => round($total, 2),
            'tasa_mensual' => round($factor * 100, 4),
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
        if (!$capitalRow) {
            $this->db->prepare("INSERT INTO capital_inversion (id_capital_inversion, id_socio) VALUES (?, ?)")
                ->execute([UUIDGenerator::generar(), $idSocio]);
            $capitalRow = ['saldo' => 0, 'fecha_ultimo_movimiento' => null];
        }

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
