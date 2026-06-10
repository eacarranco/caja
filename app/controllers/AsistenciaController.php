<?php
class AsistenciaController extends BaseController {

    public function justificar($id) {
        $this->requireAuth();
        $stmt = $this->db->prepare("SELECT a.*, s.cedula FROM asistencias a JOIN socios s ON a.id_socio = s.id_socio WHERE a.id_asistencia = ?");
        $stmt->execute([$id]);
        $asis = $stmt->fetch();
        if (!$asis) $this->json(['error' => 'No encontrada'], 404);
        if ($asis['cedula'] !== ($_SESSION['usuario_cedula'] ?? '')) $this->json(['error' => 'No autorizado'], 403);

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') $this->json(['error' => 'Método no permitido'], 405);
        $this->validateCSRF();
        $texto = trim($_POST['justificacion'] ?? '');
        if (empty($texto)) $this->json(['error' => 'Escriba una justificacion'], 400);

        $archivo = null;
        if (!empty($_FILES['archivo']) && $_FILES['archivo']['error'] === UPLOAD_ERR_OK) {
            $ext = strtolower(pathinfo($_FILES['archivo']['name'], PATHINFO_EXTENSION));
            if (!in_array($ext, ['pdf', 'jpg', 'jpeg', 'png'])) $this->json(['error' => 'Solo PDF, JPG o PNG'], 400);
            $nombre = 'justif_asistencia_' . substr($id, 0, 8) . '.' . $ext;
            move_uploaded_file($_FILES['archivo']['tmp_name'], ROOT_PATH . '/storage/documentos/' . $nombre);
            $archivo = $nombre;
        }

        if ($archivo) {
            $this->db->prepare("UPDATE asistencias SET justificacion = ?, justificacion_pdf = ? WHERE id_asistencia = ?")->execute([$texto, $archivo, $id]);
        } else {
            $this->db->prepare("UPDATE asistencias SET justificacion = ? WHERE id_asistencia = ?")->execute([$texto, $id]);
        }
        $this->json(['mensaje' => 'Justificación enviada']);
    }

    public function aprobarJustificacion($id) {
        $this->requirePermission('socio.cambiar_estado');
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') $this->json(['error' => 'Método no permitido'], 405);
        $this->validateCSRF();
        $aprobada = ($_POST['accion'] ?? '') === 'aprobar' ? 1 : 0;
        $this->db->prepare("UPDATE asistencias SET justificacion_aprobada = ? WHERE id_asistencia = ?")->execute([$aprobada, $id]);
        $this->json(['mensaje' => $aprobada ? 'Justificación aprobada' : 'Justificación rechazada']);
    }

    public function listar() {
        $this->requirePermission('cobro.aporte');
        $page = max(1, intval($_GET['p'] ?? 1));
        $porPagina = 30;
        $offset = ($page - 1) * $porPagina;

        $filtroSocio = $_GET['socio'] ?? '';
        $filtroTipo = $_GET['tipo'] ?? '';
        $filtroSesion = $_GET['sesion'] ?? '';

        $where = [];
        $params = [];
        if ($filtroSocio) { $where[] = 's.apellido1 LIKE ?'; $params[] = "%$filtroSocio%"; }
        if ($filtroTipo) { $where[] = 'a.tipo = ?'; $params[] = $filtroTipo; }
        if ($filtroSesion) { $where[] = 'a.id_sesion = ?'; $params[] = $filtroSesion; }
        $whereClause = $where ? 'WHERE ' . implode(' AND ', $where) : '';

        $stmt = $this->db->prepare("SELECT a.*, CONCAT_WS(' ', s.apellido1, s.apellido2, s.nombre1, s.nombre2) AS socio,
                                     s.cedula, ses.numero_sesion, ses.fecha_sesion
                                     FROM asistencias a
                                     JOIN socios s ON a.id_socio = s.id_socio
                                     JOIN sesiones_mensuales ses ON a.id_sesion = ses.id_sesion
                                     $whereClause
                                     ORDER BY a.fecha_registro DESC LIMIT $porPagina OFFSET $offset");
        $stmt->execute($params);
        $asistencias = $stmt->fetchAll();

        $total = $this->db->prepare("SELECT COUNT(*) FROM asistencias a JOIN socios s ON a.id_socio = s.id_socio $whereClause");
        $total->execute($params);
        $totalPaginas = ceil($total->fetchColumn() / $porPagina);

        $sesiones = $this->db->query("SELECT id_sesion, numero_sesion, fecha FROM sesiones_mensuales ORDER BY fecha DESC LIMIT 20")->fetchAll();

        $this->render('asistencias/listar', [
            'titulo' => 'Asistencias',
            'asistencias' => $asistencias,
            'page' => $page,
            'totalPaginas' => $totalPaginas,
            'filtroSocio' => $filtroSocio,
            'filtroTipo' => $filtroTipo,
            'filtroSesion' => $filtroSesion,
            'sesiones' => $sesiones,
        ]);
    }
}
