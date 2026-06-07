<div class="container-fluid">
    <h4>Certificaciones</h4>
    <p class="text-muted">Seleccione el certificado que desea imprimir</p>

    <div class="row g-3">
        <div class="col-md-4">
            <div class="card card-dashboard text-center h-100">
                <div class="card-body d-flex flex-column align-items-center justify-content-center py-4">
                    <div class="fs-1 text-warning mb-3"><i class="bi bi-wallet2"></i></div>
                    <h5 class="mb-1">Estado de cuenta</h5>
                    <p class="text-muted small mb-3">Detalle de saldos y movimientos de su cuenta de ahorro</p>
                    <a href="<?= BASE_URL ?>/documento/estadoCuenta/<?= $id_socio ?>" target="_blank" class="btn btn-warning mt-auto"><i class="bi bi-printer"></i> Imprimir</a>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card card-dashboard text-center h-100">
                <div class="card-body d-flex flex-column align-items-center justify-content-center py-4">
                    <div class="fs-1 text-success mb-3"><i class="bi bi-file-earmark-text"></i></div>
                    <h5 class="mb-1">Constancia</h5>
                    <p class="text-muted small mb-3">Constancia de socio activo con datos personales</p>
                    <a href="<?= BASE_URL ?>/documento/constanciaSocio/<?= $id_socio ?>" target="_blank" class="btn btn-success mt-auto"><i class="bi bi-printer"></i> Imprimir</a>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card card-dashboard text-center h-100">
                <div class="card-body d-flex flex-column align-items-center justify-content-center py-4">
                    <div class="fs-1 text-info mb-3"><i class="bi bi-file-earmark-check"></i></div>
                    <h5 class="mb-1">Libre deuda</h5>
                    <p class="text-muted small mb-3">Certificado de no adeudar a la caja de ahorro</p>
                    <a href="<?= BASE_URL ?>/documento/libreDeuda/<?= $id_socio ?>" target="_blank" class="btn btn-info mt-auto"><i class="bi bi-printer"></i> Imprimir</a>
                </div>
            </div>
        </div>
    </div>

    <div class="text-center mt-4">
        <a href="<?= BASE_URL ?>/portal" class="btn btn-outline-primary"><i class="bi bi-arrow-left"></i> Volver a Inicio</a>
    </div>
</div>
