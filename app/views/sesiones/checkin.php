<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h4>Sesion #<?= $sesion['numero_sesion'] ?></h4>
            <small class="text-muted"><?= htmlspecialchars($sesion['titulo'] ?? '') ?> — Reunion: <?= date('d/m/Y', strtotime($sesion['fecha_sesion'])) ?></small>
        </div>
        <div>
            <a href="<?= BASE_URL ?>/sesion/listar" class="btn btn-outline-secondary"><i class="bi bi-arrow-left"></i> Volver</a>
        </div>
    </div>

    <?php if (isset($_SESSION['error'])): ?>
    <div class="alert alert-danger"><?= $_SESSION['error']; unset($_SESSION['error']); ?></div>
    <?php endif; ?>

    <div class="row">
        <div class="col-12">
            <div class="card card-dashboard">
                <div class="card-header"><strong><i class="bi bi-people"></i> Planilla de cobro</strong></div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Cedula</th>
                                    <th>Socio</th>
                                    <th>Asistencia</th>
                                    <th>Obligaciones</th>
                                    <th>Total</th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($socios as $s):
                                    $socOblig = $obligaciones[$s['id_socio']] ?? [];
                                    $totalSocio = array_sum(array_map(function($o) { return floatval($o['monto']); }, $socOblig));
                                    $pagadas = array_filter($socOblig, function($o) { return $o['pagada']; });
                                    $totalPagado = array_sum(array_map(function($o) { return floatval($o['monto']); }, $pagadas));
                                    $pendiente = $totalSocio - $totalPagado;
                                    $pendientes = array_filter($socOblig, function($o) { return !$o['pagada']; });
                                ?>
                                <tr class="<?= isset($asistencias[$s['id_socio']]) ? 'table-success' : '' ?>">
                                    <td><?= htmlspecialchars($s['cedula']) ?></td>
                                    <td><strong><?= htmlspecialchars($s['nombre_completo']) ?></strong></td>
                                    <td>
                                        <form method="POST" class="d-flex gap-1" action="<?= BASE_URL ?>/sesion/checkin/<?= $sesion['id_sesion'] ?>">
                                            <?= CSRFMiddleware::campoHTML() ?>
                                            <input type="hidden" name="accion" value="asistencia">
                                            <input type="hidden" name="id_socio" value="<?= $s['id_socio'] ?>">
                                            <select name="tipo" class="form-select form-select-sm" style="width:auto">
                                                <option value="a_tiempo" <?= (isset($asistencias[$s['id_socio']]) && $asistencias[$s['id_socio']]['tipo'] === 'a_tiempo') ? 'selected' : '' ?>>A tiempo</option>
                                                <option value="retraso_10min" <?= (isset($asistencias[$s['id_socio']]) && $asistencias[$s['id_socio']]['tipo'] === 'retraso_10min') ? 'selected' : '' ?>>Retraso 10min</option>
                                                <option value="retraso_30min" <?= (isset($asistencias[$s['id_socio']]) && $asistencias[$s['id_socio']]['tipo'] === 'retraso_30min') ? 'selected' : '' ?>>Retraso 30min</option>
                                                <option value="falta" <?= (isset($asistencias[$s['id_socio']]) && $asistencias[$s['id_socio']]['tipo'] === 'falta') ? 'selected' : '' ?>>Falta</option>
                                            </select>
                                            <button type="submit" class="btn btn-sm btn-outline-success"><i class="bi bi-check"></i></button>
                                        </form>
                                    </td>
                                    <td>
                                        <?php if (!empty($socOblig)): ?>
                                        <ul class="list-unstyled mb-0 small">
                                            <?php foreach ($socOblig as $o): ?>
                                            <li class="<?= $o['pagada'] ? 'text-success text-decoration-line-through' : '' ?>">
                                                <?= htmlspecialchars($o['concepto']) ?>: <strong>$<?= number_format($o['monto'], 2) ?></strong>
                                                <?php if ($o['pagada']): ?><i class="bi bi-check-circle-fill text-success"></i><?php endif; ?>
                                            </li>
                                            <?php endforeach; ?>
                                        </ul>
                                        <?php else: ?>
                                        <span class="text-muted small">Sin obligaciones</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-end">
                                        <strong>$<?= number_format($totalSocio, 2) ?></strong><br>
                                        <small class="text-success">Pagado: $<?= number_format($totalPagado, 2) ?></small><br>
                                        <?php if ($pendiente > 0): ?>
                                        <small class="text-danger">Pendiente: $<?= number_format($pendiente, 2) ?></small>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if (!empty($pendientes)): ?>
                                        <button type="button" class="btn btn-sm btn-success" title="Cobrar"
                                                onclick="abrirModalCobro('<?= $s['id_socio'] ?>', '<?= htmlspecialchars($s['nombre_completo'], ENT_QUOTES) ?>')">
                                            <i class="bi bi-cash-coin"></i>
                                        </button>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

