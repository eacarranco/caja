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
                        <label class="form-label">Fecha *</label>
                        <input type="date" name="fecha" class="form-control" value="<?= date('Y-m-d') ?>" required>
                    </div>
                    <div class="col-md-8">
                        <label class="form-label">Título (opcional)</label>
                        <input type="text" name="título" class="form-control" placeholder="Ej: Sesión Ordinaria Junio 2026">
                    </div>
                </div>
                <button type="submit" class="btn btn-primary mt-3"><i class="bi bi-play-circle"></i> Abrir sesión</button>
            </form>
        </div>
    </div>
</div>
