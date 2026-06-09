<?php
class NotificacionHelper {

    public static function crear($data) {
        $db = Database::getInstance();
        $id = UUIDGenerator::generar();
        $stmt = $db->prepare("INSERT INTO notificaciones
            (id_notificacion, id_usuario, id_socio, tipo, titulo, mensaje, enviada_pusher)
            VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([
            $id,
            $data['id_usuario'] ?? null,
            $data['id_socio'] ?? null,
            $data['tipo'],
            $data['titulo'],
            $data['mensaje'],
            !empty($data['enviar_pusher']) ? 1 : 0,
        ]);

        if (!empty($data['enviar_pusher'])) {
            self::enviarPusher($data);
        }
        return $id;
    }

    public static function crearCobro($idSocio, $socioNombre, $monto, $tipo) {
        return self::crear([
            'id_socio' => $idSocio,
            'tipo' => 'cobro',
            'titulo' => 'Cobro registrado',
            'mensaje' => "Cobro de $tipo por $$monto a $socioNombre",
            'enviar_pusher' => true,
        ]);
    }

    public static function crearCredito($socioNombre, $estado, $monto) {
        return self::crear([
            'tipo' => 'credito',
            'titulo' => "Crédito $estado",
            'mensaje' => "Crédito de $$monto para $socioNombre ha sido $estado",
            'enviar_pusher' => true,
        ]);
    }

    public static function crearSesion($numero, $accion) {
        return self::crear([
            'tipo' => 'sesión',
            'titulo' => "Sesión $accion",
            'mensaje' => "Sesión #$numero ha sido $accion",
            'enviar_pusher' => true,
        ]);
    }

    public static function crearInversion($idSocio, $socioNombre, $monto, $accion) {
        return self::crear([
            'id_socio' => $idSocio,
            'tipo' => 'inversion',
            'titulo' => "Inversion $accion",
            'mensaje' => "Inversion de $$monto para $socioNombre ha sido $accion",
            'enviar_pusher' => true,
        ]);
    }

    public static function crearDepositoCapital($idSocio, $socioNombre, $monto) {
        return self::crear([
            'id_socio' => $idSocio,
            'tipo' => 'inversion',
            'titulo' => 'Deposito a capital de inversion',
            'mensaje' => "Deposito de $$monto a capital de inversion de $socioNombre",
            'enviar_pusher' => true,
        ]);
    }

    public static function crearRetornoInversion($idSocio, $socioNombre, $monto, $destino) {
        return self::crear([
            'id_socio' => $idSocio,
            'tipo' => 'inversion',
            'titulo' => 'Retorno de inversion',
            'mensaje' => "Inversion de $socioNombre por $$monto ha vencido. Destino: $destino",
            'enviar_pusher' => true,
        ]);
    }

    private static function enviarPusher($data) {
        if (defined('PUSHER_APP_KEY') && PUSHER_APP_KEY) {
            try {
                require_once ROOT_PATH . '/app/helpers/PusherHelper.php';
                PusherHelper::enviar('notificacion', $data);
            } catch (Exception $e) {
                error_log("Pusher error: " . $e->getMessage());
            }
        }
    }
}
