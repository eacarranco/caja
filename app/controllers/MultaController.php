<?php
class MultaController extends BaseController {

    public function listar() {
        $this->requireAuth();
        $cedula = $_SESSION['usuario_cedula'] ?? '';
        $idSocio = null;
        $esSocio = false;
        if ($cedula) {
            $stmt = $this->db->prepare("SELECT id_socio FROM socios WHERE cedula = ?");
            $stmt->execute([$cedula]);
            $idSocio = $stmt->fetchColumn();
            if ($idSocio) $esSocio = true;
        }

        $page = max(1, intval($_GET['p'] ?? 1));
        $porPagina = 20;
        $offset = ($page - 1) * $porPagina;

        if ($esSocio) {
            $stmt = $this->db->prepare("SELECT m.*, CONCAT_WS(' ', s.apellido1, s.apellido2, s.nombre1, s.nombre2) AS socio, s.cedula,
                                        (SELECT COUNT(*) FROM obligaciones_sesion WHERE id_referencia = m.id_multa AND tipo = 'multa' AND pagada = TRUE) AS pagada
                                        FROM multas m
                                        JOIN socios s ON m.id_socio = s.id_socio
                                        WHERE m.id_socio = ?
                                        ORDER BY m.fecha_generacion DESC LIMIT $porPagina OFFSET $offset");
            $stmt->execute([$idSocio]);
            $multas = $stmt->fetchAll();
            $total = $this->db->prepare("SELECT COUNT(*) FROM multas WHERE id_socio = ?");
            $total->execute([$idSocio]);
            $totalMultas = $total->fetchColumn();
            $totalPaginas = ceil($totalMultas / $porPagina);
            $filtroTipo = $filtroSocio = '';
        } else {
            $filtroTipo = $_GET['tipo'] ?? '';
            $filtroSocio = $_GET['socio'] ?? '';
            $where = [];
            $params = [];
            if ($filtroTipo) { $where[] = 'm.tipo = ?'; $params[] = $filtroTipo; }
            if ($filtroSocio) { $where[] = 's.apellido1 LIKE ?'; $params[] = "%$filtroSocio%"; }
            $whereClause = $where ? 'WHERE ' . implode(' AND ', $where) : '';
            $stmt = $this->db->prepare("SELECT m.*, CONCAT_WS(' ', s.apellido1, s.apellido2, s.nombre1, s.nombre2) AS socio, s.cedula,
                                        (SELECT COUNT(*) FROM obligaciones_sesion WHERE id_referencia = m.id_multa AND tipo = 'multa' AND pagada = TRUE) AS pagada
                                        FROM multas m
                                        JOIN socios s ON m.id_socio = s.id_socio
                                        $whereClause
                                        ORDER BY m.fecha_generacion DESC LIMIT $porPagina OFFSET $offset");
            $stmt->execute($params);
            $multas = $stmt->fetchAll();
            $total = $this->db->prepare("SELECT COUNT(*) FROM multas m JOIN socios s ON m.id_socio = s.id_socio $whereClause");
            $total->execute($params);
            $totalMultas = $total->fetchColumn();
            $totalPaginas = ceil($totalMultas / $porPagina);
        }

        $this->render('multas/listar', [
            'titulo' => 'Multas',
            'multas' => $multas,
            'page' => $page,
            'totalPaginas' => $totalPaginas,
            'filtroTipo' => $filtroTipo ?? '',
            'filtroSocio' => $filtroSocio ?? '',
            'esSocio' => $esSocio,
            'esPresidente' => $this->esPresidente(),
        ]);
    }

    private function esPresidente() {
        $roles = RBAC::obtenerRolesUsuario($_SESSION['usuario_id']);
        foreach ($roles as $r) {
            if ($r['nombre'] === 'Presidente') return true;
        }
        return false;
    }

    private function notificarRolesConPermiso($codigoPermiso, $titulo, $mensaje) {
        $rows = $this->db->query("
            SELECT DISTINCT u.id_usuario FROM usuarios u
            JOIN roles_usuarios ru ON u.id_usuario = ru.id_usuario
            LEFT JOIN roles r ON ru.id_rol = r.id_rol
            WHERE r.endosable = 1
            OR ru.id_rol IN (
                SELECT rp.id_rol FROM roles_permisos rp
                JOIN permisos p ON rp.id_permiso = p.id_permiso
                WHERE p.codigo = '$codigoPermiso' AND rp.permitir = 1
            )
        ")->fetchAll(PDO::FETCH_COLUMN);
        foreach ($rows as $uid) {
            try {
                require_once ROOT_PATH . '/app/helpers/NotificacionHelper.php';
                NotificacionHelper::crear([
                    'id_usuario' => $uid,
                    'tipo' => 'multa',
                    'titulo' => $titulo,
                    'mensaje' => $mensaje,
                    'enviar_pusher' => true,
                ]);
            } catch (Exception $e) {}
        }
    }

    public function ver($id) {
        $this->requireAuth();
        $stmt = $this->db->prepare("SELECT m.*, CONCAT_WS(' ', s.apellido1, s.apellido2, s.nombre1, s.nombre2) AS socio,
                                     s.cedula, s.correo_electronico
                                     FROM multas m
                                     JOIN socios s ON m.id_socio = s.id_socio
                                     WHERE m.id_multa = ?");
        $stmt->execute([$id]);
        $multa = $stmt->fetch();
        if (!$multa) $this->redirect('/multa/listar');

        $stmt = $this->db->prepare("SELECT COUNT(*) FROM obligaciones_sesion WHERE id_referencia = ? AND tipo = 'multa' AND pagada = TRUE");
        $stmt->execute([$id]);
        $pagada = $stmt->fetchColumn() > 0;

        $puedeAutorizar = $this->tienePermiso('multa.autorizar_impugnacion');

        $this->render('multas/ver', [
            'titulo' => 'Multa',
            'multa' => $multa,
            'pagada' => $pagada,
            'esPresidente' => $this->esPresidente(),
            'puedeAutorizar' => $puedeAutorizar,
        ]);
    }

    private function tienePermiso($codigo) {
        $uid = $_SESSION['usuario_id'] ?? '';
        if (!$uid) return false;
        $roles = RBAC::obtenerRolesUsuario($uid);
        foreach ($roles as $r) {
            if (!empty($r['endosable'])) return true;
        }
        $stmt = $this->db->prepare("SELECT COUNT(*) FROM roles_permisos rp
            JOIN permisos p ON rp.id_permiso = p.id_permiso
            JOIN roles_usuarios ru ON rp.id_rol = ru.id_rol
            WHERE ru.id_usuario = ? AND p.codigo = ? AND rp.permitir = 1");
        $stmt->execute([$uid, $codigo]);
        return $stmt->fetchColumn() > 0;
    }

    public function justificar($id) {
        $this->requireAuth();
        $stmt = $this->db->prepare("SELECT m.*, s.cedula FROM multas m JOIN socios s ON m.id_socio = s.id_socio WHERE m.id_multa = ?");
        $stmt->execute([$id]);
        $multa = $stmt->fetch();
        if (!$multa) $this->json(['error' => 'No encontrada'], 404);
        if ($multa['cedula'] !== ($_SESSION['usuario_cedula'] ?? '')) {
            $this->json(['error' => 'No autorizado'], 403);
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->validateCSRF();
            $texto = trim($_POST['justificacion'] ?? '');
            $archivo = null;

            if (empty($texto)) $this->json(['error' => 'Escriba una justificacion'], 400);

            if (!empty($_FILES['archivo']) && $_FILES['archivo']['error'] === UPLOAD_ERR_OK) {
                $ext = strtolower(pathinfo($_FILES['archivo']['name'], PATHINFO_EXTENSION));
                if (!in_array($ext, ['pdf', 'jpg', 'jpeg', 'png'])) {
                    $this->json(['error' => 'Solo PDF, JPG o PNG'], 400);
                }
                $nombre = 'justificacion_' . substr($id, 0, 8) . '.' . $ext;
                $destino = ROOT_PATH . '/storage/documentos/' . $nombre;
                move_uploaded_file($_FILES['archivo']['tmp_name'], $destino);
                $archivo = $nombre;
            }

            if ($archivo) {
                $this->db->prepare("UPDATE multas SET justificacion = ?, justificacion_pdf = ? WHERE id_multa = ?")
                    ->execute([$texto, $archivo, $id]);
            } else {
                $this->db->prepare("UPDATE multas SET justificacion = ? WHERE id_multa = ?")
                    ->execute([$texto, $id]);
            }

            $this->json(['mensaje' => 'Justificación enviada']);
        }
    }

    public function aprobarJustificacion($id) {
        $this->requirePermission('multa.autorizar_impugnacion');
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') $this->json(['error' => 'Método no permitido'], 405);
        $this->validateCSRF();

        $stmt = $this->db->prepare("SELECT m.*, s.cedula FROM multas m JOIN socios s ON m.id_socio = s.id_socio WHERE m.id_multa = ?");
        $stmt->execute([$id]);
        $multa = $stmt->fetch();
        if (!$multa) $this->json(['error' => 'No encontrada'], 404);
        if (empty($multa['justificacion'])) $this->json(['error' => 'Esta multa no tiene justificacion pendiente'], 400);

        $accion = $_POST['accion'] ?? '';

        try {
            require_once ROOT_PATH . '/app/helpers/NotificacionHelper.php';
            require_once ROOT_PATH . '/app/helpers/PusherHelper.php';
        } catch (Exception $e) {}

        if ($accion === 'aprobar') {
            $this->db->beginTransaction();
            try {
                $this->db->prepare("UPDATE multas SET estado = 'impugnada', justificacion_aprobada = 1 WHERE id_multa = ?")
                    ->execute([$id]);
                $this->db->prepare("UPDATE obligaciones_sesion SET pagada = TRUE WHERE id_referencia = ? AND tipo = 'multa' AND pagada = FALSE")
                    ->execute([$id]);
                $this->db->commit();

                NotificacionHelper::crear([
                    'id_socio' => $multa['id_socio'],
                    'tipo' => 'multa',
                    'titulo' => 'Impugnacion aprobada',
                    'mensaje' => 'Su impugnacion ha sido aprobada. La multa queda sin efecto.',
                    'enviar_pusher' => true,
                ]);
                PusherHelper::actualizarPortal($multa['id_socio']);
                $this->json(['mensaje' => 'Impugnación aprobada, multa sin efecto']);
            } catch (Exception $e) {
                $this->db->rollBack();
                $this->json(['error' => $e->getMessage()], 500);
            }
        } else {
            $this->db->prepare("UPDATE multas SET justificacion_aprobada = 0 WHERE id_multa = ?")
                ->execute([$id]);

            NotificacionHelper::crear([
                'id_socio' => $multa['id_socio'],
                'tipo' => 'multa',
                'titulo' => 'Impugnacion rechazada',
                'mensaje' => 'Su impugnacion ha sido rechazada. La multa sigue vigente.',
                'enviar_pusher' => true,
            ]);
            PusherHelper::actualizarPortal($multa['id_socio']);
            $this->json(['mensaje' => 'Impugnación rechazada, multa vigente']);
        }
    }

    public function impugnar($id) {
        $this->requirePermission('multa.impugnar');
        $stmt = $this->db->prepare("SELECT m.*, s.cedula FROM multas m JOIN socios s ON m.id_socio = s.id_socio WHERE m.id_multa = ?");
        $stmt->execute([$id]);
        $multa = $stmt->fetch();
        if (!$multa) $this->json(['error' => 'No encontrada'], 404);

        $stmtPag = $this->db->prepare("SELECT COUNT(*) FROM obligaciones_sesion WHERE id_referencia = ? AND tipo = 'multa' AND pagada = TRUE");
        $stmtPag->execute([$id]);
        if ($stmtPag->fetchColumn() > 0) $this->json(['error' => 'No se puede impugnar una multa ya pagada'], 400);
        if ($multa['estado'] === 'impugnada') $this->json(['error' => 'Ya fue impugnada'], 400);
        if ($multa['estado'] === 'anulada') $this->json(['error' => 'La multa fue anulada por un directivo'], 400);

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->validateCSRF();
            $texto = trim($_POST['justificacion'] ?? '');
            if (empty($texto)) $this->json(['error' => 'Escriba una justificacion'], 400);

            $archivo = null;
            if (!empty($_FILES['archivo']) && $_FILES['archivo']['error'] === UPLOAD_ERR_OK) {
                $ext = strtolower(pathinfo($_FILES['archivo']['name'], PATHINFO_EXTENSION));
                if (!in_array($ext, ['pdf', 'jpg', 'jpeg', 'png'])) {
                    $this->json(['error' => 'Solo PDF, JPG o PNG'], 400);
                }
                $nombre = 'impugnacion_' . substr($id, 0, 8) . '.' . $ext;
                move_uploaded_file($_FILES['archivo']['tmp_name'], ROOT_PATH . '/storage/documentos/' . $nombre);
                $archivo = $nombre;
            }

            if ($archivo) {
                $this->db->prepare("UPDATE multas SET justificacion = ?, justificacion_pdf = ? WHERE id_multa = ?")
                    ->execute([$texto, $archivo, $id]);
            } else {
                $this->db->prepare("UPDATE multas SET justificacion = ? WHERE id_multa = ?")
                    ->execute([$texto, $id]);
            }

            $socioNombre = trim($multa['cedula']);
            $this->notificarRolesConPermiso(
                'multa.impugnar',
                'Impugnacion de multa',
                "El socio {$socioNombre} ha impugnado una multa. Revise la justificacion en el modulo de multas."
            );

            $this->json(['mensaje' => 'Impugnacion enviada para revision']);
        }
    }

    public function eliminar($id) {
        $this->requireAuth();
        $roles = RBAC::obtenerRolesUsuario($_SESSION['usuario_id']);
        $esPresidente = false;
        foreach ($roles as $r) {
            if ($r['nombre'] === 'Presidente') { $esPresidente = true; break; }
        }
        if (!$esPresidente) $this->json(['error' => 'Solo el Presidente puede eliminar multas'], 403);

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') $this->json(['error' => 'Metodo no permitido'], 405);
        $this->validateCSRF();

        $stmt = $this->db->prepare("SELECT id_multa FROM multas WHERE id_multa = ?");
        $stmt->execute([$id]);
        if (!$stmt->fetch()) $this->json(['error' => 'No encontrada'], 404);

        $stmtPag = $this->db->prepare("SELECT COUNT(*) FROM obligaciones_sesion WHERE id_referencia = ? AND tipo = 'multa' AND pagada = TRUE");
        $stmtPag->execute([$id]);
        if ($stmtPag->fetchColumn() > 0) $this->json(['error' => 'No se puede eliminar una multa con pagos asociados'], 400);

        $this->db->prepare("DELETE FROM multas WHERE id_multa = ?")->execute([$id]);
        $this->json(['mensaje' => 'Multa eliminada']);
    }
}
