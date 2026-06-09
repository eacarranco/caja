<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4>Estado de cuenta - Capital Ahorro</h4>
        <a href="<?= BASE_URL ?>/portal" class="btn btn-outline-secondary"><i class="bi bi-arrow-left"></i> Volver</a>
    </div>

    <div class="row g-3 mb-3">
        <div class="col-md-6">
            <div class="card card-dashboard text-center">
                <div class="card-body py-2">
                    <h6 class="text-muted">Saldo obligatorio</h6>
                    <h4 class="text-primary mb-0">$ <?= number_format($saldo_obligatorio, 2) ?></h4>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card card-dashboard text-center">
                <div class="card-body py-2">
                    <h6 class="text-muted">Saldo excedente</h6>
                    <h4 class="text-success mb-0">$ <?= number_format($saldo_excedente, 2) ?></h4>
                </div>
            </div>
        </div>
    </div>

    <?php if (empty($movimientos)): ?>
    <div class="alert alert-info">No se registran movimientos.</div>
    <?php else: ?>
    <div class="card card-dashboard">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-sm mb-0 table-responsive-stack">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Fecha</th>
                            <th>Concepto</th>
                            <th>Monto</th>
                            <th>Saldo anterior</th>
                            <th>Saldo posterior</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($movimientos as $i => $m): ?>
                        <?php
                        $esDebito = in_array($m['tipo_operacion'], [
                            'retiro_ahorro', 'desembolso_credito', 'inversion_apertura',
                            'retiro_capital_inversion', 'pago_cuota', 'pago_multa', 'anulacion'
                        ]);
                        ?>
                        <tr>
                            <td data-label="#"><?= count($movimientos) - $i ?></td>
                            <td data-label="Fecha"><?= date('d/m/Y H:i', strtotime($m['fecha_registro'])) ?></td>
                            <td data-label="Concepto"><?= htmlspecialchars($m['concepto']) ?></td>
                            <td data-label="Monto" class="<?= $esDebito ? 'text-danger' : 'text-success' ?>">
                                <?= $esDebito ? '-' : '+' ?> $<?= number_format($m['monto'], 2) ?>
                            </td>
                            <td data-label="Saldo anterior">$<?= number_format(floatval($m['saldo_anterior'] ?? 0), 2) ?></td>
                            <td data-label="Saldo posterior">$<?= number_format(floatval($m['saldo_posterior'] ?? 0), 2) ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <?php endif; ?>
</div>
