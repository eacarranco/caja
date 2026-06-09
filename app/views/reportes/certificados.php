<div class="container-fluid">
    <h4>Certificados</h4>
    <p class="text-muted">Seleccione un socio y el tipo de certificado a generar</p>

    <?php if (!empty($errors['id_socio'])): ?>
    <div class="alert alert-danger"><?= htmlspecialchars($errors['id_socio']) ?></div>
    <?php endif; ?>

    <div class="card mb-4">
        <div class="card-body">
            <form method="POST" id="certForm">
                <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>">
                <div class="row g-3 align-items-end">
                    <div class="col-md-6">
                        <label class="form-label">Socio *</label>
                        <select name="id_socio" class="form-select" required onchange="this.form.submit()">
                            <option value="">Seleccione un socio...</option>
                            <?php foreach ($socios as $s): ?>
                            <option value="<?= $s['id_socio'] ?>" <?= ($_POST['id_socio'] ?? '') === $s['id_socio'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($s['cedula'] . ' - ' . $s['nombre']) ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <?php if ($socioSel): ?>
    <div class="row g-3">
        <div class="col-md-4">
            <div class="card card-dashboard text-center h-100">
                <div class="card-body d-flex flex-column align-items-center justify-content-center py-4">
                    <div class="fs-1 text-warning mb-3"><i class="bi bi-wallet2"></i></div>
                    <h5>Estado de cuenta</h5>
                    <p class="text-muted small mb-3">Detalle de saldos y movimientos de la cuenta de ahorro</p>
                    <a href="<?= BASE_URL ?>/documento/estadoCuenta/<?= htmlspecialchars($_POST['id_socio'] ?? '') ?>" target="_blank" class="btn btn-warning mt-auto"><i class="bi bi-printer"></i> Imprimir</a>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card card-dashboard text-center h-100">
                <div class="card-body d-flex flex-column align-items-center justify-content-center py-4">
                    <div class="fs-1 text-success mb-3"><i class="bi bi-file-earmark-text"></i></div>
                    <h5>Constancia</h5>
                    <p class="text-muted small mb-3">Constancia de socio activo con datos personales</p>
                    <a href="<?= BASE_URL ?>/documento/constanciaSocio/<?= htmlspecialchars($_POST['id_socio'] ?? '') ?>" target="_blank" class="btn btn-success mt-auto"><i class="bi bi-printer"></i> Imprimir</a>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card card-dashboard text-center h-100">
                <div class="card-body d-flex flex-column align-items-center justify-content-center py-4">
                    <div class="fs-1 text-info mb-3"><i class="bi bi-file-earmark-check"></i></div>
                    <h5>Libre deuda</h5>
                    <p class="text-muted small mb-3">Certificado de no adeudar a la caja de ahorro</p>
                    <a href="<?= BASE_URL ?>/documento/libreDeuda/<?= htmlspecialchars($_POST['id_socio'] ?? '') ?>" target="_blank" class="btn btn-info mt-auto"><i class="bi bi-printer"></i> Imprimir</a>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>
</div>