<!-- Modal Cobro -->
<div class="modal fade" id="modalCobro" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <form method="POST" action="<?= BASE_URL ?>/sesion/checkin/<?= $sesion['id_sesion'] ?>">
                <?= CSRFMiddleware::campoHTML() ?>
                <input type="hidden" name="accion" value="pagar_seleccion">
                <input type="hidden" name="id_socio" id="cobroIdSocio" value="">
                <div class="modal-header">
                    <h5 class="modal-title">Cobro a: <strong id="cobroNombreSocio"></strong></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <div class="form-check">
                            <input type="checkbox" class="form-check-input" id="seleccionarTodo" onchange="toggleSeleccionarTodo()">
                            <label class="form-check-label fw-bold" for="seleccionarTodo">Seleccionar / Deseleccionar todo</label>
                        </div>
                    </div>
                    <div id="obligacionesLista" class="list-group"></div>
                    <div class="mt-3 text-end">
                        <strong>Total seleccionado: $<span id="totalSeleccionado">0.00</span></strong>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-success" id="btnCobrar"><i class="bi bi-cash-coin"></i> Cobrar seleccionados</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
var obligacionesData = {};

<?php foreach ($socios as $s):
    $pends = array_values(array_filter($obligaciones[$s['id_socio']] ?? [], function($o) { return !$o['pagada']; }));
    if (!empty($pends)): ?>
obligacionesData['<?= $s['id_socio'] ?>'] = <?= json_encode($pends) ?>;
<?php endif; endforeach; ?>

function abrirModalCobro(idSocio, nombre) {
    document.getElementById('cobroIdSocio').value = idSocio;
    document.getElementById('cobroNombreSocio').textContent = nombre;
    var lista = document.getElementById('obligacionesLista');
    lista.innerHTML = '';
    document.getElementById('seleccionarTodo').checked = false;

    var obligs = obligacionesData[idSocio] || [];
    var total = 0;

    obligs.forEach(function(o, idx) {
        var div = document.createElement('div');
        div.className = 'list-group-item list-group-item-action d-flex justify-content-between align-items-center';
        div.innerHTML = '<div class="form-check">' +
            '<input type="checkbox" class="form-check-input oblig-check" name="obligaciones[]" value="' + o.id_obligacion + '" id="chk_' + idx + '" onchange="actualizarTotalCobro()">' +
            '<label class="form-check-label" for="chk_' + idx + '">' + o.concepto + '</label>' +
            '</div>' +
            '<strong>$' + parseFloat(o.monto).toFixed(2) + '</strong>';
        lista.appendChild(div);
        total += parseFloat(o.monto);
    });

    document.getElementById('totalSeleccionado').textContent = '0.00';
    var modal = new bootstrap.Modal(document.getElementById('modalCobro'));
    modal.show();
}

function toggleSeleccionarTodo() {
    var checked = document.getElementById('seleccionarTodo').checked;
    document.querySelectorAll('.oblig-check').forEach(function(el) { el.checked = checked; });
    actualizarTotalCobro();
}

function actualizarTotalCobro() {
    var total = 0;
    document.querySelectorAll('.oblig-check:checked').forEach(function(el) {
        var parent = el.closest('.list-group-item');
        var monto = parseFloat(parent.querySelector('strong').textContent.replace('$', '')) || 0;
        total += monto;
    });
    document.getElementById('totalSeleccionado').textContent = total.toFixed(2);
}
</script>
