<div class="login-container">
    <div class="card login-card">
        <div class="card-body p-4">
            <div class="text-center mb-4">
                <h4><?= APP_NAME ?></h4>
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
            </form>
        </div>
    </div>
</div>
