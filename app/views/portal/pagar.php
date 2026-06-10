<div class="container-fluid">
    <h4>Valores a pagar</h4>

    <div class="row g-3">
        <div class="col-md-6">
            <div class="card card-dashboard text-center h-100">
                <div class="card-body py-4">
                    <div class="fs-1 text-primary mb-2"><i class="bi bi-piggy-bank"></i></div>
                    <h5>Cuota ahorro obligatorio</h5>
                    <h3 class="text-primary mb-0">$ <?= number_format($pendientes['aporte_mensual'] ?? 0, 2) ?></h3>
                    <small class="text-muted">Aporte mensual fijo segun parametros</small>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card card-dashboard text-center h-100">
                <div class="card-body py-4">
                    <div class="fs-1 text-danger mb-2"><i class="bi bi-exclamation-triangle"></i></div>
                    <h5>Multas pendientes</h5>
                    <h3 class="text-danger mb-0">$ <?= number_format($pendientes['multas'] ?? 0, 2) ?></h3>
                    <small class="text-muted">Multas generadas no pagadas</small>
                </div>
            </div>
        </div>
    </div>

    <div class="card mt-3">
        <div class="card-body text-center">
            <h5>Total a pagar: <strong class="text-primary">$ <?= number_format($pendientes['total'] ?? 0, 2) ?></strong></h5>
            <p class="text-muted mb-0">Valores a pagar en la siguiente Reunion.</p>
        </div>
    </div>


</div>
