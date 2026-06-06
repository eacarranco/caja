<div class="container-fluid">
    <h4>Reportes</h4>

    <div class="row g-3">
        <div class="col-md-4">
            <div class="card card-dashboard">
                <div class="card-body">
                    <h5><i class="bi bi-people"></i> Socios</h5>
                    <p class="text-muted small">Listado completo de socios con saldos</p>
                    <a href="<?= BASE_URL ?>/reporte/socios" class="btn btn-outline-primary btn-sm">Ver tabla</a>
                    <a href="<?= BASE_URL ?>/reporte/socios?formato=csv" class="btn btn-outline-success btn-sm">CSV</a>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card card-dashboard">
                <div class="card-body">
                    <h5><i class="bi bi-graph-up"></i> Financiero</h5>
                    <p class="text-muted small">Resumen de indicadores financieros</p>
                    <a href="<?= BASE_URL ?>/reporte/financiero" class="btn btn-outline-primary btn-sm">Ver</a>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card card-dashboard">
                <div class="card-body">
                    <h5><i class="bi bi-cash-coin"></i> Cobros</h5>
                    <p class="text-muted small">Historial de cobros registrados</p>
                    <a href="<?= BASE_URL ?>/reporte/cobros" class="btn btn-outline-primary btn-sm">Ver tabla</a>
                    <a href="<?= BASE_URL ?>/reporte/cobros?formato=csv" class="btn btn-outline-success btn-sm">CSV</a>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card card-dashboard">
                <div class="card-body">
                    <h5><i class="bi bi-clock-history"></i> Historial operaciones</h5>
                    <p class="text-muted small">Auditoría de todas las operaciones</p>
                    <a href="<?= BASE_URL ?>/reporte/historialOperaciones" class="btn btn-outline-primary btn-sm">Ver</a>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card card-dashboard">
                <div class="card-body">
                    <h5><i class="bi bi-exclamation-triangle"></i> Morosidad</h5>
                    <p class="text-muted small">Cuotas vencidas y en mora</p>
                    <a href="<?= BASE_URL ?>/reporte/morosidad" class="btn btn-outline-warning btn-sm">Ver</a>
                </div>
            </div>
        </div>
    </div>
</div>
