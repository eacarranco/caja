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
                        <?php if ($pagada): ?><span class="badge bg-success">Pagada</span>
                        <?php elseif ($multa['estado'] === 'anulada'): ?><span class="badge bg-dark">Anulada por directivo</span>
                        <?php elseif ($multa['estado'] === 'impugnada'): ?><span class="badge bg-success">Impugnada (sin efecto)</span>
                        <?php elseif (!empty($multa['justificacion']) && ($multa['justificacion_aprobada'] === '' || $multa['justificacion_aprobada'] === null)): ?><span class="badge bg-warning text-dark">En revision</span>
                        <?php elseif ($multa['justificacion_aprobada'] === '0'): ?><span class="badge bg-danger">Rechazada</span>
                        <?php else: ?><span class="badge bg-danger">Pendiente</span><?php endif; ?>
                    </p>
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
            <p class="mt-2">
                <strong>Estado justificacion:</strong>
                <?php if ($multa['estado'] === 'impugnada'): ?>
                <span class="badge bg-success">Impugnacion aprobada — multa sin efecto</span>
                <?php elseif ($multa['justificacion_aprobada'] === '1'): ?>
                <span class="badge bg-success">Aprobada</span>
                <?php elseif ($multa['justificacion_aprobada'] === '0'): ?>
                <span class="badge bg-danger">Rechazada — multa vigente</span>
                <?php else: ?>
                <span class="badge bg-warning">Pendiente de revision</span>
                <?php endif; ?>
            </p>
            <?php if ($puedeAutorizar && $multa['estado'] === 'activa' && ($multa['justificacion_aprobada'] === '' || $multa['justificacion_aprobada'] === null || $multa['justificacion_aprobada'] === '0')): ?>
            <div class="mt-2">
                <form method="POST" action="<?= BASE_URL ?>/multa/aprobarJustificacion/<?= $multa['id_multa'] ?>" class="d-inline" onsubmit="return confirm('¿Autorizar impugnacion? La multa quedara sin efecto.')">
                    <input type="hidden" name="csrf_token" value="<?= CSRFMiddleware::generarToken() ?>">
                    <input type="hidden" name="accion" value="aprobar">
                    <button type="submit" class="btn btn-sm btn-success"><i class="bi bi-check-lg"></i> Autorizar impugnacion</button>
                </form>
                <form method="POST" action="<?= BASE_URL ?>/multa/aprobarJustificacion/<?= $multa['id_multa'] ?>" class="d-inline" onsubmit="return confirm('¿Rechazar impugnacion? La multa seguira vigente.')">
                    <input type="hidden" name="csrf_token" value="<?= CSRFMiddleware::generarToken() ?>">
                    <input type="hidden" name="accion" value="rechazar">
                    <button type="submit" class="btn btn-sm btn-danger"><i class="bi bi-x-lg"></i> Rechazar impugnacion</button>
                </form>
            </div>
            <?php endif; ?>
            <?php endif; ?>

            <?php if (!$pagada && $multa['estado'] === 'activa'): ?>
            <hr>
            <span class="text-muted small">El pago debe realizarse a traves de una sesion abierta.</span>
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
</script>
