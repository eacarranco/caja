<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4><?= $titulo ?></h4>
        <a href="<?= $ruta_csv ?>" class="btn btn-outline-success"><i class="bi bi-download"></i> CSV</a>
    </div>

    <div class="card card-dashboard">
        <div class="card-body p-0 table-responsive">
            <table class="table table-hover table-sm mb-0">
                <thead class="table-light">
                    <tr>
                        <?php foreach ($encabezados as $e): ?>
                        <th><?= $e ?></th>
                        <?php endforeach; ?>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($filas as $f): ?>
                    <tr>
                        <?php foreach ($campos as $c):
                            $v = $f[$c] ?? '-';
                            if (is_numeric($v) && $c !== 'cédula' && $c !== 'teléfono' && strpos($c, 'saldo') !== false):
                        ?>
                        <td class="text-end">$<?= number_format($v, 2) ?></td>
                        <?php elseif ($c === 'anulado'): ?>
                        <td><?= $v ? 'Sí' : 'No' ?></td>
                        <?php else: ?>
                        <td><?= htmlspecialchars($v) ?></td>
                        <?php endif; endforeach; ?>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
