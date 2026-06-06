<div class="container-fluid">
    <h4>Solicitudes de retiro</h4>

    <div class="mb-3">
        <a href="?estado=pendiente" class="btn btn-sm <?= $filtro === 'pendiente' ? 'btn-warning' : 'btn-outline-warning' ?>">Pendientes</a>
        <a href="?estado=aprobado" class="btn btn-sm <?= $filtro === 'aprobado' ? 'btn-success' : 'btn-outline-success' ?>">Aprobados</a>
        <a href="?estado=rechazado" class="btn btn-sm <?= $filtro === 'rechazado' ? 'btn-danger' : 'btn-outline-danger' ?>">Rechazados</a>
        <a href="?" class="btn btn-sm btn-outline-secondary">Todos</a>
    </div>

    <div class="card card-dashboard">
        <div class="card-body p-0">
            <div class="table-responsive"><table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr><th>Fecha</th><th>Socio</th><th>Cédula</th><th>Monto</th><th>Disponible</th><th>Estado</th><th></th></tr>
                </thead>
                <tbody>
                    <?php foreach ($solicitudes as $s): ?>
                    <tr>
                        <td><?= $s['fecha_solicitud'] ?></td>
                        <td><?= htmlspecialchars($s['socio']) ?></td>
                        <td><?= $s['cédula'] ?></td>
                        <td><strong>$<?= number_format($s['monto'], 2) ?></strong></td>
                        <td>$<?= number_format($s['saldo_disponible'] ?? 0, 2) ?></td>
                        <td><span class="badge bg-<?= $s['estado'] === 'aprobado' ? 'success' : ($s['estado'] === 'rechazado' ? 'danger' : 'warning') ?>"><?= ucfirst($s['estado']) ?></span></td>
                        <td>
                            <?php if ($s['estado'] === 'pendiente'): ?>
                            <form method="POST" action="<?= BASE_URL ?>/retiro/aprobar/<?= $s['id_solicitud'] ?>" class="d-inline" onsubmit="return confirm('¿Aprobar retiro de $<?= number_format($s['monto'], 2) ?>?')">
                                <input type="hidden" name="csrf_token" value="<?= CSRFMiddleware::generarToken() ?>">
                                <button type="submit" class="btn btn-sm btn-success"><i class="bi bi-check-lg"></i></button>
                            </form>
                            <form method="POST" action="<?= BASE_URL ?>/retiro/rechazar/<?= $s['id_solicitud'] ?>" class="d-inline" onsubmit="return confirm('¿Rechazar esta solicitud?')">
                                <input type="hidden" name="csrf_token" value="<?= CSRFMiddleware::generarToken() ?>">
                                <button type="submit" class="btn btn-sm btn-danger"><i class="bi bi-x-lg"></i></button>
                            </form>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table></div>
        </div>
    </div>
</div>
