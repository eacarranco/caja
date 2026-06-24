<?php
// Migration: add socio.eliminar permission and assign to Administrador Tecnico (rol 1)

$db = Database::getInstance();

// Check if permiso already exists
$stmt = $db->query("SELECT id_permiso FROM permisos WHERE codigo = 'socio.eliminar'");
$existing = $stmt->fetch();

if (!$existing) {
    $db->query("INSERT INTO permisos (codigo, nombre, descripcion, modulo) VALUES ('socio.eliminar', 'Eliminar socio', 'Permite eliminar un socio del sistema de forma permanente', 'Socios')");
    $idPermiso = $db->lastInsertId();

    $db->prepare("INSERT INTO roles_permisos (id_rol, id_permiso, permitir) VALUES (1, ?, TRUE)")->execute([$idPermiso]);
    echo "Migration OK: permiso socio.eliminar creado y asignado a Administrador\n";
} else {
    // Ensure it's assigned to Administrador if missing
    $idPermiso = $existing['id_permiso'];
    $chk = $db->prepare("SELECT COUNT(*) FROM roles_permisos WHERE id_rol = 1 AND id_permiso = ? AND permitir = TRUE");
    $chk->execute([$idPermiso]);
    if ($chk->fetchColumn() == 0) {
        $db->prepare("INSERT INTO roles_permisos (id_rol, id_permiso, permitir) VALUES (1, ?, TRUE)")->execute([$idPermiso]);
        echo "Migration OK: permiso socio.eliminar asignado a Administrador\n";
    } else {
        echo "Migration SKIP: permiso socio.eliminar ya existe y esta asignado\n";
    }
}
