<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4>Crédito #<?= substr($credito['id_crédito'], 0, 8) ?></h4>
        <div>
            <?php if ($credito['estado'] === 'pendiente'): ?>
            <button class="btn btn-success" onclick="aprobar('<?= $credito['id_crédito'] ?>')"><i class="bi bi-check-lg"></i> Aprobar</button>
            <button class="btn btn-danger" onclick="rechazar('<?= $credito['id_crédito'] ?>')"><i class="bi bi-x-lg"></i> Rechazar</button>
            <?php elseif ($credito['estado'] === 'aprobado'): ?>
            <button class="btn btn-primary" onclick="desembolsar('<?= $credito['id_crédito'] ?>')"><i class="bi bi-cash"></i> Desembolsar</button>
            <?php endif; ?>
            <a href="<?= BASE_URL ?>/credito/listar" class="btn btn-outline-secondary"><i class="bi bi-arrow-left"></i> Volver</a>
        </div>
    </div>

    <div class="row g-3 mb-3">
        <div class="col-md-4">
            <div class="card card-dashboard"><div class="card-body">
                <small class="text-muted">Socio</small>
                <p class="mb-0"><strong><?= htmlspecialchars($credito['socio']) ?></strong><br><?= $credito['cédula'] ?></p>
            </div></div>
        </div>
        <div class="col-md-2">
            <div class="card card-dashboard"><div class="card-body">
                <small class="text-muted">Producto</small>
                <p class="mb-0"><?= htmlspecialchars($credito['producto']) ?></p>
            </div></div>
        </div>
        <div class="col-md-2">
            <div class="card card-dashboard"><div class="card-body">
                <small class="text-muted">Solicitado</small>
                <p class="mb-0"><strong>$<?= number_format($credito['monto_solicitado'], 2) ?></strong></p>
            </div></div>
        </div>
        <div class="col-md-2">
            <div class="card card-dashboard"><div class="card-body">
                <small class="text-muted">Aprobado</small>
                <p class="mb-0"><strong>$<?= number_format($credito['monto_aprobado'] ?? 0, 2) ?></strong></p>
            </div></div>
        </div>
        <div class="col-md-2">
            <div class="card card-dashboard"><div class="card-body">
                <small class="text-muted">Estado</small>
                <p class="mb-0"><span class="badge bg-<?= match($credito['estado']) { 'pendiente'=>'warning', 'aprobado'=>'success', 'desembolsado'=>'primary', 'rechazado'=>'danger', default=>'secondary' } ?>"><?= ucfirst($credito['estado']) ?></span></p>
            </div></div>
        </div>
    </div>

    <?php if (!empty($garantes)): ?>
    <div class="card card-dashboard mb-3">
        <div class="card-body">
            <h6>Garantes</h6>
            <div class="table-responsive"><table class="table table-sm mb-0">
                <thead><tr><th>Socio</th><th>Cédula</th><th>Tipo</th><th>Monto garantizado</th></tr></thead>
                <tbody>
                <?php foreach ($garantes as $g): ?>
                <tr>
                    <td><?= htmlspecialchars($g['nombre']) ?></td>
                    <td><?= $g['cédula'] ?></td>
                    <td><?= str_replace('_', ' ', $g['tipo_garante']) ?></td>
                    <td>$<?= number_format($g['monto_garantizado'], 2) ?></td>
                </tr>
                <?php endforeach; ?>
                </tbody>
            </table></div>
        </div>
    </div>
    <?php endif; ?>

    <?php if (!empty($amortizaciones)): ?>
    <div class="card card-dashboard">
        <div class="card-body p-0">
            <div class="table-responsive"><table class="table table-sm table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th>#</th>
                        <th>Vencimiento</th>
                        <th class="text-end">Capital</th>
                        <th class="text-end">Interés</th>
                        <th class="text-end">Cuota</th>
                        <th class="text-end">Saldo</th>
                        <th>Estado</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($amortizaciones as $a): ?>
                    <tr class="<?= $a['estado'] === 'pagada' ? 'table-success' : ($a['estado'] === 'vencida' ? 'table-danger' : '') ?>">
                        <td><?= $a['número_cuota'] ?></td>
                        <td><?= $a['fecha_vencimiento'] ?></td>
                        <td class="text-end">$<?= number_format($a['capital'], 2) ?></td>
                        <td class="text-end">$<?= number_format($a['interés'], 2) ?></td>
                        <td class="text-end"><strong>$<?= number_format($a['total'], 2) ?></strong></td>
                        <td class="text-end">$<?= number_format($a['saldo_restante'], 2) ?></td>
                        <td><?= ucfirst($a['estado']) ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table></div>
        </div>
    </div>
    <?php endif; ?>
</div>

<script>
function aprobar(id) {
    var monto = prompt('Monto a aprobar ($):', '<?= $credito['monto_solicitado'] ?>');
    if (!monto) return;
    fetch('<?= BASE_URL ?>/credito/aprobar/' + id, {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: 'csrf_token=<?= CSRFMiddleware::generarToken() ?>&monto_aprobado=' + encodeURIComponent(monto)
    }).then(function(r) { return r.json(); }).then(function(d) {
        if (d.error) { alert(d.error); } else { location.reload(); }
    });
}

function rechazar(id) {
    if (!confirm('Ã‚¿Rechazar este crédito?')) return;
    fetch('<?= BASE_URL ?>/credito/rechazar/' + id, {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: 'csrf_token=<?= CSRFMiddleware::generarToken() ?>'
    }).then(function(r) { return r.json(); }).then(function(d) {
        if (d.error) { alert(d.error); } else { location.reload(); }
    });
}

function desembolsar(id) {
    if (!confirm('Ã‚¿Confirmar desembolso de este crédito?')) return;
    fetch('<?= BASE_URL ?>/credito/desembolsar/' + id, {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: 'csrf_token=<?= CSRFMiddleware::generarToken() ?>'
    }).then(function(r) { return r.json(); }).then(function(d) {
        if (d.error) { alert(d.error); } else { location.reload(); }
    });
}
</script>
