<div class="container-fluid">
    <h4 class="mb-3">Dashboard</h4>

    <div class="row row-cols-2 row-cols-md-5 g-3 mb-4">
        <div class="col-md-3">
            <div class="card card-dashboard text-center h-100">
                <div class="card-body d-flex flex-column justify-content-center">
                    <h2 class="mb-0"><?= $totalSocios ?></h2>
                    <small class="text-muted">Total socios</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card card-dashboard text-center h-100">
                <div class="card-body d-flex flex-column justify-content-center">
                    <h2 class="mb-0 text-success"><?= $sociosActivos ?></h2>
                    <small class="text-muted">Socios activos</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card card-dashboard text-center h-100">
                <div class="card-body d-flex flex-column justify-content-center">
                    <h2 class="mb-0 <?= $sesionAbierta ? 'text-success' : 'text-muted' ?>"><?= $sesionAbierta ? 'Si' : 'No' ?></h2>
                    <small class="text-muted">Sesion abierta</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card card-dashboard text-center h-100">
                <div class="card-body d-flex flex-column justify-content-center">
                    <h2 class="mb-0 text-warning"><?= $creditosPendientes ?></h2>
                    <small class="text-muted">Creditos pendientes</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card card-dashboard text-center h-100 border-success">
                <div class="card-body d-flex flex-column justify-content-center">
                    <h3 class="mb-1 text-success">$ <?= number_format($capitalCaja, 2) ?></h3>
                    <small class="text-muted">Capital de Caja</small>
                    <a href="<?= BASE_URL ?>/caja/estadoCuenta" class="btn btn-sm btn-outline-success mt-2"><i class="bi bi-list-ul"></i> Detalle</a>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-3">
        <div class="col-md-6">
            <div class="card card-dashboard">
                <div class="card-body">
                    <h5>Últimos cobros</h5>
                    <?php if (empty($ultimosCobros)): ?>
                    <p class="text-muted small">Sin cobros registrados</p>
                    <?php else: ?>
                    <div class="table-responsive"><table class="table table-sm mb-0">
                        <?php foreach ($ultimosCobros as $c): ?>
                        <tr>
                            <td class="small"><?= $c['fecha_registro'] ?></td>
                            <td><?= htmlspecialchars($c['socio']) ?></td>
                            <td><?= $c['tipo'] ?></td>
                            <td class="text-end">$<?= number_format($c['monto'], 2) ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </table></div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card card-dashboard">
                <div class="card-body">
                    <h5>Últimas sesiones</h5>
                    <?php if (empty($ultimasSesiones)): ?>
                    <p class="text-muted small">Sin sesiones registradas</p>
                    <?php else: ?>
                    <div class="table-responsive"><table class="table table-sm mb-0">
                        <?php foreach ($ultimasSesiones as $s): ?>
                        <tr>
                            <td>#<?= $s['numero_sesion'] ?></td>
                            <td><?= $s['fecha_sesion'] ?></td>
                            <td><span class="badge <?= $s['estado'] === 'abierta' ? 'bg-success' : 'bg-secondary' ?>"><?= ucfirst($s['estado']) ?></span></td>
                        </tr>
                        <?php endforeach; ?>
                    </table></div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <?php if (!empty($permisos)): ?>
    <div class="card card-dashboard mt-3">
        <div class="card-body">
            <h5>Acceso rápido</h5>
            <div class="d-flex flex-wrap gap-2">
                <?php if (in_array('param.usuarios', $permisos)): ?>
                <a href="<?= BASE_URL ?>/usuario/listar" class="btn btn-outline-primary btn-sm">Usuarios</a>
                <?php endif; ?>
                <?php if (in_array('param.roles', $permisos)): ?>
                <a href="<?= BASE_URL ?>/rol/listar" class="btn btn-outline-primary btn-sm">Roles</a>
                <?php endif; ?>
                <?php if (in_array('param.financiero', $permisos)): ?>
                <a href="<?= BASE_URL ?>/parametro/listar" class="btn btn-outline-primary btn-sm">Parámetros</a>
                <a href="<?= BASE_URL ?>/producto/listar" class="btn btn-outline-primary btn-sm">Productos</a>
                <?php endif; ?>
                <?php if (in_array('cobro.aporte', $permisos)): ?>
                <a href="<?= BASE_URL ?>/sesion/listar" class="btn btn-outline-success btn-sm">Sesiones</a>
                <a href="<?= BASE_URL ?>/cobro/listar" class="btn btn-outline-success btn-sm">Cobros</a>
                <?php endif; ?>
                <?php if (in_array('cobro.desembolso', $permisos)): ?>
                <a href="<?= BASE_URL ?>/credito/listar" class="btn btn-outline-warning btn-sm">Créditos</a>
                <?php endif; ?>
                <?php if (in_array('calculo.intereses', $permisos)): ?>
                <a href="<?= BASE_URL ?>/calculo/simulador" class="btn btn-outline-info btn-sm">Simulador</a>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <?php if (!empty($chartLabels) || !empty($chartTipoLabels)): ?>
    <div class="row g-3 mt-2">
        <?php if (!empty($chartLabels)): ?>
        <div class="col-md-6">
            <div class="card card-dashboard">
                <div class="card-body">
                    <h5>Cobros por mes (últimos 6)</h5>
                    <canvas id="chartMes" height="150"></canvas>
                </div>
            </div>
        </div>
        <?php endif; ?>
        <?php if (!empty($chartTipoLabels)): ?>
        <div class="col-md-6">
            <div class="card card-dashboard">
                <div class="card-body">
                    <h5>Cobros por tipo</h5>
                    <canvas id="chartTipo" height="150"></canvas>
                </div>
            </div>
        </div>
        <?php endif; ?>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.7/dist/chart.umd.min.js"></script>
    <script>
    <?php if (!empty($chartLabels)): ?>
    new Chart(document.getElementById('chartMes'), {
        type: 'bar',
        data: {
            labels: <?= json_encode($chartLabels) ?>,
            datasets: [{ label: 'Total ($)', data: <?= json_encode($chartData) ?>, backgroundColor: '#0d6efd' }]
        },
        options: { responsive: true, plugins: { legend: { display: false } } }
    });
    <?php endif; ?>
    <?php if (!empty($chartTipoLabels)): ?>
    new Chart(document.getElementById('chartTipo'), {
        type: 'doughnut',
        data: {
            labels: <?= json_encode($chartTipoLabels) ?>,
            datasets: [{ data: <?= json_encode($chartTipoData) ?>, backgroundColor: ['#0d6efd','#198754','#ffc107','#dc3545','#6f42c1'] }]
        }
    });
    <?php endif; ?>
    </script>
    <?php endif; ?>
</div>
