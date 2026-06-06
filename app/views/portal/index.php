<div class="container-fluid">
    <h4>Mi portal</h4>

    <?php if (!$socio): ?>
    <div class="alert alert-info">No se encontró un socio asociado a tu cédula. Contacta al administrador.</div>
    <?php return; endif; ?>

    <div class="row g-3 mb-3">
        <div class="col-md-4">
            <div class="card card-dashboard">
                <div class="card-body">
                    <h5><?= htmlspecialchars($socio['apellido1'] . ' ' . $socio['apellido2'] . ' ' . $socio['nombre1'] . ' ' . $socio['nombre2']) ?></h5>
                    <p class="mb-1 small">Cédula: <?= $socio['cédula'] ?></p>
                    <p class="mb-1 small">Estado: <span class="badge bg-<?= $socio['estado'] === 'activo' ? 'success' : 'warning' ?>"><?= $socio['estado'] ?></span></p>
                    <p class="mb-0 small">Correo: <?= htmlspecialchars($socio['correo_electrónico']) ?></p>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card card-dashboard">
                <div class="card-body">
                    <h5>Cuenta de ahorro</h5>
                    <?php if ($cuenta): ?>
                    <p class="mb-1 small">Aporte obligatorio: <strong>$<?= number_format($cuenta['saldo_obligatorio'], 2) ?></strong></p>
                    <p class="mb-1 small">Aporte excedente: <strong>$<?= number_format($cuenta['saldo_excedente'], 2) ?></strong></p>
                    <p class="mb-0 small">Disponible: <strong>$<?= number_format($cuenta['saldo_disponible'], 2) ?></strong></p>
                    <?php else: ?>
                    <p class="text-muted small mb-0">Sin cuenta registrada</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card card-dashboard">
                <div class="card-body">
                    <h5>Resumen</h5>
                    <p class="mb-1 small">Créditos: <strong><?= count($creditos) ?></strong></p>
                    <p class="mb-1 small">Inversiones: <strong><?= count($inversiones) ?></strong></p>
                    <p class="mb-0 small">Últimos cobros: <strong><?= count($cobros) ?></strong></p>
                </div>
            </div>
        </div>
    </div>

    <div class="mb-3">
        <a href="<?= BASE_URL ?>/documento/estadoCuenta/<?= $socio['id_socio'] ?>" class="btn btn-sm btn-outline-warning"><i class="bi bi-wallet2"></i> Estado de cuenta</a>
        <a href="<?= BASE_URL ?>/documento/constanciaSocio/<?= $socio['id_socio'] ?>" class="btn btn-sm btn-outline-success"><i class="bi bi-file-earmark-text"></i> Constancia</a>
        <a href="<?= BASE_URL ?>/documento/libreDeuda/<?= $socio['id_socio'] ?>" class="btn btn-sm btn-outline-info"><i class="bi bi-file-earmark-check"></i> Libre deuda</a>
    </div>
    <div class="row g-3">
        <div class="col-md-6">
            <div class="card card-dashboard">
                <div class="card-body p-0">
                    <h5 class="p-3 pb-0">Créditos</h5>
                    <?php if (empty($creditos)): ?><p class="p-3 text-muted small">Sin créditos</p><?php else: ?>
                    <div class="table-responsive"><table class="table table-sm mb-0 table-responsive-stack">
                        <thead><tr><th>Producto</th><th>Monto</th><th>Estado</th><th>Fecha</th></tr></thead>
                        <tbody>
                        <?php foreach ($creditos as $c): ?>
                        <tr>
                            <td data-label="Producto"><?= htmlspecialchars($c['producto']) ?></td>
                            <td data-label="Monto">$<?= number_format($c['monto_solicitado'], 2) ?></td>
                            <td data-label="Estado"><span class="badge bg-<?= $c['estado'] === 'aprobado' ? 'success' : ($c['estado'] === 'pendiente' ? 'warning' : 'secondary') ?>"><?= ucfirst($c['estado']) ?></span></td>
                            <td data-label="Fecha"><?= $c['fecha_solicitud'] ?></td>
                        </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table></div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card card-dashboard">
                <div class="card-body p-0">
                    <h5 class="p-3 pb-0">Inversiones</h5>
                    <?php if (empty($inversiones)): ?><p class="p-3 text-muted small">Sin inversiones</p><?php else: ?>
                    <div class="table-responsive"><table class="table table-sm mb-0 table-responsive-stack">
                        <thead><tr><th>Producto</th><th>Monto</th><th>Vencimiento</th><th>Estado</th></tr></thead>
                        <tbody>
                        <?php foreach ($inversiones as $i): ?>
                        <tr>
                            <td data-label="Producto"><?= htmlspecialchars($i['producto']) ?></td>
                            <td data-label="Monto">$<?= number_format($i['monto'], 2) ?></td>
                            <td data-label="Vencimiento"><?= $i['fecha_vencimiento'] ?></td>
                            <td data-label="Estado"><span class="badge bg-<?= $i['estado'] === 'activa' ? 'success' : 'secondary' ?>"><?= ucfirst(str_replace('_', ' ', $i['estado'])) ?></span></td>
                        </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table></div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <div class="mt-3">
        <a href="<?= BASE_URL ?>/portal/solicitarRetiro" class="btn btn-outline-success btn-sm"><i class="bi bi-cash-stack"></i> Solicitar retiro</a>
        <a href="<?= BASE_URL ?>/portal/historial" class="btn btn-outline-primary btn-sm"><i class="bi bi-clock-history"></i> Historial operaciones</a>
        <a href="<?= BASE_URL ?>/portal/multas" class="btn btn-outline-warning btn-sm"><i class="bi bi-exclamation-triangle"></i> Multas</a>
        <a href="<?= BASE_URL ?>/portal/asistencias" class="btn btn-outline-secondary btn-sm"><i class="bi bi-calendar-check"></i> Asistencias</a>
        <a href="<?= BASE_URL ?>/portal/notificaciones" class="btn btn-outline-info btn-sm"><i class="bi bi-bell"></i> Notificaciones</a>
        <a href="<?= BASE_URL ?>/portal/password" class="btn btn-outline-danger btn-sm"><i class="bi bi-key"></i> Cambiar contraseña</a>
    </div>

    <div class="card card-dashboard mt-3">
        <div class="card-body p-0">
            <h5 class="p-3 pb-0">Últimos cobros</h5>
            <?php if (empty($cobros)): ?><p class="p-3 text-muted small">Sin cobros registrados</p><?php else: ?>
            <div class="table-responsive"><table class="table table-sm mb-0 table-responsive-stack">
                <thead><tr><th>Fecha</th><th>Sesión</th><th>Tipo</th><th>Monto</th><th>Comprobante</th></tr></thead>
                <tbody>
                <?php foreach ($cobros as $c): ?>
                <tr>
                    <td data-label="Fecha"><?= $c['fecha_registro'] ?></td>
                    <td data-label="Sesión">#<?= $c['número_sesión'] ?? '-' ?></td>
                    <td data-label="Tipo"><?= ucfirst(str_replace('_', ' ', $c['tipo'])) ?></td>
                    <td data-label="Monto"><strong>$<?= number_format($c['monto'], 2) ?></strong></td>
                    <td data-label="Comp."><a href="<?= BASE_URL ?>/documento/comprobante/<?= $c['id_cobro'] ?>" class="btn btn-sm btn-outline-info"><i class="bi bi-file-earmark-pdf"></i></a></td>
                </tr>
                <?php endforeach; ?>
                </tbody>
            </table></div>
            <?php endif; ?>
        </div>
    </div>
</div>
