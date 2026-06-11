<?php
class NotificacionController extends BaseController {

    public function listar() {
        $this->requireAuth();
        $buzon = $_GET['buzon'] ?? 'entrada';
        if (!in_array($buzon, ['entrada', 'archivadas', 'papelera'])) $buzon = 'entrada';

        // Limpiar papelera vencida
        $diasRetencion = intval($this->db->query("SELECT valor FROM parametros WHERE codigo = 'retencion_papelera_dias'")->fetchColumn() ?: 30);
        $fechaLimite = date('Y-m-d H:i:s', strtotime("-{$diasRetencion} days"));
        $this->db->prepare("DELETE FROM notificaciones WHERE buzon = 'papelera' AND fecha_eliminacion IS NOT NULL AND fecha_eliminacion <= ?")->execute([$fechaLimite]);

        $cedula = $_SESSION['usuario_cedula'] ?? '';
        $idSocio = null;
        if ($cedula) {
            $stmt = $this->db->prepare("SELECT id_socio FROM socios WHERE cedula = ?");
            $stmt->execute([$cedula]);
            $idSocio = $stmt->fetchColumn();
        }

        $stmt = $this->db->prepare("SELECT n.*, CONCAT_WS(' ', s.apellido1, s.apellido2, s.nombre1, s.nombre2) AS socio_nombre
                                     FROM notificaciones n
                                     LEFT JOIN socios s ON n.id_socio = s.id_socio
                                     WHERE (n.id_usuario = ? OR n.id_usuario IS NULL OR n.id_socio = ?)
                                     AND n.buzon = ?
                                     ORDER BY n.fecha_creacion DESC LIMIT 50");
        $stmt->execute([$_SESSION['usuario_id'], $idSocio, $buzon]);
        $notificaciones = $stmt->fetchAll();

        // Contar por buzon
        $conteos = [];
        foreach (['entrada', 'archivadas', 'papelera'] as $b) {
            $stmt = $this->db->prepare("SELECT COUNT(*) FROM notificaciones WHERE (id_usuario = ? OR id_usuario IS NULL OR id_socio = ?) AND buzon = ? AND leida = FALSE");
            $stmt->execute([$_SESSION['usuario_id'], $idSocio, $b]);
            $conteos[$b] = (int)$stmt->fetchColumn();
        }

        $this->render('notificaciones/listar', [
            'titulo' => 'Notificaciones',
            'notificaciones' => $notificaciones,
            'buzonActual' => $buzon,
            'conteos' => $conteos,
            'retencionDias' => $diasRetencion,
        ]);
    }

    public function leer($id) {
        $this->requireAuth();
        $no = isset($_GET['no']);
        $owner = $this->getOwnerFilter();
        if ($no) {
            $this->db->prepare("UPDATE notificaciones SET leida = FALSE, fecha_lectura = NULL WHERE id_notificacion = ? AND {$owner['sql']}")->execute(array_merge([$id], $owner['params']));
            $this->json(['mensaje' => 'Marcada como no leida']);
        } else {
            $this->db->prepare("UPDATE notificaciones SET leida = TRUE, fecha_lectura = NOW() WHERE id_notificacion = ? AND {$owner['sql']}")->execute(array_merge([$id], $owner['params']));
            $this->json(['mensaje' => 'Marcada como leida']);
        }
    }

    public function leerTodas() {
        $this->requireAuth();
        $buzon = $_POST['buzon'] ?? 'entrada';
        $this->db->prepare("UPDATE notificaciones SET leida = TRUE, fecha_lectura = NOW() WHERE (id_usuario = ? OR id_usuario IS NULL) AND buzon = ? AND leida = FALSE")->execute([$_SESSION['usuario_id'], $buzon]);
        $this->json(['mensaje' => 'Todas marcadas como leidas']);
    }

    public function contar() {
        $this->requireAuth();
        $cedula = $_SESSION['usuario_cedula'] ?? '';
        $idSocio = null;
        if ($cedula) {
            $stmt = $this->db->prepare("SELECT id_socio FROM socios WHERE cedula = ?");
            $stmt->execute([$cedula]);
            $idSocio = $stmt->fetchColumn();
        }

        $entrada = 0;
        $stmt = $this->db->prepare("SELECT COUNT(*) FROM notificaciones WHERE (id_usuario = ? OR id_usuario IS NULL OR id_socio = ?) AND buzon = 'entrada' AND leida = FALSE");
        $stmt->execute([$_SESSION['usuario_id'], $idSocio]);
        $entrada += (int)$stmt->fetchColumn();

        $this->json(['pendientes' => $entrada]);
    }

    private function getOwnerFilter() {
        $cedula = $_SESSION['usuario_cedula'] ?? '';
        $idSocio = null;
        if ($cedula) {
            $stmt = $this->db->prepare("SELECT id_socio FROM socios WHERE cedula = ?");
            $stmt->execute([$cedula]);
            $idSocio = $stmt->fetchColumn();
        }
        $conds = ['id_usuario = ?', 'id_usuario IS NULL'];
        $params = [$_SESSION['usuario_id']];
        if ($idSocio) {
            $conds[] = 'id_socio = ?';
            $params[] = $idSocio;
        }
        return ['sql' => '(' . implode(' OR ', $conds) . ')', 'params' => $params];
    }

    public function archivar($id) {
        $this->requireAuth();
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') $this->json(['error' => 'Metodo no permitido'], 405);
        $this->validateCSRF();
        $owner = $this->getOwnerFilter();
        $this->db->prepare("UPDATE notificaciones SET buzon = 'archivadas' WHERE id_notificacion = ? AND {$owner['sql']}")->execute(array_merge([$id], $owner['params']));
        $this->json(['mensaje' => 'Archivada']);
    }

    public function eliminar($id) {
        $this->requireAuth();
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') $this->json(['error' => 'Metodo no permitido'], 405);
        $this->validateCSRF();
        $owner = $this->getOwnerFilter();
        $this->db->prepare("UPDATE notificaciones SET buzon = 'papelera', fecha_eliminacion = NOW() WHERE id_notificacion = ? AND {$owner['sql']}")->execute(array_merge([$id], $owner['params']));
        $this->json(['mensaje' => 'Movida a papelera']);
    }

    public function restaurar($id) {
        $this->requireAuth();
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') $this->json(['error' => 'Metodo no permitido'], 405);
        $this->validateCSRF();
        $owner = $this->getOwnerFilter();
        $this->db->prepare("UPDATE notificaciones SET buzon = 'entrada', fecha_eliminacion = NULL WHERE id_notificacion = ? AND {$owner['sql']}")->execute(array_merge([$id], $owner['params']));
        $this->json(['mensaje' => 'Restaurada a entrada']);
    }

    public function destruir($id) {
        $this->requireAuth();
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') $this->json(['error' => 'Metodo no permitido'], 405);
        $this->validateCSRF();
        $owner = $this->getOwnerFilter();
        $this->db->prepare("DELETE FROM notificaciones WHERE id_notificacion = ? AND {$owner['sql']}")->execute(array_merge([$id], $owner['params']));
        $this->json(['mensaje' => 'Eliminada definitivamente']);
    }

    public function vaciarPapelera() {
        $this->requireAuth();
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') $this->json(['error' => 'Metodo no permitido'], 405);
        $this->validateCSRF();
        $owner = $this->getOwnerFilter();
        $this->db->prepare("DELETE FROM notificaciones WHERE buzon = 'papelera' AND {$owner['sql']}")->execute($owner['params']);
        $this->json(['mensaje' => 'Papelera vaciada']);
    }
}
