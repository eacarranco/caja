<div class="container-fluid">
    <h4>Multa</h4>
    <a href="<?= BASE_URL ?>/multa/listar" class="btn btn-sm btn-outline-secondary mb-3"><i class="bi bi-arrow-left"></i> Volver</a>

    <div class="card card-dashboard">
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-6">
                    <p><strong>Socio:</strong> <?= htmlspecialchars($multa['socio']) ?></p>
                    <p><strong>Cédula:</strong> <?= $multa['cédula'] ?></p>
                    <p><strong>Tipo:</strong> <span class="badge bg-info"><?= str_replace('_', ' ', $multa['tipo']) ?></span></p>
                    <p><strong>Monto:</strong> <strong>$<?= number_format($multa['monto'], 2) ?></strong></p>
                </div>
                <div class="col-md-6">
                    <p><strong>Generada:</strong> <?= $multa['fecha_generación'] ?></p>
                    <p><strong>Pagada:</strong> <?= $multa['pagada'] ? '<span class="badge bg-success">Sí</span>' : '<span class="badge bg-danger">No</span>' ?></p>
                    <?php if ($multa['fecha_pago']): ?><p><strong>Fecha pago:</strong> <?= $multa['fecha_pago'] ?></p><?php endif; ?>
                    <?php if ($multa['id_sesión']): ?><p><strong>Sesión:</strong> <?= $multa['id_sesión'] ?></p><?php endif; ?>
                </div>
            </div>

            <?php if ($multa['justificación']): ?>
            <hr>
            <h6>Justificación</h6>
            <p><?= nl2br(htmlspecialchars($multa['justificación'])) ?></p>
            <?php if ($multa['justificación_pdf']): ?>
            <a href="<?= BASE_URL ?>/storage/documentos/<?= $multa['justificación_pdf'] ?>" class="btn btn-sm btn-outline-primary" target="_blank"><i class="bi bi-file-earmark-pdf"></i> Ver archivo</a>
            <?php endif; ?>
            <p class="mt-2">
                <strong>Estado:</strong>
                <?php if ($multa['justificación_aprobada'] === '1'): ?>
                <span class="badge bg-success">Aprobada</span>
                <?php elseif ($multa['justificación_aprobada'] === '0'): ?>
                <span class="badge bg-danger">Rechazada</span>
                <?php else: ?>
                <span class="badge bg-warning">Pendiente de revisión</span>
                <?php endif; ?>
            </p>
            <?php if ($multa['justificación_aprobada'] === '' || $multa['justificación_aprobada'] === null): ?>
            <div class="mt-2">
                <form method="POST" action="<?= BASE_URL ?>/multa/aprobarJustificacion/<?= $multa['id_multa'] ?>" class="d-inline" onsubmit="return confirm('¿Aprobar esta justificación?')">
                    <input type="hidden" name="csrf_token" value="<?= CSRFMiddleware::generarToken() ?>">
                    <input type="hidden" name="accion" value="aprobar">
                    <button type="submit" class="btn btn-sm btn-success"><i class="bi bi-check-lg"></i> Aprobar</button>
                </form>
                <form method="POST" action="<?= BASE_URL ?>/multa/aprobarJustificacion/<?= $multa['id_multa'] ?>" class="d-inline" onsubmit="return confirm('¿Rechazar esta justificación?')">
                    <input type="hidden" name="csrf_token" value="<?= CSRFMiddleware::generarToken() ?>">
                    <input type="hidden" name="accion" value="rechazar">
                    <button type="submit" class="btn btn-sm btn-danger"><i class="bi bi-x-lg"></i> Rechazar</button>
                </form>
            </div>
            <?php endif; ?>
            <?php endif; ?>

            <?php if (!$multa['pagada']): ?>
            <hr>
            <form method="POST" action="<?= BASE_URL ?>/multa/marcarPagada/<?= $multa['id_multa'] ?>" class="d-inline" onsubmit="return confirm('¿Marcar como pagada?')">
                <input type="hidden" name="csrf_token" value="<?= CSRFMiddleware::generarToken() ?>">
                <button type="submit" class="btn btn-sm btn-success"><i class="bi bi-cash-coin"></i> Marcar pagada</button>
            </form>
            <?php endif; ?>
        </div>
    </div>
</div>
