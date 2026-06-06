<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4>Cantones</h4>
        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalAgregar">
            <i class="bi bi-plus-circle"></i> Agregar
        </button>
    </div>

    <div class="card card-dashboard">
        <div class="card-body p-0">
            <div class="table-responsive"><table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr><th>#</th><th>Provincia</th><th>Cantón</th><th></th></tr>
                </thead>
                <tbody>
                    <?php foreach ($items as $i => $item): ?>
                    <tr>
                        <td><?= $i + 1 ?></td>
                        <td><?= htmlspecialchars($item['provincia']) ?></td>
                        <td><?= htmlspecialchars($item['nombre']) ?></td>
                        <td>
                            <a href="<?= BASE_URL ?>/catalogo/editar/cantones/<?= $item['id_cantón'] ?>" class="btn btn-sm btn-outline-primary"><i class="bi bi-pencil"></i></a>
                            <a href="<?= BASE_URL ?>/catalogo/eliminar/cantones/<?= $item['id_cantón'] ?>"
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
            <form method="POST" action="<?= BASE_URL ?>/catalogo/agregarCanton" class="modal-content">
                <?= CSRFMiddleware::campoHTML() ?>
                <div class="modal-header">
                    <h5 class="modal-title">Agregar cantón</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Provincia</label>
                        <select name="id_provincia" class="form-select" required>
                            <?php foreach ($provincias as $p): ?>
                            <option value="<?= $p['id_provincia'] ?>"><?= htmlspecialchars($p['nombre']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Cantón</label>
                        <input type="text" name="nombre" class="form-control" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary">Guardar</button>
                </div>
            </form>
        </div>
    </div>
</div>
