<div class="container-fluid">
    <h4>Imagen corporativa</h4>

    <div class="row g-3">
        <div class="col-md-6">
            <div class="card card-dashboard">
                <div class="card-body">
                    <h5>Logo institucional</h5>
                    <?php
                    $logo = '';
                    foreach ($params as $p) {
                        if ($p['código'] === 'img.logo') $logo = $p['valor'];
                    }
                    ?>
                    <div class="mb-3">
                        <img id="previewLogo" src="<?= BASE_URL ?>/<?= $logo ?>"
                             style="max-height:120px; max-width:100%" alt="Logo actual">
                    </div>
                    <form id="formLogo" enctype="multipart/form-data">
                        <?= CSRFMiddleware::campoHTML() ?>
                        <input type="file" name="logo" class="form-control mb-2" accept=".png,.jpg,.jpeg,.svg" required>
                        <button type="submit" class="btn btn-primary"><i class="bi bi-upload"></i> Subir logo</button>
                    </form>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card card-dashboard">
                <div class="card-body">
                    <h5>Colores institucionales</h5>
                    <?php foreach ($params as $p):
                        if (strpos($p['código'], 'color.') !== 0) continue; ?>
                    <div class="mb-2 d-flex align-items-center">
                        <label class="me-2" style="min-width:120px"><?= htmlspecialchars($p['nombre']) ?></label>
                        <input type="color" class="form-control form-control-color w-auto"
                               value="<?= htmlspecialchars($p['valor']) ?>"
                               onchange="guardarColor('<?= $p['código'] ?>', this.value)">
                        <code class="ms-2 small"><?= htmlspecialchars($p['valor']) ?></code>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.getElementById('formLogo')?.addEventListener('submit', function(e) {
    e.preventDefault();
    var f = new FormData(this);
    f.append('csrf_token', document.querySelector('[name="csrf_token"]').value);
    fetch('<?= BASE_URL ?>/imagen/subirLogo', { method: 'POST', body: f })
    .then(function(r) { return r.json(); })
    .then(function(d) {
        if (d.error) { alert(d.error); } else {
            document.getElementById('previewLogo').src = '<?= BASE_URL ?>/' + f.get('logo').name + '?_=' + Date.now();
            location.reload();
        }
    });
});

function guardarColor(codigo, valor) {
    fetch('<?= BASE_URL ?>/imagen/guardarColor', {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: 'csrf_token=<?= CSRFMiddleware::generarToken() ?>&codigo=' + encodeURIComponent(codigo) + '&valor=' + encodeURIComponent(valor)
    });
}
</script>
