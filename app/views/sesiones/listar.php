<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4>Sesiones mensuales</h4>
        <a href="<?= BASE_URL ?>/sesion/abrir" class="btn btn-primary"><i class="bi bi-plus-circle"></i> Abrir sesión</a>
    </div>

    <div class="card card-dashboard">
        <div class="card-body p-0">
            <div class="table-responsive"><table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th>#</th>
                        <th>Fecha</th>
                        <th>Título</th>
                        <th>Estado</th>
                        <th>Recaudado</th>
                        <th>Desembolsado</th>
                        <th>Saldo</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($sesiones as $s): ?>
                    <tr>
                        <td><?= $s['número_sesión'] ?></td>
                        <td><?= $s['fecha'] ?></td>
                        <td><?= htmlspecialchars($s['título'] ?? 'Sesión #' . $s['número_sesión']) ?></td>
                        <td>
                            <span class="badge <?= $s['estado'] === 'abierta' ? 'bg-success' : 'bg-secondary' ?>">
                                <?= $s['estado'] === 'abierta' ? 'Abierta' : 'Cerrada' ?>
                            </span>
                        </td>
                        <td>$<?= number_format($s['total_recaudado'], 2) ?></td>
                        <td>$<?= number_format($s['total_desembolsado'], 2) ?></td>
                        <td>$<?= number_format($s['saldo_caja'], 2) ?></td>
                        <td>
                            <?php if ($s['estado'] === 'abierta'): ?>
                            <a href="<?= BASE_URL ?>/sesion/checkin/<?= $s['id_sesión'] ?>" class="btn btn-sm btn-outline-success"><i class="bi bi-check-circle"></i> Gestión</a>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table></div>
        </div>
    </div>
</div>
