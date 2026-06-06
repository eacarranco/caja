<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4>Créditos</h4>
        <a href="<?= BASE_URL ?>/credito/solicitar" class="btn btn-primary"><i class="bi bi-plus-circle"></i> Nueva solicitud</a>
    </div>

    <div class="card card-dashboard">
        <div class="card-body p-0">
            <div class="table-responsive"><table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Fecha</th>
                        <th>Socio</th>
                        <th>Producto</th>
                        <th class="text-end">Solicitado</th>
                        <th class="text-end">Aprobado</th>
                        <th>Plazo</th>
                        <th>Estado</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($creditos as $c): ?>
                    <tr>
                        <td><?= $c['fecha_solicitud'] ?></td>
                        <td><?= htmlspecialchars($c['socio']) ?></td>
                        <td><span class="badge bg-warning"><?= htmlspecialchars($c['producto']) ?></span></td>
                        <td class="text-end">$<?= number_format($c['monto_solicitado'], 2) ?></td>
                        <td class="text-end">$<?= number_format($c['monto_aprobado'] ?? 0, 2) ?></td>
                        <td><?= $c['plazo_meses'] ?> meses</td>
                        <td>
                            <span class="badge bg-<?= match($c['estado']) {
                                'pendiente' => 'warning',
                                'en_revisión' => 'info',
                                'aprobado' => 'success',
                                'rechazado' => 'danger',
                                'desembolsado' => 'primary',
                                'cancelado' => 'secondary',
                                default => 'secondary'
                            } ?>"><?= ucfirst($c['estado']) ?></span>
                        </td>
                        <td>
                            <a href="<?= BASE_URL ?>/credito/ver/<?= $c['id_crédito'] ?>" class="btn btn-sm btn-outline-primary"><i class="bi bi-eye"></i></a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table></div>
        </div>
    </div>
</div>
