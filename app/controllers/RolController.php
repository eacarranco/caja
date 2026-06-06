<?php
class RolController extends BaseController {

    public function listar() {
        $this->requirePermission('param.roles');
        $stmt = $this->db->query("SELECT roles.*, (SELECT COUNT(*) FROM roles_usuarios WHERE roles_usuarios.id_rol = roles.id_rol) AS usuarios FROM roles ORDER BY roles.nombre");
        $roles = $stmt->fetchAll();

        $this->render('parametros/roles', [
            'titulo' => 'Roles del sistema',
            'roles' => $roles,
        ]);
    }

    public function registrar() {
        $this->requirePermission('param.roles');
        $errors = [];

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->validateCSRF();
            $nombre = trim($_POST['nombre'] ?? '');
            $descripcion = trim($_POST['descripcion'] ?? '');
            $endosable = !empty($_POST['endosable']) ? 1 : 0;

            if (empty($nombre)) {
                $errors['nombre'] = 'El nombre del rol es obligatorio';
            } else {
                $stmt = $this->db->prepare("SELECT COUNT(*) FROM roles WHERE nombre = ?");
                $stmt->execute([$nombre]);
                if ($stmt->fetchColumn() > 0) {
                    $errors['nombre'] = 'Ya existe un rol con ese nombre';
                }
            }

            if (empty($errors)) {
                $stmt = $this->db->prepare("INSERT INTO roles (nombre, descripción, endosable) VALUES (?, ?, ?)");
                $stmt->execute([$nombre, $descripcion, $endosable]);
                $this->redirect('/rol/permisos/' . $this->db->lastInsertId());
            }
        }

        $this->render('parametros/rol_form', [
            'titulo' => 'Nuevo rol',
            'errors' => $errors,
            'data' => $_POST ?? [],
            'editando' => false,
        ]);
    }

    public function editar($id) {
        $this->requirePermission('param.roles');
        $stmt = $this->db->prepare("SELECT * FROM roles WHERE id_rol = ?");
        $stmt->execute([$id]);
        $rol = $stmt->fetch();
        if (!$rol) $this->redirect('/rol/listar');

        $errors = [];
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->validateCSRF();
            $nombre = trim($_POST['nombre'] ?? '');
            $descripcion = trim($_POST['descripcion'] ?? '');
            $endosable = !empty($_POST['endosable']) ? 1 : 0;

            if (empty($nombre)) {
                $errors['nombre'] = 'El nombre del rol es obligatorio';
            } else {
                $stmt = $this->db->prepare("SELECT COUNT(*) FROM roles WHERE nombre = ? AND id_rol != ?");
                $stmt->execute([$nombre, $id]);
                if ($stmt->fetchColumn() > 0) {
                    $errors['nombre'] = 'Ya existe un rol con ese nombre';
                }
            }

            if (empty($errors)) {
                $stmt = $this->db->prepare("UPDATE roles SET nombre = ?, descripción = ?, endosable = ? WHERE id_rol = ?");
                $stmt->execute([$nombre, $descripcion, $endosable, $id]);
                $this->redirect('/rol/listar');
            }
        }

        $this->render('parametros/rol_form', [
            'titulo' => 'Editar rol',
            'errors' => $errors,
            'data' => $rol,
            'editando' => true,
        ]);
    }

    public function eliminar($id) {
        $this->requirePermission('param.roles');
        $stmt = $this->db->prepare("SELECT COUNT(*) FROM roles_usuarios WHERE id_rol = ?");
        $stmt->execute([$id]);
        if ($stmt->fetchColumn() > 0) {
            $this->json(['error' => 'No se puede eliminar: hay usuarios asignados a este rol'], 400);
        }
        $this->db->prepare("DELETE FROM roles WHERE id_rol = ?")->execute([$id]);
        $this->json(['mensaje' => 'Rol eliminado']);
    }

    public function permisos($id) {
        $this->requirePermission('param.roles');
        $stmt = $this->db->prepare("SELECT * FROM roles WHERE id_rol = ?");
        $stmt->execute([$id]);
        $rol = $stmt->fetch();
        if (!$rol) $this->redirect('/rol/listar');

        $permisos = $this->db->query("SELECT * FROM permisos ORDER BY código")->fetchAll();

        $stmt = $this->db->prepare("SELECT id_permiso, permitir FROM roles_permisos WHERE id_rol = ?");
        $stmt->execute([$id]);
        $asignados = [];
        while ($row = $stmt->fetch()) {
            $asignados[$row['id_permiso']] = $row['permitir'];
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->validateCSRF();
            $this->db->prepare("DELETE FROM roles_permisos WHERE id_rol = ?")->execute([$id]);
            $insertStmt = $this->db->prepare("INSERT INTO roles_permisos (id_rol, id_permiso, permitir) VALUES (?, ?, ?)");
            foreach ($permisos as $p) {
                $checked = isset($_POST['permiso_' . $p['id_permiso']]);
                if ($checked) {
                    $insertStmt->execute([$id, $p['id_permiso'], 1]);
                }
            }
            $this->redirect('/rol/listar');
        }

        $this->render('parametros/rol_permisos', [
            'titulo' => 'Permisos: ' . $rol['nombre'],
            'rol' => $rol,
            'permisos' => $permisos,
            'asignados' => $asignados,
        ]);
    }
}
