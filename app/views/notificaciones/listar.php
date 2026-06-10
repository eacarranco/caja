<div class="container-fluid">
    <h4>Notificaciones</h4>
    <div class="row g-3">
        <!-- Panel izquierdo: Buzones -->
        <div class="col-md-3">
            <div class="card">
                <div class="list-group list-group-flush">
                    <a href="?buzon=entrada" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center <?= $buzonActual === 'entrada' ? 'active' : '' ?>">
                        <span><i class="bi bi-inbox-fill"></i> Entrada</span>
                        <span class="badge bg-danger rounded-pill"><?= $conteos['entrada'] ?? 0 ?></span>
                    </a>
                    <a href="?buzon=archivadas" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center <?= $buzonActual === 'archivadas' ? 'active' : '' ?>">
                        <span><i class="bi bi-archive-fill"></i> Archivadas</span>
                        <span class="badge bg-secondary rounded-pill"><?= $conteos['archivadas'] ?? 0 ?></span>
                    </a>
                    <a href="?buzon=papelera" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center <?= $buzonActual === 'papelera' ? 'active' : '' ?>">
                        <span><i class="bi bi-trash-fill"></i> Papelera</span>
                        <span class="badge bg-secondary rounded-pill"><?= $conteos['papelera'] ?? 0 ?></span>
                    </a>
                </div>
            </div>
            <?php if ($buzonActual === 'papelera'): ?>
            <div class="card mt-2">
                <div class="card-body small text-muted">
                    Las notificaciones se eliminan automaticamente despues de <strong><?= $retencionDias ?></strong> dias.
                </div>
            </div>
            <?php endif; ?>
        </div>

        <!-- Panel derecho: Lista de notificaciones -->
        <div class="col-md-9">
            <div class="card card-dashboard">
                <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
                    <div class="d-flex align-items-center gap-2">
                        <strong>
                            <?php if ($buzonActual === 'entrada'): ?><i class="bi bi-inbox-fill"></i> Entrada
                            <?php elseif ($buzonActual === 'archivadas'): ?><i class="bi bi-archive-fill"></i> Archivadas
                            <?php else: ?><i class="bi bi-trash-fill"></i> Papelera
                            <?php endif; ?>
                        </strong>
                        <?php if (!empty($notificaciones)): ?>
                        <div class="form-check ms-2">
                            <input type="checkbox" class="form-check-input" id="seleccionarTodo" onchange="toggleSeleccionarTodo()">
                        </div>
                        <div id="batchActions" style="display:none" class="d-flex gap-1">
                            <?php if ($buzonActual === 'entrada'): ?>
                            <button class="btn btn-sm btn-outline-success" onclick="batchLeer()"><i class="bi bi-check-lg"></i> Leer</button>
                            <button class="btn btn-sm btn-outline-info" onclick="batchArchivar()"><i class="bi bi-archive"></i> Archivar</button>
                            <button class="btn btn-sm btn-outline-danger" onclick="batchEliminar()"><i class="bi bi-trash"></i> Eliminar</button>
                            <?php elseif ($buzonActual === 'archivadas'): ?>
                            <button class="btn btn-sm btn-outline-primary" onclick="batchRestaurar()"><i class="bi bi-inbox"></i> Mover a entrada</button>
                            <button class="btn btn-sm btn-outline-danger" onclick="batchEliminar()"><i class="bi bi-trash"></i> Eliminar</button>
                            <?php elseif ($buzonActual === 'papelera'): ?>
                            <button class="btn btn-sm btn-outline-primary" onclick="batchRestaurar()"><i class="bi bi-inbox"></i> Restaurar</button>
                            <button class="btn btn-sm btn-outline-danger" onclick="batchDestruir()"><i class="bi bi-trash-fill"></i> Eliminar definitivo</button>
                            <?php endif; ?>
                        </div>
                        <?php endif; ?>
                    </div>
                    <?php if ($buzonActual === 'entrada' && !empty($notificaciones)): ?>
                    <button class="btn btn-sm btn-outline-secondary" onclick="leerTodas()"><i class="bi bi-check2-all"></i> Leidas todas</button>
                    <?php endif; ?>
                    <?php if ($buzonActual === 'papelera' && !empty($notificaciones)): ?>
                    <button class="btn btn-sm btn-outline-danger" onclick="vaciarPapelera()"><i class="bi bi-trash"></i> Vaciar papelera</button>
                    <?php endif; ?>
                </div>
                <div class="card-body p-0">
                    <?php if (empty($notificaciones)): ?>
                    <div class="text-center text-muted py-5">
                        <i class="bi bi-inbox" style="font-size:3rem"></i>
                        <p class="mt-2">No hay notificaciones en este buzon</p>
                    </div>
                    <?php else: ?>
                        <div class="list-group list-group-flush">
                        <?php foreach ($notificaciones as $n): ?>
                        <div class="list-group-item list-group-item-action <?= !$n['leida'] && $buzonActual === 'entrada' ? 'fw-bold' : '' ?>">
                            <div class="d-flex justify-content-between">
                                <div class="d-flex align-items-start gap-2 flex-grow-1">
                                    <div class="form-check mt-1">
                                        <input type="checkbox" class="form-check-input notif-check" value="<?= $n['id_notificacion'] ?>" onchange="actualizarBatchActions()">
                                    </div>
                                    <div>
                                        <div class="small text-muted"><?= $n['fecha_creacion'] ?></div>
                                        <div><?= htmlspecialchars($n['titulo']) ?></div>
                                        <div class="small text-muted"><?= htmlspecialchars($n['mensaje']) ?></div>
                                    </div>
                                </div>
                                <div class="ms-3 d-flex align-items-start gap-1" style="min-width:80px">
                                    <?php if ($buzonActual === 'entrada'): ?>
                                        <?php if (!$n['leida']): ?>
                                        <a href="#" onclick="marcarLeida('<?= $n['id_notificacion'] ?>')" class="btn btn-sm btn-outline-success" title="Marcar como leida"><i class="bi bi-check-lg"></i></a>
                                        <?php else: ?>
                                        <a href="#" onclick="marcarNoLeida('<?= $n['id_notificacion'] ?>')" class="btn btn-sm btn-outline-secondary" title="Marcar como no leida"><i class="bi bi-envelope"></i></a>
                                        <a href="#" onclick="archivar('<?= $n['id_notificacion'] ?>')" class="btn btn-sm btn-outline-info" title="Archivar"><i class="bi bi-archive"></i></a>
                                        <a href="#" onclick="eliminarNotif('<?= $n['id_notificacion'] ?>')" class="btn btn-sm btn-outline-danger" title="Eliminar"><i class="bi bi-trash"></i></a>
                                        <?php endif; ?>
                                    <?php elseif ($buzonActual === 'archivadas'): ?>
                                        <a href="#" onclick="restaurar('<?= $n['id_notificacion'] ?>')" class="btn btn-sm btn-outline-primary" title="Mover a entrada"><i class="bi bi-inbox"></i></a>
                                        <a href="#" onclick="eliminarNotif('<?= $n['id_notificacion'] ?>')" class="btn btn-sm btn-outline-danger" title="Eliminar"><i class="bi bi-trash"></i></a>
                                    <?php elseif ($buzonActual === 'papelera'): ?>
                                        <a href="#" onclick="restaurar('<?= $n['id_notificacion'] ?>')" class="btn btn-sm btn-outline-primary" title="Restaurar"><i class="bi bi-inbox"></i></a>
                                        <a href="#" onclick="destruir('<?= $n['id_notificacion'] ?>')" class="btn btn-sm btn-outline-danger" title="Eliminar definitivamente"><i class="bi bi-trash-fill"></i></a>
                                        <?php if ($n['fecha_eliminacion']): ?>
                                        <small class="text-muted" style="font-size:10px"><?= $retencionDias ?> dias</small>
                                        <?php endif; ?>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function marcarLeida(id) {
    fetch('<?= BASE_URL ?>/notificacion/leer/' + id, {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: 'csrf_token=<?= CSRFMiddleware::generarToken() ?>'
    }).then(function(r) { return r.json(); }).then(function() { location.reload(); });
}

