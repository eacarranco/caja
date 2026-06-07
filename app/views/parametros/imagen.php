<?php
$logoSidebarId = '';
$logoSdId = '';
foreach ($params as $p) {
    if ($p['código'] === 'logo_sidebar') $logoSidebarId = $p['valor'];
    if ($p['código'] === 'logo_sd') $logoSdId = $p['valor'];
}
function fmPreview($idArchivo, $baseUrl) {
    if (empty($idArchivo)) return '';
    $meta = FileManager::get($idArchivo);
    if (!$meta) return '';
    $src = $baseUrl . '/archivo/ver/' . $idArchivo;
    $mime = $meta['mime_type'] ?? '';
    if (strpos($mime, 'image/') === 0) {
        return '<img src="' . $src . '" style="max-height:80px;max-width:100%" alt="preview">';
    }
    return '<i class="bi bi-file-earmark fs-1 text-muted"></i>';
}
?>
<div class="container-fluid">
    <h4>Imagen corporativa</h4>

    <div class="row g-3">
        <div class="col-md-6">
            <div class="card card-dashboard">
                <div class="card-body">
                    <h5>Logo del sidebar</h5>
                    <div class="mb-3 text-center p-3 bg-light rounded">
                        <?= fmPreview($logoSidebarId, BASE_URL) ?>
                        <?php if (empty($logoSidebarId)): ?>
                        <p class="text-muted small mb-0">Sin imagen</p>
                        <?php endif; ?>
                    </div>
                    <form class="fmUploadForm" data-codigo="logo_sidebar" enctype="multipart/form-data">
                        <?= CSRFMiddleware::campoHTML() ?>
                        <input type="file" name="archivo" class="form-control mb-2" accept="image/png,image/jpeg,image/svg+xml" required>
                        <button type="submit" class="btn btn-primary"><i class="bi bi-upload"></i> Subir</button>
                    </form>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card card-dashboard">
                <div class="card-body">
                    <h5>Logo sin fondo</h5>
                    <div class="mb-3 text-center p-3 bg-light rounded">
                        <?= fmPreview($logoSdId, BASE_URL) ?>
                        <?php if (empty($logoSdId)): ?>
                        <p class="text-muted small mb-0">Sin imagen</p>
                        <?php endif; ?>
                    </div>
                    <form class="fmUploadForm" data-codigo="logo_sd" enctype="multipart/form-data">
                        <?= CSRFMiddleware::campoHTML() ?>
                        <input type="file" name="archivo" class="form-control mb-2" accept="image/png,image/jpeg,image/svg+xml" required>
                        <button type="submit" class="btn btn-primary"><i class="bi bi-upload"></i> Subir</button>
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
document.querySelectorAll('.fmUploadForm').forEach(function(form) {
    form.addEventListener('submit', function(e) {
        e.preventDefault();
        var f = new FormData(this);
        f.append('csrf_token', document.querySelector('[name="csrf_token"]').value);
        f.append('codigo', this.dataset.codigo);
        fetch('<?= BASE_URL ?>/imagen/subirImagenParam', { method: 'POST', body: f })
        .then(function(r) { return r.json(); })
        .then(function(d) {
            if (d.error) { alert(d.error); } else { location.reload(); }
        });
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
