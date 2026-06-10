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

    <div class="row g-3">
        <div class="col-md-8">
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
                                        <?php if ($pendiente > 0): ?>
                                        <form method="POST" style="display:inline">
                                            <?= CSRFMiddleware::campoHTML() ?>
                                            <input type="hidden" name="accion" value="pagar_todo_socio">
                                            <input type="hidden" name="id_socio" value="<?= $s['id_socio'] ?>">
                                            <button type="submit" class="btn btn-sm btn-success" title="Pagar todo"><i class="bi bi-cash"></i></button>
                                        </form>
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

        <div class="col-md-4">
            <div class="card card-dashboard mb-3">
                <div class="card-body">
                    <h5>Resumen de cobros</h5>
                    <?php if (empty($resumen_cobros)): ?>
                    <p class="text-muted small">Sin cobros registrados</p>
                    <?php else: ?>
                    <div class="table-responsive"><table class="table table-sm mb-0">
                        <?php foreach ($resumen_cobros as $r): ?>
                        <tr>
                            <td><?= htmlspecialchars($r['tipo']) ?></td>
                            <td><?= $r['total'] ?></td>
                            <td class="text-end">$<?= number_format($r['suma'], 2) ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </table></div>
                    <?php endif; ?>
                </div>
            </div>

            <form method="POST" onsubmit="return confirm('¿Cerrar la sesión? No se podrán registrar más cobros.')">
                <?= CSRFMiddleware::campoHTML() ?>
                <input type="hidden" name="accion" value="cierre">
                <button type="submit" class="btn btn-danger w-100"><i class="bi bi-lock"></i> Cerrar sesion</button>
            </form>
        </div>
    </div>
</div>
