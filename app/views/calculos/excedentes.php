<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4>Distribución de excedentes</h4>
        <?php if ($resultado): ?>
        <form method="POST" action="<?= BASE_URL ?>/calculo/aprobarExcedentes" style="display:inline" onsubmit="return confirm('¿Aprobar distribución? Los montos se acreditarán a las cuentas de ahorro.')">
            <?= CSRFMiddleware::campoHTML() ?>
            <input type="hidden" name="total_excedente" value="<?= $resultado['total_excedente'] ?>">
            <button type="submit" class="btn btn-success"><i class="bi bi-check2-all"></i> Aprobar distribución</button>
        </form>
        <?php endif; ?>
    </div>

    <div class="mb-2">
        <button class="btn btn-sm btn-outline-info" onclick="interesesAhorro()"><i class="bi bi-percent"></i> Calcular intereses de ahorro mensuales</button>
    </div>

    <div class="card card-dashboard mb-3">
        <div class="card-body">
            <form method="POST" class="row g-2">
                <?= CSRFMiddleware::campoHTML() ?>
                <div class="col-md-4">
                    <label class="form-label">Total excedente a distribuir $</label>
                    <input type="number" step="0.01" min="0.01" name="total_excedente" class="form-control <?= isset($errors['total']) ? 'is-invalid' : '' ?>" required>
                </div>
                <div class="col-md-2 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary"><i class="bi bi-calculator"></i> Calcular</button>
                </div>
            </form>
            <?php if (isset($errors['general'])): ?><div class="text-danger mt-2 small"><?= $errors['general'] ?></div><?php endif; ?>
        </div>
    </div>

    <?php if ($resultado): ?>
    <div class="card card-dashboard">
        <div class="card-body p-0">
            <div class="table-responsive"><table class="table table-hover table-sm mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Cédula</th>
                        <th>Socio</th>
                        <th class="text-end">Ahorro mensual</th>
                        <th class="text-end">Participación</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($resultado['socios'] as $s): ?>
                    <tr>
                        <td><?= htmlspecialchars($s['cedula']) ?></td>
                        <td><?= htmlspecialchars($s['nombre']) ?></td>
                        <td class="text-end">$<?= number_format($s['saldo_obligatorio'], 2) ?></td>
                        <td class="text-end"><strong>$<?= number_format($s['participacion'], 2) ?></strong></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
                <tfoot class="table-secondary">
                    <tr>
                        <td colspan="2"><strong>Total</strong></td>
                        <td class="text-end">$<?= number_format($resultado['total_aportes'], 2) ?></td>
                        <td class="text-end">$<?= number_format($resultado['distribuido'], 2) ?>
                            <small class="text-muted">(dif: $<?= number_format($resultado['diferencia'], 2) ?>)</small>
                        </td>
                    </tr>
                </tfoot>
            </table></div>
        </div>
    </div>
    <?php endif; ?>
</div>
<script>
function interesesAhorro() {
    if (!confirm('¿Calcular y acreditar intereses de ahorro mensuales?')) return;
    var formData = new FormData();
    formData.append('csrf_token', '<?= $csrfToken ?? '' ?>');
    fetch('<?= BASE_URL ?>/calculo/interesesAhorro', {
        method: 'POST', body: formData
    }).then(function(r) { return r.json(); }).then(function(d) {
        if (d.error) { alert(d.error); } else { alert(d.mensaje); location.reload(); }
    });
}
</script>
