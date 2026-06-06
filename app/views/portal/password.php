<div class="container-fluid">
    <h4>Cambiar contraseña</h4>
    <a href="<?= BASE_URL ?>/portal" class="btn btn-outline-secondary btn-sm mb-3"><i class="bi bi-arrow-left"></i> Volver</a>
    <?php if (!empty($exito)): ?><div class="alert alert-success"><?= $exito ?></div><?php endif; ?>
    <?php if (!empty($errors)): ?><div class="alert alert-danger"><?= implode('<br>', $errors) ?></div><?php endif; ?>
    <form method="POST" class="row g-3" style="max-width:400px">
        <input type="hidden" name="csrf_token" value="<?= $csrfToken ?? '' ?>">
        <div class="col-12">
            <label class="form-label">Contraseña actual</label>
            <input type="password" name="actual" class="form-control" required>
        </div>
        <div class="col-12">
            <label class="form-label">Nueva contraseña</label>
            <input type="password" name="nueva" class="form-control" required minlength="6">
        </div>
        <div class="col-12">
            <label class="form-label">Confirmar nueva contraseña</label>
            <input type="password" name="confirmar" class="form-control" required minlength="6">
        </div>
        <div class="col-12">
            <button type="submit" class="btn btn-primary">Actualizar</button>
        </div>
    </form>
</div>