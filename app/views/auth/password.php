<div class="container-fluid">
    <h4>Cambiar contraseña</h4>

    <div class="card card-dashboard" style="max-width:500px">
        <div class="card-body">
            <?php if (!empty($errors['exito'])): ?>
            <div class="alert alert-success"><?= $errors['exito'] ?></div>
            <?php endif; ?>
            <form method="POST">
                <?= CSRFMiddleware::campoHTML() ?>
                <div class="mb-3">
                    <label class="form-label">Contraseña actual</label>
                    <input type="password" name="actual" class="form-control <?= isset($errors['actual']) ? 'is-invalid' : '' ?>" required>
                    <div class="invalid-feedback"><?= $errors['actual'] ?? '' ?></div>
                </div>
                <div class="mb-3">
                    <label class="form-label">Nueva contraseña</label>
                    <input type="password" name="nueva" class="form-control <?= isset($errors['nueva']) ? 'is-invalid' : '' ?>" required minlength="6">
                    <div class="invalid-feedback"><?= $errors['nueva'] ?? '' ?></div>
                </div>
                <div class="mb-3">
                    <label class="form-label">Confirmar nueva contraseña</label>
                    <input type="password" name="confirmar" class="form-control <?= isset($errors['confirmar']) ? 'is-invalid' : '' ?>" required>
                    <div class="invalid-feedback"><?= $errors['confirmar'] ?? '' ?></div>
                </div>
                <button type="submit" class="btn btn-primary"><i class="bi bi-key"></i> Cambiar contraseña</button>
            </form>
        </div>
    </div>
</div>
