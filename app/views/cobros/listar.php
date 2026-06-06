<div class="container-fluid">
    <h4>Cobros</h4>

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
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($cobros as $c): ?>
                    <tr>
                        <td><?= $c['fecha_registro'] ?></td>
                        <td>#<?= $c['número_sesión'] ?> (<?= $c['fecha_sesión'] ?>)</td>
                        <td><?= htmlspecialchars($c['socio']) ?></td>
                        <td><span class="badge bg-info"><?= $tiposCobro[$c['tipo']] ?? $c['tipo'] ?></span></td>
                        <td><strong>$<?= number_format($c['monto'], 2) ?></strong></td>
                        <td><?= $mediosPago[$c['medio_pago']] ?? $c['medio_pago'] ?></td>
                        <td>
                            <a href="<?= BASE_URL ?>/documento/comprobante/<?= $c['id_cobro'] ?>" class="btn btn-sm btn-outline-info" title="Comprobante"><i class="bi bi-file-earmark-pdf"></i></a>
                            <a href="#" onclick="anularCobro('<?= $c['id_cobro'] ?>')" class="btn btn-sm btn-outline-danger" title="Anular"><i class="bi bi-x-circle"></i></a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table></div>
        </div>
    </div>
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
