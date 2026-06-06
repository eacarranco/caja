<?php
require_once 'C:\laragon\www\caja\config\app.php';
require_once 'C:\laragon\www\caja\config\database.php';
require_once 'C:\laragon\www\caja\app\helpers\UUIDGenerator.php';

$db = Database::getInstance();
$id = UUIDGenerator::generate();
$hash = password_hash("Admin123", PASSWORD_BCRYPT);

// Use column names WITHOUT accents in the SQL for compatibility
// The real column names in the DB have accents, but MySQL handles this
// when the query bytes match. We need to ensure proper UTF-8 encoding.
$sql = "INSERT INTO usuarios (id_usuario, nombres, apellidos, c" . chr(195) . chr(169) . "dula, correo_electr" . chr(195) . chr(179) . "nico, tel" . chr(195) . chr(169) . "fono, nombre_usuario, contrase" . chr(195) . chr(177) . "a, activo, _2fa_obligatorio) VALUES (?, 'Admin', 'Sistema', '1002606083', 'admin@caja.test', '0999999999', 'admin', ?, TRUE, FALSE)";
$stmt = $db->prepare($sql);
$stmt->execute([$id, $hash]);
echo "Usuario admin creado: $id\n";

$stmt2 = $db->prepare("INSERT INTO roles_usuarios (id_usuario, id_rol) VALUES (?, 1)");
$stmt2->execute([$id]);
echo "Rol asignado: Administrador Técnico\n";
