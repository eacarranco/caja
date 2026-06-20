<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4>Inversiones</h4>
        <div class="d-flex gap-2">
            <a href="<?= BASE_URL ?>/inversion/depositar" class="btn btn-success"><i class="bi bi-wallet2"></i> Depositar</a>
            <a href="<?= BASE_URL ?>/inversion/apertura" class="btn btn-primary"><i class="bi bi-plus-circle"></i> Nueva inversion</a>
            <button class="btn btn-outline-warning" onclick="cerrarVencidas()"><i class="bi bi-clock"></i> Cerrar vencidas</button>
        </div>
    </div>

    <?php if (count($capitales) > 0): ?>
    <div class="row mb-4">
        <?php foreach ($capitales as $cap): ?>
        <div class="col-md-4 mb-2">
            <div class="card card-dashboard bg-success-subtle border-success">
                <div class="card-body p-3">
                    <small class="text-muted">Capital de inversión</small>
                    <h6 class="mb-0"><?= htmlspecialchars($cap['socio']) ?></h6>
                    <strong class="text-success fs-5">$<?= number_format($cap['saldo'], 2) ?></strong>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>

    <ul class="nav nav-pills nav-justified mb-3" id="invTabs" role="tablist">
        <li class="nav-item" role="presentation">
            <button class="nav-link active" id="contratos-tab" data-bs-toggle="pill" data-bs-target="#contratos" type="button" role="tab">
                <i class="bi bi-file-text"></i> Contratos activos (<?= count($inversiones) ?>)
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="depositos-tab" data-bs-toggle="pill" data-bs-target="#depositos" type="button" role="tab">
                <i class="bi bi-wallet2"></i> Depósitos a capital (<?= count($depositos) ?>)
            </button>
        </li>
    </ul>

    <div class="tab-content">
        <div class="tab-pane fade show active" id="contratos" role="tabpanel">
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
                                    <span class="badge bg-<?= match($i['estado']) { 'pendiente'=>'info', 'activa'=>'success', 'vencida'=>'warning', 'retiro_anticipado'=>'secondary', 'cancelada'=>'danger', 'rechazada'=>'danger', default=>'secondary' } ?>">
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
                            <?php if (count($inversiones) === 0): ?>
                            <tr><td colspan="9" class="text-center text-muted py-3">No hay contratos de inversión registrados</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table></div>
                </div>
            </div>
        </div>

        <div class="tab-pane fade" id="depositos" role="tabpanel">
            <div class="card card-dashboard">
                <div class="card-body p-0">
                    <div class="table-responsive"><table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Fecha</th>
                                <th>Socio</th>
                                <th class="text-end">Monto</th>
                                <th>Medio pago</th>
                                <th>Registrado por</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($depositos as $d): ?>
                            <tr>
                                <td><?= $d['fecha_registro'] ?></td>
                                <td><?= htmlspecialchars($d['socio']) ?></td>
                                <td class="text-end text-success">+$<?= number_format($d['monto'], 2) ?></td>
                                <td><?= ucfirst($d['medio_pago']) ?></td>
                                <td><?= htmlspecialchars($d['usuario_registra'] ?? '-') ?></td>
                            </tr>
                            <?php endforeach; ?>
                            <?php if (count($depositos) === 0): ?>
                            <tr><td colspan="5" class="text-center text-muted py-3">No hay depósitos a capital registrados</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table></div>
                </div>
            </div>
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
