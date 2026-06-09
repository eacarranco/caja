<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4>Depositar a capital de inversion</h4>
        <a href="<?= BASE_URL ?>/inversion/listar" class="btn btn-outline-secondary"><i class="bi bi-arrow-left"></i> Volver</a>
    </div>

    <?php if (!empty($errors['general'])): ?>
    <div class="alert alert-danger"><?= htmlspecialchars($errors['general']) ?></div>
    <?php endif; ?>

    <div class="card">
        <div class="card-body">
            <form method="POST" enctype="multipart/form-data">
                <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>">
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Socio *</label>
                        <select name="id_socio" class="form-select <?= isset($errors['id_socio']) ? 'is-invalid' : '' ?>" required>
                            <option value="">Seleccione un socio...</option>
                            <?php foreach ($socios as $s): ?>
                            <option value="<?= $s['id_socio'] ?>" <?= ($_POST['id_socio'] ?? '') === $s['id_socio'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($s['cedula'] . ' - ' . $s['nombre']) ?> (Saldo: $<?= number_format($s['capital_inversion'], 2) ?>)
                            </option>
                            <?php endforeach; ?>
                        </select>
                        <?php if (isset($errors['id_socio'])): ?><div class="invalid-feedback"><?= $errors['id_socio'] ?></div><?php endif; ?>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Monto $ *</label>
                        <input type="number" step="0.01" min="0.01" name="monto" class="form-control <?= isset($errors['monto']) ? 'is-invalid' : '' ?>" value="<?= htmlspecialchars($_POST['monto'] ?? '') ?>" required>
                        <?php if (isset($errors['monto'])): ?><div class="invalid-feedback"><?= $errors['monto'] ?></div><?php endif; ?>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Medio de pago *</label>
                        <select name="medio_pago" id="medioPago" class="form-select" onchange="toggleComprobante()">
                            <option value="efectivo">Efectivo</option>
                            <option value="transferencia">Transferencia</option>
                            <option value="compensacion">Compensacion</option>
                            <option value="digital">Digital</option>
                        </select>
                    </div>
                </div>
                <div class="row" id="comprobanteGroup" style="display:none">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Comprobante (imagen o PDF) *</label>
                        <input type="file" name="comprobante" id="comprobanteInput" class="form-control <?= isset($errors['comprobante']) ? 'is-invalid' : '' ?>" accept=".jpg,.jpeg,.png,.gif,.webp,.pdf">
                        <?php if (isset($errors['comprobante'])): ?><div class="invalid-feedback"><?= $errors['comprobante'] ?></div><?php endif; ?>
                        <small class="text-muted">Formatos: JPG, PNG, PDF. Max 2MB</small>
                    </div>
                </div>
                <button type="submit" class="btn btn-primary"><i class="bi bi-wallet2"></i> Depositar</button>
            </form>
        </div>
    </div>
</div>

<script>
function toggleComprobante() {
    var medio = document.getElementById('medioPago').value;
    var group = document.getElementById('comprobanteGroup');
    var input = document.getElementById('comprobanteInput');
    if (medio === 'transferencia' || medio === 'compensacion' || medio === 'digital') {
        group.style.display = 'flex';
        input.required = true;
    } else {
        group.style.display = 'none';
        input.required = false;
        input.value = '';
    }
}
<?php if (in_array(($_POST['medio_pago'] ?? ''), ['transferencia', 'compensacion', 'digital'])): ?>
document.addEventListener('DOMContentLoaded', toggleComprobante);
<?php endif; ?>
</script>
