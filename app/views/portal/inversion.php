<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4>Inversion</h4>
        <a href="<?= BASE_URL ?>/portal" class="btn btn-outline-secondary"><i class="bi bi-arrow-left"></i> Volver</a>
    </div>

    <?php if (isset($_GET['ok'])): ?>
    <div class="alert alert-success alert-dismissible fade show">Inversion creada exitosamente.<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
    <?php endif; ?>
    <?php if (!empty($errors['general'])): ?>
    <div class="alert alert-danger"><?= htmlspecialchars($errors['general']) ?></div>
    <?php endif; ?>

    <div class="row g-3 mb-4">
        <div class="col-md-4">
            <div class="card card-dashboard text-center">
                <div class="card-body py-3">
                    <h6 class="text-muted">Capital disponible</h6>
                    <h3 class="text-success mb-0">$ <?= number_format($saldoCapital, 2) ?></h3>
                </div>
            </div>
        </div>
    </div>

    <div class="card mb-3">
        <div class="card-header"><strong>Nueva inversion</strong></div>
        <div class="card-body">
            <form method="POST">
                <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>">
                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label">Producto *</label>
                        <select name="id_producto" id="selProdInv" class="form-select <?= isset($errors['id_producto']) ? 'is-invalid' : '' ?>" required onchange="cargarLimitesInv()">
                            <option value="">Seleccione...</option>
                            <?php foreach ($productos as $p): ?>
                            <option value="<?= $p['id_producto'] ?>" data-tasa="<?= $p['tasa_interes_anual'] ?>" data-min="<?= $p['plazo_min_meses'] ?>" data-max="<?= $p['plazo_max_meses'] ?>"><?= htmlspecialchars($p['nombre']) ?> (<?= $p['tasa_interes_anual'] ?>%)</option>
                            <?php endforeach; ?>
                        </select>
                        <?php if (isset($errors['id_producto'])): ?><div class="invalid-feedback"><?= $errors['id_producto'] ?></div><?php endif; ?>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Monto $ *</label>
                        <input type="number" step="0.01" min="1" name="monto" class="form-control <?= isset($errors['monto']) ? 'is-invalid' : '' ?>" value="<?= htmlspecialchars($_POST['monto'] ?? '') ?>" required>
                        <?php if (isset($errors['monto'])): ?><div class="invalid-feedback"><?= $errors['monto'] ?></div><?php endif; ?>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Plazo (meses) *</label>
                        <input type="number" min="1" name="plazo" id="plazoInv" class="form-control <?= isset($errors['plazo']) ? 'is-invalid' : '' ?>" value="<?= htmlspecialchars($_POST['plazo'] ?? '') ?>" required>
                        <small class="text-muted" id="plazoAyudaInv"></small>
                        <?php if (isset($errors['plazo'])): ?><div class="invalid-feedback"><?= $errors['plazo'] ?></div><?php endif; ?>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Destino al vencimiento</label>
                        <select name="destino_final" class="form-select">
                            <option value="capital_inversion">Reinvertir (capital de inversion)</option>
                            <option value="efectivo">Efectivo</option>
                            <option value="transferencia">Transferencia</option>
                        </select>
                    </div>
                </div>
                <div class="mt-3">
                    <small class="text-muted" id="rendimientoDisplayInv"></small>
                </div>
                <button type="submit" class="btn btn-primary mt-2"><i class="bi bi-plus-circle"></i> Crear inversion</button>
            </form>
        </div>
    </div>

    <?php if (!empty($inversiones)): ?>
    <div class="card mb-3">
        <div class="card-header"><strong>Mis inversiones</strong></div>
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
    <div class="alert alert-info">No tienes inversiones registradas.</div>
    <?php endif; ?>
</div>

<script>
document.querySelector('[name="monto"]')?.addEventListener('input', calcularRendimientoInv);
document.querySelector('[name="plazo"]')?.addEventListener('input', calcularRendimientoInv);

function cargarLimitesInv() {
    var sel = document.getElementById('selProdInv');
    var opt = sel.options[sel.selectedIndex];
    if (opt.value) {
        document.querySelector('[name="plazo"]').min = opt.dataset.min;
        document.querySelector('[name="plazo"]').max = opt.dataset.max;
        document.getElementById('plazoAyudaInv').textContent = 'Min: ' + opt.dataset.min + ', Max: ' + opt.dataset.max;
    }
}

function calcularRendimientoInv() {
    var sel = document.getElementById('selProdInv');
    var opt = sel.options[sel.selectedIndex];
    if (!opt.value) return;
    var monto = parseFloat(document.querySelector('[name="monto"]').value) || 0;
    var plazo = parseInt(document.querySelector('[name="plazo"]').value) || 0;
    var tasa = parseFloat(opt.dataset.tasa) || 0;
    var rend = monto * (tasa / 100 / 12) * plazo;
    document.getElementById('rendimientoDisplayInv').textContent = rend > 0 ? 'Rendimiento proyectado: $' + rend.toFixed(2) : '';
}
</script>
