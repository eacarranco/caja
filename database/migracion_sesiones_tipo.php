<?php

$db = Database::getInstance();

$stmt = $db->query("SHOW COLUMNS FROM sesiones_mensuales LIKE 'tipo'");
$exists = $stmt->fetch();

if (!$exists) {
    $db->query("ALTER TABLE sesiones_mensuales ADD COLUMN tipo ENUM('ordinaria','extraordinaria','informativa') DEFAULT 'ordinaria' NOT NULL COMMENT 'Tipo de sesion: ordinaria (max 1/mes), extraordinaria, informativa' AFTER titulo");
    echo "Migration OK: columna tipo agregada a sesiones_mensuales\n";
} else {
    echo "Migration SKIP: columna tipo ya existe en sesiones_mensuales\n";
}
