<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4>Productos financieros</h4>
        <a href="<?= BASE_URL ?>/producto/registrar" class="btn btn-primary"><i class="bi bi-plus-circle"></i> Nuevo producto</a>
    </div>

    <div class="card card-dashboard">
        <div class="card-body p-0">
            <div class="table-responsive"><table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Nombre</th>
                        <th>Tipo</th>
                        <th>Tasa interes</th>
                        <th>Método</th>
                        <th>Plazo (meses)</th>
                        <th>Monto</th>
                        <th>Estado</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($productos as $p): ?>
                    <tr>
                        <td><strong><?= htmlspecialchars($p['nombre']) ?></strong></td>
                        <td><span class="badge <?= $p['tipo'] === 'credito' ? 'bg-warning' : 'bg-info' ?>"><?= $tipos[$p['tipo']] ?></span>
                            <?php if (!empty($p['es_emergente'])): ?><span class="badge bg-danger ms-1">Emergente</span><?php endif; ?>
                            <?php if (!empty($p['requiere_documento_firmado'])): ?><span class="badge bg-secondary ms-1">PDF</span><?php endif; ?>
                        </td>
                        <td><?= number_format($p['tasa_interes_anual'], 2) ?>%</td>
                        <td><?= $metodos[$p['metodo_interes']] ?></td>
                        <td><?= $p['plazo_min_meses'] ?> - <?= $p['plazo_max_meses'] ?></td>
                        <td>$<?= number_format($p['monto_min'], 2) ?> - $<?= number_format($p['monto_max'], 2) ?></td>
                        <td>
                            <span class="badge <?= $p['activo'] ? 'bg-success' : 'bg-secondary' ?>">
                                <?= $p['activo'] ? 'Activo' : 'Inactivo' ?>
                            </span>
                        </td>
                        <td>
                            <a href="<?= BASE_URL ?>/producto/editar/<?= $p['id_producto'] ?>" class="btn btn-sm btn-outline-primary"><i class="bi bi-pencil"></i></a>
                            <?php if (empty($dependencias[$p['id_producto']])): ?>
                            <a href="#" onclick="eliminar('<?= $p['id_producto'] ?>', '<?= htmlspecialchars($p['nombre'], ENT_QUOTES) ?>')" class="btn btn-sm btn-outline-danger" title="Eliminar">
                                <i class="bi bi-trash"></i>
                            </a>
                            <?php endif; ?>
                            <a href="#" onclick="toggleEstado('<?= $p['id_producto'] ?>')" class="btn btn-sm btn-outline-<?= $p['activo'] ? 'warning' : 'success' ?>"
                               title="<?= $p['activo'] ? 'Desactivar' : 'Activar' ?>">
                               <i class="bi bi-<?= $p['activo'] ? 'pause-circle' : 'play-circle' ?>"></i>
                            </a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table></div>
        </div>
    </div>
</div>

<script>
function toggleEstado(id) {
    if (!confirm('¿Cambiar estado de este producto?')) return;
    fetch('<?= BASE_URL ?>/producto/toggleEstado/' + id, {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: 'csrf_token=<?= CSRFMiddleware::generarToken() ?>'
    }).then(function(r) { return r.json(); }).then(function(d) {
        if (d.error) { alert(d.error); } else { location.reload(); }
    });
}

function eliminar(id, nombre) {
    if (!confirm('¿Eliminar el producto "' + nombre + '"? Esta accion no se puede deshacer.')) return;
    fetch('<?= BASE_URL ?>/producto/eliminar/' + id, {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: 'csrf_token=<?= CSRFMiddleware::generarToken() ?>'
    }).then(function(r) { return r.json(); }).then(function(d) {
        if (d.error) { alert(d.error); } else { location.reload(); }
    });
}
</script>
