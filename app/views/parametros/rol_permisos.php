<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4>Permisos: <?= htmlspecialchars($rol['nombre']) ?></h4>
        <div>
            <button type="button" class="btn btn-outline-success btn-sm me-2" onclick="marcarTodos(true)"><i class="bi bi-check-all"></i> Marcar todos</button>
            <button type="button" class="btn btn-outline-danger btn-sm me-2" onclick="marcarTodos(false)"><i class="bi bi-x"></i> Desmarcar todos</button>
            <a href="<?= BASE_URL ?>/rol/listar" class="btn btn-outline-secondary btn-sm"><i class="bi bi-arrow-left"></i> Volver</a>
        </div>
    </div>

    <div class="card card-dashboard">
        <div class="card-body">
            <form method="POST" id="formPermisos">
                <?= CSRFMiddleware::campoHTML() ?>
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
                <button type="submit" class="btn btn-primary"><i class="bi bi-save"></i> Guardar permisos</button>
            </form>
        </div>
    </div>
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

document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('.modulo-toggle').forEach(function(toggle) {
        updateModuloToggle(toggle.dataset.modulo);
    });
});
</script>