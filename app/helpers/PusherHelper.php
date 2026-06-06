<?php
class PusherHelper {

    public static function enviar($evento, $data) {
        if (empty(PUSHER_APP_KEY)) return false;
        try {
            $canal = 'canal-general';
            $payload = json_encode([
                'evento' => $evento,
                'data' => $data,
                'time' => time(),
            ]);
            $url = "https://api-" . PUSHER_APP_CLUSTER . ".pusher.com/apps/" . PUSHER_APP_ID . "/events";
            $body = json_encode([
                'name' => $evento,
                'channel' => $canal,
                'data' => json_encode($data),
            ]);
            $ch = curl_init($url);
            curl_setopt_array($ch, [
                CURLOPT_POST => true,
                CURLOPT_POSTFIELDS => $body,
                CURLOPT_HTTPHEADER => ['Content-Type: application/json'],
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_TIMEOUT => 5,
            ]);
            curl_exec($ch);
            curl_close($ch);
            return true;
        } catch (Exception $e) {
            error_log("Pusher error: " . $e->getMessage());
            return false;
        }
    }

    public static function notificar($canal, $evento, $data) {
        return self::enviar($evento, $data);
    }

    public static function notificarSocio($socioId, $titulo, $mensaje, $url = '') {
        self::persistirNotificacion(null, $socioId, $titulo, $mensaje);
    }

    public static function notificarUsuario($usuarioId, $titulo, $mensaje, $url = '') {
        self::persistirNotificacion($usuarioId, null, $titulo, $mensaje);
    }

    private static function persistirNotificacion($usuarioId, $socioId, $titulo, $mensaje) {
        $db = Database::getInstance();
        $stmt = $db->prepare("INSERT INTO notificaciones (id_notificación, id_usuario, id_socio, tipo, título, mensaje, enviada_pusher)
                               VALUES (?, ?, ?, 'sistema', ?, ?, ?)");
        $stmt->execute([UUIDGenerator::generate(), $usuarioId, $socioId, $titulo, $mensaje, defined('PUSHER_APP_KEY') && PUSHER_APP_KEY ? 1 : 0]);
    }
}
