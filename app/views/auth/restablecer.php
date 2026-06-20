<div class="login-container">
    <div class="auth-logo">
        <h3><?= APP_NAME ?></h3>
    </div>
    <div class="card">
        <div class="card-body p-4">
            <?php if (!empty($exito)): ?>
                <div class="text-center mb-4">
                    <h5>Contraseña restablecida</h5>
                </div>
                <div class="alert alert-success"><?= htmlspecialchars($exito) ?></div>
                <div class="text-center mt-3">
                    <a href="<?= BASE_URL ?>/login" class="btn btn-primary">Iniciar sesión</a>
                </div>
            <?php elseif ($step === 'pin'): ?>
                <div class="text-center mb-4">
                    <h5>Verificar PIN</h5>
                    <p class="text-muted">Ingresa el PIN de 6 dígitos enviado a tu correo</p>
                </div>
                <?php if (!empty($error)): ?>
                    <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
                <?php endif; ?>
                <form method="POST" action="<?= BASE_URL ?>/login/restablecer">
                    <?= CSRFMiddleware::campoHTML() ?>
                    <input type="hidden" name="step" value="pin">
                    <div class="mb-3">
                        <label class="form-label">PIN de verificación</label>
                        <input type="text" name="pin" class="form-control text-center fs-3" required maxlength="6" pattern="\d{6}" inputmode="numeric">
                    </div>
                    <button type="submit" class="btn btn-primary w-100">Verificar</button>
                </form>
                <div class="text-center mt-3">
                    <a href="<?= BASE_URL ?>/login/olvide" class="text-decoration-none small">Solicitar nuevo PIN</a>
                </div>
            <?php else: ?>
                <div class="text-center mb-4">
                    <h5>Nueva contraseña</h5>
                    <p class="text-muted">Establece tu nueva contraseña</p>
                </div>
                <?php if (!empty($error)): ?>
                    <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
                <?php endif; ?>
                <form method="POST" action="<?= BASE_URL ?>/login/restablecer">
                    <?= CSRFMiddleware::campoHTML() ?>
                    <input type="hidden" name="step" value="nueva">
                    <div class="mb-3">
                        <label class="form-label">Nueva contraseña</label>
                        <input type="password" name="password" class="form-control" required minlength="6" autocomplete="new-password">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Confirmar contraseña</label>
                        <input type="password" name="confirmar" class="form-control" required minlength="6" autocomplete="new-password">
                    </div>
                    <button type="submit" class="btn btn-primary w-100">Guardar</button>
                </form>
            <?php endif; ?>
            <div class="text-center mt-3">
                <a href="<?= BASE_URL ?>/login" class="text-decoration-none small">Volver al inicio de sesión</a>
            </div>
        </div>
    </div>
</div>
