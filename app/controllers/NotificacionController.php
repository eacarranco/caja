<?php
class NotificacionController extends BaseController {

    public function listar() {
        $this->requireAuth();
        $cedula = $_SESSION['usuario_cedula'] ?? '';
        $idSocio = null;
        if ($cedula) {
            $stmt = $this->db->prepare("SELECT id_socio FROM socios WHERE cédula = ?");
            $stmt->execute([$cedula]);
            $idSocio = $stmt->fetchColumn();
        }
        $stmt = $this->db->prepare("SELECT n.*, CONCAT_WS(' ', s.apellido1, s.apellido2, s.nombre1, s.nombre2) AS socio_nombre
                                     FROM notificaciones n
                                     LEFT JOIN socios s ON n.id_socio = s.id_socio
                                     WHERE n.id_usuario = ? OR n.id_usuario IS NULL OR n.id_socio = ?
                                     ORDER BY n.fecha_creación DESC LIMIT 50");
        $stmt->execute([$_SESSION['usuario_id'], $idSocio]);
        $notificaciones = $stmt->fetchAll();
        $this->render('notificaciones/listar', [
            'titulo' => 'Notificaciones',
            'notificaciones' => $notificaciones,
        ]);
    }

    public function leer($id) {
        $this->requireAuth();
        $this->db->prepare("UPDATE notificaciones SET leída = TRUE, fecha_lectura = NOW() WHERE id_notificación = ?")->execute([$id]);
        $this->json(['mensaje' => 'Marcada como leída']);
    }

    public function leerTodas() {
        $this->requireAuth();
        $this->db->prepare("UPDATE notificaciones SET leída = TRUE, fecha_lectura = NOW() WHERE (id_usuario = ? OR id_usuario IS NULL) AND leída = FALSE")->execute([$_SESSION['usuario_id']]);
        $this->json(['mensaje' => 'Todas marcadas como leídas']);
    }

    public function contar() {
        $this->requireAuth();
        $count = 0;

        $stmt = $this->db->prepare("SELECT COUNT(*) FROM notificaciones WHERE (id_usuario = ? OR (id_usuario IS NULL AND id_socio IS NULL)) AND leída = FALSE");
        $stmt->execute([$_SESSION['usuario_id']]);
        $count += (int)$stmt->fetchColumn();

        $cedula = $_SESSION['usuario_cedula'] ?? '';
        if ($cedula) {
            $stmt = $this->db->prepare("SELECT id_socio FROM socios WHERE cédula = ?");
            $stmt->execute([$cedula]);
            $idSocio = $stmt->fetchColumn();
            if ($idSocio) {
                $stmt = $this->db->prepare("SELECT COUNT(*) FROM notificaciones WHERE id_socio = ? AND leída = FALSE");
                $stmt->execute([$idSocio]);
                $count += (int)$stmt->fetchColumn();
            }
        }

        $this->json(['pendientes' => $count]);
    }
}
