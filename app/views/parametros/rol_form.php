<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4><?= $editando ? 'Editar rol' : 'Nuevo rol' ?></h4>
        <a href="<?= BASE_URL ?>/rol/listar" class="btn btn-outline-secondary"><i class="bi bi-arrow-left"></i> Volver</a>
    </div>

    <div class="card card-dashboard">
        <div class="card-body">
            <form method="POST">
                <?= CSRFMiddleware::campoHTML() ?>
                <div class="mb-3">
                    <label class="form-label">Nombre del rol *</label>
                    <input type="text" name="nombre" class="form-control <?= isset($errors['nombre']) ? 'is-invalid' : '' ?>"
                           value="<?= htmlspecialchars($data['nombre'] ?? '') ?>" required>
                    <?php if (isset($errors['nombre'])): ?><div class="invalid-feedback"><?= $errors['nombre'] ?></div><?php endif; ?>
                </div>
                <div class="mb-3">
                    <label class="form-label">Descripción</label>
                    <textarea name="descripcion" class="form-control" rows="3"><?= htmlspecialchars($data['descripción'] ?? '') ?></textarea>
                </div>
                <div class="mb-3">
                    <div class="form-check">
                        <input type="checkbox" name="endosable" class="form-check-input" value="1" id="endosable"
                               <?= !empty($data['endosable']) ? 'checked' : '' ?>>
                        <label class="form-check-label" for="endosable">
                            Rol endosable <small class="text-muted">(puede acumular permisos de otros roles)</small>
                        </label>
                    </div>
                </div>
                <button type="submit" class="btn btn-primary"><i class="bi bi-save"></i> <?= $editando ? 'Guardar cambios' : 'Crear rol' ?></button>
                <?php if (!$editando): ?>
                <small class="text-muted ms-2">Después puedes asignar permisos</small>
                <?php endif; ?>
            </form>
        </div>
    </div>
</div>
