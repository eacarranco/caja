<div class="container-fluid">
    <h4>Historial de operaciones</h4>
    <a href="<?= BASE_URL ?>/portal" class="btn btn-sm btn-outline-secondary mb-3"><i class="bi bi-arrow-left"></i> Volver al portal</a>

    <?php if (empty($historial)): ?>
    <div class="card card-dashboard"><div class="card-body text-muted">Sin operaciones registradas</div></div>
    <?php else: ?>
    <div class="card card-dashboard">
        <div class="card-body p-0">
            <div class="table-responsive"><table class="table table-hover mb-0 table-responsive-stack">
                <thead class="table-light">
                    <tr><th>Fecha</th><th>Tipo</th><th>Monto</th><th>Saldo anterior</th><th>Saldo posterior</th></tr>
                </thead>
                <tbody>
                <?php foreach ($historial as $h): ?>
                <?php
                    $monto = floatval($h['monto'] ?? 0);
                    $negativo = in_array($h['tipo_operacion'], ['retiro_ahorro', 'inversion_apertura']);
                    if ($negativo) {
                        $monto = -abs($monto);
                    }
                    $montoClass = $monto < 0 ? 'text-danger' : 'text-dark';
                ?>
                <tr>
                    <td data-label="Fecha"><?= htmlspecialchars($h['fecha_registro']) ?></td>
                    <td data-label="Tipo"><span class="badge bg-info"><?= htmlspecialchars(ucfirst(str_replace('_', ' ', $h['tipo_operacion']))) ?></span></td>
                    <td data-label="Monto"><strong class="<?= $montoClass ?>"><?= $monto < 0 ? '- ' : '' ?>$<?= number_format(abs($monto), 2) ?></strong></td>
                    <td data-label="Saldo ant.">$<?= number_format((float)($h['saldo_anterior'] ?? 0), 2) ?></td>
                    <td data-label="Saldo post.">$<?= number_format((float)($h['saldo_posterior'] ?? 0), 2) ?></td>
                </tr>
                <?php endforeach; ?>
                </tbody>
            </table></div>
        </div>
    </div>
    <?php endif; ?>
</div>
