<?php
require_once 'app/models/BaseModel.php';

class Usuario extends BaseModel {
    public function __construct() {
        parent::__construct('usuarios', 'id_usuario');
    }

    public function getByCedula($cedula) {
        return $this->getOneWhere('cédula = ?', [$cedula]);
    }

    public function getByUsername($username) {
        return $this->getOneWhere('nombre_usuario = ?', [$username]);
    }

    public function getRoles($usuarioId) {
        $stmt = $this->db->prepare("SELECT r.id_rol, r.nombre, r.descripción, r.endosable
                                     FROM roles_usuarios ru
                                     JOIN roles r ON ru.id_rol = r.id_rol
                                     WHERE ru.id_usuario = ?");
        $stmt->execute([$usuarioId]);
        return $stmt->fetchAll();
    }

    public function asignarRoles($usuarioId, $roles) {
        $this->db->prepare("DELETE FROM roles_usuarios WHERE id_usuario = ?")->execute([$usuarioId]);
        $stmt = $this->db->prepare("INSERT INTO roles_usuarios (id_usuario, id_rol) VALUES (?, ?)");
        foreach ($roles as $rolId) {
            $stmt->execute([$usuarioId, $rolId]);
        }
    }
}
