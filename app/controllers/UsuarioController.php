<?php
require_once 'app/models/Usuario.php';

class UsuarioController extends BaseController {

    public function listar() {
        $this->requirePermission('param.usuarios');
        $model = new Usuario();
        $usuarios = $model->getAll('apellidos, nombres');

        $rolesStmt = $this->db->query("SELECT * FROM roles ORDER BY nombre");
        $roles = $rolesStmt->fetchAll();

        $this->render('parametros/usuarios', [
            'titulo' => 'Usuarios del sistema',
            'usuarios' => $usuarios,
            'roles' => $roles,
        ]);
    }

    public function registrar() {
        $this->requirePermission('param.usuarios');
        $errors = [];

        $rolesStmt = $this->db->query("SELECT * FROM roles ORDER BY nombre");
        $roles = $rolesStmt->fetchAll();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->validateCSRF();
            $data = $_POST;

            $validator = new Validator();
            $validator
                ->required('cedula', 'Cédula', $data['cedula'] ?? '')
                ->cedula('cedula', 'Cédula', $data['cedula'] ?? '')
                ->unique('cedula', 'Cédula', $data['cedula'] ?? '', 'usuarios', 'cédula')
                ->required('nombres', 'Nombres', $data['nombres'] ?? '')
                ->required('apellidos', 'Apellidos', $data['apellidos'] ?? '')
                ->required('correo', 'Correo', $data['correo'] ?? '')
                ->email('correo', 'Correo', $data['correo'] ?? '')
                ->unique('correo', 'Correo', $data['correo'] ?? '', 'usuarios', 'correo_electrónico')
                ->required('username', 'Usuario', $data['username'] ?? '')
                ->minLength('username', 'Usuario', $data['username'] ?? '', 3)
                ->unique('username', 'Usuario', $data['username'] ?? '', 'usuarios', 'nombre_usuario')
                ->required('password', 'Contraseña', $data['password'] ?? '')
                ->minLength('password', 'Contraseña', $data['password'] ?? '', 6);

            if ($validator->hasErrors()) {
                $errors = $validator->getErrors();
            } else {
                $model = new Usuario();
                $id = UUIDGenerator::generate();
                $insert = [
                    'id_usuario' => $id,
                    'nombres' => $data['nombres'],
                    'apellidos' => $data['apellidos'],
                    'cédula' => $data['cedula'],
                    'correo_electrónico' => $data['correo'],
                    'teléfono' => $data['telefono'] ?? '',
                    'nombre_usuario' => $data['username'],
                    'contraseña' => password_hash($data['password'], PASSWORD_BCRYPT),
                    'activo' => !empty($data['activo']) ? 1 : 0,
                    '_2fa_obligatorio' => !empty($data['_2fa_obligatorio']) ? 1 : 0,
                ];
                if ($model->insert($insert)) {
                    $rolesAsignados = $data['roles'] ?? [];
                    $model->asignarRoles($id, $rolesAsignados);
                    $this->redirect('/usuario/listar');
                } else {
                    $errors['general'] = 'Error al crear el usuario';
                }
            }
        }

        $this->render('parametros/usuario_form', [
            'titulo' => 'Registrar usuario',
            'errors' => $errors,
            'data' => $_POST ?? [],
            'roles' => $roles,
            'editando' => false,
        ]);
    }

    public function editar($id) {
        $this->requirePermission('param.usuarios');
        $model = new Usuario();
        $usuario = $model->getById($id);
        if (!$usuario) $this->redirect('/usuario/listar');

        $rolesStmt = $this->db->query("SELECT * FROM roles ORDER BY nombre");
        $roles = $rolesStmt->fetchAll();
        $rolesUsuario = $model->getRoles($id);
        $rolesUsuarioIds = array_column($rolesUsuario, 'id_rol');

        $errors = [];
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->validateCSRF();
            $data = $_POST;

            $validator = new Validator();
            $validator
                ->required('nombres', 'Nombres', $data['nombres'] ?? '')
                ->required('apellidos', 'Apellidos', $data['apellidos'] ?? '')
                ->email('correo', 'Correo', $data['correo'] ?? '');

            if ($validator->hasErrors()) {
                $errors = $validator->getErrors();
            } else {
                $update = [
                    'nombres' => $data['nombres'],
                    'apellidos' => $data['apellidos'],
                    'correo_electrónico' => $data['correo'],
                    'teléfono' => $data['telefono'] ?? '',
                    'activo' => !empty($data['activo']) ? 1 : 0,
                    '_2fa_obligatorio' => !empty($data['_2fa_obligatorio']) ? 1 : 0,
                ];
                if (!empty($data['password'])) {
                    $validator->minLength('password', 'Contraseña', $data['password'], 6);
                    if (!$validator->hasErrors()) {
                        $update['contraseña'] = password_hash($data['password'], PASSWORD_BCRYPT);
                    } else {
                        $errors = $validator->getErrors();
                    }
                }
                if (empty($errors)) {
                    if ($model->update($id, $update)) {
                        $rolesAsignados = $data['roles'] ?? [];
                        $model->asignarRoles($id, $rolesAsignados);
                        $this->redirect('/usuario/listar');
                    } else {
                        $errors['general'] = 'Error al actualizar';
                    }
                }
            }
        }

        $this->render('parametros/usuario_form', [
            'titulo' => 'Editar usuario',
            'errors' => $errors,
            'data' => $usuario,
            'roles' => $roles,
            'rolesUsuario' => $rolesUsuarioIds,
            'editando' => true,
        ]);
    }

    public function eliminar($id) {
        $this->requirePermission('param.usuarios');
        if ($id === $_SESSION['usuario_id']) {
            $this->json(['error' => 'No puedes eliminarte a ti mismo'], 400);
        }
        $model = new Usuario();
        $model->delete($id);
        $this->json(['mensaje' => 'Usuario eliminado']);
    }
}
