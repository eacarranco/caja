<?php
require_once __DIR__ . '/../config/database.php';

$db = Database::getInstance()->getConnection();

$tablas = [
    'amortizaciones',
    'garantes',
    'cobros',
    'multas',
    'asistencias',
    'notificaciones',
    'historial_operaciones',
    'solicitudes_retiro',
    'creditos',
    'inversiones',
    'capital_inversion',
    'archivos',
    'obligaciones_sesion',
    'caja_movimientos',
    'sesiones_mensuales',
];

try {
    $db->exec("SET FOREIGN_KEY_CHECKS = 0");
    foreach ($tablas as $t) {
        $db->exec("TRUNCATE TABLE $t");
        echo "OK: $t truncada\n";
    }
    // Resetear saldos de cuentas_ahorro (1:1 con socios, se conservan las filas)
    $db->exec("UPDATE cuentas_ahorro SET saldo_obligatorio = 0, saldo_excedente = 0, saldo_disponible = 0");
    echo "OK: cuentas_ahorro saldos reseteados a 0\n";
    $db->exec("SET FOREIGN_KEY_CHECKS = 1");
    echo "\nListo. Se truncaron " . count($tablas) . " tablas transaccionales y se resetearon saldos.\n";
} catch (Exception $e) {
    $db->exec("SET FOREIGN_KEY_CHECKS = 1");
    echo "Error: " . $e->getMessage() . "\n";
}
