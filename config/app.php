<?php
define('APP_NAME', 'Caja de Ahorro y Crédito Solidaria Familiar Pujota-Simbaña');
define('APP_VERSION', '1.0.0');
define('BASE_URL', 'http://localhost/caja');
define('DEBUG', true);
define('TIMEZONE', 'America/Guayaquil');
define('SESSION_TIMEOUT_MINUTES', 30);
define('MAX_LOGIN_ATTEMPTS', 3);
define('BLOCK_MINUTES', 15);
define('PIN_2FA_DIGITS', 6);
define('PIN_2FA_EXPIRATION_MIN', 5);
define('MAX_PIN_RESEND_HOUR', 3);
define('APORTE_OBLIGATORIO_MENSUAL', 10.00);
define('CUOTA_INGRESO', 20.00);
define('MULTA_RETRASO_10MIN', 1.00);
define('MULTA_RETRASO_30MIN', 5.00);
define('MULTA_INASISTENCIA', 5.00);
define('MULTA_MORA_CREDITO', 5.00);

date_default_timezone_set(TIMEZONE);

if (DEBUG) {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
} else {
    error_reporting(0);
    ini_set('display_errors', 0);
}
