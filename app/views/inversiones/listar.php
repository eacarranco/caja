<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4>Inversiones</h4>
        <div class="d-flex gap-2">
            <a href="<?= BASE_URL ?>/inversion/depositar" class="btn btn-success"><i class="bi bi-wallet2"></i> Depositar</a>
            <a href="<?= BASE_URL ?>/inversion/apertura" class="btn btn-primary"><i class="bi bi-plus-circle"></i> Nueva inversion</a>
            <button class="btn btn-outline-warning" onclick="cerrarVencidas()"><i class="bi bi-clock"></i> Cerrar vencidas</button>
        </div>
    </div>

    <div class="card card-dashboard">
        <div class="card-body p-0">
            <div class="table-responsive"><table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Registro</th>
                        <th>Socio</th>
                        <th>Producto</th>
                        <th class="text-end">Monto</th>
                        <th>Plazo</th>
                        <th>Vencimiento</th>
                        <th class="text-end">Rendimiento</th>
                        <th>Estado</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($inversiones as $i): ?>
                    <tr>
                        <td><?= $i['fecha_registro'] ?></td>
                        <td><?= htmlspecialchars($i['socio']) ?></td>
                        <td><?= htmlspecialchars($i['producto']) ?></td>
                        <td class="text-end">$<?= number_format($i['monto'], 2) ?></td>
                        <td><?= $i['plazo_meses'] ?> meses</td>
                        <td><?= $i['fecha_vencimiento'] ?></td>
                        <td class="text-end">$<?= number_format($i['rendimiento_proyectado'] ?? 0, 2) ?></td>
                        <td>
                            <span class="badge bg-<?= match($i['estado']) { 'activa'=>'success', 'vencida'=>'warning', 'retiro_anticipado'=>'secondary', 'cancelada'=>'danger', default=>'secondary' } ?>">
                                <?= ucfirst(str_replace('_', ' ', $i['estado'])) ?>
                            </span>
                        </td>
                        <td>
                            <?php if ($i['estado'] === 'activa'): ?>
                            <a href="#" onclick="retirar('<?= $i['id_inversion'] ?>')" class="btn btn-sm btn-outline-warning"><i class="bi bi-box-arrow-left"></i></a>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table></div>
        </div>
    </div>
</div>

<script>
function retirar(id) {
    if (!confirm('¿Procesar retiro anticipado de esta inversión?')) return;
    fetch('<?= BASE_URL ?>/inversion/retirar/' + id, {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: 'csrf_token=<?= CSRFMiddleware::generarToken() ?>'
    }).then(function(r) { return r.json(); }).then(function(d) {
        if (d.error) { alert(d.error); } else { alert('Retiro procesado. Devolución: $' + d.devolución + ' | Penalidad: $' + d.penalidad); location.reload(); }
    });
}
function cerrarVencidas() {
    if (!confirm('¿Cerrar todas las inversiones vencidas?')) return;
    fetch('<?= BASE_URL ?>/inversion/cerrarVencidas', {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: 'csrf_token=<?= CSRFMiddleware::generarToken() ?>'
    }).then(function(r) { return r.json(); }).then(function(d) {
        if (d.error) { alert(d.error); } else { alert(d.mensaje); location.reload(); }
    });
}
</script>
