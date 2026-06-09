<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4>Solicitar crédito</h4>
        <a href="<?= BASE_URL ?>/portal" class="btn btn-outline-secondary"><i class="bi bi-arrow-left"></i> Volver</a>
    </div>

    <?php if (!empty($errors['general'])): ?>
    <div class="alert alert-danger alert-dismissible fade show"><i class="bi bi-exclamation-circle"></i> <?= htmlspecialchars($errors['general']) ?><button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
    <?php endif; ?>

    <?php if (!empty($exito)): ?>
    <div class="alert alert-success alert-dismissible fade show">
        <i class="bi bi-check-circle"></i> <?= htmlspecialchars($exito) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    <?php else: ?>

    <!-- WIZARD CON BOOTSTRAP PURO -->
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
            <div class="step-label">Condiciones</div>
        </div>
        <div class="step-item" data-step="3">
            <div class="step-number">3</div>
            <div class="step-label">Confirmar</div>
        </div>
    </div>

    <!-- Form Wrapper -->
    <form method="POST" id="creditoForm">
        <?= CSRFMiddleware::campoHTML() ?>

        <!-- STEP 1: Simular -->
        <div class="step-content active" data-step="1">
            <div class="card card-dashboard">
                <div class="card-body">
                    <h5 class="card-title mb-4"><i class="bi bi-calculator"></i> Paso 1: Simular mi crédito</h5>
                    
                    <div class="row g-3 mb-4">
                        <div class="col-md-6">
                            <label class="form-label fw-500">Producto *</label>
                            <select name="id_producto" id="selProducto" class="form-select <?= isset($errors['id_producto']) ? 'is-invalid' : '' ?>" required onchange="onProductChange()">
                                <option value="">Seleccione un producto...</option>
                                <?php foreach ($productos as $p): ?>
                                <option value="<?= $p['id_producto'] ?>"
                                    data-tasa="<?= $p['tasa_interes_anual'] ?>"
                                    data-metodo="<?= $p['metodo_interes'] ?>"
                                    data-plazo_min="<?= $p['plazo_min_meses'] ?>"
                                    data-plazo_max="<?= $p['plazo_max_meses'] ?>"
                                    data-monto_min="<?= $p['monto_min'] ?>"
                                    data-monto_max="<?= $p['monto_max'] ?>"
                                    data-condiciones="<?= htmlspecialchars($p['condiciones_html'] ?? '') ?>"
                                    data-min_permanencia="<?= intval($p['min_permanencia_meses'] ?? 0) ?>"
                                    data-min_ahorro="<?= floatval($p['min_ahorro'] ?? 0) ?>"
                                    data-requiere_garante="<?= !empty($p['requiere_garante']) ? 1 : 0 ?>"
                                    data-destino_caracteres="<?= intval($p['min_destino_caracteres'] ?? 0) ?>"
                                    data-perm_valor="<?= intval($p['min_permanencia_valor'] ?? 0) ?>"
                                    data-perm_unidad="<?= htmlspecialchars($p['min_permanencia_unidad'] ?? 'meses') ?>"
                                    <?= ($_POST['id_producto'] ?? '') === $p['id_producto'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($p['nombre']) ?>
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
                            <?php if (isset($errors['monto'])): ?><div class="invalid-feedback" style="display:block"><?= $errors['monto'] ?></div><?php endif; ?>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-500">Plazo (meses) *</label>
                            <input type="number" min="1" name="plazo" id="inpPlazo"
                                   class="form-control <?= isset($errors['plazo']) ? 'is-invalid' : '' ?>"
                                   value="<?= htmlspecialchars($_POST['plazo'] ?? '') ?>" required oninput="onSimParamsChange()">
                            <?php if (isset($errors['plazo'])): ?><div class="invalid-feedback" style="display:block"><?= $errors['plazo'] ?></div><?php endif; ?>
                        </div>
                    </div>

                    <button type="button" class="btn btn-primary" onclick="simular()"><i class="bi bi-calculator"></i> Simular crédito</button>

                    <!-- Simulation Result -->
                    <div id="simResult" style="display:none" class="mt-4 pt-3 border-top">
                        <div id="elegibilidadMsg" class="mb-3"></div>
                        <div id="amortizacionContainer">
                            <h6 class="fw-semibold mb-3">Tabla de amortizacion</h6>
                            <div class="table-responsive">
                                <table class="table table-sm table-hover mb-0" id="tablaAmort">
                                    <thead class="table-light">
                                        <tr>
                                            <th>#</th>
                                            <th>Capital</th>
                                            <th>Interes</th>
                                            <th>Cuota</th>
                                            <th>Saldo</th>
                                        </tr>
                                    </thead>
                                    <tbody></tbody>
                                </table>
                            </div>
                            <div class="row mt-3">
                                <div class="col-md-6">
                                    <p class="mb-0"><strong>Total a pagar:</strong> <span class="text-primary fw-bold">$<span id="totalPagar">0.00</span></span></p>
                                </div>
                                <div class="col-md-6">
                                    <p class="mb-0"><strong>Cuota mensual:</strong> <span class="text-primary fw-bold">$<span id="resCuotaDisplay">0.00</span></span></p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- STEP 2: Condiciones -->
        <div class="step-content" data-step="2">
            <div class="card card-dashboard">
                <div class="card-body">
                    <h5 class="card-title mb-4"><i class="bi bi-file-text"></i> Paso 2: Condiciones del crédito</h5>
                    
                    <div id="condicionesDisplay" class="p-3 bg-light rounded mb-4 border-start border-primary border-4"></div>

                    <div class="form-check mb-3">
                        <input type="checkbox" name="acepta_condiciones" class="form-check-input" value="1" id="aceptaCheck" onchange="validarStep2()">
                        <label class="form-check-label" for="aceptaCheck">
                            <strong>Acepto las condiciones del crédito</strong>
                        </label>
                    </div>

                    <hr>

                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-500">Destino del crédito *</label>
                            <textarea name="destino" id="destinoInput" class="form-control" rows="3" 
                                      placeholder="Describa el propósito del crédito (mínimo 10 caracteres)..."
                                      oninput="validarStep2()"><?= htmlspecialchars($_POST['destino'] ?? '') ?></textarea>
                            <small class="text-muted d-block mt-1">Caracteres: <span id="destinoCount">0</span> / <span id="destinoMinLabel">10</span> min.</small>
                        </div>
                        <div class="col-md-6" id="garantesGroup" style="display:none">
                            <label class="form-label fw-500">Seleccionar garante(s)</label>
                            <select name="garantes[]" class="form-select" multiple size="5">
                                <option value="" disabled>-- Seleccione uno o más garantes --</option>
                                <?php foreach ($sociosActivos as $sa): ?>
                                <option value="<?= $sa['id_socio'] ?>" <?= in_array($sa['id_socio'], $_POST['garantes'] ?? []) ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($sa['nombre']) ?> (<?= htmlspecialchars($sa['cedula']) ?>)
                                </option>
                                <?php endforeach; ?>
                            </select>
                            <?php if (isset($errors['garantes'])): ?><div class="text-danger small mt-1"><?= htmlspecialchars($errors['garantes']) ?></div><?php endif; ?>
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
                                <h6 class="mb-0">$<span id="resMonto">0.00</span></h6>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="p-3 bg-light rounded">
                                <small class="text-muted">Plazo</small>
                                <h6 class="mb-0"><span id="resPlazo">0</span> meses</h6>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="p-3 bg-light rounded">
                                <small class="text-muted">Cuota mensual</small>
                                <h6 class="mb-0">$<span id="resCuota">0.00</span></h6>
                            </div>
                        </div>
                    </div>

                    <div class="row g-3 mb-4">
                        <div class="col-md-3">
                            <div class="p-3 bg-light rounded">
                                <small class="text-muted">Tasa anual</small>
                                <h6 class="mb-0"><span id="resTasa">0</span>%</h6>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="p-3 bg-light rounded">
                                <small class="text-muted">Método</small>
                                <h6 class="mb-0"><span id="resMetodo">-</span></h6>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="p-3 bg-light rounded">
                                <small class="text-muted">Total a pagar</small>
                                <h6 class="mb-0 fw-bold text-primary">$<span id="resTotal">0.00</span></h6>
                            </div>
                        </div>
                    </div>

                    <?php if (isset($errors['elegibilidad'])): ?>
                    <div class="alert alert-warning"><i class="bi bi-exclamation-triangle"></i> <?= htmlspecialchars($errors['elegibilidad']) ?></div>
                    <?php endif; ?>

                    <button type="submit" class="btn btn-success btn-lg w-100">
                        <i class="bi bi-send"></i> Enviar solicitud
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

    <script>
    var currentStep = 1;
    var simData = null;
    var elegible = false;

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
        
        // Hide all steps content and reset headers
        document.querySelectorAll('.step-content').forEach(el => el.classList.remove('active'));
        document.querySelectorAll('.step-item').forEach(el => {
            el.classList.remove('active', 'completed');
        });
        
        // Show current step content and header
        var currentContent = document.querySelector(`.step-content[data-step="${step}"]`);
        if (currentContent) {
            currentContent.classList.add('active');
        }
        var currentHeader = document.querySelector(`.step-item[data-step="${step}"]`);
        if (currentHeader) {
            currentHeader.classList.add('active');
        }
        
        // Mark previous steps as completed
        for (let i = 1; i < step; i++) {
            var headerItem = document.querySelector(`.step-item[data-step="${i}"]`);
            if (headerItem) {
                headerItem.classList.add('completed');
            }
        }
        
        currentStep = step;
        
        // Populate step content
        if (step === 2) populateStep2();
        if (step === 3) populateStep3();
        
        // Update buttons
        updateButtonsVisibility();
        
        // Scroll to top
        window.scrollTo({top: 0, behavior: 'smooth'});
    }

    function validateCurrentStep() {
        if (currentStep === 1) {
            if (!simData) {
                alert('Ejecute la simulación primero');
                return false;
            }
            return true;
        }
            if (currentStep === 2) {
                if (!document.getElementById('aceptaCheck').checked) {
                    alert('Debe aceptar las condiciones del credito');
                    return false;
                }
                var selP = document.getElementById('selProducto');
                var optP = selP.options[selP.selectedIndex];
                var reqChars = parseInt(optP.dataset.destino_caracteres) || 10;
                if (document.getElementById('destinoInput').value.trim().length < reqChars) {
                    alert('El destino debe tener al menos ' + reqChars + ' caracteres');
                    return false;
                }
            var selProd = document.getElementById('selProducto');
            var optProd = selProd.options[selProd.selectedIndex];
            if (optProd && optProd.dataset.requiere_garante == 1) {
                var garantes = document.querySelector('select[name="garantes[]"]');
                if (!garantes || garantes.selectedOptions.length === 0) {
                    alert('Debe seleccionar al menos un garante');
                    return false;
                }
            }
            if (!elegible) {
                if (!confirm('No cumple todos los requisitos. Desea continuar?')) {
                    return false;
                }
            }
            return true;
        }
        return true;
    }

    function populateStep2() {
        var sel = document.getElementById('selProducto');
        var opt = sel.options[sel.selectedIndex];
        if (opt && opt.value) {
            document.getElementById('condicionesDisplay').innerHTML = opt.dataset.condiciones || '<p class="text-muted">El producto no tiene condiciones específicas.</p>';
            if (opt.dataset.requiere_garante == 1) {
                document.getElementById('garantesGroup').style.display = 'block';
            } else {
                document.getElementById('garantesGroup').style.display = 'none';
            }
        }
    }

    function populateStep3() {
        var sel = document.getElementById('selProducto');
        var opt = sel.options[sel.selectedIndex];
        if (opt && opt.value) {
            document.getElementById('resProducto').textContent = opt.textContent;
            document.getElementById('resMonto').textContent = parseFloat(document.getElementById('inpMonto').value).toFixed(2);
            document.getElementById('resPlazo').textContent = document.getElementById('inpPlazo').value;
            document.getElementById('resTasa').textContent = opt.dataset.tasa;
            document.getElementById('resMetodo').textContent = opt.dataset.metodo.charAt(0).toUpperCase() + opt.dataset.metodo.slice(1);
        }
    }

    function updateButtonsVisibility() {
        document.getElementById('btnPrev').style.display = currentStep > 1 ? 'inline-block' : 'none';
        document.getElementById('btnNext').style.display = currentStep < 3 ? 'inline-block' : 'none';
    }

    function onProductChange() {
        var sel = document.getElementById('selProducto');
        var opt = sel.options[sel.selectedIndex];
        if (opt && opt.value) {
            document.getElementById('inpMonto').min = opt.dataset.monto_min;
            document.getElementById('inpMonto').max = opt.dataset.monto_max;
            document.getElementById('inpPlazo').min = opt.dataset.plazo_min;
            document.getElementById('inpPlazo').max = opt.dataset.plazo_max;
        }
    }

    function onSimParamsChange() {
        document.getElementById('simResult').style.display = 'none';
    }

    function simular() {
        var sel = document.getElementById('selProducto');
        var opt = sel.options[sel.selectedIndex];
        var monto = parseFloat(document.getElementById('inpMonto').value);
        var plazo = parseInt(document.getElementById('inpPlazo').value);

        if (!opt || !opt.value) { alert('Seleccione un producto'); return; }
        if (!monto || monto <= 0) { alert('Ingrese un monto válido'); return; }
        if (!plazo || plazo < parseInt(opt.dataset.plazo_min) || plazo > parseInt(opt.dataset.plazo_max)) {
            alert('Plazo debe ser entre ' + opt.dataset.plazo_min + ' y ' + opt.dataset.plazo_max);
            return;
        }

        var tasa = parseFloat(opt.dataset.tasa);
        var metodo = opt.dataset.metodo;

        fetch('<?= BASE_URL ?>/portal/simularCredito', {
            method: 'POST',
            headers: {'Content-Type': 'application/x-www-form-urlencoded'},
            body: 'csrf_token=<?= CSRFMiddleware::generarToken() ?>&monto=' + monto + '&tasa=' + tasa + '&plazo=' + plazo + '&metodo=' + encodeURIComponent(metodo)
        })
        .then(r => r.json())
        .then(d => {
            if (d.error) { alert(d.error); return; }
            simData = d;
            renderTable(d);
            document.getElementById('simResult').style.display = 'block';

            var permanencia = parseInt(opt.dataset.min_permanencia);
            var ahorroReq = parseFloat(opt.dataset.min_ahorro);
            var destCarMin = parseInt(opt.dataset.destino_caracteres);
            var permValor = parseInt(opt.dataset.perm_valor);
            var permUnidad = opt.dataset.perm_unidad || 'meses';
            elegible = true;
            var msgs = [];

            <?php if ($tieneSolicitudActiva): ?>
            elegible = false;
            msgs.push('Ya tiene una solicitud activa');
            <?php endif; ?>

            <?php if ($socio): ?>
            var fechaIngreso = new Date('<?= $socio['fecha_ingreso'] ?>');
            var hoy = new Date();
            var mesesActivo = (hoy.getFullYear() - fechaIngreso.getFullYear()) * 12 + (hoy.getMonth() - fechaIngreso.getMonth());
            if (permValor > 0) {
                var mesesReq = permValor;
                if (permUnidad === 'dias') mesesReq = Math.max(1, Math.round(permValor / 30));
                if (permUnidad === 'anios') mesesReq = permValor * 12;
                if (mesesActivo < mesesReq) {
                    elegible = false;
                    msgs.push('Requiere ' + permValor + ' ' + permUnidad + ' de permanencia (tiene ' + mesesActivo + ' meses)');
                }
            }
            <?php $ahorroTotal = floatval($socio['saldo_obligatorio'] ?? 0) + floatval($socio['saldo_excedente'] ?? 0); ?>
            var ahorroTotal = <?= $ahorroTotal ?>;
            if (ahorroReq > 0 && ahorroTotal < ahorroReq) {
                elegible = false;
                msgs.push('Requiere $' + ahorroReq.toFixed(2) + ' de ahorro (tiene $' + ahorroTotal.toFixed(2) + ')');
            }
            if (destCarMin > 0) {
                var destInput = document.getElementById('destinoInput');
                if (destInput && destInput.value.trim().length < destCarMin) {
                    elegible = false;
                    msgs.push('El destino debe tener al menos ' + destCarMin + ' caracteres');
                }
            }
            <?php endif; ?>

            var elMsg = document.getElementById('elegibilidadMsg');
            var amortContainer = document.getElementById('amortizacionContainer');
            if (msgs.length > 0) {
                elMsg.className = 'alert alert-warning';
                elMsg.innerHTML = '<i class="bi bi-exclamation-triangle"></i> <strong>No cumple requisitos:</strong> ' + msgs.join('. ');
                amortContainer.style.display = 'none';
            } else {
                elMsg.className = 'alert alert-success';
                elMsg.innerHTML = '<i class="bi bi-check-circle"></i> Cumple con todos los requisitos';
                amortContainer.style.display = 'block';
            }
        });
    }

    function renderTable(d) {
        var tbody = document.querySelector('#tablaAmort tbody');
        tbody.innerHTML = '';
        var total = 0;
        d.forEach(function(c) {
            var tr = document.createElement('tr');
            tr.innerHTML = '<td>' + c.numero + '</td><td>$' + c.capital.toFixed(2) + '</td><td>$' + c.interes.toFixed(2) + '</td><td>$' + c.total.toFixed(2) + '</td><td>$' + c.saldo.toFixed(2) + '</td>';
            tbody.appendChild(tr);
            total += c.total;
        });
        document.getElementById('totalPagar').textContent = total.toFixed(2);
        document.getElementById('resCuotaDisplay').textContent = d.length > 0 ? d[0].total.toFixed(2) : '0.00';
        document.getElementById('resCuota').textContent = d.length > 0 ? d[0].total.toFixed(2) : '0.00';
        document.getElementById('resTotal').textContent = total.toFixed(2);
    }

    function validarStep2() {
        var destino = document.getElementById('destinoInput').value.trim();
        document.getElementById('destinoCount').textContent = destino.length;
        var selP = document.getElementById('selProducto');
        var optP = selP.options[selP.selectedIndex];
        var reqChars = parseInt(optP.dataset.destino_caracteres) || 10;
        document.getElementById('destinoMinLabel').textContent = reqChars;
    }

    // Initialize
    document.addEventListener('DOMContentLoaded', function() {
        updateButtonsVisibility();
        document.getElementById('destinoInput').addEventListener('input', validarStep2);
    });
    </script>
    <?php endif; ?>
</div>
