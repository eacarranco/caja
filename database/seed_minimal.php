<?php
require_once __DIR__ . '/../config/app.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../app/helpers/UUIDGenerator.php';

$db = Database::getInstance();

function insertIfNotExists($db, $checkSql, $insertSql, $checkParams = [], $insertParams = []) {
    $stmt = $db->prepare($checkSql);
    $stmt->execute($checkParams);
    if (!$stmt->fetchColumn()) {
        $stmt = $db->prepare($insertSql);
        $stmt->execute($insertParams);
        return true;
    }
    return false;
}

$cedula = '1003003000';
$nombreUsuario = 'socio_prueba';
$passwordHash = password_hash('Test1234', PASSWORD_BCRYPT);
$email = 'socio_prueba@caja.test';

$stmt = $db->prepare('SELECT id_usuario FROM usuarios WHERE cedula = ?');
$stmt->execute([$cedula]);
$usuarioId = $stmt->fetchColumn();
if (!$usuarioId) {
    $usuarioId = UUIDGenerator::generate();
    $db->prepare('INSERT INTO usuarios (id_usuario, nombres, apellidos, cedula, correo_electronico, telefono, nombre_usuario, contrasena, activo, _2fa_obligatorio) VALUES (?, ?, ?, ?, ?, ?, ?, ?, TRUE, FALSE)')
        ->execute([$usuarioId, 'Prueba', 'Socio', $cedula, $email, '0995000000', $nombreUsuario, $passwordHash]);
}

$roleId = 6; // Socio
$db->prepare('INSERT IGNORE INTO roles_usuarios (id_usuario, id_rol) VALUES (?, ?)')->execute([$usuarioId, $roleId]);

$stmt = $db->prepare('SELECT id_socio FROM socios WHERE cedula = ?');
$stmt->execute([$cedula]);
$socioId = $stmt->fetchColumn();
if (!$socioId) {
    $socioId = UUIDGenerator::generate();
    $db->prepare('INSERT INTO socios (id_socio, cedula, apellido1, apellido2, nombre1, nombre2, fecha_nacimiento, genero, direccion, celular, correo_electronico, estado, fecha_ingreso, hash_integridad) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, CURDATE(), ?)')
        ->execute([$socioId, $cedula, 'Prueba', 'Socio', 'Usuario', 'Demo', '1990-01-01', 'masculino', 'Av. Prueba 123', '0995000000', $email, 'activo', hash('sha256', $cedula . time())]);
}

$stmt = $db->prepare('SELECT COUNT(*) FROM cuentas_ahorro WHERE id_socio = ?');
$stmt->execute([$socioId]);
if (!$stmt->fetchColumn()) {
    $db->prepare('INSERT INTO cuentas_ahorro (id_cuenta_ahorro, id_socio, saldo_obligatorio, saldo_excedente, saldo_disponible, fecha_ultimo_movimiento) VALUES (?, ?, ?, ?, ?, NOW())')
        ->execute([UUIDGenerator::generate(), $socioId, 20.00, 50.00, 70.00]);
}

insertIfNotExists(
    $db,
    'SELECT COUNT(*) FROM productos_financieros WHERE nombre = ? AND tipo = ?',
    'INSERT INTO productos_financieros (id_producto, nombre, tipo, tasa_interes_anual, metodo_interes, plazo_min_meses, plazo_max_meses, monto_min, monto_max, requiere_garante, condiciones_html, min_permanencia_meses, min_ahorro, activo, fecha_creacion) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, TRUE, NOW())',
    ['Crédito Prueba', 'crédito'],
    [UUIDGenerator::generate(), 'Crédito Prueba', 'crédito', 8.5, 'francés', 3, 12, 100.00, 1000.00, 1, '<p>Crédito de prueba para socios.</p>', 3, 20.00]
);

insertIfNotExists(
    $db,
    'SELECT COUNT(*) FROM productos_financieros WHERE nombre = ? AND tipo = ?',
    'INSERT INTO productos_financieros (id_producto, nombre, tipo, tasa_interes_anual, metodo_interes, plazo_min_meses, plazo_max_meses, monto_min, monto_max, requiere_garante, condiciones_html, activo, fecha_creacion) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, TRUE, NOW())',
    ['Inversión Prueba', 'inversión'],
    [UUIDGenerator::generate(), 'Inversión Prueba', 'inversión', 6.0, 'simple', 6, 12, 50.00, 5000.00, 0, '<p>Inversión de prueba para socios.</p>']
);

echo "Seed mínimo aplicado:\n";
echo "- Usuario socio: $nombreUsuario / $cedula / Test1234\n";
echo "- Socio ID: $socioId\n";
echo "- Cuenta ahorro: saldo_obligatorio=20, saldo_excedente=50\n";
echo "- Producto crédito: Crédito Prueba\n";
echo "- Producto inversión: Inversión Prueba\n";
