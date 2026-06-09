<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4>Nueva inversión</h4>
        <a href="<?= BASE_URL ?>/inversion/listar" class="btn btn-outline-secondary"><i class="bi bi-arrow-left"></i> Volver</a>
    </div>

    <div class="card card-dashboard">
        <div class="card-body">
            <?php if (isset($errors['general'])): ?><div class="alert alert-danger"><?= $errors['general'] ?></div><?php endif; ?>
            <form method="POST">
                <?= CSRFMiddleware::campoHTML() ?>
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">Socio *</label>
                        <select name="id_socio" class="form-select <?= isset($errors['id_socio']) ? 'is-invalid' : '' ?>" required>
                            <option value="">Seleccione...</option>
                            <?php foreach ($socios as $s): ?>
                            <option value="<?= $s['id_socio'] ?>"><?= htmlspecialchars($s['cedula'] . ' — ' . $s['nombre']) ?> (Cap. Inv.: $<?= number_format($s['capital_inversion'], 2) ?>)</option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Producto *</label>
                        <select name="id_producto" id="selProd" class="form-select <?= isset($errors['id_producto']) ? 'is-invalid' : '' ?>" required onchange="cargarLimites()">
                            <option value="">Seleccione...</option>
                            <?php foreach ($productos as $p): ?>
                            <option value="<?= $p['id_producto'] ?>" data-tasa="<?= $p['tasa_interes_anual'] ?>" data-min="<?= $p['plazo_min_meses'] ?>" data-max="<?= $p['plazo_max_meses'] ?>"><?= htmlspecialchars($p['nombre']) ?> (<?= $p['tasa_interes_anual'] ?>%)</option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Monto a invertir $ *</label>
                        <input type="number" step="0.01" min="1" name="monto" class="form-control <?= isset($errors['monto']) ? 'is-invalid' : '' ?>" required>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Plazo (meses) *</label>
                        <input type="number" min="1" name="plazo" class="form-control <?= isset($errors['plazo']) ? 'is-invalid' : '' ?>" required>
                        <small class="text-muted" id="plazoAyuda"></small>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Rendimiento proyectado</label>
                        <input type="text" id="rendimientoDisplay" class="form-control" readonly>
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
                <button type="submit" class="btn btn-primary mt-3"><i class="bi bi-check-lg"></i> Registrar inversion</button>
            </form>
        </div>
    </div>
</div>

<script>
document.querySelector('[name="monto"]')?.addEventListener('input', calcularRendimiento);
document.querySelector('[name="plazo"]')?.addEventListener('input', calcularRendimiento);

function cargarLimites() {
    var sel = document.getElementById('selProd');
    var opt = sel.options[sel.selectedIndex];
    if (opt.value) {
        document.querySelector('[name="plazo"]').min = opt.dataset.min;
        document.querySelector('[name="plazo"]').max = opt.dataset.max;
        document.querySelector('[name="plazo"]').value = opt.dataset.min;
        document.getElementById('plazoAyuda').textContent = 'Mín: ' + opt.dataset.min + ', Máx: ' + opt.dataset.max;
    }
}

function calcularRendimiento() {
    var sel = document.getElementById('selProd');
    var opt = sel.options[sel.selectedIndex];
    if (!opt.value) return;
    var monto = parseFloat(document.querySelector('[name="monto"]').value) || 0;
    var plazo = parseInt(document.querySelector('[name="plazo"]').value) || 0;
    var tasa = parseFloat(opt.dataset.tasa) || 0;
    var rendimiento = monto * (tasa / 100 / 12) * plazo;
    document.getElementById('rendimientoDisplay').value = '$' + rendimiento.toFixed(2);
}
</script>
