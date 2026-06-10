<?php
require_once ROOT_PATH . '/vendor/autoload.php';
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

class EmailHelper {
    private static $config = null;

    private static function getConfig() {
        if (self::$config === null) {
            self::$config = require ROOT_PATH . '/config/email.php';
        }
        return self::$config;
    }

    public static function enviarPIN($email, $nombre, $pin) {
        $config = self::getConfig();
        $mailer = new PHPMailer(true);
        try {
            $mailer->isSMTP();
            $mailer->Host = $config['smtp']['host'];
            $mailer->SMTPAuth = true;
            $mailer->Username = $config['smtp']['username'];
            $mailer->Password = $config['smtp']['password'];
            $mailer->SMTPSecure = $config['smtp']['encryption'];
            $mailer->Port = $config['smtp']['port'];
            $mailer->CharSet = 'UTF-8';
            $mailer->SMTPOptions = ['ssl' => ['verify_peer' => false, 'verify_peer_name' => false, 'allow_self_signed' => true]];

            $mailer->setFrom($config['smtp']['from_email'], $config['smtp']['from_name']);
            $mailer->addAddress($email, $nombre);

            $mailer->isHTML(true);
            $mailer->Subject = 'Código de verificación - ' . APP_NAME;
            $mailer->Body = self::pinTemplate($nombre, $pin);
            $mailer->AltBody = "Hola $nombre,\n\nTu codigo PIN es: $pin\n\nVálido por " . PIN_2FA_EXPIRATION_MIN . " minutos.\n\n" . APP_NAME;

            $mailer->send();
            return true;
        } catch (Exception $e) {
            error_log("Email error: " . $e->getMessage());
            return false;
        }
    }

    private static function pinTemplate($nombre, $pin) {
        return '<!DOCTYPE html><html lang="es"><head><meta charset="UTF-8"><style>
            body{font-family:Arial,sans-serif;line-height:1.6;color:#333;margin:0;padding:0}
            .container{max-width:600px;margin:20px auto;padding:20px}
            .header{background:#2c3e50;color:#fff;padding:20px;text-align:center;border-radius:8px 8px 0 0}
            .content{padding:30px;background:#f8f9fa;border:1px solid #ddd;border-top:none}
            .pin-box{background:#fff;border:2px solid #2c3e50;padding:20px;text-align:center;margin:20px 0;border-radius:8px}
            .pin-number{font-size:36px;font-weight:bold;color:#2c3e50;letter-spacing:10px}
            .footer{text-align:center;padding:15px;color:#999;font-size:11px}
        </style></head><body>
        <div class="container">
            <div class="header"><h2>' . APP_NAME . '</h2><p>Código de verificación</p></div>
            <div class="content">
                <h3>Hola ' . htmlspecialchars($nombre) . ',</h3>
                <p>Se ha generado un codigo PIN para verificar tu identidad:</p>
                <div class="pin-box"><div class="pin-number">' . $pin . '</div></div>
                <p style="color:#666;font-size:13px">Este codigo es válido por <strong>' . PIN_2FA_EXPIRATION_MIN . ' minutos</strong>.</p>
                <p style="color:#999;font-size:12px">Si no solicitaste este codigo, ignora este correo.</p>
            </div>
            <div class="footer"><p>' . APP_NAME . ' - Este es un correo automático</p></div>
        </div></body></html>';
    }

    public static function enviarNotificacion($email, $nombre, $asunto, $mensajeTexto) {
        $config = self::getConfig();
        $mailer = new PHPMailer(true);
        try {
            $mailer->isSMTP();
            $mailer->Host = $config['smtp']['host'];
            $mailer->SMTPAuth = true;
            $mailer->Username = $config['smtp']['username'];
            $mailer->Password = $config['smtp']['password'];
            $mailer->SMTPSecure = $config['smtp']['encryption'];
            $mailer->Port = $config['smtp']['port'];
            $mailer->CharSet = 'UTF-8';
            $mailer->SMTPOptions = ['ssl' => ['verify_peer' => false, 'verify_peer_name' => false, 'allow_self_signed' => true]];

            $mailer->setFrom($config['smtp']['from_email'], $config['smtp']['from_name']);
            $mailer->addAddress($email, $nombre);

            $mailer->isHTML(true);
            $mailer->Subject = $asunto . ' - ' . APP_NAME;
            $body = '<!DOCTYPE html><html lang="es"><head><meta charset="UTF-8"><style>
                body{font-family:Arial,sans-serif;line-height:1.6;color:#333;margin:0;padding:0}
                .container{max-width:600px;margin:20px auto;padding:20px}
                .header{background:#c0392b;color:#fff;padding:20px;text-align:center;border-radius:8px 8px 0 0}
                .content{padding:30px;background:#f8f9fa;border:1px solid #ddd}
                .footer{text-align:center;padding:15px;color:#999;font-size:11px}
            </style></head><body>
            <div class="container">
                <div class="header"><h2>' . APP_NAME . '</h2></div>
                <div class="content">
                    <h3>Hola ' . htmlspecialchars($nombre) . ',</h3>
                    <p>' . nl2br(htmlspecialchars($mensajeTexto)) . '</p>
                </div>
                <div class="footer"><p>' . APP_NAME . ' - Este es un correo automático</p></div>
            </div></body></html>';
            $mailer->Body = $body;
            $mailer->AltBody = strip_tags($mensajeTexto);

            $mailer->send();
            return true;
        } catch (Exception $e) {
            error_log("Email notificacion error: " . $e->getMessage());
            return false;
        }
    }
}
