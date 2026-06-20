<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4>Editar socio</h4>
        <a href="<?= BASE_URL ?>/socio/ver/<?= $socio['id_socio'] ?>" class="btn btn-outline-secondary"><i class="bi bi-arrow-left"></i> Volver</a>
    </div>

    <div class="card card-dashboard">
        <div class="card-body">
            <?php if (!empty($errors['general'])): ?>
                <div class="alert alert-danger"><?= htmlspecialchars($errors['general']) ?></div>
            <?php endif; ?>
            <form method="POST">
                <?= CSRFMiddleware::campoHTML() ?>
                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label">Primer apellido *</label>
                        <input type="text" name="apellido1" class="form-control" value="<?= htmlspecialchars($socio['apellido1']) ?>" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Segundo apellido</label>
                        <input type="text" name="apellido2" class="form-control" value="<?= htmlspecialchars($socio['apellido2'] ?? '') ?>">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Primer nombre *</label>
                        <input type="text" name="nombre1" class="form-control" value="<?= htmlspecialchars($socio['nombre1']) ?>" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Segundo nombre</label>
                        <input type="text" name="nombre2" class="form-control" value="<?= htmlspecialchars($socio['nombre2'] ?? '') ?>">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Dirección</label>
                        <input type="text" name="direccion" class="form-control" value="<?= htmlspecialchars($socio['direccion']) ?>">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Teléfono</label>
                        <input type="text" name="telefono" class="form-control" value="<?= htmlspecialchars($socio['telefono'] ?? '') ?>">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Celular</label>
                        <input type="text" name="celular" class="form-control" value="<?= htmlspecialchars($socio['celular']) ?>">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Profesión</label>
                        <input type="text" name="profesion" class="form-control" value="<?= htmlspecialchars($socio['profesion'] ?? '') ?>">
                    </div>
                </div>
                <div class="mt-4">
                    <button type="submit" class="btn btn-primary"><i class="bi bi-save"></i> Guardar cambios</button>
                </div>
            </form>
        </div>
    </div>

    <div class="card card-dashboard mt-3">
        <div class="card-header"><strong>Contraseña de acceso</strong></div>
        <div class="card-body">
            <?php if ($usuario): ?>
            <p class="mb-2">
                <i class="bi bi-envelope-at"></i> Correo: <strong><?= htmlspecialchars($usuario['correo_electronico'] ?? 'Sin correo') ?></strong><br>
                <i class="bi bi-shield-check"></i> Estado:
                <?php if ($usuario['token_activacion']): ?>
                    <span class="badge bg-warning">Pendiente de activación</span>
                <?php elseif ($usuario['fecha_contrasena']): ?>
                    <span class="badge bg-success">Contraseña establecida</span>
                <?php else: ?>
                    <span class="badge bg-secondary">Sin contraseña</span>
                <?php endif; ?>
            </p>
            <div class="d-flex flex-wrap gap-2">
                <button class="btn btn-sm btn-warning" onclick="forzarCambio()">
                    <i class="bi bi-key"></i> Forzar cambio en próximo login
                </button>
                <button class="btn btn-sm btn-danger" onclick="restablecerContrasena()">
                    <i class="bi bi-arrow-clockwise"></i> Restablecer contraseña
                </button>
            </div>
            <?php else: ?>
            <p class="text-muted mb-2">Este socio no tiene un usuario asociado. Al crear un usuario podrás gestionar su contraseña.</p>
            <a href="<?= BASE_URL ?>/usuario/registrar" class="btn btn-sm btn-primary">
                <i class="bi bi-person-plus"></i> Crear usuario
            </a>
            <?php endif; ?>
            <div id="passMsg" class="mt-2 small"></div>
        </div>
    </div>
</div>

<script>
function forzarCambio() {
    if (!confirm('¿Forzar cambio de contrasena en el proximo login del socio?')) return;
    var msg = document.getElementById('passMsg');
    msg.innerHTML = '<span class="text-muted">Procesando...</span>';
    var formData = new FormData();
    formData.append('csrf_token', '<?= $csrfToken ?? '' ?>');
    fetch('<?= BASE_URL ?>/socio/forzarCambioContrasena/<?= $socio['id_socio'] ?>', {
        method: 'POST', body: formData
    }).then(function(r) { return r.json(); }).then(function(d) {
        if (d.error) { msg.innerHTML = '<span class="text-danger">' + d.error + '</span>'; }
        else { msg.innerHTML = '<span class="text-success">' + d.mensaje + '</span>'; }
    }).catch(function() { msg.innerHTML = '<span class="text-danger">Error de red</span>'; });
}
function restablecerContrasena() {
    if (!confirm('Se generara una contrasena temporal y se enviara al correo del socio. ¿Continuar?')) return;
    var msg = document.getElementById('passMsg');
    msg.innerHTML = '<span class="text-muted">Generando y enviando...</span>';
    var formData = new FormData();
    formData.append('csrf_token', '<?= $csrfToken ?? '' ?>');
    fetch('<?= BASE_URL ?>/socio/restablecerContrasena/<?= $socio['id_socio'] ?>', {
        method: 'POST', body: formData
    }).then(function(r) { return r.json(); }).then(function(d) {
        if (d.error) { msg.innerHTML = '<span class="text-danger">' + d.error + '</span>'; }
        else { msg.innerHTML = '<span class="text-success">' + d.mensaje + '</span>'; }
    }).catch(function() { msg.innerHTML = '<span class="text-danger">Error de red</span>'; });
}
</script>
