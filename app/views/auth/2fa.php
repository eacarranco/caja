<div class="login-container">
    <div class="card login-card">
        <div class="card-body p-4">
            <div class="text-center mb-4">
                <h4>Verificación de dos factores</h4>
                <p class="text-muted">Ingresa el PIN de 6 dígitos enviado a tu correo</p>
            </div>
            <?php if (!empty($error)): ?>
                <div class="alert alert-danger alert-dismissible fade show"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>
            <form method="POST" action="<?= BASE_URL ?>/login/2fa">
                <?= CSRFMiddleware::campoHTML() ?>
                <div class="mb-3">
                    <label class="form-label">PIN de verificación</label>
                    <input type="text" name="pin" class="form-control text-center fs-3" required
                           maxlength="<?= PIN_2FA_DIGITS ?>" pattern="\d{<?= PIN_2FA_DIGITS ?>}"
                           autocomplete="one-time-code" inputmode="numeric">
                </div>
                <button type="submit" class="btn btn-primary w-100">Verificar</button>
            </form>
            <div class="text-center mt-3">
                <small><a href="#" onclick="reenviarPIN(); return false;">Reenviar PIN</a></small>
            </div>
        </div>
    </div>
</div>
<script>
function reenviarPIN() {
    fetch('<?= BASE_URL ?>/auth/reenviarPIN', {
        method: 'POST',
        headers: { 'X-CSRF-TOKEN': '<?= $csrfToken ?? '' ?>' }
    }).then(function(r) { return r.json(); }).then(function(d) {
        alert(d.mensaje || d.error || 'Error');
    });
}
</script>
