<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h4>Sesión #<?= $sesion['número_sesión'] ?> — <?= $sesion['fecha'] ?></h4>
            <small class="text-muted"><?= htmlspecialchars($sesion['título'] ?? '') ?></small>
        </div>
        <div>
            <a href="<?= BASE_URL ?>/cobro/registrar/<?= $sesion['id_sesión'] ?>" class="btn btn-outline-primary"><i class="bi bi-cash-coin"></i> Nuevo cobro</a>
            <a href="<?= BASE_URL ?>/sesion/listar" class="btn btn-outline-secondary"><i class="bi bi-arrow-left"></i> Volver</a>
        </div>
    </div>

    <?php if (isset($_SESSION['error'])): ?>
    <div class="alert alert-danger"><?= $_SESSION['error']; unset($_SESSION['error']); ?></div>
    <?php endif; ?>

    <div class="row g-3">
        <div class="col-md-7">
            <div class="card card-dashboard">
                <div class="card-body p-0">
                    <div class="table-responsive"><table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Cédula</th>
                                <th>Socio</th>
                                <th>Asistencia</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($socios as $s): ?>
                            <tr class="<?= isset($asistencias[$s['id_socio']]) ? 'table-success' : '' ?>">
                                <td><?= htmlspecialchars($s['cédula']) ?></td>
                                <td><?= htmlspecialchars($s['nombre_completo']) ?></td>
                                <td>
                                    <form method="POST" class="d-flex gap-1" action="<?= BASE_URL ?>/sesion/checkin/<?= $sesion['id_sesión'] ?>">
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
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table></div>
                </div>
            </div>
        </div>

        <div class="col-md-5">
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
                <button type="submit" class="btn btn-danger w-100"><i class="bi bi-lock"></i> Cerrar sesión</button>
            </form>
        </div>
    </div>
</div>

<?php if (isset($_SESSION['success'])): ?>
<script>document.addEventListener('DOMContentLoaded', function() { alert('<?= $_SESSION['success'] ?>'); });</script>
<?php unset($_SESSION['success']); endif; ?>
