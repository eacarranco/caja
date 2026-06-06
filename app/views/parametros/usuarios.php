<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4>Usuarios del sistema</h4>
        <a href="<?= BASE_URL ?>/usuario/registrar" class="btn btn-primary"><i class="bi bi-plus-circle"></i> Nuevo usuario</a>
    </div>

    <div class="card card-dashboard">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Usuario</th>
                            <th>Nombres</th>
                            <th>Cédula</th>
                            <th>Correo</th>
                            <th>Roles</th>
                            <th>2FA</th>
                            <th>Activo</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($usuarios as $u): ?>
                        <tr>
                            <td><strong><?= htmlspecialchars($u['nombre_usuario']) ?></strong></td>
                            <td><?= htmlspecialchars($u['nombres'] . ' ' . $u['apellidos']) ?></td>
                            <td><?= htmlspecialchars($u['cédula']) ?></td>
                            <td><?= htmlspecialchars($u['correo_electrónico']) ?></td>
                            <td>
                                <?php
                                $stmt = $this->db->prepare("SELECT r.nombre FROM roles_usuarios ru JOIN roles r ON ru.id_rol = r.id_rol WHERE ru.id_usuario = ?");
                                $stmt->execute([$u['id_usuario']]);
                                $roles = $stmt->fetchAll(PDO::FETCH_COLUMN);
                                foreach ($roles as $r): ?>
                                <span class="badge bg-primary"><?= htmlspecialchars($r) ?></span>
                                <?php endforeach; ?>
                                <?php if (empty($roles)): ?><span class="text-muted">—</span><?php endif; ?>
                            </td>
                            <td><?= $u['_2fa_obligatorio'] ? '<span class="text-success">Sí</span>' : '<span class="text-muted">No</span>' ?></td>
                            <td><?= $u['activo'] ? '<span class="text-success">Sí</span>' : '<span class="text-danger">No</span>' ?></td>
                            <td>
                                <a href="<?= BASE_URL ?>/usuario/editar/<?= $u['id_usuario'] ?>" class="btn btn-sm btn-outline-primary"><i class="bi bi-pencil"></i></a>
                                <?php if ($u['id_usuario'] !== $_SESSION['usuario_id']): ?>
                                <a href="#" onclick="eliminarUsuario('<?= $u['id_usuario'] ?>')" class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></a>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script>
function eliminarUsuario(id) {
    if (!confirm('¿Eliminar este usuario? No se puede deshacer.')) return;
    fetch('<?= BASE_URL ?>/usuario/eliminar/' + id, {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: 'csrf_token=<?= CSRFMiddleware::generarToken() ?>'
    }).then(function(r) { return r.json(); }).then(function(d) {
        if (d.error) { alert(d.error); } else { location.reload(); }
    });
}
</script>
