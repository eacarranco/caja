<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4>Cobros</h4>
        <?php if ($sesionAbierta): ?>
        <a href="<?= BASE_URL ?>/cobro/registrar/<?= $sesionAbierta ?>" class="btn btn-primary"><i class="bi bi-cash-coin"></i> Nuevo cobro</a>
        <?php else: ?>
        <a href="<?= BASE_URL ?>/sesion/abrir" class="btn btn-primary"><i class="bi bi-cash-coin"></i> Nuevo cobro</a>
        <?php endif; ?>
    </div>

    <div class="card card-dashboard">
        <div class="card-body p-0">
            <div class="table-responsive"><table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Fecha</th>
                        <th>Sesión</th>
                        <th>Socio</th>
                        <th>Tipo</th>
                        <th>Monto</th>
                        <th>Medio</th>
                        <th>Estado</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($cobros as $c): ?>
                    <tr class="<?= $c['anulado'] ? 'table-danger text-muted' : '' ?>">
                        <td><?= $c['fecha_registro'] ?></td>
                        <td>#<?= $c['numero_sesion'] ?> (<?= $c['fecha_sesion'] ?>)</td>
                        <td><?= htmlspecialchars($c['socio']) ?></td>
                        <td><span class="badge bg-info"><?= $tiposCobro[$c['tipo']] ?? $c['tipo'] ?></span></td>
                        <td><strong class="<?= $c['anulado'] ? 'text-danger' : 'text-success' ?>"><?= $c['anulado'] ? '-' : '+' ?>$<?= number_format($c['monto'], 2) ?></strong></td>
                        <td><?= $mediosPago[$c['medio_pago']] ?? $c['medio_pago'] ?></td>
                        <td>
                            <?php if ($c['anulado']): ?>
                            <span class="badge bg-danger">Anulado</span>
                            <?php else: ?>
                            <span class="badge bg-success">Activo</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <a href="<?= BASE_URL ?>/documento/comprobante/<?= $c['id_cobro'] ?>" class="btn btn-sm btn-outline-info" title="Comprobante"><i class="bi bi-file-earmark-pdf"></i></a>
                            <?php if (!$c['anulado']): ?>
                            <a href="#" onclick="anularCobro('<?= $c['id_cobro'] ?>')" class="btn btn-sm btn-outline-danger" title="Anular"><i class="bi bi-x-circle"></i></a>
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
            <a class="page-link" href="?p=<?= $i ?>"><?= $i ?></a>
        </li>
        <?php endfor; ?>
    </ul></nav>
    <?php endif; ?>
</div>

<script>
function anularCobro(id) {
    var motivo = prompt('Motivo de anulación:');
    if (!motivo) return;
    fetch('<?= BASE_URL ?>/cobro/anular/' + id, {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: 'csrf_token=<?= CSRFMiddleware::generarToken() ?>&motivo=' + encodeURIComponent(motivo)
    }).then(function(r) { return r.json(); }).then(function(d) {
        if (d.error) { alert(d.error); } else { location.reload(); }
    });
}
</script>
