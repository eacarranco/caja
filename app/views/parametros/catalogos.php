<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4><?= $titulo ?></h4>
        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalAgregar">
            <i class="bi bi-plus-circle"></i> Agregar
        </button>
    </div>

    <div class="card card-dashboard">
        <div class="card-body p-0">
            <div class="table-responsive"><table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th>#</th>
                        <?php foreach ($campos as $c): ?>
                        <th><?= $c[1] ?></th>
                        <?php endforeach; ?>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($items as $i => $item): ?>
                    <tr>
                        <td><?= $i + 1 ?></td>
                        <?php foreach ($campos as $c): ?>
                        <td><?= htmlspecialchars($item[$c[0]] ?? '-') ?></td>
                        <?php endforeach; ?>
                        <td>
                            <?php $pk = $item['id_provincia'] ?? $item['id_entidad'] ?? $item['id'] ?? ''; ?>
                            <a href="<?= BASE_URL ?>/catalogo/editar/<?= $tipo ?>/<?= $pk ?>" class="btn btn-sm btn-outline-primary"><i class="bi bi-pencil"></i></a>
                            <a href="<?= BASE_URL ?>/catalogo/eliminar/<?= $tipo ?>/<?= $pk ?>"
                               class="btn btn-sm btn-outline-danger" onclick="return confirm('¿Eliminar?')">
                               <i class="bi bi-trash"></i>
                            </a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table></div>
        </div>
    </div>

    <div class="modal fade" id="modalAgregar" tabindex="-1">
        <div class="modal-dialog">
            <form method="POST" action="<?= BASE_URL ?>/catalogo/agregar/<?= $tipo ?>" class="modal-content">
                <?= CSRFMiddleware::campoHTML() ?>
                <div class="modal-header">
                    <h5 class="modal-title">Agregar <?= strtolower($titulo) ?></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <?php foreach ($campos as $c): ?>
                    <div class="mb-3">
                        <label class="form-label"><?= $c[1] ?></label>
                        <input type="<?= $c[2] ?>" name="<?= $c[0] ?>" class="form-control" required>
                    </div>
                    <?php endforeach; ?>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary">Guardar</button>
                </div>
            </form>
        </div>
    </div>
</div>
