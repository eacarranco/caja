<div class="login-container">
    <div class="auth-logo">
        <h3><?= APP_NAME ?></h3>
    </div>
    <div class="card">
        <div class="card-body p-4">
            <div class="text-center mb-4">
                <h5>Restablecer contraseña</h5>
                <p class="text-muted">Ingresa tu cédula para recibir un PIN de verificación</p>
            </div>
            <?php if (!empty($error)): ?>
                <div class="alert alert-danger alert-dismissible fade show"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>
            <form method="POST" action="<?= BASE_URL ?>/login/olvide">
                <?= CSRFMiddleware::campoHTML() ?>
                <div class="mb-3">
                    <label class="form-label">Cédula</label>
                    <input type="text" name="cedula" class="form-control" required maxlength="10" value="<?= htmlspecialchars($cedula ?? '') ?>" autocomplete="username">
                </div>
                <button type="submit" class="btn btn-primary w-100">Enviar PIN</button>
            </form>
            <div class="text-center mt-3">
                <a href="<?= BASE_URL ?>/login" class="text-decoration-none small">Volver al inicio de sesión</a>
            </div>
        </div>
    </div>
</div>
