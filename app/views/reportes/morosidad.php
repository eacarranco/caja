<div class="container-fluid">
    <h4>Reporte de morosidad</h4>
    <a href="<?= BASE_URL ?>/reporte/listar" class="btn btn-sm btn-outline-secondary mb-3"><i class="bi bi-arrow-left"></i> Volver</a>

    <?php if (empty($cuotas)): ?>
    <div class="card card-dashboard"><div class="card-body text-muted">No hay cuotas vencidas</div></div>
    <?php else: ?>
    <div class="alert alert-warning"><strong><?= count($cuotas) ?></strong> cuota(s) vencida(s) por un total de <strong>$<?= number_format($totalMoroso, 2) ?></strong> — <strong><?= count($sociosMorosos) ?></strong> socio(s) en mora</div>

    <div class="card card-dashboard">
        <div class="card-body p-0">
            <div class="table-responsive"><table class="table table-sm table-hover mb-0">
                <thead class="table-light">
                    <tr><th>Socio</th><th>Cédula</th><th># Cuota</th><th>Vencimiento</th><th>Capital</th><th>Interés</th><th>Total</th><th>Días vencido</th></tr>
                </thead>
                <tbody>
                <?php foreach ($cuotas as $a): ?>
                <?php $diasVencido = (new DateTime())->diff(new DateTime($a['fecha_vencimiento']))->days ?>
                <tr class="<?= $diasVencido > 90 ? 'table-danger' : ($diasVencido > 30 ? 'table-warning' : '') ?>">
                    <td><?= htmlspecialchars($a['socio']) ?></td>
                    <td><?= $a['cédula'] ?></td>
                    <td><?= $a['número_cuota'] ?></td>
                    <td><?= $a['fecha_vencimiento'] ?></td>
                    <td class="text-end">$<?= number_format($a['capital'], 2) ?></td>
                    <td class="text-end">$<?= number_format($a['interés'], 2) ?></td>
                    <td class="text-end"><strong>$<?= number_format($a['total'], 2) ?></strong></td>
                    <td><span class="badge bg-<?= $diasVencido > 90 ? 'danger' : ($diasVencido > 30 ? 'warning' : 'secondary') ?>"><?= $diasVencido ?> días</span></td>
                </tr>
                <?php endforeach; ?>
                </tbody>
            </table></div>
        </div>
    </div>
    <?php endif; ?>
</div>
