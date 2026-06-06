<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4>Nueva solicitud de crédito</h4>
        <a href="<?= BASE_URL ?>/credito/listar" class="btn btn-outline-secondary"><i class="bi bi-arrow-left"></i> Volver</a>
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
                            <option value="<?= $s['id_socio'] ?>"><?= htmlspecialchars($s['cédula'] . ' — ' . $s['nombre']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Producto *</label>
                        <select name="id_producto" id="selProd" class="form-select <?= isset($errors['id_producto']) ? 'is-invalid' : '' ?>" required onchange="cargarLimites()">
                            <option value="">Seleccione...</option>
                            <?php foreach ($productos as $p): ?>
                            <option value="<?= $p['id_producto'] ?>" data-tasa="<?= $p['tasa_interés_anual'] ?>" data-min="<?= $p['plazo_mín_meses'] ?>" data-max="<?= $p['plazo_máx_meses'] ?>" data-monto-min="<?= $p['monto_mín'] ?>" data-monto-max="<?= $p['monto_máx'] ?>"><?= htmlspecialchars($p['nombre']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Monto solicitado $ *</label>
                        <input type="number" step="0.01" min="1" name="monto" class="form-control <?= isset($errors['monto']) ? 'is-invalid' : '' ?>" required>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Plazo (meses) *</label>
                        <input type="number" min="1" name="plazo" class="form-control <?= isset($errors['plazo']) ? 'is-invalid' : '' ?>" required>
                        <small id="plazoAyuda" class="text-muted"></small>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Tasa interés anual</label>
                        <input type="text" id="tasaDisplay" class="form-control" readonly>
                    </div>
                    <div class="col-md-12">
                        <label class="form-label">Destino del crédito</label>
                        <textarea name="destino" class="form-control" rows="3"></textarea>
                    </div>
                    <div class="col-md-12" id="garantesGroup" style="display:none">
                        <label class="form-label">Garantes <small class="text-muted">(seleccione uno o más socios activos)</small></label>
                        <div class="row g-2">
                            <?php foreach ($socios as $s): ?>
                            <div class="col-md-4">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="garantes[]" value="<?= $s['id_socio'] ?>" id="g<?= substr($s['id_socio'], 0, 8) ?>">
                                    <label class="form-check-label" for="g<?= substr($s['id_socio'], 0, 8) ?>"><?= htmlspecialchars($s['cédula'] . ' — ' . $s['nombre']) ?></label>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                        <?php if (isset($errors['garantes'])): ?><div class="text-danger small mt-1"><?= $errors['garantes'] ?></div><?php endif; ?>
                    </div>
                </div>
                <button type="submit" class="btn btn-primary mt-3"><i class="bi bi-send"></i> Enviar solicitud</button>
            </form>
        </div>
    </div>
</div>

<script>
<?php
$prodReqGarante = [];
foreach ($productos as $p) {
    if ($p['requiere_garante']) $prodReqGarante[] = $p['id_producto'];
}
?>
var productosConGarante = <?= json_encode($prodReqGarante) ?>;

function cargarLimites() {
    var sel = document.getElementById('selProd');
    var opt = sel.options[sel.selectedIndex];
    if (opt.value) {
        document.querySelector('[name="plazo"]').min = opt.dataset.min;
        document.querySelector('[name="plazo"]').max = opt.dataset.max;
        document.querySelector('[name="plazo"]').value = opt.dataset.min;
        document.getElementById('plazoAyuda').textContent = 'Mín: ' + opt.dataset.min + ', Máx: ' + opt.dataset.max;
        document.getElementById('tasaDisplay').value = opt.dataset.tasa + '%';
        document.querySelector('[name="monto"]').min = opt.dataset.montoMin;
        document.querySelector('[name="monto"]').max = opt.dataset.montoMax;
        document.querySelector('[name="monto"]').value = opt.dataset.montoMin;
        document.getElementById('garantesGroup').style.display = productosConGarante.includes(opt.value) ? 'block' : 'none';
    }
}
</script>
