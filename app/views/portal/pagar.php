<div class="container-fluid">
    <h4>Resumen de pagos</h4>

    <div class="row g-3">
        <?php if ($pendientes['aporte_obligatorio'] > 0): ?>
        <div class="col-md-3">
            <div class="card card-dashboard text-center">
                <div class="card-body">
                    <div class="fs-1 text-primary mb-2"><i class="bi bi-piggy-bank"></i></div>
                    <h6>Aporte obligatorio</h6>
                    <h3 class="text-primary">$ <?= number_format($pendientes['aporte_obligatorio'], 2) ?></h3>
                    <small class="text-muted">Saldo acumulado</small>
                </div>
            </div>
        </div>
        <?php endif; ?>
        <?php if ($pendientes['aporte_excedente'] > 0): ?>
        <div class="col-md-3">
            <div class="card card-dashboard text-center">
                <div class="card-body">
                    <div class="fs-1 text-success mb-2"><i class="bi bi-graph-up-arrow"></i></div>
                    <h6>Aporte excedente</h6>
                    <h3 class="text-success">$ <?= number_format($pendientes['aporte_excedente'], 2) ?></h3>
                    <small class="text-muted">Saldo acumulado</small>
                </div>
            </div>
        </div>
        <?php endif; ?>
        <?php if ($pendientes['cuotas_credito'] > 0): ?>
        <div class="col-md-3">
            <div class="card card-dashboard text-center">
                <div class="card-body">
                    <div class="fs-1 text-warning mb-2"><i class="bi bi-bank"></i></div>
                    <h6>Cuota crédito</h6>
                    <h3 class="text-warning">$ <?= number_format($pendientes['cuotas_credito'], 2) ?></h3>
                    <small class="text-muted">Pendiente de pago</small>
                </div>
            </div>
        </div>
        <?php endif; ?>
        <?php if ($pendientes['multas'] > 0): ?>
        <div class="col-md-3">
            <div class="card card-dashboard text-center">
                <div class="card-body">
                    <div class="fs-1 text-danger mb-2"><i class="bi bi-exclamation-triangle"></i></div>
                    <h6>Multas</h6>
                    <h3 class="text-danger">$ <?= number_format($pendientes['multas'], 2) ?></h3>
                    <small class="text-muted">Pendientes de pago</small>
                </div>
            </div>
        </div>
        <?php endif; ?>
    </div>

    <div class="text-center mt-4">
        <a href="<?= BASE_URL ?>/portal" class="btn btn-outline-primary"><i class="bi bi-arrow-left"></i> Volver a Inicio</a>
    </div>
</div>
