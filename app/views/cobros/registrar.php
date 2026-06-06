<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4>Registrar cobro — Sesión #<?= $sesion['número_sesión'] ?></h4>
        <a href="<?= BASE_URL ?>/sesion/checkin/<?= $sesion['id_sesión'] ?>" class="btn btn-outline-secondary"><i class="bi bi-arrow-left"></i> Volver</a>
    </div>

    <div class="card card-dashboard">
        <div class="card-body">
            <form id="formCobro">
                <?= CSRFMiddleware::campoHTML() ?>
                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label">Socio *</label>
                        <select name="id_socio" class="form-select" required>
                            <option value="">Seleccione...</option>
                            <?php foreach ($socios as $s): ?>
                            <option value="<?= $s['id_socio'] ?>"><?= htmlspecialchars($s['cédula'] . ' — ' . $s['nombre']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Tipo *</label>
                        <select name="tipo" class="form-select" required>
                            <?php foreach ($tiposCobro as $k => $v): ?>
                            <option value="<?= $k ?>"><?= $v ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Monto $ *</label>
                        <input type="number" step="0.01" min="0.01" name="monto" class="form-control" required>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Medio de pago</label>
                        <select name="medio_pago" class="form-select">
                            <?php foreach ($mediosPago as $k => $v): ?>
                            <option value="<?= $k ?>"><?= $v ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-1 d-flex align-items-end">
                        <button type="submit" class="btn btn-primary"><i class="bi bi-check-lg"></i></button>
                    </div>
                </div>
            </form>
            <div id="resultadoCobro" class="mt-3"></div>
        </div>
    </div>
</div>

<script>
document.getElementById('formCobro').addEventListener('submit', function(e) {
    e.preventDefault();
    var f = new FormData(this);
    f.append('csrf_token', document.querySelector('[name="csrf_token"]').value);
    fetch('<?= BASE_URL ?>/cobro/registrar/<?= $sesion['id_sesión'] ?>', {
        method: 'POST', body: f
    }).then(function(r) { return r.json(); }).then(function(d) {
        if (d.error) {
            document.getElementById('resultadoCobro').innerHTML = '<div class="alert alert-danger">' + d.error + '</div>';
        } else {
            document.getElementById('resultadoCobro').innerHTML = '<div class="alert alert-success">Cobro registrado</div>';
            document.querySelector('[name="monto"]').value = '';
        }
    }).catch(function() {
        document.getElementById('resultadoCobro').innerHTML = '<div class="alert alert-danger">Error al registrar</div>';
    });
});
</script>
