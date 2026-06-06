<div class="container-fluid">
    <h4>Asistencias</h4>

    <form method="GET" class="row g-2 mb-3">
        <div class="col-auto">
            <input type="text" name="socio" class="form-control form-control-sm" placeholder="Buscar socio..." value="<?= htmlspecialchars($filtroSocio) ?>">
        </div>
        <div class="col-auto">
            <select name="tipo" class="form-select form-select-sm">
                <option value="">Todos los tipos</option>
                <option value="a_tiempo" <?= $filtroTipo === 'a_tiempo' ? 'selected' : '' ?>>A tiempo</option>
                <option value="retraso_10min" <?= $filtroTipo === 'retraso_10min' ? 'selected' : '' ?>>Retraso 10 min</option>
                <option value="retraso_30min" <?= $filtroTipo === 'retraso_30min' ? 'selected' : '' ?>>Retraso 30 min</option>
                <option value="falta" <?= $filtroTipo === 'falta' ? 'selected' : '' ?>>Falta</option>
            </select>
        </div>
        <div class="col-auto">
            <select name="sesion" class="form-select form-select-sm">
                <option value="">Todas las sesiones</option>
                <?php foreach ($sesiones as $ses): ?>
                <option value="<?= $ses['id_sesión'] ?>" <?= $filtroSesion === $ses['id_sesión'] ? 'selected' : '' ?>>#<?= $ses['número_sesión'] ?> Ã¢â‚¬â€ <?= $ses['fecha'] ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-auto">
            <button type="submit" class="btn btn-sm btn-primary"><i class="bi bi-filter"></i> Filtrar</button>
            <a href="<?= BASE_URL ?>/asistencia/listar" class="btn btn-sm btn-outline-secondary"><i class="bi bi-x-circle"></i></a>
        </div>
    </form>

    <div class="card card-dashboard">
        <div class="card-body p-0">
            <div class="table-responsive"><table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr><th>Fecha</th><th>Sesión</th><th>Socio</th><th>Cédula</th><th>Tipo</th><th>Justificación</th><?php if (RBAC::tienePermiso($_SESSION['usuario_id'], 'socio.cambiar_estado')): ?><th>Acción</th><?php endif; ?></tr>
                </thead>
                <tbody>
                    <?php foreach ($asistencias as $a): ?>
                    <tr>
                        <td><?= $a['fecha_registro'] ?></td>
                        <td>#<?= $a['número_sesión'] ?> (<?= $a['fecha_sesión'] ?>)</td>
                        <td><?= htmlspecialchars($a['socio']) ?></td>
                        <td><?= $a['cédula'] ?></td>
                        <td>
                            <span class="badge bg-<?= match($a['tipo']) { 'a_tiempo'=>'success', 'retraso_10min'=>'warning', 'retraso_30min'=>'danger', 'falta'=>'dark', default=>'secondary' } ?>">
                                <?= str_replace('_', ' ', $a['tipo']) ?>
                            </span>
                        </td>
                        <td>
                            <?php if ($a['justificación']): ?>
                            <span class="badge bg-info">Enviada</span>
                            <?php if ($a['justificación_aprobada'] === '1'): ?><span class="badge bg-success">Aprobada</span>
                            <?php elseif ($a['justificación_aprobada'] === '0'): ?><span class="badge bg-danger">Rechazada</span>
                            <?php endif; ?>
                            <?php else: ?>
                            <span class="text-muted">Ã¢â‚¬â€</span>
                            <?php endif; ?>
                        </td>
                        <?php if (RBAC::tienePermiso($_SESSION['usuario_id'], 'socio.cambiar_estado')): ?>
                        <td>
                            <?php if ($a['justificación'] && $a['justificación_aprobada'] === '0'): ?>
                            <button class="btn btn-sm btn-outline-success" onclick="aprobarJustif('<?= $a['id_asistencia'] ?>','aprobar')"><i class="bi bi-check-lg"></i></button>
                            <?php endif; ?>
                            <?php if ($a['justificación'] && $a['justificación_aprobada'] === '0'): ?>
                            <button class="btn btn-sm btn-outline-danger" onclick="aprobarJustif('<?= $a['id_asistencia'] ?>','rechazar')"><i class="bi bi-x-lg"></i></button>
                            <?php endif; ?>
                        </td>
                        <?php endif; ?>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table></div>
        </div>
    </div>

    <?php if ($totalPaginas > 1): ?>
    <nav class="mt-3"><ul class="pagination pagination-sm">
        <?php for ($i = 1; $i <= $totalPaginas; $i++): ?>
        <li class="page-item <?= $i === $page ? 'active' : '' ?>">
            <a class="page-link" href="?p=<?= $i ?>&socio=<?= urlencode($filtroSocio) ?>&tipo=<?= urlencode($filtroTipo) ?>&sesion=<?= urlencode($filtroSesion) ?>"><?= $i ?></a>
        </li>
        <?php endfor; ?>
    </ul></nav>
    <?php endif; ?>
</div>
<?php if (RBAC::tienePermiso($_SESSION['usuario_id'], 'socio.cambiar_estado')): ?>
<script>
function aprobarJustif(id, accion) {
    if (!confirm('Ã‚¿' + (accion === 'aprobar' ? 'Aprobar' : 'Rechazar') + ' esta justificación?')) return;
    var formData = new FormData();
    formData.append('csrf_token', '<?= $csrfToken ?? '' ?>');
    formData.append('accion', accion);
    fetch('<?= BASE_URL ?>/asistencia/aprobarJustificacion/' + id, {
        method: 'POST', body: formData
    }).then(function(r) { return r.json(); }).then(function(d) {
        if (d.error) { alert(d.error); } else { alert(d.mensaje); location.reload(); }
    });
}
</script>
<?php endif; ?>
