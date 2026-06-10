<div class="container-fluid">
    <h4>Notificaciones</h4>

    <?php if (empty($notificaciones)): ?>
    <div class="alert alert-info">Sin notificaciones</div>
    <?php else: ?>
    <div class="list-group">
        <?php foreach ($notificaciones as $n): ?>
        <div class="list-group-item list-group-item-action <?= $n['leida'] ? '' : 'list-group-item-primary' ?>">
            <div class="d-flex w-100 justify-content-between">
                <h6 class="mb-1"><?= htmlspecialchars($n['titulo']) ?></h6>
                <small><?= $n['fecha_creacion'] ?? '' ?></small>
            </div>
            <p class="mb-1 small"><?= htmlspecialchars($n['mensaje']) ?></p>
        </div>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>
</div>