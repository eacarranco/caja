<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4>Inversion</h4>
        <a href="<?= BASE_URL ?>/portal" class="btn btn-outline-secondary"><i class="bi bi-arrow-left"></i> Volver</a>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-md-4">
            <div class="card card-dashboard text-center">
                <div class="card-body py-3">
                    <h6 class="text-muted">Capital disponible</h6>
                    <h3 class="text-success mb-0">$ <?= number_format(floatval($capital['saldo'] ?? 0), 2) ?></h3>
                </div>
            </div>
        </div>
    </div>

    <?php if (!empty($inversiones)): ?>
    <div class="card mb-3">
        <div class="card-header d-flex justify-content-between align-items-center">
            <strong>Mis inversiones</strong>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-striped mb-0">
                    <thead>
                        <tr>
                            <th>Producto</th>
                            <th>Monto</th>
                            <th>Plazo</th>
                            <th>Inicio</th>
                            <th>Vencimiento</th>
                            <th>Rendimiento</th>
                            <th>Estado</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($inversiones as $i): ?>
                        <tr>
                            <td><?= htmlspecialchars($i['producto']) ?></td>
                            <td>$ <?= number_format($i['monto'], 2) ?></td>
                            <td><?= $i['plazo_meses'] ?> meses</td>
                            <td><?= date('d/m/Y', strtotime($i['fecha_inicio'])) ?></td>
                            <td><?= date('d/m/Y', strtotime($i['fecha_vencimiento'])) ?></td>
                            <td>$ <?= number_format($i['rendimiento_proyectado'] ?? 0, 2) ?></td>
                            <td>
                                <?php if ($i['estado'] === 'activa'): ?>
                                <span class="badge bg-success">Activa</span>
                                <?php elseif ($i['estado'] === 'vencida'): ?>
                                <span class="badge bg-warning text-dark">Vencida</span>
                                <?php elseif ($i['estado'] === 'retiro_anticipado'): ?>
                                <span class="badge bg-secondary">Retiro anticipado</span>
                                <?php else: ?>
                                <span class="badge bg-danger"><?= htmlspecialchars($i['estado']) ?></span>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <?php else: ?>
    <div class="alert alert-info">No tienes inversiones activas. Para invertir, el deposito debe realizarlo el tesorero en sesion.</div>
    <?php endif; ?>
</div>
