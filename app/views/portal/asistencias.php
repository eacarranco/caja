<div class="container-fluid">
    <h4>Mis asistencias</h4>
    <?php if (empty($asistencias)): ?>
    <div class="alert alert-info">Sin registros de asistencia</div>
    <?php else: ?>
    <div class="table-responsive">
        <table class="table table-sm table-striped">
            <thead><tr><th>Sesión</th><th>Fecha</th><th>Tipo</th><th>Justificación</th><th>Estado</th><th>Acción</th></tr></thead>
            <tbody>
            <?php foreach ($asistencias as $a): ?>
            <tr>
                <td>#<?= $a['numero_sesion'] ?></td>
                <td><?= $a['fecha_sesion'] ?></td>
                <td><?= str_replace('_', ' ', ucfirst($a['tipo'])) ?></td>
                <td><?= htmlspecialchars(substr($a['justificacion'] ?? '-', 0, 60)) ?></td>
                <td>
                    <?php if ($a['tipo'] === 'a_tiempo'): ?><span class="badge bg-success">Asistió</span>
                    <?php elseif ($a['justificacion_aprobada']): ?><span class="badge bg-info">Justificada</span>
                    <?php elseif ($a['justificacion']): ?><span class="badge bg-warning">Pendiente revisión</span>
                    <?php else: ?><span class="badge bg-danger">Pendiente</span>
                    <?php endif; ?>
                </td>
                <td>
                    <?php if ($a['tipo'] !== 'a_tiempo' && empty($a['justificacion']) && !$a['justificacion_aprobada']): ?>
                    <button class="btn btn-sm btn-outline-warning" onclick="justificar('<?= $a['id_asistencia'] ?>')"><i class="bi bi-pencil-square"></i> Justificar</button>
                    <?php endif; ?>
                </td>
            </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php endif; ?>
</div>
<script>
function justificar(id) {
    var texto = prompt('Escriba su justificacion:');
    if (!texto || texto.trim() === '') return;
    var formData = new FormData();
    formData.append('csrf_token', '<?= $csrfToken ?? '' ?>');
    formData.append('justificacion', texto.trim());
    fetch('<?= BASE_URL ?>/asistencia/justificar/' + id, {
        method: 'POST', body: formData
    }).then(function(r) { return r.json(); }).then(function(d) {
        if (d.error) { mostrarNotificacion('error','Error',d.error,false); } else { mostrarNotificacion('success','Exito',d.mensaje,true); location.reload(); }
    });
}
</script>