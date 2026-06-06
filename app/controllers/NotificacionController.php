<?php
class NotificacionController extends BaseController {

    public function listar() {
        $this->requireAuth();
        $stmt = $this->db->prepare("SELECT n.*, CONCAT_WS(' ', s.apellido1, s.apellido2, s.nombre1, s.nombre2) AS socio_nombre
                                     FROM notificaciones n
                                     LEFT JOIN socios s ON n.id_socio = s.id_socio
                                     WHERE n.id_usuario = ? OR n.id_usuario IS NULL
                                     ORDER BY n.fecha_creación DESC LIMIT 50");
        $stmt->execute([$_SESSION['usuario_id']]);
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
}
