<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4>Capital de Inversion</h4>
        <a href="<?= BASE_URL ?>/portal" class="btn btn-outline-secondary"><i class="bi bi-arrow-left"></i> Volver</a>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-md-4">
            <div class="card card-dashboard text-center">
                <div class="card-body py-3">
                    <h6 class="text-muted">Saldo disponible</h6>
                    <h3 class="text-success mb-0">$ <?= number_format(floatval($capital['saldo'] ?? 0), 2) ?></h3>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card card-dashboard text-center">
                <div class="card-body py-3">
                    <h6 class="text-muted">Inversiones activas</h6>
                    <h3 class="text-primary mb-0">
                        <?php
                        $activas = array_filter($inversiones, function($i) { return $i['estado'] === 'activa'; });
                        echo count($activas);
                        ?>
                    </h3>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card card-dashboard text-center">
                <div class="card-body py-3">
                    <h6 class="text-muted">Total invertido</h6>
                    <h3 class="text-warning mb-0">
                        $ <?= number_format(array_sum(array_map(function($i) { return floatval($i['monto']); }, $activas)), 2) ?>
                    </h3>
                </div>
            </div>
        </div>
    </div>

    <?php if (!empty($inversiones)): ?>
    <div class="card mb-3">
        <div class="card-header"><strong>Inversiones</strong></div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-striped mb-0">
                    <thead>
                        <tr>
                            <th>Producto</th>
                            <th>Monto</th>
                            <th>Plazo</th>
                            <th>Vencimiento</th>
                            <th>Rendimiento</th>
                            <th>Destino</th>
                            <th>Estado</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($inversiones as $i): ?>
                        <tr>
                            <td><?= htmlspecialchars($i['producto']) ?></td>
                            <td>$ <?= number_format($i['monto'], 2) ?></td>
                            <td><?= $i['plazo_meses'] ?> meses</td>
                            <td><?= date('d/m/Y', strtotime($i['fecha_vencimiento'])) ?></td>
                            <td>$ <?= number_format($i['rendimiento_proyectado'] ?? 0, 2) ?></td>
                            <td><?= htmlspecialchars($i['destino_final'] ?? '-') ?></td>
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
    <?php endif; ?>

    <?php if (!empty($movimientos)): ?>
    <div class="card">
        <div class="card-header"><strong>Movimientos del capital</strong></div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-sm mb-0">
                    <thead>
                        <tr>
                            <th>Fecha</th>
                            <th>Tipo</th>
                            <th>Monto</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($movimientos as $m): ?>
                        <tr>
                            <td><?= date('d/m/Y H:i', strtotime($m['fecha_registro'])) ?></td>
                            <td>
                                <?php
                                $label = $m['tipo_operacion'];
                                if ($label === 'deposito_capital_inversion') echo '<span class="text-success">Deposito</span>';
                                elseif ($label === 'inversion_apertura') echo '<span class="text-primary">Inversion apertura</span>';
                                elseif ($label === 'inversion_retiro') echo '<span class="text-warning">Retorno de inversion</span>';
                                else echo htmlspecialchars($label);
                                ?>
                            </td>
                            <td class="<?= ($m['tipo_operacion'] === 'inversion_apertura') ? 'text-danger' : 'text-success' ?>">
                                $ <?= number_format($m['monto'], 2) ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <?php endif; ?>
</div>
