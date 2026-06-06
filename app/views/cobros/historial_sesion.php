<div class="container-fluid">
    <h4>Cobros de la sesión</h4>

    <div class="card card-dashboard">
        <div class="card-body p-0">
            <div class="table-responsive"><table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Fecha</th>
                        <th>Socio</th>
                        <th>Tipo</th>
                        <th>Monto</th>
                        <th>Medio</th>
                        <th>Anulado</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($cobros as $c): ?>
                    <tr class="<?= $c['anulado'] ? 'text-decoration-line-through text-muted' : '' ?>">
                        <td><?= $c['fecha_registro'] ?></td>
                        <td><?= htmlspecialchars($c['socio']) ?></td>
                        <td><?= $tiposCobro[$c['tipo']] ?? $c['tipo'] ?></td>
                        <td>$<?= number_format($c['monto'], 2) ?></td>
                        <td><?= $mediosPago[$c['medio_pago']] ?? $c['medio_pago'] ?></td>
                        <td><?= $c['anulado'] ? 'Sí' : 'No' ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table></div>
        </div>
    </div>
</div>
