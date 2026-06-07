<div class="container-fluid">
    <h4>Multas</h4>

    <?php if (!$esSocio): ?>
    <form method="GET" class="row g-2 mb-3">
        <div class="col-auto">
            <select name="tipo" class="form-select form-select-sm">
                <option value="">Todos los tipos</option>
                <option value="retraso_10min" <?= $filtroTipo === 'retraso_10min' ? 'selected' : '' ?>>Retraso 10 min</option>
                <option value="retraso_30min" <?= $filtroTipo === 'retraso_30min' ? 'selected' : '' ?>>Retraso 30 min</option>
                <option value="inasistencia" <?= $filtroTipo === 'inasistencia' ? 'selected' : '' ?>>Inasistencia</option>
                <option value="mora_crédito" <?= $filtroTipo === 'mora_crédito' ? 'selected' : '' ?>>Mora crédito</option>
            </select>
        </div>
        <div class="col-auto">
            <select name="pagada" class="form-select form-select-sm">
                <option value="">Pagada/No pagada</option>
                <option value="0" <?= $filtroPagada === '0' ? 'selected' : '' ?>>No pagada</option>
                <option value="1" <?= $filtroPagada === '1' ? 'selected' : '' ?>>Pagada</option>
            </select>
        </div>
        <div class="col-auto">
            <input type="text" name="socio" class="form-control form-control-sm" placeholder="Buscar socio..." value="<?= htmlspecialchars($filtroSocio) ?>">
        </div>
        <div class="col-auto">
            <button type="submit" class="btn btn-sm btn-primary"><i class="bi bi-filter"></i> Filtrar</button>
            <a href="<?= BASE_URL ?>/multa/listar" class="btn btn-sm btn-outline-secondary"><i class="bi bi-x-circle"></i></a>
        </div>
    </form>
    <?php endif; ?>

    <div class="card card-dashboard">
        <div class="card-body p-0">
            <div class="table-responsive"><table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr><th>Fecha</th><?php if (!$esSocio): ?><th>Socio</th><?php endif; ?><th>Tipo</th><th>Monto</th><th>Justificación</th><th>Pagada</th><th></th></tr>
                </thead>
                <tbody>
                    <?php foreach ($multas as $m): ?>
                    <tr>
                        <td><?= $m['fecha_generación'] ?></td>
                        <?php if (!$esSocio): ?>
                        <td><?= htmlspecialchars($m['socio']) ?></td>
                        <?php endif; ?>
                        <td><span class="badge bg-<?= $m['tipo'] === 'inasistencia' ? 'danger' : ($m['tipo'] === 'mora_crédito' ? 'warning' : 'info') ?>"><?= str_replace('_', ' ', $m['tipo']) ?></span></td>
                        <td><strong>$<?= number_format($m['monto'], 2) ?></strong></td>
                        <td>
                            <?php if ($m['justificación']): ?>
                            <span class="badge bg-success">Sí</span>
                            <?php if ($m['justificación_aprobada'] === '1'): ?><span class="badge bg-primary">Aprobada</span>
                            <?php elseif ($m['justificación_aprobada'] === '0'): ?><span class="badge bg-danger">Rechazada</span>
                            <?php else: ?><span class="badge bg-warning">Pendiente</span><?php endif; ?>
                            <?php else: ?>
                            <span class="badge bg-secondary">No</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if ($m['pagada']): ?><span class="badge bg-success">Pagada</span>
                            <?php else: ?><span class="badge bg-danger">Pendiente</span><?php endif; ?>
                        </td>
                        <td>
                            <a href="<?= BASE_URL ?>/multa/ver/<?= $m['id_multa'] ?>" class="btn btn-sm btn-outline-info"><i class="bi bi-eye"></i></a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table></div>
        </div>
    </div>

    <?php if ($totalPaginas > 1): ?>
    <nav class="mt-3"><ul class="pagination pagination-sm">
        <?php for ($i = 1; $i <= $totalPaginas; $i++): ?>
        <li class="page-item <?= $i === $page ? 'active' : '' ?>">
            <a class="page-link" href="?p=<?= $i ?>&tipo=<?= urlencode($filtroTipo) ?>&pagada=<?= urlencode($filtroPagada) ?>&socio=<?= urlencode($filtroSocio) ?>"><?= $i ?></a>
        </li>
        <?php endfor; ?>
    </ul></nav>
    <?php endif; ?>
</div>
