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

    <!-- Buscador -->
    <form method="GET" class="row g-2 mb-3">
        <div class="col-auto" style="min-width:300px">
            <div class="input-group">
                <input type="text" name="buscar" class="form-control" placeholder="Buscar por nombre o cedula..." value="<?= htmlspecialchars($buscar) ?>">
                <button type="submit" class="btn btn-primary"><i class="bi bi-search"></i></button>
                <?php if ($buscar): ?>
                <a href="<?= BASE_URL ?>/sesion/checkin/<?= $sesion['id_sesion'] ?>" class="btn btn-outline-secondary"><i class="bi bi-x-circle"></i></a>
                <?php endif; ?>
            </div>
        </div>
    </form>

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
                                    <th class="text-end">Total adeudado</th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($socios)): ?>
                                <tr><td colspan="5" class="text-center text-muted py-3">No se encontraron socios</td></tr>
                                <?php else: foreach ($socios as $s):
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
                                    <td class="text-end">
                                        <?php if ($pendiente > 0): ?>
                                        <strong class="text-danger">$<?= number_format($pendiente, 2) ?></strong>
                                        <?php if ($totalPagado > 0): ?><br><small class="text-success">Pagado: $<?= number_format($totalPagado, 2) ?></small><?php endif; ?>
                                        <?php else: ?>
                                        <span class="text-muted">$0.00</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if (!empty($pendientes)): ?>
                                        <button type="button" class="btn btn-sm btn-success" title="Cobrar"
                                                onclick="abrirModalCobro('<?= $s['id_socio'] ?>', '<?= htmlspecialchars($s['nombre_completo'], ENT_QUOTES) ?>')">
                                            <i class="bi bi-cash-coin"></i>
                                        </button>
                                        <?php endif; ?>
                                        <?php if (!empty($socOblig)): ?>
                                        <a href="<?= BASE_URL ?>/documento/comprobanteSocio/<?= $sesion['id_sesion'] ?>/<?= $s['id_socio'] ?>" target="_blank" class="btn btn-sm btn-outline-info" title="Comprobante"><i class="bi bi-printer"></i></a>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                                <?php endforeach; endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Paginacion -->
    <?php if ($totalPaginas > 1): ?>
    <nav class="mt-3">
        <ul class="pagination pagination-sm justify-content-center">
            <?php for ($i = 1; $i <= $totalPaginas; $i++): ?>
            <li class="page-item <?= $i === $pagina ? 'active' : '' ?>">
                <a class="page-link" href="?pagina=<?= $i ?>&buscar=<?= urlencode($buscar) ?>"><?= $i ?></a>
            </li>
            <?php endfor; ?>
        </ul>
    </nav>
    <?php endif; ?>

    <!-- Modal Cobro (carga via AJAX) -->
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
                        <div class="text-center py-3" id="cobroLoading"><div class="spinner-border text-primary"></div> Cargando...</div>
                        <div class="mb-3" id="cobroSelectAll" style="display:none">
                            <div class="form-check">
                                <input type="checkbox" class="form-check-input" id="seleccionarTodo" onchange="toggleSeleccionarTodo()">
                                <label class="form-check-label fw-bold" for="seleccionarTodo">Seleccionar / Deseleccionar todo</label>
                            </div>
                        </div>
                        <div id="obligacionesLista" class="list-group"></div>
                        <div class="mt-3 row g-2" id="cobroTotal" style="display:none">
                            <div class="col-md-6">
                                <select name="medio_pago" class="form-select form-select-sm">
                                    <option value="efectivo">Efectivo</option>
                                    <option value="transferencia">Transferencia</option>
                                    <option value="compensacion">Compensación</option>
                                    <option value="digital">Digital</option>
                                </select>
                            </div>
                            <div class="col-md-6 text-end">
                                <strong>Total seleccionado: $<span id="totalSeleccionado">0.00</span></strong>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-success" id="btnCobrar" style="display:none"><i class="bi bi-cash-coin"></i> Cobrar seleccionados</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
var sesionId = '<?= $sesion['id_sesion'] ?>';

function abrirModalCobro(idSocio, nombre) {
    document.getElementById('cobroIdSocio').value = idSocio;
    document.getElementById('cobroNombreSocio').textContent = nombre;
    document.getElementById('obligacionesLista').innerHTML = '';
    document.getElementById('seleccionarTodo').checked = false;
    document.getElementById('totalSeleccionado').textContent = '0.00';
    document.getElementById('cobroLoading').style.display = 'block';
    document.getElementById('cobroSelectAll').style.display = 'none';
    document.getElementById('cobroTotal').style.display = 'none';
    document.getElementById('btnCobrar').style.display = 'none';

    fetch(BASE_URL + '/sesion/obligaciones/' + sesionId + '/' + idSocio)
        .then(function(r) { return r.json(); })
        .then(function(obligs) {
            document.getElementById('cobroLoading').style.display = 'none';
            var lista = document.getElementById('obligacionesLista');
            lista.innerHTML = '';
            if (!obligs || obligs.length === 0) {
                lista.innerHTML = '<div class="text-center text-muted py-3">No tiene obligaciones pendientes</div>';
                return;
            }
            document.getElementById('cobroSelectAll').style.display = 'block';
            document.getElementById('cobroTotal').style.display = 'block';
            document.getElementById('btnCobrar').style.display = 'inline-block';
            obligs.forEach(function(o, idx) {
                var badgeHtml = '';
                if (o.tipo === 'cuota_credito') badgeHtml = '<span class="badge bg-info me-1">Crédito</span>';
                else if (o.tipo === 'cuota_mensual') badgeHtml = '<span class="badge bg-primary me-1">Cuota</span>';
                else if (o.tipo === 'multa') badgeHtml = '<span class="badge bg-warning text-dark me-1">Multa</span>';
                var div = document.createElement('div');
                div.className = 'list-group-item list-group-item-action d-flex justify-content-between align-items-center';
                div.innerHTML = '<div class="form-check d-flex align-items-center gap-2">' +
                    '<input type="checkbox" class="form-check-input oblig-check" name="obligaciones[]" value="' + o.id_obligacion + '" id="chk_' + idx + '" onchange="actualizarTotalCobro()">' +
                    '<label class="form-check-label d-flex align-items-center gap-2 flex-wrap" for="chk_' + idx + '">' + badgeHtml + '<span>' + o.concepto + '</span></label>' +
                    '</div>' +
                    '<strong>$' + parseFloat(o.monto).toFixed(2) + '</strong>';
                lista.appendChild(div);
            });
            var modal = new bootstrap.Modal(document.getElementById('modalCobro'));
            modal.show();
        })
        .catch(function(e) {
            document.getElementById('cobroLoading').innerHTML = '<span class="text-danger">Error al cargar obligaciones</span>';
        });
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
