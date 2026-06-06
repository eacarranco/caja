<?php
require_once 'app/models/BaseModel.php';

class Socio extends BaseModel {
    public function __construct() {
        parent::__construct('socios', 'id_socio');
    }

    public function getByCedula($cedula) {
        return $this->getOneWhere('cédula = ?', [$cedula]);
    }

    public function getActivos() {
        return $this->getWhere("estado IN ('activo', 'pre_activo')", [], 'apellido1, apellido2, nombre1');
    }

    public function getConCuenta() {
        $stmt = $this->db->prepare("
            SELECT s.*, c.saldo_obligatorio, c.saldo_excedente, c.saldo_disponible
            FROM socios s
            LEFT JOIN cuentas_ahorro c ON s.id_socio = c.id_socio
            WHERE s.estado = 'activo'
            ORDER BY s.apellido1, s.apellido2, s.nombre1
        ");
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function buscar($termino) {
        $term = "%$termino%";
        return $this->getWhere(
            "(cédula LIKE ? OR apellido1 LIKE ? OR nombre1 LIKE ?)",
            [$term, $term, $term],
            'apellido1, apellido2, nombre1'
        );
    }
}
