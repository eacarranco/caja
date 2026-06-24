<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4><?= $titulo ?></h4>
        <a href="<?= BASE_URL ?>/sesion/listar" class="btn btn-outline-secondary"><i class="bi bi-arrow-left"></i> Volver</a>
    </div>

    <div class="card card-dashboard">
        <div class="card-body">
            <form method="POST">
                <?= CSRFMiddleware::campoHTML() ?>
                <div class="row g-3">
                    <div class="col-md-3">
                        <label class="form-label">Numero de sesion</label>
                        <input type="text" class="form-control" value="#<?= $sesion['numero_sesion'] ?>" disabled>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Fecha de la sesion *</label>
                        <input type="date" name="fecha_sesion" class="form-control <?= isset($errors['fecha_sesion']) ? 'is-invalid' : '' ?>" value="<?= htmlspecialchars(date('Y-m-d', strtotime($sesion['fecha_sesion'] ?? date('Y-m-d')))) ?>" required>
                        <div class="invalid-feedback"><?= $errors['fecha_sesion'] ?? '' ?></div>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Hora</label>
                        <input type="time" name="hora_sesion" class="form-control" value="<?= htmlspecialchars(date('H:i', strtotime($sesion['fecha_sesion'] ?? '19:00'))) ?>">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Titulo *</label>
                        <input type="text" name="titulo" class="form-control <?= isset($errors['titulo']) ? 'is-invalid' : '' ?>" value="<?= htmlspecialchars($sesion['titulo'] ?? '') ?>" required>
                        <div class="invalid-feedback"><?= $errors['titulo'] ?? '' ?></div>
                    </div>
                </div>
                <div class="mt-3">
                    <span class="badge <?= $sesion['estado'] === 'abierta' ? 'bg-success' : 'bg-secondary' ?>"><?= $sesion['estado'] === 'abierta' ? 'Abierta' : 'Cerrada' ?></span>
                </div>
                <button type="submit" class="btn btn-primary mt-3"><i class="bi bi-save"></i> Guardar cambios</button>
            </form>
        </div>
    </div>
</div>
