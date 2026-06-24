<div class="container-fluid">
    <h4>Inversión</h4>

    <?php if (isset($_GET['ok'])): ?>
    <div class="alert alert-success alert-dismissible fade show">Solicitud de inversión enviada. Queda pendiente de aprobación.<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
    <?php endif; ?>
    <?php if (!empty($errors['general'])): ?>
    <div class="alert alert-danger"><i class="bi bi-exclamation-circle"></i> <?= htmlspecialchars($errors['general']) ?><button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
    <?php endif; ?>

    <div class="row g-3 mb-4">
        <div class="col-md-4">
            <div class="card card-dashboard text-center border-success">
                <div class="card-body py-3">
                    <h6 class="text-muted">Capital disponible</h6>
                    <h3 class="text-success mb-0">$ <?= number_format($saldoCapital, 2) ?></h3>
                </div>
            </div>
        </div>
    </div>

    <!-- WIZARD -->
    <style>
        .wizard-steps {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 3rem;
            position: relative;
        }
        .wizard-steps::before {
            content: '';
            position: absolute;
            top: 20px;
            left: 0;
            right: 0;
            height: 2px;
            background: #e9ecef;
            z-index: 0;
        }
        .step-item {
            flex: 1;
            text-align: center;
            position: relative;
            z-index: 1;
        }
        .step-number {
            width: 40px;
            height: 40px;
            margin: 0 auto 10px;
            border-radius: 50%;
            background: #e9ecef;
            color: #6c757d;
            font-weight: bold;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 18px;
            transition: all 0.3s;
        }
        .step-item.active .step-number {
            background: var(--bs-primary);
            color: white;
        }
        .step-item.completed .step-number {
            background: var(--bs-success);
            color: transparent;
            position: relative;
        }
        .step-item.completed .step-number::after {
            content: '✓';
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            color: white;
            font-weight: bold;
            font-size: 18px;
        }
        .step-label {
            font-size: 0.95rem;
            color: #6c757d;
        }
        .step-item.active .step-label {
            color: var(--bs-primary);
            font-weight: 600;
        }
        .step-item.completed .step-label {
            color: var(--bs-success);
            font-weight: 600;
        }
        .step-content {
            display: none;
            animation: fadeIn 0.3s;
        }
        .step-content.active {
            display: block;
        }
        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }
    </style>

    <!-- Steps Header -->
    <div class="wizard-steps">
        <div class="step-item active" data-step="1">
            <div class="step-number">1</div>
            <div class="step-label">Simular</div>
        </div>
        <div class="step-item" data-step="2">
            <div class="step-number">2</div>
            <div class="step-label">Términos</div>
        </div>
        <div class="step-item" data-step="3">
            <div class="step-number">3</div>
            <div class="step-label">Confirmar</div>
        </div>
    </div>

    <!-- Form Wrapper -->
    <form method="POST" id="invForm">
        <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>">

        <!-- STEP 1: Simular -->
        <div class="step-content active" data-step="1">
            <div class="card card-dashboard">
                <div class="card-body">
                    <h5 class="card-title mb-4"><i class="bi bi-calculator"></i> Paso 1: Simular mi inversión</h5>

                    <div class="row g-3 mb-4">
                        <div class="col-md-6">
                            <label class="form-label fw-500">Producto *</label>
                            <select name="id_producto" id="selProducto" class="form-select <?= isset($errors['id_producto']) ? 'is-invalid' : '' ?>" required onchange="onProductChange()">
                                <option value="">Seleccione un producto...</option>
                                <?php foreach ($productos as $p): ?>
                                <option value="<?= $p['id_producto'] ?>"
                                    data-tasa="<?= $p['tasa_interes_anual'] ?>"
                                    data-plazo_min="<?= $p['plazo_min_meses'] ?>"
                                    data-plazo_max="<?= $p['plazo_max_meses'] ?>"
                                    data-monto_min="<?= $p['monto_min'] ?>"
                                    data-monto_max="<?= $p['monto_max'] ?>"
                                    data-condiciones="<?= htmlspecialchars($p['condiciones_html'] ?? '') ?>"
                                    data-min_permanencia="<?= intval($p['min_permanencia_meses'] ?? 0) ?>"
                                    data-penalidad="<?= floatval($p['penalidad_retiro_anticipado'] ?? 0) ?>"
                                    <?= ($_POST['id_producto'] ?? '') === $p['id_producto'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($p['nombre']) ?> (<?= $p['tasa_interes_anual'] ?>% anual)
                                </option>
                                <?php endforeach; ?>
                            </select>
                            <?php if (isset($errors['id_producto'])): ?><div class="invalid-feedback" style="display:block"><?= $errors['id_producto'] ?></div><?php endif; ?>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-500">Monto $ *</label>
                            <input type="number" step="0.01" min="0" name="monto" id="inpMonto"
                                   class="form-control <?= isset($errors['monto']) ? 'is-invalid' : '' ?>"
                                   value="<?= htmlspecialchars($_POST['monto'] ?? '') ?>" required oninput="onSimParamsChange()">
                            <small class="text-muted" id="montoAyuda"></small>
                            <?php if (isset($errors['monto'])): ?><div class="invalid-feedback" style="display:block"><?= $errors['monto'] ?></div><?php endif; ?>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-500">Plazo (meses) *</label>
                            <input type="number" min="1" name="plazo" id="inpPlazo"
                                   class="form-control <?= isset($errors['plazo']) ? 'is-invalid' : '' ?>"
                                   value="<?= htmlspecialchars($_POST['plazo'] ?? '') ?>" required oninput="onSimParamsChange()">
                            <small class="text-muted" id="plazoAyuda"></small>
                            <?php if (isset($errors['plazo'])): ?><div class="invalid-feedback" style="display:block"><?= $errors['plazo'] ?></div><?php endif; ?>
                        </div>
                    </div>

                    <button type="button" class="btn btn-primary" onclick="simular()"><i class="bi bi-calculator"></i> Simular inversión</button>

                    <!-- Simulation Result -->
                    <div id="simResult" style="display:none" class="mt-4 pt-3 border-top">
                        <div id="elegibilidadMsg" class="mb-3"></div>
                        <div class="row g-3" id="resumenSim">
                            <div class="col-md-3">
                                <div class="p-3 bg-light rounded text-center">
                                    <small class="text-muted">Monto a invertir</small>
                                    <h5 class="mb-0 text-primary">$<span id="resMonto">0.00</span></h5>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="p-3 bg-light rounded text-center">
                                    <small class="text-muted">Rendimiento proyectado</small>
                                    <h5 class="mb-0 text-success">$<span id="resRendimiento">0.00</span></h5>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="p-3 bg-light rounded text-center">
                                    <small class="text-muted">Total al vencimiento</small>
                                    <h5 class="mb-0 fw-bold">$<span id="resTotal">0.00</span></h5>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="p-3 bg-light rounded text-center">
                                    <small class="text-muted">Tasa mensual</small>
                                    <h5 class="mb-0"><span id="resTasaMensual">0.0000</span>%</h5>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- STEP 2: Términos -->
        <div class="step-content" data-step="2">
            <div class="card card-dashboard">
                <div class="card-body">
                    <h5 class="card-title mb-4"><i class="bi bi-file-text"></i> Paso 2: Términos de la inversión</h5>

                    <div id="condicionesDisplay" class="p-3 bg-light rounded mb-4 border-start border-success border-4"></div>

                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-500">Destino al vencimiento</label>
                            <select name="destino_final" class="form-select">
                                <option value="capital_inversion">Reinvertir (capital de inversión)</option>
                                <option value="efectivo">Efectivo</option>
                                <option value="transferencia">Transferencia</option>
                            </select>
                        </div>
                        <div class="col-md-6 d-flex align-items-end">
                            <div class="form-check">
                                <input type="checkbox" name="acepta_condiciones" class="form-check-input" value="1" id="aceptaCheck" onchange="validarStep2()">
                                <label class="form-check-label" for="aceptaCheck">
                                    <strong>Acepto los términos y condiciones</strong>
                                </label>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- STEP 3: Confirmar -->
        <div class="step-content" data-step="3">
            <div class="card card-dashboard">
                <div class="card-body">
                    <h5 class="card-title mb-4"><i class="bi bi-check-circle"></i> Paso 3: Revisar y confirmar</h5>

                    <div class="row g-3 mb-4">
                        <div class="col-md-3">
                            <div class="p-3 bg-light rounded">
                                <small class="text-muted">Producto</small>
                                <h6 id="resProducto" class="mb-0">-</h6>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="p-3 bg-light rounded">
                                <small class="text-muted">Monto</small>
                                <h6 class="mb-0">$<span id="resMontoConfirm">0.00</span></h6>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="p-3 bg-light rounded">
                                <small class="text-muted">Plazo</small>
                                <h6 class="mb-0"><span id="resPlazoConfirm">0</span> meses</h6>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="p-3 bg-light rounded">
                                <small class="text-muted">Tasa anual</small>
                                <h6 class="mb-0"><span id="resTasaConfirm">0</span>%</h6>
                            </div>
                        </div>
                    </div>

                    <div class="row g-3 mb-4">
                        <div class="col-md-4">
                            <div class="p-3 bg-light rounded">
                                <small class="text-muted">Rendimiento proyectado</small>
                                <h6 class="mb-0 text-success">$<span id="resRendConfirm">0.00</span></h6>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="p-3 bg-light rounded">
                                <small class="text-muted">Total al vencimiento</small>
                                <h6 class="mb-0 fw-bold text-primary">$<span id="resTotalConfirm">0.00</span></h6>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="p-3 bg-light rounded">
                                <small class="text-muted">Destino</small>
                                <h6 class="mb-0" id="resDestinoConfirm">Reinvertir</h6>
                            </div>
                        </div>
                    </div>

                    <button type="submit" class="btn btn-success btn-lg w-100">
                        <i class="bi bi-send"></i> Confirmar y crear inversión
                    </button>
                </div>
            </div>
        </div>

        <!-- Navigation Buttons -->
        <div class="mt-4 d-flex justify-content-between">
            <button type="button" class="btn btn-outline-secondary" id="btnPrev" style="display:none" onclick="goToPreviousStep()">
                <i class="bi bi-chevron-left"></i> Anterior
            </button>
            <div></div>
            <button type="button" class="btn btn-primary" id="btnNext" onclick="goToNextStep()">
                Siguiente <i class="bi bi-chevron-right"></i>
            </button>
        </div>
    </form>

    <!-- Mis inversiones -->
    <?php if (!empty($inversiones)): ?>
    <div class="card mb-3 mt-4">
        <div class="card-header"><strong>Mis inversiones</strong></div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-striped mb-0">
                    <thead>
                        <tr>
                            <th>Producto</th>
                            <th>Monto</th>
                            <th>Plazo</th>
                            <th>Inicio</th>
                            <th>Vencimiento</th>
                            <th>Rendimiento</th>
                            <th>Estado</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($inversiones as $i): ?>
                        <tr>
                            <td><?= htmlspecialchars($i['producto']) ?></td>
                            <td>$ <?= number_format($i['monto'], 2) ?></td>
                            <td><?= $i['plazo_meses'] ?> meses</td>
                            <td><?= date('d/m/Y', strtotime($i['fecha_inicio'])) ?></td>
                            <td><?= date('d/m/Y', strtotime($i['fecha_vencimiento'])) ?></td>
                            <td>$ <?= number_format($i['rendimiento_proyectado'] ?? 0, 2) ?></td>
                            <td>
                                <?php if ($i['estado'] === 'pendiente'): ?>
                                <span class="badge bg-info">Pendiente</span>
                                <?php elseif ($i['estado'] === 'activa'): ?>
                                <span class="badge bg-success">Activa</span>
                                <?php elseif ($i['estado'] === 'vencida'): ?>
                                <span class="badge bg-warning text-dark">Vencida</span>
                                <?php elseif ($i['estado'] === 'retiro_anticipado'): ?>
                                <span class="badge bg-secondary">Retiro anticipado</span>
                                <?php elseif ($i['estado'] === 'rechazada'): ?>
                                <span class="badge bg-danger">Rechazada</span>
                                <?php else: ?>
                                <span class="badge bg-secondary"><?= htmlspecialchars($i['estado']) ?></span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if ($i['estado'] === 'activa'): ?>
                                <button class="btn btn-sm btn-outline-secondary" onclick="solicitarRetiroAnticipado(<?= htmlspecialchars(json_encode($i['id_inversion']), ENT_COMPAT, 'UTF-8') ?>, <?= htmlspecialchars(json_encode($i['producto']), ENT_COMPAT, 'UTF-8') ?>, <?= (float)($i['monto']) ?>, <?= (float)($i['rendimiento_proyectado'] ?? 0) ?>, <?= (int)($i['plazo_meses']) ?>, <?= htmlspecialchars(json_encode($i['destino_final'] ?? 'capital_inversion'), ENT_COMPAT, 'UTF-8') ?>, <?= htmlspecialchars(json_encode($i['fecha_inicio'] ?? date('Y-m-d')), ENT_COMPAT, 'UTF-8') ?>, <?= (float)($i['penalidad'] ?? 0) ?>)" title="Solicitar retiro anticipado"><i class="bi bi-box-arrow-left"></i></button>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <?php endif; ?>
</div>

<script>
var currentStep = 1;
var simData = null;

function goToNextStep() {
    if (validateCurrentStep()) {
        if (currentStep < 3) {
            setStep(currentStep + 1);
        }
    }
}

function goToPreviousStep() {
    if (currentStep > 1) {
        setStep(currentStep - 1);
    }
}

function setStep(step) {
    if (step < 1 || step > 3) return;

    document.querySelectorAll('.step-content').forEach(el => el.classList.remove('active'));
    document.querySelectorAll('.step-item').forEach(el => {
        el.classList.remove('active', 'completed');
    });

    var currentContent = document.querySelector(`.step-content[data-step="${step}"]`);
    if (currentContent) currentContent.classList.add('active');

    var currentHeader = document.querySelector(`.step-item[data-step="${step}"]`);
    if (currentHeader) currentHeader.classList.add('active');

    for (let i = 1; i < step; i++) {
        var headerItem = document.querySelector(`.step-item[data-step="${i}"]`);
        if (headerItem) headerItem.classList.add('completed');
    }

    currentStep = step;
    if (step === 2) populateStep2();
    if (step === 3) populateStep3();
    updateButtonsVisibility();
    window.scrollTo({top: 0, behavior: 'smooth'});
}

function validateCurrentStep() {
    if (currentStep === 1) {
        if (!simData) {
            mostrarNotificacion('warning','Aviso','Ejecute la simulación primero',true);
            return false;
        }
        return true;
    }
    if (currentStep === 2) {
        if (!document.getElementById('aceptaCheck').checked) {
            mostrarNotificacion('warning','Aviso','Debe aceptar los términos y condiciones',true);
            return false;
        }
        return true;
    }
    return true;
}

function populateStep2() {
    var sel = document.getElementById('selProducto');
    var opt = sel.options[sel.selectedIndex];
    if (opt && opt.value) {
        document.getElementById('condicionesDisplay').innerHTML = opt.dataset.condiciones || '<p class="text-muted">Sin condiciones específicas.</p>';
    }
}

function populateStep3() {
    var sel = document.getElementById('selProducto');
    var opt = sel.options[sel.selectedIndex];
    if (opt && opt.value) {
        document.getElementById('resProducto').textContent = opt.textContent;
        document.getElementById('resMontoConfirm').textContent = simData ? simData.monto.toFixed(2) : parseFloat(document.getElementById('inpMonto').value).toFixed(2);
        document.getElementById('resPlazoConfirm').textContent = document.getElementById('inpPlazo').value;
        document.getElementById('resTasaConfirm').textContent = opt.dataset.tasa;
        document.getElementById('resRendConfirm').textContent = simData ? simData.rendimiento.toFixed(2) : '0.00';
        document.getElementById('resTotalConfirm').textContent = simData ? simData.total.toFixed(2) : '0.00';
        var dest = document.querySelector('select[name="destino_final"]');
        var destLabel = dest.options[dest.selectedIndex]?.text || 'Reinvertir';
        document.getElementById('resDestinoConfirm').textContent = destLabel;
    }
}

function updateButtonsVisibility() {
    document.getElementById('btnPrev').style.display = currentStep > 1 ? 'inline-block' : 'none';
    document.getElementById('btnNext').style.display = currentStep < 3 ? 'inline-block' : 'none';
}

function onProductChange() {
    var sel = document.getElementById('selProducto');
    var opt = sel.options[sel.selectedIndex];
    document.getElementById('simResult').style.display = 'none';

    if (!opt.value) return;

    var inpMonto = document.getElementById('inpMonto');
    inpMonto.min = parseFloat(opt.dataset.monto_min);
    inpMonto.max = parseFloat(opt.dataset.monto_max);
    document.getElementById('montoAyuda').textContent = 'Min: $' + parseFloat(opt.dataset.monto_min).toFixed(2) + ', Max: $' + parseFloat(opt.dataset.monto_max).toFixed(2);

    var inpPlazo = document.getElementById('inpPlazo');
    inpPlazo.min = parseInt(opt.dataset.plazo_min);
    inpPlazo.max = parseInt(opt.dataset.plazo_max);
    document.getElementById('plazoAyuda').textContent = 'Min: ' + opt.dataset.plazo_min + ', Max: ' + opt.dataset.plazo_max + ' meses';
}

function onSimParamsChange() {
    document.getElementById('simResult').style.display = 'none';
}

function simular() {
    var sel = document.getElementById('selProducto');
    var opt = sel.options[sel.selectedIndex];
    var monto = parseFloat(document.getElementById('inpMonto').value);
    var plazo = parseInt(document.getElementById('inpPlazo').value);

    if (!opt || !opt.value) { mostrarNotificacion('warning','Aviso','Seleccione un producto',true); return; }
    if (!monto || monto <= 0) { mostrarNotificacion('warning','Aviso','Ingrese un monto válido',true); return; }
    if (!plazo || plazo < parseInt(opt.dataset.plazo_min) || plazo > parseInt(opt.dataset.plazo_max)) {
        mostrarNotificacion('warning','Aviso','Plazo debe ser entre ' + opt.dataset.plazo_min + ' y ' + opt.dataset.plazo_max + ' meses',true);
        return;
    }
    if (monto < parseFloat(opt.dataset.monto_min) || monto > parseFloat(opt.dataset.monto_max)) {
        mostrarNotificacion('warning','Aviso','Monto debe ser entre $' + parseFloat(opt.dataset.monto_min).toFixed(2) + ' y $' + parseFloat(opt.dataset.monto_max).toFixed(2),true);
        return;
    }
    if (monto > <?= floatval($saldoCapital) ?>) {
        mostrarNotificacion('warning','Aviso','Saldo insuficiente en capital de inversión. Disponible: $<?= number_format($saldoCapital, 2) ?>',true);
        return;
    }

    var tasa = parseFloat(opt.dataset.tasa);

    fetch('<?= BASE_URL ?>/portal/simularInversion', {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: 'csrf_token=<?= $_SESSION['csrf_token'] ?? '' ?>&monto=' + monto + '&tasa=' + tasa + '&plazo=' + plazo
    })
    .then(r => r.json())
    .then(d => {
        if (d.error) { mostrarNotificacion('error','Error',d.error,false); return; }
        simData = d;
        document.getElementById('resMonto').textContent = d.monto.toFixed(2);
        document.getElementById('resRendimiento').textContent = d.rendimiento.toFixed(2);
        document.getElementById('resTotal').textContent = d.total.toFixed(2);
        document.getElementById('resTasaMensual').textContent = d.tasa_mensual.toFixed(4);
        document.getElementById('simResult').style.display = 'block';

        var elegible = true;
        var msgs = [];

        <?php $ahorroTotal = floatval($socio['saldo_obligatorio'] ?? 0) + floatval($socio['saldo_excedente'] ?? 0); ?>
        var ahorroTotal = <?= $ahorroTotal ?>;

        if (<?= $saldoCapital ?> < monto) {
            elegible = false;
            msgs.push('Saldo insuficiente en capital de inversión');
        }

        var elMsg = document.getElementById('elegibilidadMsg');
        if (msgs.length > 0) {
            elMsg.className = 'alert alert-warning';
            elMsg.innerHTML = '<i class="bi bi-exclamation-triangle"></i> <strong>Observaciones:</strong> ' + msgs.join('. ');
        } else {
            elMsg.className = 'alert alert-success';
            elMsg.innerHTML = '<i class="bi bi-check-circle"></i> Parámetros válidos para la inversión';
        }
    });
}

function validarStep2() {
    // placeholder - aceptaCheck validation handled in validateCurrentStep
}

document.addEventListener('DOMContentLoaded', function() {
    updateButtonsVisibility();
    if (document.getElementById('selProducto').value) {
        onProductChange();
    }
});
</script>

<!-- Modal Retiro Anticipado -->
<div id="retiroOverlay" style="display:none;position:fixed;top:0;left:0;right:0;bottom:0;z-index:100000;background:rgba(0,0,0,0.5);justify-content:center;align-items:center">
    <div style="background:#fff;border-radius:12px;padding:2rem 1.5rem;max-width:420px;width:90%;box-shadow:0 20px 60px rgba(0,0,0,0.3);animation:notifFadeIn 0.2s ease-out">
        <h5 class="mb-3">Retiro anticipado de inversión</h5>
        <p class="text-muted small mb-3" style="text-align:justify">Según el Reglamento Interno, el socio debe notificar con al menos un (1) mes de anticipación. Se aplicará una penalidad sobre el rendimiento (utilidad) del monto invertido.</p>
        <table class="table table-sm table-borderless mb-3">
            <tr><td class="text-muted">Producto:</td><td class="fw-bold" id="retiroProducto"></td></tr>
            <tr><td class="text-muted">Monto invertido:</td><td class="fw-bold" id="retiroMonto"></td></tr>
            <tr><td class="text-muted">Rendimiento:</td><td id="retiroRend"></td></tr>
            <tr><td class="text-muted">Penalidad:</td><td class="text-danger" id="retiroPenalidad"></td></tr>
            <tr><td class="text-muted">Devolución:</td><td class="fw-bold text-success" id="retiroDevolucion"></td></tr>
        </table>
        <form id="formRetiro" method="POST" action="<?= BASE_URL ?>/portal/retirarInversion">
            <input type="hidden" name="csrf_token" value="<?= CSRFMiddleware::generarToken() ?>">
            <input type="hidden" name="id_inversion" id="retiroIdInversion" value="">
            <div class="d-flex gap-2 justify-content-center">
                <button type="button" class="btn btn-outline-secondary px-4" onclick="document.getElementById('retiroOverlay').style.display='none'">Cancelar</button>
                <button type="submit" class="btn btn-warning px-4">Solicitar retiro anticipado</button>
            </div>
        </form>
    </div>
</div>

<script>
function solicitarRetiroAnticipado(id, producto, monto, rendimientoTotal, plazo, destino, fechaInicio, penalidadPorc) {
    document.getElementById('retiroIdInversion').value = id;
    document.getElementById('retiroProducto').textContent = producto;
    document.getElementById('retiroMonto').textContent = '$' + monto.toFixed(2);

    var inicio = new Date(fechaInicio || Date.now());
    var hoy = new Date();
    var diasTranscurridos = Math.max(0, Math.floor((hoy - inicio) / (1000 * 60 * 60 * 24)));
    var plazoTotalDias = Math.max(1, plazo * 30);
    var rendimientoDiario = rendimientoTotal / plazoTotalDias;
    var rendimientoDevengado = rendimientoDiario * diasTranscurridos;
    var penalidad = penalidadPorc / 100 * rendimientoDevengado;
    var devolucion = monto + rendimientoDevengado - penalidad;

    document.getElementById('retiroRend').textContent = isNaN(rendimientoDevengado) ? '$0.00' : '$' + rendimientoDevengado.toFixed(2) + ' (' + diasTranscurridos + ' dias)';
    document.getElementById('retiroPenalidad').textContent = isNaN(penalidad) ? '$0.00' : '$' + penalidad.toFixed(2) + ' (' + penalidadPorc + '%)';
    document.getElementById('retiroDevolucion').textContent = isNaN(devolucion) ? '$0.00' : '$' + devolucion.toFixed(2);
    document.getElementById('retiroOverlay').style.display = 'flex';
}

document.getElementById('formRetiro').addEventListener('submit', function(e) {
    e.preventDefault();
    var formData = new FormData(this);
    fetch(this.action, { method: 'POST', body: formData })
    .then(r => r.json())
    .then(d => {
        if (d.error) { mostrarNotificacion('error','Error',d.error,false); }
        else {
            mostrarNotificacion('success','Retiro procesado','Devolución: $' + d.devolucion.toFixed(2) + ' | Penalidad: $' + d.penalidad.toFixed(2),true);
            location.reload();
        }
    })
    .catch(function() { mostrarNotificacion('error','Error','Error al procesar',false); });
});
document.getElementById('retiroOverlay').addEventListener('click', function(e) { if (e.target === this) this.style.display = 'none'; });
</script>