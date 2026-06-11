<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4><i class="bi bi-cash-stack"></i> Capital de Caja</h4>
    </div>

    <!-- Saldo actual -->
    <div class="row mb-3">
        <div class="col-md-4">
            <div class="card border-success">
                <div class="card-body text-center py-3">
                    <h6 class="text-muted">Saldo actual</h6>
                    <h2 class="text-success mb-0">$ <?= number_format($saldoActual, 2) ?></h2>
                </div>
            </div>
        </div>
    </div>

    <!-- Filtros -->
    <div class="card mb-3">
        <div class="card-body">
            <form method="GET" class="row g-2 align-items-end">
                <div class="col-auto">
                    <label class="form-label small">Desde</label>
                    <input type="date" name="desde" class="form-control form-control-sm" value="<?= $desde ?>">
                </div>
                <div class="col-auto">
                    <label class="form-label small">Hasta</label>
                    <input type="date" name="hasta" class="form-control form-control-sm" value="<?= $hasta ?>">
                </div>
                <div class="col-auto">
                    <label class="form-label small">Categoria</label>
                    <select name="categoria" class="form-select form-select-sm">
                        <option value="">Todas</option>
                        <?php foreach ($categorias as $c): ?>
                        <option value="<?= $c ?>" <?= $categoriaSel === $c ? 'selected' : '' ?>><?= ucfirst(str_replace('_', ' ', $c)) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-auto">
                    <button type="submit" class="btn btn-sm btn-primary"><i class="bi bi-filter"></i> Filtrar</button>
                </div>
                <div class="col-auto">
                    <a href="<?= BASE_URL ?>/caja/exportarCSV?desde=<?= $desde ?>&hasta=<?= $hasta ?>&categoria=<?= $categoriaSel ?>" class="btn btn-sm btn-outline-success"><i class="bi bi-filetype-csv"></i> CSV</a>
                    <a href="<?= BASE_URL ?>/caja/exportarXLSX?desde=<?= $desde ?>&hasta=<?= $hasta ?>&categoria=<?= $categoriaSel ?>" class="btn btn-sm btn-outline-primary"><i class="bi bi-file-earmark-excel"></i> Excel</a>
                    <a href="<?= BASE_URL ?>/caja/exportarPDF?desde=<?= $desde ?>&hasta=<?= $hasta ?>&categoria=<?= $categoriaSel ?>" target="_blank" class="btn btn-sm btn-outline-danger"><i class="bi bi-filetype-pdf"></i> PDF</a>
                </div>
            </form>
        </div>
    </div>

    <!-- Tabla estado de cuenta -->
    <div class="card">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-sm table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Fecha</th>
                            <th>Concepto</th>
                            <th>Categoria</th>
                            <th class="text-end">Ingreso</th>
                            <th class="text-end">Egreso</th>
                            <th class="text-end">Saldo</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($movimientos)): ?>
                        <tr><td colspan="6" class="text-center text-muted py-4">No hay movimientos en el periodo seleccionado</td></tr>
                        <?php else:
                            $totalIngresos = 0; $totalEgresos = 0;
                            foreach ($movimientos as $m):
                            $esIngreso = $m['tipo_movimiento'] === 'ingreso';
                            if ($esIngreso) $totalIngresos += floatval($m['monto']);
                            else $totalEgresos += floatval($m['monto']);
                        ?>
                        <tr>
                            <td><?= $m['fecha_registro'] ?></td>
                            <td><?= htmlspecialchars($m['concepto']) ?></td>
                            <td><span class="badge bg-info"><?= ucfirst(str_replace('_', ' ', $m['categoria'])) ?></span></td>
                            <td class="text-end text-success"><?= $esIngreso ? '$' . number_format($m['monto'], 2) : '' ?></td>
                            <td class="text-end text-danger"><?= !$esIngreso ? '$' . number_format($m['monto'], 2) : '' ?></td>
                            <td class="text-end"><strong>$<?= number_format($m['saldo_posterior'], 2) ?></strong></td>
                        </tr>
                        <?php endforeach; endif; ?>
                    </tbody>
                    <tfoot class="table-light fw-bold">
                        <tr>
                            <td colspan="3" class="text-end">TOTALES</td>
                            <td class="text-end text-success">$<?= number_format($totalIngresos, 2) ?></td>
                            <td class="text-end text-danger">$<?= number_format($totalEgresos, 2) ?></td>
                            <td class="text-end">$<?= number_format($totalIngresos - $totalEgresos, 2) ?></td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>

    <!-- Resumen por categoria -->
    <?php if (!empty($resumen)): ?>
    <div class="card mt-3">
        <div class="card-header"><strong>Resumen por categoria</strong></div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-sm mb-0">
                    <thead class="table-light">
                        <tr><th>Categoria</th><th class="text-end">Ingresos</th><th class="text-end">Egresos</th></tr>
                    </thead>
                    <tbody>
                        <?php $catRes = []; foreach ($resumen as $r) { $catRes[$r['categoria']][$r['tipo_movimiento']] = floatval($r['total']); } ?>
                        <?php foreach ($catRes as $cat => $val): ?>
                        <tr>
                            <td><?= ucfirst(str_replace('_', ' ', $cat)) ?></td>
                            <td class="text-end text-success">$<?= number_format($val['ingreso'] ?? 0, 2) ?></td>
                            <td class="text-end text-danger">$<?= number_format($val['egreso'] ?? 0, 2) ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <?php endif; ?>
</div>
