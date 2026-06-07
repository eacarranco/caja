<div class="container-fluid">
    <h4>Inicio</h4>

    <?php if (!$socio): ?>
    <div class="alert alert-info">No se encontró un socio asociado a tu cedula. Contacta al administrador.</div>
    <?php return; endif; ?>

    <div class="row g-3 mb-3">
        <div class="col-md-3">
            <div class="card card-dashboard text-center">
                <div class="card-body py-3">
                    <div class="fs-3 text-primary mb-1"><i class="bi bi-piggy-bank"></i></div>
                    <h6 class="mb-1">Capital Ahorro</h6>
                    <h4 class="text-primary mb-0">$ <?= number_format($pendientes['aporte_obligatorio_mensual'] ?? $pendientes['aporte_obligatorio'] ?? 0, 2) ?></h4>
                    <a href="<?= BASE_URL ?>/portal/detalleAhorro" class="btn btn btn-primary">Detalles</a>
                </div>
            </div>
        </div>
        <?php if ($pendientes['aporte_excedente'] > 0): ?>
        <div class="col-md-3">
            <div class="card card-dashboard text-center">
                <div class="card-body py-3">
                    <div class="fs-3 text-success mb-1"><i class="bi bi-graph-up-arrow"></i></div>
                    <h6 class="mb-1">Aporte excedente</h6>
                    <h4 class="text-success mb-0">$ <?= number_format($pendientes['aporte_excedente'], 2) ?></h4>
                </div>
            </div>
        </div>
        <?php endif; ?>
        <?php if ($pendientes['cuotas_credito'] > 0): ?>
        <div class="col-md-3">
            <div class="card card-dashboard text-center">
                <div class="card-body py-3">
                    <div class="fs-3 text-warning mb-1"><i class="bi bi-bank"></i></div>
                    <h6 class="mb-1">Cuota crédito</h6>
                    <h4 class="text-warning mb-0">$ <?= number_format($pendientes['cuotas_credito'], 2) ?></h4>
                </div>
            </div>
        </div>
        <?php endif; ?>
        <?php if ($pendientes['multas'] > 0): ?>
        <div class="col-md-3">
            <div class="card card-dashboard text-center">
                <div class="card-body py-3">
                    <div class="fs-3 text-danger mb-1"><i class="bi bi-exclamation-triangle"></i></div>
                    <h6 class="mb-1">Multas</h6>
                    <h4 class="text-danger mb-0">$ <?= number_format($pendientes['multas'], 2) ?></h4>
                </div>
            </div>
        </div>
        <?php endif; ?>
    </div>


    <div class="mt-3">
        <a href="<?= BASE_URL ?>/portal/historial" class="btn btn-outline-primary btn-sm"><i class="bi bi-clock-history"></i> Historial operaciones</a>
        <a href="<?= BASE_URL ?>/portal/multas" class="btn btn-outline-warning btn-sm"><i class="bi bi-exclamation-triangle"></i> Multas</a>
        <a href="<?= BASE_URL ?>/portal/asistencias" class="btn btn-outline-secondary btn-sm"><i class="bi bi-calendar-check"></i> Asistencias</a>
        <a href="<?= BASE_URL ?>/portal/notificaciones" class="btn btn-outline-info btn-sm"><i class="bi bi-bell"></i> Notificaciones</a>
        <a href="<?= BASE_URL ?>/portal/password" class="btn btn-outline-danger btn-sm"><i class="bi bi-key"></i> Cambiar contrasena</a>
    </div>
</div>
