<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4>Parámetros del sistema</h4>
    </div>

    <div class="card card-dashboard">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Código</th>
                            <th>Nombre</th>
                            <th>Valor</th>
                            <th>Tipo</th>
                            <th>Módulo</th>
                            <th>Editable</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($params as $p): ?>
                        <tr>
                            <td><code><?= htmlspecialchars($p['código']) ?></code></td>
                            <td><?= htmlspecialchars($p['nombre']) ?></td>
                            <td><strong><?= htmlspecialchars($p['valor']) ?></strong></td>
                            <td><span class="badge bg-secondary"><?= $p['tipo'] ?></span></td>
                            <td><span class="badge bg-info"><?= $p['módulo'] ?></span></td>
                            <td><?= $p['editable'] ? '<span class="text-success">Sí</span>' : '<span class="text-muted">No</span>' ?></td>
                            <td>
                                <?php if ($p['editable']): ?>
                                <a href="<?= BASE_URL ?>/parametro/editar/<?= $p['id_parámetro'] ?>" class="btn btn-sm btn-outline-primary"><i class="bi bi-pencil"></i></a>
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
