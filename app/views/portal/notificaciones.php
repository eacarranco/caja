<div class="container-fluid">
    <h4>Notificaciones</h4>
    <a href="<?= BASE_URL ?>/portal" class="btn btn-outline-secondary btn-sm mb-3"><i class="bi bi-arrow-left"></i> Volver</a>
    <?php if (empty($notificaciones)): ?>
    <div class="alert alert-info">Sin notificaciones</div>
    <?php else: ?>
    <div class="list-group">
        <?php foreach ($notificaciones as $n): ?>
        <div class="list-group-item list-group-item-action <?= $n['leída'] ? '' : 'list-group-item-primary' ?>">
            <div class="d-flex w-100 justify-content-between">
                <h6 class="mb-1"><?= htmlspecialchars($n['título']) ?></h6>
                <small><?= $n['fecha_creación'] ?? '' ?></small>
            </div>
            <p class="mb-1 small"><?= htmlspecialchars($n['mensaje']) ?></p>
        </div>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>
</div>