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
                <tr>
                    <td data-label="Fecha"><?= $h['fecha_registro'] ?></td>
                    <td data-label="Tipo"><span class="badge bg-info"><?= ucfirst(str_replace('_', ' ', $h['tipo_operación'])) ?></span></td>
                    <td data-label="Monto"><strong>$<?= number_format($h['monto'], 2) ?></strong></td>
                    <td data-label="Saldo ant.">$<?= number_format($h['saldo_anterior'], 2) ?></td>
                    <td data-label="Saldo post.">$<?= number_format($h['saldo_posterior'], 2) ?></td>
                </tr>
                <?php endforeach; ?>
                </tbody>
            </table></div>
        </div>
    </div>
    <?php endif; ?>
</div>
