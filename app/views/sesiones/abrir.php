<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4>Abrir nueva sesión mensual</h4>
        <a href="<?= BASE_URL ?>/sesion/listar" class="btn btn-outline-secondary"><i class="bi bi-arrow-left"></i> Volver</a>
    </div>

    <div class="card card-dashboard">
        <div class="card-body">
            <?php if (isset($errors['general'])): ?>
            <div class="alert alert-danger"><?= $errors['general'] ?></div>
            <?php endif; ?>

            <form method="POST">
                <?= CSRFMiddleware::campoHTML() ?>
                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label">Fecha de la sesion *</label>
                        <input type="date" name="fecha_sesion" class="form-control" value="<?= date('Y-m-d') ?>" required>
                        <small class="text-muted">Las obligaciones se calculan con corte a esta fecha.</small>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Hora</label>
                        <input type="time" name="hora_sesion" class="form-control" value="19:00">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Titulo *</label>
                        <input type="text" name="titulo" class="form-control <?= isset($errors['titulo']) ? 'is-invalid' : '' ?>" placeholder="Ej: Sesion Ordinaria Junio 2026" required>
                        <?php if (isset($errors['titulo'])): ?><div class="invalid-feedback"><?= $errors['titulo'] ?></div><?php endif; ?>
                    </div>
                </div>
                <button type="submit" class="btn btn-primary mt-3"><i class="bi bi-play-circle"></i> Abrir sesion</button>
            </form>
        </div>
    </div>
</div>
