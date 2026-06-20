SET FOREIGN_KEY_CHECKS = 0;

TRUNCATE amortizaciones;
TRUNCATE cobros;
TRUNCATE creditos;
TRUNCATE inversiones;
TRUNCATE solicitudes_retiro;
TRUNCATE multas;
TRUNCATE asistencias;
TRUNCATE caja_movimientos;
TRUNCATE capital_inversion;
TRUNCATE obligaciones_sesion;
TRUNCATE historial_operaciones;
TRUNCATE notificaciones;
TRUNCATE garantes;
TRUNCATE sesiones_mensuales;

UPDATE cuentas_ahorro SET saldo_obligatorio = 0, saldo_excedente = 0, saldo_disponible = 0, fecha_ultimo_movimiento = NULL;

SET FOREIGN_KEY_CHECKS = 1;