function marcarNoLeida(id) {
    fetch('<?= BASE_URL ?>/notificacion/leer/' + id + '?no=1', {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: 'csrf_token=<?= CSRFMiddleware::generarToken() ?>'
    }).then(function(r) { return r.json(); }).then(function() { location.reload(); });
}

function leerTodas() {
    fetch('<?= BASE_URL ?>/notificacion/leerTodas', {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: 'csrf_token=<?= CSRFMiddleware::generarToken() ?>'
    }).then(function(r) { return r.json(); }).then(function() { location.reload(); });
}

function archivar(id) {
    fetch('<?= BASE_URL ?>/notificacion/archivar/' + id, {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: 'csrf_token=<?= CSRFMiddleware::generarToken() ?>'
    }).then(function(r) { return r.json(); }).then(function() { location.reload(); });
}

function eliminarNotif(id) {
    if (!confirm('¿Mover esta notificacion a la papelera?')) return;
    fetch('<?= BASE_URL ?>/notificacion/eliminar/' + id, {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: 'csrf_token=<?= CSRFMiddleware::generarToken() ?>'
    }).then(function(r) { return r.json(); }).then(function() { location.reload(); });
}

function restaurar(id) {
    fetch('<?= BASE_URL ?>/notificacion/restaurar/' + id, {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: 'csrf_token=<?= CSRFMiddleware::generarToken() ?>'
    }).then(function(r) { return r.json(); }).then(function() { location.reload(); });
}

