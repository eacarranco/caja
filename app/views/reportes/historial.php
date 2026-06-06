<div class="container-fluid">
    <h4>Historial de operaciones (auditoría)</h4>

    <div class="card card-dashboard">
        <div class="card-body p-0">
            <div class="table-responsive"><table class="table table-sm table-hover mb-0">
                <thead class="table-light">
                    <tr><th>Fecha</th><th>Socio</th><th>Tipo</th><th>Monto</th><th>Saldo ant.</th><th>Saldo post.</th><th>Referencia</th></tr>
                </thead>
                <tbody>
                <?php foreach ($historial as $h): ?>
                <tr>
                    <td><?= $h['fecha_registro'] ?></td>
                    <td><?= htmlspecialchars($h['socio']) ?></td>
                    <td><span class="badge bg-info"><?= ucfirst(str_replace('_', ' ', $h['tipo_operación'])) ?></span></td>
                    <td>$<?= number_format($h['monto'], 2) ?></td>
                    <td>$<?= number_format($h['saldo_anterior'], 2) ?></td>
                    <td>$<?= number_format($h['saldo_posterior'], 2) ?></td>
                    <td><small class="text-muted"><?= substr($h['id_referencia'] ?? '', 0, 8) ?>...</small></td>
                </tr>
                <?php endforeach; ?>
                </tbody>
            </table></div>
        </div>
    </div>

    <?php if ($totalPaginas > 1): ?>
    <nav class="mt-3"><ul class="pagination pagination-sm">
        <?php for ($i = 1; $i <= $totalPaginas; $i++): ?>
        <li class="page-item <?= $i === $page ? 'active' : '' ?>"><a class="page-link" href="?p=<?= $i ?>"><?= $i ?></a></li>
        <?php endfor; ?>
    </ul></nav>
    <?php endif; ?>
</div>
