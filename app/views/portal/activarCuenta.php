<div class="login-container">
    <div class="auth-logo">
        <h3><?= APP_NAME ?></h3>
    </div>
    <div class="card">
        <div class="card-body p-4">
            <div class="text-center mb-4">
                <h5>Activar cuenta</h5>
                <p class="text-muted">Establece tu contraseña de acceso</p>
            </div>
            <?php if (!empty($error)): ?>
                <div class="alert alert-danger alert-dismissible fade show"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>
            <?php if (!empty($exito)): ?>
                <div class="alert alert-success"><?= htmlspecialchars($exito) ?></div>
                <div class="text-center mt-3">
                    <a href="<?= BASE_URL ?>/login" class="btn btn-primary">Iniciar sesión</a>
                </div>
            <?php else: ?>
            <form method="POST" action="<?= BASE_URL ?>/portal/activarCuenta">
                <?= CSRFMiddleware::campoHTML() ?>
                <input type="hidden" name="token" value="<?= htmlspecialchars($token) ?>">
                <div class="mb-3">
                    <label class="form-label">Nueva contraseña</label>
                    <input type="password" name="password" class="form-control" required minlength="6" autocomplete="new-password">
                </div>
                <div class="mb-3">
                    <label class="form-label">Confirmar contraseña</label>
                    <input type="password" name="confirmar" class="form-control" required minlength="6" autocomplete="new-password">
                </div>
                <button type="submit" class="btn btn-primary w-100">Guardar contraseña</button>
            </form>
            <?php endif; ?>
        </div>
    </div>
</div>
