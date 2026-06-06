<div class="container-fluid">
    <h4><?= $titulo ?></h4>
    <a href="<?= BASE_URL ?>/catalogo/<?= $tipo === 'cantones' ? 'cantones' : $tipo ?>" class="btn btn-sm btn-outline-secondary mb-3"><i class="bi bi-arrow-left"></i> Volver</a>

    <div class="card card-dashboard">
        <div class="card-body">
            <form method="POST">
                <?= CSRFMiddleware::campoHTML() ?>
                <?php foreach ($cols as $col => $postField): ?>
                <div class="mb-3">
                    <label class="form-label"><?= htmlspecialchars($postField) ?></label>
                    <?php if ($tipo === 'cantones' && $col === 'id_provincia'): ?>
                    <select name="id_provincia" class="form-select" required>
                        <?php foreach ($provincias as $p): ?>
                        <option value="<?= $p['id_provincia'] ?>" <?= $p['id_provincia'] == $item['id_provincia'] ? 'selected' : '' ?>><?= htmlspecialchars($p['nombre']) ?></option>
                        <?php endforeach; ?>
                    </select>
                    <?php elseif ($col === 'razón_social'): ?>
                    <input type="text" name="razon_social" class="form-control" value="<?= htmlspecialchars($item[$col] ?? '') ?>" required>
                    <?php else: ?>
                    <input type="text" name="<?= $postField ?>" class="form-control" value="<?= htmlspecialchars($item[$col] ?? '') ?>" required>
                    <?php endif; ?>
                </div>
                <?php endforeach; ?>
                <button type="submit" class="btn btn-primary">Guardar cambios</button>
            </form>
        </div>
    </div>
</div>
