<div class="container-fluid">
    <h4>Simulador de amortización</h4>

    <div class="card card-dashboard mb-3">
        <div class="card-body">
            <form method="POST" class="row g-2">
                <?= CSRFMiddleware::campoHTML() ?>
                <div class="col-md-3">
                    <label class="form-label">Producto</label>
                    <select id="selProducto" class="form-select" onchange="cargarProducto()">
                        <option value="">Seleccione...</option>
                        <?php foreach ($productos as $p): ?>
                        <option value="<?= $p['id_producto'] ?>"
                            data-tasa="<?= $p['tasa_interés_anual'] ?>"
                            data-metodo="<?= $p['método_interés'] ?>"
                            data-min="<?= $p['plazo_mín_meses'] ?>"
                            data-max="<?= $p['plazo_máx_meses'] ?>">
                            <?= htmlspecialchars($p['nombre']) ?> (<?= $p['tipo'] ?>)
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">Monto $</label>
                    <input type="number" step="0.01" min="1" name="monto" class="form-control" required value="<?= $_POST['monto'] ?? '' ?>">
                </div>
                <div class="col-md-2">
                    <label class="form-label">Tasa anual %</label>
                    <input type="number" step="0.01" min="0" max="100" name="tasa" class="form-control" required value="<?= $_POST['tasa'] ?? '' ?>">
                </div>
                <div class="col-md-1">
                    <label class="form-label">Plazo</label>
                    <input type="number" min="1" name="plazo" class="form-control" required value="<?= $_POST['plazo'] ?? '12' ?>">
                </div>
                <div class="col-md-2">
                    <label class="form-label">Método</label>
                    <select name="método" class="form-select">
                        <option value="simple" <?= ($_POST['método'] ?? '') === 'simple' ? 'selected' : '' ?>>Simple</option>
                        <option value="francés" <?= ($_POST['método'] ?? '') === 'francés' ? 'selected' : '' ?>>Francés</option>
                        <option value="alemán" <?= ($_POST['método'] ?? '') === 'alemán' ? 'selected' : '' ?>>Alemán</option>
                    </select>
                </div>
                <div class="col-md-2 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary w-100"><i class="bi bi-calculator"></i> Simular</button>
                </div>
            </form>
        </div>
    </div>

    <?php if ($resultado): ?>
    <div class="card card-dashboard">
        <div class="card-body p-0">
            <div class="table-responsive"><table class="table table-sm table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th>#</th>
                        <th class="text-end">Capital</th>
                        <th class="text-end">Interés</th>
                        <th class="text-end">Cuota</th>
                        <th class="text-end">Saldo</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $totalCapital = 0; $totalInteres = 0; $totalCuota = 0; ?>
                    <?php foreach ($resultado as $c): ?>
                    <?php $totalCapital += $c['capital']; $totalInteres += $c['interés']; $totalCuota += $c['total']; ?>
                    <tr>
                        <td><?= $c['número'] ?></td>
                        <td class="text-end">$<?= number_format($c['capital'], 2) ?></td>
                        <td class="text-end">$<?= number_format($c['interés'], 2) ?></td>
                        <td class="text-end"><strong>$<?= number_format($c['total'], 2) ?></strong></td>
                        <td class="text-end">$<?= number_format($c['saldo'], 2) ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
                <tfoot class="table-secondary">
                    <tr>
                        <td><strong>Total</strong></td>
                        <td class="text-end">$<?= number_format($totalCapital, 2) ?></td>
                        <td class="text-end">$<?= number_format($totalInteres, 2) ?></td>
                        <td class="text-end">$<?= number_format($totalCuota, 2) ?></td>
                        <td></td>
                    </tr>
                </tfoot>
            </table></div>
        </div>
    </div>
    <?php endif; ?>
</div>

<script>
function cargarProducto() {
    var sel = document.getElementById('selProducto');
    var opt = sel.options[sel.selectedIndex];
    if (opt.value) {
        document.querySelector('[name="tasa"]').value = opt.dataset.tasa;
        document.querySelector('[name="método"]').value = opt.dataset.metodo;
        document.querySelector('[name="plazo"]').value = opt.dataset.min;
        document.querySelector('[name="monto"]').focus();
    }
}
</script>
