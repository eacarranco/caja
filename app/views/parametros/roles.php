<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4>Roles del sistema</h4>
        <a href="<?= BASE_URL ?>/rol/registrar" class="btn btn-primary"><i class="bi bi-plus-circle"></i> Nuevo rol</a>
    </div>

    <div class="card card-dashboard">
        <div class="card-body p-0">
            <div class="table-responsive"><table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Nombre</th>
                        <th>Descripción</th>
                        <th>Endosable</th>
                        <th>Usuarios</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($roles as $r): ?>
                    <tr>
                        <td><strong><?= htmlspecialchars($r['nombre']) ?></strong></td>
                        <td><?= htmlspecialchars($r['descripción'] ?? '-') ?></td>
                        <td><?= $r['endosable'] ? '<span class="text-success">Sí</span>' : '<span class="text-muted">No</span>' ?></td>
                        <td><span class="badge bg-secondary"><?= $r['usuarios'] ?></span></td>
                        <td>
                            <a href="<?= BASE_URL ?>/rol/permisos/<?= $r['id_rol'] ?>" class="btn btn-sm btn-outline-info" title="Permisos"><i class="bi bi-check2-square"></i></a>
                            <a href="<?= BASE_URL ?>/rol/editar/<?= $r['id_rol'] ?>" class="btn btn-sm btn-outline-primary"><i class="bi bi-pencil"></i></a>
                            <?php if ($r['usuarios'] == 0): ?>
                            <a href="#" onclick="eliminarRol(<?= $r['id_rol'] ?>)" class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></a>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table></div>
        </div>
    </div>
</div>

<script>
function eliminarRol(id) {
    if (!confirm('¿Eliminar este rol?')) return;
    fetch('<?= BASE_URL ?>/rol/eliminar/' + id, {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: 'csrf_token=<?= CSRFMiddleware::generarToken() ?>'
    }).then(function(r) { return r.json(); }).then(function(d) {
        if (d.error) { alert(d.error); } else { location.reload(); }
    });
}
</script>
