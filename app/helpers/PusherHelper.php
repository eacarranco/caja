<?php
class PusherHelper {

    public static function enviar($evento, $data) {
        if (empty(PUSHER_APP_KEY)) return false;
        try {
            $canal = 'canal-general';
            $body = json_encode([
                'name' => $evento,
                'channel' => $canal,
                'data' => json_encode($data),
            ]);

            $timestamp = time();
            $bodyMd5 = md5($body);
            $path = '/apps/' . PUSHER_APP_ID . '/events';

            $queryParams = [
                'auth_key' => PUSHER_APP_KEY,
                'auth_timestamp' => $timestamp,
                'auth_version' => '1.0',
                'body_md5' => $bodyMd5,
            ];
            ksort($queryParams);
            $queryString = http_build_query($queryParams);

            $signatureString = "POST\n$path\n$queryString";
            $authSignature = hash_hmac('sha256', $signatureString, PUSHER_APP_SECRET);

            $url = 'https://api-' . PUSHER_APP_CLUSTER . '.pusher.com' . $path . '?' . $queryString . '&auth_signature=' . $authSignature;

            $ch = curl_init($url);
            curl_setopt_array($ch, [
                CURLOPT_POST => true,
                CURLOPT_POSTFIELDS => $body,
                CURLOPT_HTTPHEADER => ['Content-Type: application/json'],
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_TIMEOUT => 5,
                CURLOPT_SSL_VERIFYPEER => false,
            ]);
            $resp = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);

            if ($httpCode !== 200) {
                error_log("Pusher error HTTP $httpCode: $resp");
            }
            return $httpCode === 200;
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

    public static function actualizarPortal($socioId) {
        if (empty(PUSHER_APP_KEY)) return;
        try {
            $db = Database::getInstance()->getConnection();
            $stmt = $db->prepare("SELECT saldo_obligatorio, saldo_excedente FROM cuentas_ahorro WHERE id_socio = ?");
            $stmt->execute([$socioId]);
            $cuenta = $stmt->fetch(PDO::FETCH_ASSOC);

            $stmt = $db->prepare("SELECT COALESCE(saldo, 0) FROM capital_inversion WHERE id_socio = ?");
            $stmt->execute([$socioId]);
            $capitalInv = floatval($stmt->fetchColumn());

            $stmt = $db->prepare("SELECT COALESCE(SUM(monto), 0) FROM obligaciones_sesion WHERE id_socio = ? AND pagada = FALSE");
            $stmt->execute([$socioId]);
            $valoresPagar = floatval($stmt->fetchColumn());

            $data = [
                'id_socio' => $socioId,
                'saldo_obligatorio' => floatval($cuenta['saldo_obligatorio'] ?? 0),
                'saldo_excedente' => floatval($cuenta['saldo_excedente'] ?? 0),
                'ahorro_total' => floatval($cuenta['saldo_obligatorio'] ?? 0) + floatval($cuenta['saldo_excedente'] ?? 0),
                'capital_inversion' => $capitalInv,
                'valores_pagar' => $valoresPagar,
            ];
            self::enviar('actualizar-portal', $data);
        } catch (Exception $e) {
            error_log("Pusher actualizarPortal error: " . $e->getMessage());
        }
    }

    public static function notificarUsuario($usuarioId, $titulo, $mensaje, $url = '') {
        self::persistirNotificacion($usuarioId, null, $titulo, $mensaje);
    }

    private static function persistirNotificacion($usuarioId, $socioId, $titulo, $mensaje) {
        $db = Database::getInstance();
        $stmt = $db->prepare("INSERT INTO notificaciones (id_notificacion, id_usuario, id_socio, tipo, titulo, mensaje, enviada_pusher)
                               VALUES (?, ?, ?, 'sistema', ?, ?, ?)");
        $stmt->execute([UUIDGenerator::generate(), $usuarioId, $socioId, $titulo, $mensaje, defined('PUSHER_APP_KEY') && PUSHER_APP_KEY ? 1 : 0]);
    }
}