function destruir(id) {
    if (!confirm('¿Eliminar esta notificacion definitivamente? No se puede deshacer.')) return;
    fetch('<?= BASE_URL ?>/notificacion/destruir/' + id, {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: 'csrf_token=<?= CSRFMiddleware::generarToken() ?>'
    }).then(function(r) { return r.json(); }).then(function() { location.reload(); });
}

function vaciarPapelera() {
    if (!confirm('¿Vaciar la papelera? Todas las notificaciones se eliminaran definitivamente.')) return;
    fetch('<?= BASE_URL ?>/notificacion/vaciarPapelera', {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: 'csrf_token=<?= CSRFMiddleware::generarToken() ?>'
    }).then(function(r) { return r.json(); }).then(function() { location.reload(); });
}

// Batch functions
function getSeleccionados() {
    var ids = [];
    document.querySelectorAll('.notif-check:checked').forEach(function(el) { ids.push(el.value); });
    return ids;
}

function toggleSeleccionarTodo() {
    var checked = document.getElementById('seleccionarTodo').checked;
    document.querySelectorAll('.notif-check').forEach(function(el) { el.checked = checked; });
    actualizarBatchActions();
}

function actualizarBatchActions() {
    var count = getSeleccionados().length;
    document.getElementById('batchActions').style.display = count > 0 ? 'inline-flex' : 'none';
}

function batchEjecutar(accion, msg) {
    var ids = getSeleccionados();
    if (ids.length === 0) return;
    if (msg && !confirm(msg.replace('{n}', ids.length))) return;
    var url = '<?= BASE_URL ?>/notificacion/' + accion;
    var promesas = ids.map(function(id) {
        return fetch(url + '/' + id, {
            method: 'POST',
            headers: {'Content-Type': 'application/x-www-form-urlencoded'},
            body: 'csrf_token=<?= CSRFMiddleware::generarToken() ?>'
        }).then(function(r) { return r.json(); });
    });
    Promise.all(promesas).then(function() { location.reload(); });
}

function batchLeer() { batchEjecutar('leer'); }
function batchArchivar() { batchEjecutar('archivar'); }
function batchEliminar() { batchEjecutar('eliminar', '¿Mover {n} notificaciones a la papelera?'); }
function batchRestaurar() { batchEjecutar('restaurar'); }
function batchDestruir() { batchEjecutar('destruir', '¿Eliminar definitivamente {n} notificaciones?'); }
</script>
