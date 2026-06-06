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
                        <input type="text" name="direccion" class="form-control" value="<?= htmlspecialchars($socio['dirección']) ?>">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Teléfono</label>
                        <input type="text" name="telefono" class="form-control" value="<?= htmlspecialchars($socio['teléfono'] ?? '') ?>">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Celular</label>
                        <input type="text" name="celular" class="form-control" value="<?= htmlspecialchars($socio['celular']) ?>">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Profesión</label>
                        <input type="text" name="profesion" class="form-control" value="<?= htmlspecialchars($socio['profesión'] ?? '') ?>">
                    </div>
                </div>
                <div class="mt-4">
                    <button type="submit" class="btn btn-primary"><i class="bi bi-save"></i> Guardar cambios</button>
                </div>
            </form>
        </div>
    </div>
</div>
