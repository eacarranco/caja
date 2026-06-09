<div class="container-fluid">
    <h4>Inicio</h4>

    <?php if (!$socio): ?>
    <div class="alert alert-info">No se encontro un socio asociado a tu cedula. Contacta al administrador.</div>
    <?php return; endif; ?>

    <div class="row g-3 mb-3">
        <div class="col-md-4">
            <div class="card card-dashboard text-center h-100">
                <div class="card-body py-4">
                    <div class="fs-1 text-primary mb-2"><i class="bi bi-piggy-bank"></i></div>
                    <h5 class="mb-1">Cuenta de Ahorro</h5>
                    <h3 class="text-primary mb-2">$ <?= number_format(floatval($cuenta['saldo_obligatorio'] ?? 0) + floatval($cuenta['saldo_excedente'] ?? 0), 2) ?></h3>
                    <a href="<?= BASE_URL ?>/portal/detalleAhorro" class="btn btn-outline-primary"><i class="bi bi-list-ul"></i> Detalle</a>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card card-dashboard text-center h-100">
                <div class="card-body py-4">
                    <div class="fs-1 text-success mb-2"><i class="bi bi-wallet2"></i></div>
                    <h5 class="mb-1">Capital de Inversion</h5>
                    <h3 class="text-success mb-2">$ <?= number_format($capital_inversion, 2) ?></h3>
                    <a href="<?= BASE_URL ?>/portal/detalleCapitalInversion" class="btn btn-outline-success"><i class="bi bi-list-ul"></i> Detalle</a>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card card-dashboard text-center h-100">
                <div class="card-body py-4">
                    <div class="fs-1 text-danger mb-2"><i class="bi bi-credit-card-2-front"></i></div>
                    <h5 class="mb-1">Valores a Pagar</h5>
                    <h3 class="text-danger mb-2">$ <?= number_format($valores_pagar, 2) ?></h3>
                    <a href="<?= BASE_URL ?>/portal/pagar" class="btn btn-outline-danger"><i class="bi bi-list-ul"></i> Detalle</a>
                </div>
            </div>
        </div>
    </div>

    <div class="mt-3">
        <a href="<?= BASE_URL ?>/portal/historial" class="btn btn-outline-primary btn-sm"><i class="bi bi-clock-history"></i> Historial</a>
        <a href="<?= BASE_URL ?>/portal/inversion" class="btn btn-outline-success btn-sm"><i class="bi bi-wallet2"></i> Inversion</a>
        <a href="<?= BASE_URL ?>/portal/multas" class="btn btn-outline-warning btn-sm"><i class="bi bi-exclamation-triangle"></i> Multas</a>
        <a href="<?= BASE_URL ?>/portal/asistencias" class="btn btn-outline-secondary btn-sm"><i class="bi bi-calendar-check"></i> Asistencias</a>
        <a href="<?= BASE_URL ?>/portal/notificaciones" class="btn btn-outline-info btn-sm"><i class="bi bi-bell"></i> Notificaciones</a>
        <a href="<?= BASE_URL ?>/portal/password" class="btn btn-outline-danger btn-sm"><i class="bi bi-key"></i> Contrasena</a>
    </div>
</div>
