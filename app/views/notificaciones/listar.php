<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4>Notificaciones</h4>
        <button class="btn btn-outline-secondary btn-sm" onclick="leerTodas()"><i class="bi bi-check2-all"></i> Marcar todas como leídas</button>
    </div>

    <div class="card card-dashboard">
        <div class="card-body p-0">
            <div class="list-group list-group-flush">
                <?php if (empty($notificaciones)): ?>
                <div class="list-group-item text-center text-muted py-4">No hay notificaciones</div>
                <?php else: ?>
                <?php foreach ($notificaciones as $n): ?>
                <div class="list-group-item list-group-item-action d-flex justify-content-between align-items-center <?= !$n['leída'] ? 'fw-bold' : '' ?>">
                    <div class="flex-grow-1">
                        <div class="small text-muted"><?= $n['fecha_creación'] ?></div>
                        <div><?= htmlspecialchars($n['título']) ?></div>
                        <div class="small text-muted"><?= htmlspecialchars($n['mensaje']) ?></div>
                    </div>
                    <?php if (!$n['leída']): ?>
                    <a href="#" onclick="marcarLeida('<?= $n['id_notificación'] ?>')" class="btn btn-sm btn-outline-success ms-2"><i class="bi bi-check"></i></a>
                    <?php endif; ?>
                </div>
                <?php endforeach; ?>
                <?php endif; ?>
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

function leerTodas() {
    fetch('<?= BASE_URL ?>/notificacion/leerTodas', {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: 'csrf_token=<?= CSRFMiddleware::generarToken() ?>'
    }).then(function(r) { return r.json(); }).then(function() { location.reload(); });
}
</script>
