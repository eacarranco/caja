<div class="login-container">
    <div class="auth-logo">
        <h3><?= APP_NAME ?></h3>
    </div>
    <div class="card">
        <div class="card-body p-4">
            <div class="text-center mb-4">
                <p class="text-muted">Ingresa tus credenciales</p>
            </div>
            <?php if (!empty($error)): ?>
                <div class="alert alert-danger alert-dismissible fade show"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>
            <form method="POST" action="<?= BASE_URL ?>/login">
                <?= CSRFMiddleware::campoHTML() ?>
                <div class="mb-3">
                    <label class="form-label">Cédula</label>
                    <input type="text" name="cedula" class="form-control" required maxlength="10" autocomplete="username">
                </div>
                <div class="mb-3">
                    <label class="form-label">Contraseña</label>
                    <input type="password" name="password" class="form-control" required autocomplete="current-password">
                </div>
                <button type="submit" class="btn btn-primary w-100">Ingresar</button>
                <div class="text-center mt-3">
                    <a href="<?= BASE_URL ?>/login/olvide" class="text-decoration-none small">¿Olvidaste tu contraseña?</a>
                </div>
            </form>
        </div>
    </div>
</div>
