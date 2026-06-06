<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4><?= $titulo ?></h4>
        <a href="<?= BASE_URL ?>/producto/listar" class="btn btn-outline-secondary"><i class="bi bi-arrow-left"></i> Volver</a>
    </div>

    <div class="card card-dashboard">
        <div class="card-body">
            <form method="POST">
                <?= CSRFMiddleware::campoHTML() ?>

                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">Nombre *</label>
                        <input type="text" name="nombre" class="form-control <?= isset($errors['nombre']) ? 'is-invalid' : '' ?>"
                               value="<?= htmlspecialchars($data['nombre'] ?? '') ?>" required>
                        <div class="invalid-feedback"><?= $errors['nombre'] ?? '' ?></div>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Tipo *</label>
                        <select name="tipo" class="form-select <?= isset($errors['tipo']) ? 'is-invalid' : '' ?>">
                            <?php foreach ($tipos as $k => $v): ?>
                            <option value="<?= $k ?>" <?= ($data['tipo'] ?? '') === $k ? 'selected' : '' ?>><?= $v ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Tasa interés anual % *</label>
                        <input type="number" step="0.01" min="0" max="100" name="tasa_interés_anual"
                               class="form-control <?= isset($errors['tasa_interés_anual']) ? 'is-invalid' : '' ?>"
                               value="<?= htmlspecialchars($data['tasa_interés_anual'] ?? '6.00') ?>">
                        <div class="invalid-feedback"><?= $errors['tasa_interés_anual'] ?? '' ?></div>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Método de interés *</label>
                        <select name="método_interés" class="form-select <?= isset($errors['método_interés']) ? 'is-invalid' : '' ?>">
                            <?php foreach ($metodos as $k => $v): ?>
                            <option value="<?= $k ?>" <?= ($data['método_interés'] ?? '') === $k ? 'selected' : '' ?>><?= $v ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Plazo mínimo (meses) *</label>
                        <input type="number" min="1" name="plazo_mín_meses"
                               class="form-control <?= isset($errors['plazo_mín_meses']) ? 'is-invalid' : '' ?>"
                               value="<?= htmlspecialchars($data['plazo_mín_meses'] ?? '1') ?>">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Plazo máximo (meses) *</label>
                        <input type="number" min="1" name="plazo_máx_meses"
                               class="form-control <?= isset($errors['plazo_máx_meses']) ? 'is-invalid' : '' ?>"
                               value="<?= htmlspecialchars($data['plazo_máx_meses'] ?? '12') ?>">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Monto mínimo $ *</label>
                        <input type="number" step="0.01" min="0" name="monto_mín"
                               class="form-control <?= isset($errors['monto_mín']) ? 'is-invalid' : '' ?>"
                               value="<?= htmlspecialchars($data['monto_mín'] ?? '0') ?>">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Monto máximo $ *</label>
                        <input type="number" step="0.01" min="0" name="monto_máx"
                               class="form-control <?= isset($errors['monto_máx']) ? 'is-invalid' : '' ?>"
                               value="<?= htmlspecialchars($data['monto_máx'] ?? '1000') ?>">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Penalidad retiro anticipado (%)</label>
                        <input type="number" step="0.01" min="0" max="100" name="penalidad_retiro_anticipado"
                               class="form-control"
                               value="<?= htmlspecialchars($data['penalidad_retiro_anticipado'] ?? '0') ?>">
                    </div>
                    <div class="col-md-3 d-flex align-items-end">
                        <div class="form-check">
                            <input type="checkbox" name="requiere_garante" class="form-check-input" value="1" id="reqGarante"
                                   <?= !empty($data['requiere_garante']) ? 'checked' : '' ?>>
                            <label class="form-check-label" for="reqGarante">Requiere garante</label>
                        </div>
                    </div>
                </div>

                <div class="mt-4">
                    <button type="submit" class="btn btn-primary"><i class="bi bi-save"></i> <?= $editando ? 'Guardar cambios' : 'Crear producto' ?></button>
                </div>
            </form>
        </div>
    </div>
</div>
