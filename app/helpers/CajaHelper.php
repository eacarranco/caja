<?php
class CajaHelper {

    public static function registrar($data) {
        $db = Database::getInstance()->getConnection();
        $saldoAnterior = $db->query("SELECT COALESCE(saldo_posterior, 0) FROM caja_movimientos ORDER BY fecha_registro DESC LIMIT 1")->fetchColumn() ?: 0;
        $saldoPosterior = $data['tipo'] === 'ingreso' ? $saldoAnterior + $data['monto'] : $saldoAnterior - $data['monto'];
        $stmt = $db->prepare("INSERT INTO caja_movimientos (id_movimiento, id_sesion, id_socio, id_referencia, tipo_movimiento, concepto, categoria, monto, saldo_anterior, saldo_posterior) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([UUIDGenerator::generar(), $data['id_sesion'] ?? null, $data['id_socio'] ?? null, $data['id_referencia'] ?? null, $data['tipo'], $data['concepto'], $data['categoria'], $data['monto'], $saldoAnterior, $saldoPosterior]);
    }

    public static function obtenerSaldo() {
        $db = Database::getInstance()->getConnection();
        return floatval($db->query("SELECT COALESCE(saldo_posterior, 0) FROM caja_movimientos ORDER BY fecha_registro DESC LIMIT 1")->fetchColumn() ?: 0);
    }
}
