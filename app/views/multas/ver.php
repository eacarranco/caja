<div class="container-fluid">
    <h4>Multa</h4>
    <a href="<?= BASE_URL ?>/multa/listar" class="btn btn-sm btn-outline-secondary mb-3"><i class="bi bi-arrow-left"></i> Volver</a>

    <div class="card card-dashboard">
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-6">
                    <p><strong>Socio:</strong> <?= htmlspecialchars($multa['socio']) ?></p>
                    <p><strong>Cedula:</strong> <?= $multa['cedula'] ?></p>
                    <p><strong>Tipo:</strong> <span class="badge bg-info"><?= str_replace('_', ' ', $multa['tipo']) ?></span></p>
                    <p><strong>Monto:</strong> <strong>$<?= number_format($multa['monto'], 2) ?></strong></p>
                </div>
                <div class="col-md-6">
                    <p><strong>Generada:</strong> <?= $multa['fecha_generacion'] ?></p>
                    <p><strong>Estado:</strong>
                        <?php if ($multa['pagada']): ?><span class="badge bg-success">Pagada</span>
                        <?php elseif ($multa['impugnada']): ?><span class="badge bg-secondary">Impugnada (sin efecto)</span>
                        <?php else: ?><span class="badge bg-danger">Pendiente</span><?php endif; ?>
                    </p>
                    <?php if ($multa['fecha_pago']): ?><p><strong>Fecha pago:</strong> <?= $multa['fecha_pago'] ?></p><?php endif; ?>
                    <?php if ($multa['id_sesion']): ?><p><strong>Sesion:</strong> <?= $multa['id_sesion'] ?></p><?php endif; ?>
                </div>
            </div>

            <?php if ($multa['justificacion']): ?>
            <hr>
            <h6>Justificacion / Impugnacion</h6>
            <p><?= nl2br(htmlspecialchars($multa['justificacion'])) ?></p>
            <?php if ($multa['justificacion_pdf']): ?>
            <a href="<?= BASE_URL ?>/storage/documentos/<?= $multa['justificacion_pdf'] ?>" class="btn btn-sm btn-outline-primary" target="_blank"><i class="bi bi-file-earmark-pdf"></i> Ver archivo</a>
            <?php endif; ?>
            <?php if (!$multa['impugnada']): ?>
            <p class="mt-2">
                <strong>Estado justificacion:</strong>
                <?php if ($multa['justificacion_aprobada'] === '1'): ?>
                <span class="badge bg-success">Aprobada</span>
                <?php elseif ($multa['justificacion_aprobada'] === '0'): ?>
                <span class="badge bg-danger">Rechazada</span>
                <?php else: ?>
                <span class="badge bg-warning">Pendiente de revision</span>
                <?php endif; ?>
            </p>
            <?php if ($multa['justificacion_aprobada'] === '' || $multa['justificacion_aprobada'] === null): ?>
            <div class="mt-2">
                <form method="POST" action="<?= BASE_URL ?>/multa/aprobarJustificacion/<?= $multa['id_multa'] ?>" class="d-inline" onsubmit="return confirm('Aprobar justificacion?')">
                    <input type="hidden" name="csrf_token" value="<?= CSRFMiddleware::generarToken() ?>">
                    <input type="hidden" name="accion" value="aprobar">
                    <button type="submit" class="btn btn-sm btn-success"><i class="bi bi-check-lg"></i> Aprobar</button>
                </form>
                <form method="POST" action="<?= BASE_URL ?>/multa/aprobarJustificacion/<?= $multa['id_multa'] ?>" class="d-inline" onsubmit="return confirm('Rechazar justificacion?')">
                    <input type="hidden" name="csrf_token" value="<?= CSRFMiddleware::generarToken() ?>">
                    <input type="hidden" name="accion" value="rechazar">
                    <button type="submit" class="btn btn-sm btn-danger"><i class="bi bi-x-lg"></i> Rechazar</button>
                </form>
            </div>
            <?php endif; ?>
            <?php endif; ?>
            <?php endif; ?>

            <?php if (!$multa['pagada'] && !$multa['impugnada']): ?>
            <hr>
            <button type="button" class="btn btn-sm btn-warning" onclick="impugnarMulta('<?= $multa['id_multa'] ?>')"><i class="bi bi-shield-exclamation"></i> Impugnar</button>
            <span class="text-muted ms-2 small">El pago debe realizarse a traves de una sesion abierta.</span>
            <?php if ($esPresidente): ?>
            <a href="#" onclick="eliminarMulta('<?= $multa['id_multa'] ?>')" class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i> Eliminar (Presidente)</a>
            <?php endif; ?>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
function eliminarMulta(id) {
    if (!confirm('Eliminar esta multa definitivamente? No se puede deshacer.')) return;
    fetch('<?= BASE_URL ?>/multa/eliminar/' + id, {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: 'csrf_token=<?= CSRFMiddleware::generarToken() ?>'
    }).then(function(r) { return r.json(); }).then(function(d) {
        if (d.error) { alert(d.error); } else { alert(d.mensaje); location.reload(); }
    });
}
function impugnarMulta(id) {
    var texto = prompt('Describa el motivo de la impugnacion:');
    if (!texto || texto.trim().length < 10) { alert('Escriba al menos 10 caracteres'); return; }
    var formData = new FormData();
    formData.append('csrf_token', '<?= CSRFMiddleware::generarToken() ?>');
    formData.append('justificacion', texto.trim());
    fetch('<?= BASE_URL ?>/multa/impugnar/' + id, {
        method: 'POST',
        body: formData
    }).then(function(r) { return r.json(); }).then(function(d) {
        if (d.error) { alert(d.error); } else { alert(d.mensaje); location.reload(); }
    });
}
</script>
