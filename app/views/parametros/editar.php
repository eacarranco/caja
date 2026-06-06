<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4>Editar parámetro</h4>
        <a href="<?= BASE_URL ?>/parametro/listar" class="btn btn-outline-secondary"><i class="bi bi-arrow-left"></i> Volver</a>
    </div>

    <div class="card card-dashboard">
        <div class="card-body">
            <?php if (!empty($error)): ?>
                <div class="alert alert-warning"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>
            <form method="POST">
                <?= CSRFMiddleware::campoHTML() ?>
                <div class="mb-3">
                    <label class="form-label">Código</label>
                    <input type="text" class="form-control" value="<?= htmlspecialchars($param['código']) ?>" readonly>
                </div>
                <div class="mb-3">
                    <label class="form-label">Nombre</label>
                    <input type="text" class="form-control" value="<?= htmlspecialchars($param['nombre']) ?>" readonly>
                </div>
                <div class="mb-3">
                    <label class="form-label">Valor</label>
                    <?php if ($param['tipo'] === 'booleano'): ?>
                    <select name="valor" class="form-select">
                        <option value="1" <?= $param['valor'] == '1' ? 'selected' : '' ?>>Verdadero</option>
                        <option value="0" <?= $param['valor'] == '0' ? 'selected' : '' ?>>Falso</option>
                    </select>
                    <?php elseif ($param['tipo'] === 'color'): ?>
                    <input type="color" name="valor" class="form-control form-control-color" value="<?= htmlspecialchars($param['valor']) ?>">
                    <?php else: ?>
                    <input type="<?= $param['tipo'] === 'número' ? 'number' : ($param['tipo'] === 'decimal' ? 'number' : 'text') ?>"
                           name="valor" class="form-control"
                           value="<?= htmlspecialchars($param['valor']) ?>"
                           <?= $param['tipo'] === 'decimal' ? 'step="0.01"' : '' ?>>
                    <?php endif; ?>
                </div>
                <button type="submit" class="btn btn-primary"><i class="bi bi-save"></i> Guardar</button>
            </form>
        </div>
    </div>
</div>
