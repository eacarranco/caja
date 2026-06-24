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
                <option value="mora_credito" <?= $filtroTipo === 'mora_credito' ? 'selected' : '' ?>>Mora credito</option>
                <option value="cuota_impaga" <?= $filtroTipo === 'cuota_impaga' ? 'selected' : '' ?>>Cuota impaga</option>
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

    <div class="d-flex justify-content-between align-items-center mb-2 flex-wrap gap-2">
        <div></div>
        <div>
            <?php
            $pendientes = 0;
            foreach ($multas as $m) {
                if (!empty($m['justificacion']) && ($m['justificacion_aprobada'] === '' || $m['justificacion_aprobada'] === null || $m['justificacion_aprobada'] === '0') && $m['estado'] === 'activa') {
                    $pendientes++;
                }
            }
            ?>
            <?php if ($pendientes > 0 && !$esSocio): ?>
            <span class="badge bg-warning text-dark"><?= $pendientes ?> justificacion(es) pendiente(s) de revision</span>
            <?php endif; ?>
        </div>
    </div>

    <div class="card card-dashboard">
        <div class="card-body p-0">
            <div class="table-responsive"><table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr><th>Fecha</th><?php if (!$esSocio): ?><th>Socio</th><?php endif; ?><th>Tipo</th><th>Monto</th><th>Justificación</th><th>Estado</th><th></th></tr>
                </thead>
                <tbody>
                    <?php foreach ($multas as $m): ?>
                    <tr>
                        <td><?= $m['fecha_generacion'] ?></td>
                        <?php if (!$esSocio): ?>
                        <td><?= htmlspecialchars($m['socio']) ?></td>
                        <?php endif; ?>
                        <td><span class="badge bg-<?= $m['tipo'] === 'inasistencia' ? 'danger' : ($m['tipo'] === 'mora_credito' ? 'warning' : ($m['tipo'] === 'cuota_impaga' ? 'dark' : 'info')) ?>"><?= str_replace('_', ' ', $m['tipo']) ?></span></td>
                        <td><strong>$<?= number_format($m['monto'], 2) ?></strong></td>
                        <td>
                            <?php if ($m['justificacion']): ?>
                            <span class="badge bg-success">Sí</span>
                            <?php if ($m['justificacion_aprobada'] === '1'): ?><span class="badge bg-primary">Aprobada</span>
                            <?php elseif ($m['justificacion_aprobada'] === '0'): ?><span class="badge bg-danger">Rechazada</span>
                            <?php else: ?><span class="badge bg-warning">Pendiente</span><?php endif; ?>
                            <?php else: ?>
                            <span class="badge bg-secondary">No</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if ($m['pagada'] > 0): ?><span class="badge bg-success">Pagada</span>
                            <?php elseif ($m['estado'] === 'impugnada'): ?><span class="badge bg-success">Impugnada (sin efecto)</span>
                            <?php elseif ($m['estado'] === 'en_impugnacion'): ?><span class="badge bg-warning text-dark">En impugnación</span>
                            <?php elseif ($m['estado'] === 'anulada'): ?><span class="badge bg-dark">Anulada</span>
                            <?php elseif (!empty($m['justificacion']) && ($m['justificacion_aprobada'] === '' || $m['justificacion_aprobada'] === null)): ?><span class="badge bg-warning text-dark">En revision</span>
                            <?php elseif ($m['justificacion_aprobada'] === '0'): ?><span class="badge bg-danger">Rechazada</span>
                            <?php else: ?><span class="badge bg-danger">Pendiente</span><?php endif; ?>
                        </td>
                        <td>
                            <a href="<?= BASE_URL ?>/multa/ver/<?= $m['id_multa'] ?>" class="btn btn-sm btn-outline-info"><i class="bi bi-eye"></i></a>
                            <?php if ($m['pagada'] == 0 && $m['estado'] === 'activa'): ?>
                            <a href="<?= BASE_URL ?>/multa/ver/<?= $m['id_multa'] ?>" class="btn btn-sm btn-outline-warning" title="Impugnar"><i class="bi bi-shield-exclamation"></i></a>
                            <?php endif; ?>
                            <?php if ($m['pagada'] == 0 && $m['estado'] === 'activa' && $esPresidente): ?>
                            <a href="#" onclick="eliminarMulta('<?= $m['id_multa'] ?>')" class="btn btn-sm btn-outline-danger" title="Eliminar (Presidente)"><i class="bi bi-trash"></i></a>
                            <?php endif; ?>
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
            <a class="page-link" href="?p=<?= $i ?>&tipo=<?= urlencode($filtroTipo) ?>&socio=<?= urlencode($filtroSocio) ?>"><?= $i ?></a>
        </li>
        <?php endfor; ?>
    </ul></nav>
    <?php endif; ?>
</div>
<script>
function eliminarMulta(id) {
    if (!confirm('¿Eliminar esta multa definitivamente? Esta acción no se puede deshacer.')) return;
    fetch('<?= BASE_URL ?>/multa/eliminar/' + id, {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: 'csrf_token=<?= CSRFMiddleware::generarToken() ?>'
    }).then(function(r) { return r.json(); }).then(function(d) {
        if (d.error) { mostrarNotificacion('error','Error',d.error,false); } else { location.reload(); }
    });
}
</script>
