<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4><?= $editando ? 'Editar rol' : 'Nuevo rol' ?></h4>
        <a href="<?= BASE_URL ?>/rol/listar" class="btn btn-outline-secondary"><i class="bi bi-arrow-left"></i> Volver</a>
    </div>

    <form method="POST" id="formRol">
        <?= CSRFMiddleware::campoHTML() ?>

        <ul class="nav nav-pills nav-justified mb-3" id="rolTabs" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link active" id="descripcion-tab" data-bs-toggle="pill" data-bs-target="#descripcion" type="button" role="tab">
                    <i class="bi bi-info-circle"></i> Descripción
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="permisos-tab" data-bs-toggle="pill" data-bs-target="#permisos" type="button" role="tab">
                    <i class="bi bi-check2-square"></i> Permisos por rol
                </button>
            </li>
        </ul>

        <div class="tab-content">
            <div class="tab-pane fade show active" id="descripcion" role="tabpanel">
                <div class="card card-dashboard">
                    <div class="card-body">
                        <div class="mb-3">
                            <label class="form-label">Nombre del rol *</label>
                            <input type="text" name="nombre" class="form-control <?= isset($errors['nombre']) ? 'is-invalid' : '' ?>"
                                   value="<?= htmlspecialchars($data['nombre'] ?? '') ?>" required>
                            <?php if (isset($errors['nombre'])): ?><div class="invalid-feedback"><?= $errors['nombre'] ?></div><?php endif; ?>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Descripción</label>
                            <textarea name="descripcion" class="form-control" rows="3"><?= htmlspecialchars($data['descripcion'] ?? '') ?></textarea>
                        </div>
                        <div class="mb-3">
                            <div class="form-check">
                                <input type="checkbox" name="endosable" class="form-check-input" value="1" id="endosable"
                                       <?= !empty($data['endosable']) ? 'checked' : '' ?>>
                                <label class="form-check-label" for="endosable">
                                    Rol endosable <small class="text-muted">(acumula TODOS los permisos automáticamente)</small>
                                </label>
                            </div>
                        </div>
                        <div class="text-end">
                            <button type="button" class="btn btn-primary" onclick="activarPestanyaPermisos()">
                                Siguiente <i class="bi bi-arrow-right"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <div class="tab-pane fade" id="permisos" role="tabpanel">
                <div class="card card-dashboard">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h5 class="card-title mb-0">Permisos del rol</h5>
                            <div>
                                <button type="button" class="btn btn-outline-success btn-sm me-2" onclick="marcarTodos(true)"><i class="bi bi-check-all"></i> Marcar todos</button>
                                <button type="button" class="btn btn-outline-danger btn-sm" onclick="marcarTodos(false)"><i class="bi bi-x"></i> Desmarcar todos</button>
                            </div>
                        </div>
                        <div class="table-responsive">
                            <table class="table table-sm" id="tablaPermisos">
                                <thead class="table-light">
                                    <tr>
                                        <th style="width:40px">✓</th>
                                        <th>Código</th>
                                        <th>Nombre</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php $grupo = ''; ?>
                                    <?php foreach ($permisos as $p):
                                        $g = $p['modulo'] ?: explode('.', $p['codigo'])[0];
                                        if ($g !== $grupo):
                                            $grupo = $g;
                                    ?>
                                    <tr class="table-secondary modulo-header" data-modulo="<?= htmlspecialchars($grupo) ?>">
                                        <td>
                                            <input type="checkbox" class="form-check-input modulo-toggle" data-modulo="<?= htmlspecialchars($grupo) ?>"
                                                   onchange="toggleModulo(this, '<?= htmlspecialchars($grupo) ?>')">
                                        </td>
                                        <td colspan="2"><strong><?= htmlspecialchars(ucfirst($grupo)) ?></strong></td>
                                    </tr>
                                    <?php endif; ?>
                                    <tr class="permiso-row" data-modulo="<?= htmlspecialchars($g) ?>">
                                        <td>
                                            <input type="checkbox" name="permiso_<?= $p['id_permiso'] ?>" class="form-check-input permiso-check"
                                                   value="1" id="perm_<?= $p['id_permiso'] ?>"
                                                   data-modulo="<?= htmlspecialchars($g) ?>"
                                                   onchange="updateModuloToggle('<?= htmlspecialchars($g) ?>')"
                                                   <?= isset($asignados[$p['id_permiso']]) ? 'checked' : '' ?>>
                                        </td>
                                        <td><code><?= htmlspecialchars($p['codigo']) ?></code></td>
                                        <td><label for="perm_<?= $p['id_permiso'] ?>"><?= htmlspecialchars($p['nombre']) ?></label></td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <div class="d-flex justify-content-between mt-3">
                    <button type="button" class="btn btn-outline-secondary" onclick="activarPestanyaDescripcion()"><i class="bi bi-arrow-left"></i> Anterior</button>
                    <button type="submit" class="btn btn-primary"><i class="bi bi-save"></i> <?= $editando ? 'Guardar cambios' : 'Crear rol' ?></button>
                </div>
            </div>
        </div>
    </form>
</div>

<script>
function marcarTodos(checked) {
    document.querySelectorAll('.permiso-check').forEach(function(cb) {
        cb.checked = checked;
    });
    document.querySelectorAll('.modulo-toggle').forEach(function(cb) {
        cb.checked = checked;
    });
}

function toggleModulo(toggle, modulo) {
    document.querySelectorAll('.permiso-check[data-modulo="' + modulo + '"]').forEach(function(cb) {
        cb.checked = toggle.checked;
    });
}

function updateModuloToggle(modulo) {
    var checks = document.querySelectorAll('.permiso-check[data-modulo="' + modulo + '"]');
    var allChecked = true;
    checks.forEach(function(cb) { if (!cb.checked) allChecked = false; });
    var toggle = document.querySelector('.modulo-toggle[data-modulo="' + modulo + '"]');
    if (toggle) toggle.checked = allChecked;
}

function activarPestanyaPermisos() {
    var tab = new bootstrap.Tab(document.getElementById('permisos-tab'));
    tab.show();
}

function activarPestanyaDescripcion() {
    var tab = new bootstrap.Tab(document.getElementById('descripcion-tab'));
    tab.show();
}

document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('.modulo-toggle').forEach(function(toggle) {
        updateModuloToggle(toggle.dataset.modulo);
    });
});
</script>