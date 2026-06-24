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
                    <td data-label="Tipo"><span class="badge bg-<?= $m['tipo'] === 'inasistencia' ? 'danger' : 'info' ?>"><?= str_replace('_', ' ', $m['tipo']) ?></span></td>
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
                    <td data-label="Estado"><?= $m['pagada'] ? '<span class="badge bg-success">Pagada</span>' : '<span class="badge bg-danger">Pendiente</span>' ?></td>
                    <td data-label="Acción">
                        <?php if (!$m['justificacion'] && $m['estado'] === 'activa'): ?>
                        <button class="btn btn-sm btn-outline-warning" onclick="mostrarFormJustificacion('<?= $m['id_multa'] ?>')"><i class="bi bi-pencil"></i></button>
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

<div class="modal fade" id="modalJustificacion" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="formJustificacion" method="POST" enctype="multipart/form-data">
                <div class="modal-header"><h5 class="modal-title">Justificar multa</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
                <div class="modal-body">
                    <input type="hidden" name="csrf_token" value="<?= CSRFMiddleware::generarToken() ?>">
                    <div class="mb-3">
                        <label class="form-label">Explicación</label>
                        <textarea name="justificacion" class="form-control" rows="4" required></textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Archivo (PDF, JPG, PNG)</label>
                        <input type="file" name="archivo" class="form-control" accept=".pdf,.jpg,.jpeg,.png">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary">Enviar justificacion</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
var multaJustificarId = null;
function mostrarFormJustificacion(id) {
    multaJustificarId = id;
    document.getElementById('formJustificacion').reset();
    new bootstrap.Modal(document.getElementById('modalJustificacion')).show();
}

document.getElementById('formJustificacion').addEventListener('submit', function(e) {
    e.preventDefault();
    var formData = new FormData(this);
    fetch('<?= BASE_URL ?>/multa/justificar/' + multaJustificarId, { method: 'POST', body: formData })
    .then(function(r) { return r.json(); })
    .then(function(d) {
        if (d.error) { mostrarNotificacion('error','Error',d.error,false); } else { mostrarNotificacion('success','Exito',d.mensaje,true); location.reload(); }
    })
    .catch(function() { mostrarNotificacion('error','Error','Error al enviar justificacion',false); });
});
</script>
