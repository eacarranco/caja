<div class="container-fluid">
    <h4>Resumen de pagos</h4>

    <div class="row g-3">
        <?php if ($pendientes['aporte_obligatorio_mensual'] > 0): ?>
        <div class="col-md-3">
            <div class="card card-dashboard text-center">
                <div class="card-body">
                    <div class="fs-1 text-primary mb-2"><i class="bi bi-piggy-bank"></i></div>
                    <h6>Aporte obligatorio</h6>
                    <h3 class="text-primary">$ <?= number_format($pendientes['aporte_obligatorio_mensual'], 2) ?></h3>
                    <button class="btn btn-primary btn-sm mt-3" type="button" data-bs-toggle="modal" data-bs-target="#detallePagoModal">
                        Detalle
                    </button>
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

    <!-- Modal detalle de pago -->
    <div class="modal fade" id="detallePagoModal" tabindex="-1" aria-labelledby="detallePagoModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="detallePagoModalLabel">Detalle del pago obligatorio</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>
                <div class="modal-body">
                    <p class="text-muted">Este detalle corresponde al pago de la siguiente cuota mensual del crédito, el valor de ahorro obligatorio, deudas pendientes y multas.</p>
                    <div class="table-responsive">
                        <table class="table table-borderless">
                            <tbody>
                                <tr>
                                    <td class="text-muted">Siguiente cuota del crédito</td>
                                    <td class="text-end fw-semibold">$ <?= number_format($pendientes['siguiente_cuota'] ?? 0, 2) ?></td>
                                </tr>
                                <?php if (!empty($pendientes['siguiente_cuota_fecha'])): ?>
                                <tr>
                                    <td class="text-muted">Fecha de vencimiento</td>
                                    <td class="text-end"><?= htmlspecialchars($pendientes['siguiente_cuota_fecha']) ?></td>
                                </tr>
                                <?php endif; ?>
                                <tr>
                                    <td class="text-muted">Ahorro obligatorio</td>
                                    <td class="text-end fw-semibold">$ <?= number_format($pendientes['saldo_obligatorio'] ?? 0, 2) ?></td>
                                </tr>
                                <tr>
                                    <td class="text-muted">Deudas pendientes</td>
                                    <td class="text-end fw-semibold">$ <?= number_format($pendientes['cuotas_credito'] ?? 0, 2) ?></td>
                                </tr>
                                <tr>
                                    <td class="text-muted">Multas pendientes</td>
                                    <td class="text-end fw-semibold text-danger">$ <?= number_format($pendientes['multas'] ?? 0, 2) ?></td>
                                </tr>
                                <tr class="border-top">
                                    <td class="fw-bold">Total a pagar</td>
                                    <td class="text-end fw-bold">$ <?= number_format($pendientes['aporte_obligatorio_mensual'], 2) ?></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cerrar</button>
                    <a href="<?= BASE_URL ?>/portal/pagar" class="btn btn-primary">Ir a pagar</a>
                </div>
            </div>
        </div>
    </div>
</div>
