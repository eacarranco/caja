<div class="container-fluid">
    <h4>Valores pendientes de pago</h4>

    <?php if (empty($obligaciones)): ?>
    <div class="alert alert-success"><i class="bi bi-check-circle"></i> No tienes valores pendientes de pago.</div>
    <?php else: ?>
    <div class="card mb-3">
        <div class="card-body text-center">
            <h5>Total pendiente: <strong class="text-danger">$ <?= number_format($totalPendiente, 2) ?></strong></h5>
            <p class="text-muted mb-0">Los pagos deben realizarse en la proxima sesion por el tesorero.</p>
        </div>
    </div>

    <div class="card">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-sm mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Concepto</th>
                            <th class="text-end">Monto</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($obligaciones as $o): ?>
                        <tr>
                            <td><?= htmlspecialchars($o['concepto']) ?></td>
                            <td class="text-end text-danger">$<?= number_format($o['monto'], 2) ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <?php endif; ?>
</div>
