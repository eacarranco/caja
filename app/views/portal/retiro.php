<div class="container-fluid">
    <h4>Solicitar retiro de ahorro</h4>


    <div class="row g-3">
        <div class="col-md-6">
            <div class="card card-dashboard">
                <div class="card-body">
                    <p class="mb-1">Saldo disponible: <strong>$<?= number_format($saldo, 2) ?></strong></p>
                    <?php if (isset($errors['general'])): ?><div class="alert alert-danger"><?= $errors['general'] ?></div><?php endif; ?>
                    <form method="POST">
                        <?= CSRFMiddleware::campoHTML() ?>
                        <div class="mb-3">
                            <label class="form-label">Monto a retirar $</label>
                            <input type="number" step="0.01" min="1" max="<?= $saldo ?>" name="monto" class="form-control <?= isset($errors['monto']) ? 'is-invalid' : '' ?>" required>
                            <?php if (isset($errors['monto'])): ?><div class="invalid-feedback"><?= $errors['monto'] ?></div><?php endif; ?>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Motivo</label>
                            <textarea name="motivo" class="form-control <?= isset($errors['motivo']) ? 'is-invalid' : '' ?>" rows="3" required></textarea>
                            <?php if (isset($errors['motivo'])): ?><div class="invalid-feedback"><?= $errors['motivo'] ?></div><?php endif; ?>
                        </div>
                        <button type="submit" class="btn btn-primary">Enviar solicitud</button>
                    </form>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card card-dashboard">
                <div class="card-body p-0">
                    <h5 class="p-3 pb-0">Mis solicitudes</h5>
                    <?php if (empty($solicitudes)): ?>
                    <p class="p-3 text-muted small">Sin solicitudes previas</p>
                    <?php else: ?>
                    <div class="table-responsive"><table class="table table-sm mb-0">
                        <thead><tr><th>Fecha</th><th>Monto</th><th>Estado</th></tr></thead>
                        <tbody>
                        <?php foreach ($solicitudes as $s): ?>
                        <tr>
                            <td><?= $s['fecha_solicitud'] ?></td>
                            <td>$<?= number_format($s['monto'], 2) ?></td>
                            <td><span class="badge bg-<?= $s['estado'] === 'aprobado' ? 'success' : ($s['estado'] === 'rechazado' ? 'danger' : 'warning') ?>"><?= ucfirst($s['estado']) ?></span></td>
                        </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table></div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>
