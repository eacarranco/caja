<div class="container-fluid">
    <h4>Mis multas</h4>

    <?php if (empty($multas)): ?>
    <div class="card card-dashboard"><div class="card-body text-muted">Sin multas registradas</div></div>
    <?php else: ?>
    <div class="card card-dashboard">
        <div class="card-body p-0">
            <div class="table-responsive"><table class="table table-hover mb-0 table-responsive-stack">
                <thead class="table-light">
                    <tr><th>Fecha</th><th>Tipo</th><th>Monto</th><th>Justificación</th><th>Pagada</th><th></th></tr>
                </thead>
                <tbody>
                <?php foreach ($multas as $m): ?>
                <tr>
                    <td data-label="Fecha"><?= $m['fecha_generacion'] ?></td>
                    <td data-label="Tipo"><span class="badge bg-<?= $m['tipo'] === 'inasistencia' ? 'danger' : ($m['tipo'] === 'cuota_impaga' ? 'dark' : ($m['tipo'] === 'mora_credito' ? 'warning' : 'info')) ?>"><?= str_replace('_', ' ', $m['tipo']) ?></span></td>
                    <td data-label="Monto"><strong>$<?= number_format($m['monto'], 2) ?></strong></td>
                    <td data-label="Justif.">
                        <?php if ($m['justificacion']): ?>
                        <span class="badge bg-success">Enviada</span>
                        <?php if ($m['justificacion_aprobada'] === '1'): ?><span class="badge bg-primary">Aprobada</span>
                        <?php elseif ($m['justificacion_aprobada'] === '0'): ?><span class="badge bg-danger">Rechazada</span>
                        <?php endif; ?>
                        <?php else: ?>
                        <span class="badge bg-secondary">Pendiente</span>
                        <?php endif; ?>
                    </td>
                    <td data-label="Estado">
                        <?php if ($m['pagada'] > 0): ?><span class="badge bg-success">Pagada</span>
                        <?php elseif ($m['estado'] === 'en_impugnacion'): ?><span class="badge bg-warning text-dark">En impugnación</span>
                        <?php elseif ($m['estado'] === 'impugnada'): ?><span class="badge bg-success">Impugnada (sin efecto)</span>
                        <?php elseif ($m['estado'] === 'anulada'): ?><span class="badge bg-dark">Anulada</span>
                        <?php else: ?><span class="badge bg-danger">Pendiente</span><?php endif; ?>
                    </td>
                    <td data-label="Acción">
                        <button class="btn btn-sm btn-outline-info" onclick="verDetalle('<?= addslashes($m['id_multa']) ?>', '<?= addslashes(str_replace('_', ' ', $m['tipo'])) ?>', <?= $m['monto'] ?>, '<?= addslashes($m['fecha_generacion']) ?>', '<?= addslashes($m['justificacion'] ?? '') ?>', '<?= addslashes($m['justificacion_pdf'] ?? '') ?>', '<?= $m['justificacion_aprobada'] ?? '' ?>', <?= $m['pagada'] > 0 ? 'true' : 'false' ?>, '<?= $m['estado'] ?>')" title="Ver detalle"><i class="bi bi-eye"></i></button>
                        <?php if (!$m['justificacion'] && $m['estado'] === 'activa' && !$m['pagada']): ?>
                        <button class="btn btn-sm btn-outline-warning" onclick="mostrarFormImpugnar('<?= $m['id_multa'] ?>')" title="Impugnar"><i class="bi bi-shield-exclamation"></i></button>
                        <?php elseif ($m['estado'] === 'en_impugnacion'): ?>
                        <span class="badge bg-warning text-dark">En revisión</span>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; ?>
                </tbody>
            </table></div>
        </div>
    </div>
    <?php endif; ?>
</div>

<!-- Modal Ver Detalle (overlay CSS puro) -->
<div id="detalleOverlay" style="display:none;position:fixed;top:0;left:0;right:0;bottom:0;z-index:100000;background:rgba(0,0,0,0.5);justify-content:center;align-items:center">
    <div style="background:#fff;border-radius:12px;padding:2rem 1.5rem;max-width:420px;width:90%;box-shadow:0 20px 60px rgba(0,0,0,0.3);animation:notifFadeIn 0.2s ease-out">
        <h5 class="mb-3">Detalle de multa</h5>
        <table class="table table-sm table-borderless mb-3">
            <tr><td class="text-muted">Tipo:</td><td class="fw-bold" id="detTipo"></td></tr>
            <tr><td class="text-muted">Monto:</td><td class="fw-bold text-danger" id="detMonto"></td></tr>
            <tr><td class="text-muted">Fecha:</td><td id="detFecha"></td></tr>
            <tr><td class="text-muted">Estado:</td><td id="detEstado"></td></tr>
        </table>
        <div id="detJustificacion" style="display:none">
            <hr>
            <h6>Justificación / Impugnación</h6>
            <p class="small text-muted" id="detJustTexto"></p>
            <div id="detJustArchivo" style="display:none" class="mb-2">
                <a id="detJustLink" href="#" target="_blank" class="btn btn-sm btn-outline-primary"><i class="bi bi-file-earmark-pdf"></i> Ver archivo</a>
            </div>
            <span id="detJustEstado" class="badge"></span>
        </div>
        <div class="text-center mt-3">
            <button class="btn btn-primary px-4" onclick="document.getElementById('detalleOverlay').style.display='none'">Cerrar</button>
        </div>
    </div>
