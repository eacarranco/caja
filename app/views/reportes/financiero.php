<div class="container-fluid">
    <h4>Reporte financiero</h4>

    <div class="row g-3">
        <?php foreach ($resumen as $label => $valor): ?>
        <div class="col-md-4">
            <div class="card card-dashboard">
                <div class="card-body">
                    <h6 class="text-muted"><?= htmlspecialchars($label) ?></h6>
                    <h3 class="mb-0"><?php
                        if (is_numeric($valor) && strpos($label, 'Total') === 0) {
                            echo '$' . number_format($valor, 2);
                        } else {
                            echo $valor;
                        }
                    ?></h3>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
</div>
