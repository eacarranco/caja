<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4>Permisos: <?= htmlspecialchars($rol['nombre']) ?></h4>
        <a href="<?= BASE_URL ?>/rol/listar" class="btn btn-outline-secondary"><i class="bi bi-arrow-left"></i> Volver</a>
    </div>

    <div class="card card-dashboard">
        <div class="card-body">
            <form method="POST">
                <?= CSRFMiddleware::campoHTML() ?>
                <div class="table-responsive">
                    <table class="table table-sm">
                        <thead class="table-light">
                            <tr>
                                <th style="width:40px">✓</th>
                                <th>Código</th>
                                <th>Nombre</th>
                                <th>Descripción</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $grupo = ''; ?>
                            <?php foreach ($permisos as $p):
                                $g = explode('.', $p['código'])[0];
                                if ($g !== $grupo):
                                    $grupo = $g;
                            ?>
                            <tr class="table-secondary">
                                <td colspan="4"><strong><?= ucfirst($grupo) ?></strong></td>
                            </tr>
                            <?php endif; ?>
                            <tr>
                                <td>
                                    <input type="checkbox" name="permiso_<?= $p['id_permiso'] ?>" class="form-check-input"
                                           value="1" id="perm_<?= $p['id_permiso'] ?>"
                                           <?= isset($asignados[$p['id_permiso']]) ? 'checked' : '' ?>>
                                </td>
                                <td><code><?= htmlspecialchars($p['código']) ?></code></td>
                                <td><label for="perm_<?= $p['id_permiso'] ?>"><?= htmlspecialchars($p['nombre']) ?></label></td>
                                <td class="text-muted small"><?= htmlspecialchars($p['descripción'] ?? '') ?></td>
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