</div>

<!-- Modal Impugnar (overlay CSS puro) -->
<div id="impugnarOverlay" style="display:none;position:fixed;top:0;left:0;right:0;bottom:0;z-index:100000;background:rgba(0,0,0,0.5);justify-content:center;align-items:center">
    <div style="background:#fff;border-radius:12px;padding:2rem 1.5rem;max-width:420px;width:90%;box-shadow:0 20px 60px rgba(0,0,0,0.3);animation:notifFadeIn 0.2s ease-out">
        <h5 class="mb-3">Impugnar multa</h5>
        <form id="formImpugnar" method="POST" enctype="multipart/form-data">
            <input type="hidden" name="csrf_token" value="<?= CSRFMiddleware::generarToken() ?>">
            <div class="mb-3">
                <label class="form-label">Explicación *</label>
                <textarea name="justificacion" class="form-control" rows="4" required></textarea>
            </div>
            <div class="mb-3">
                <label class="form-label">Archivo (PDF, JPG, PNG) <small class="text-muted">opcional</small></label>
                <input type="file" name="archivo" class="form-control" accept=".pdf,.jpg,.jpeg,.png">
            </div>
            <div class="d-flex gap-2 justify-content-center">
                <button type="button" class="btn btn-outline-secondary px-4" onclick="document.getElementById('impugnarOverlay').style.display='none'">Cancelar</button>
                <button type="submit" class="btn btn-primary px-4">Enviar</button>
            </div>
        </form>
    </div>
</div>

<script>
var multaImpugnarId = null;

function verDetalle(id, tipo, monto, fecha, justificacion, justPdf, justAprobada, pagada, estado) {
    document.getElementById('detTipo').textContent = tipo;
    document.getElementById('detMonto').textContent = '$' + parseFloat(monto).toFixed(2);
    document.getElementById('detFecha').textContent = fecha;
    var estHtml = '';
    if (pagada) estHtml = '<span class="badge bg-success">Pagada</span>';
    else if (estado === 'impugnada') estHtml = '<span class="badge bg-success">Impugnada (sin efecto)</span>';
    else if (estado === 'en_impugnacion') estHtml = '<span class="badge bg-warning text-dark">En impugnación</span>';
    else if (estado === 'anulada') estHtml = '<span class="badge bg-dark">Anulada</span>';
    else estHtml = '<span class="badge bg-danger">Pendiente</span>';
    document.getElementById('detEstado').innerHTML = estHtml;

    var justDiv = document.getElementById('detJustificacion');
    if (justificacion) {
        justDiv.style.display = 'block';
        document.getElementById('detJustTexto').textContent = justificacion;
        var link = document.getElementById('detJustLink');
        if (justPdf) {
            document.getElementById('detJustArchivo').style.display = 'block';
            link.href = '<?= BASE_URL ?>/storage/documentos/' + justPdf;
        } else {
            document.getElementById('detJustArchivo').style.display = 'none';
        }
        var badge = document.getElementById('detJustEstado');
        if (justAprobada === '1') { badge.className = 'badge bg-success'; badge.textContent = 'Aprobada'; }
        else if (justAprobada === '0') { badge.className = 'badge bg-danger'; badge.textContent = 'Rechazada'; }
        else { badge.className = 'badge bg-warning text-dark'; badge.textContent = 'Pendiente de revisión'; }
    } else {
        justDiv.style.display = 'none';
    }
    document.getElementById('detalleOverlay').style.display = 'flex';
}

function mostrarFormImpugnar(id) {
    multaImpugnarId = id;
    document.getElementById('formImpugnar').reset();
    document.getElementById('impugnarOverlay').style.display = 'flex';
}

document.getElementById('formImpugnar').addEventListener('submit', function(e) {
    e.preventDefault();
    var formData = new FormData(this);
    fetch('<?= BASE_URL ?>/multa/justificar/' + multaImpugnarId, { method: 'POST', body: formData })
    .then(function(r) { return r.json(); })
    .then(function(d) {
        if (d.error) { mostrarNotificacion('error','Error',d.error,false); } else { mostrarNotificacion('success','Exito',d.mensaje,true); location.reload(); }
    })
    .catch(function() { mostrarNotificacion('error','Error','Error al enviar',false); });
});

// Cerrar overlays al hacer clic fuera
document.getElementById('detalleOverlay').addEventListener('click', function(e) { if (e.target === this) this.style.display = 'none'; });
document.getElementById('impugnarOverlay').addEventListener('click', function(e) { if (e.target === this) this.style.display = 'none'; });
</script>
