<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4><?= $titulo ?></h4>
        <a href="<?= BASE_URL ?>/producto/listar" class="btn btn-outline-secondary"><i class="bi bi-arrow-left"></i> Volver</a>
    </div>

    <form method="POST" id="productoForm">
        <?= CSRFMiddleware::campoHTML() ?>

        <!-- Card 1: Información General -->
        <div class="card mb-3">
            <div class="card-header"><strong><i class="bi bi-info-circle"></i> Información general</strong></div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-5">
                        <label class="form-label">Nombre *</label>
                        <input type="text" name="nombre" class="form-control <?= isset($errors['nombre']) ? 'is-invalid' : '' ?>"
                               value="<?= htmlspecialchars($data['nombre'] ?? '') ?>" required>
                        <div class="invalid-feedback"><?= $errors['nombre'] ?? '' ?></div>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Tipo *</label>
                        <select name="tipo" id="tipoProducto" class="form-select <?= isset($errors['tipo']) ? 'is-invalid' : '' ?>"
                                onchange="toggleTipo()">
                            <?php foreach ($tipos as $k => $v): ?>
                            <option value="<?= $k ?>" <?= ($data['tipo'] ?? 'credito') === $k ? 'selected' : '' ?>><?= $v ?></option>
                            <?php endforeach; ?>
                        </select>
                        <div class="invalid-feedback"><?= $errors['tipo'] ?? '' ?></div>
                    </div>
                    <div class="col-md-4 d-flex align-items-end pb-1">
                        <div class="form-check">
                            <input type="checkbox" name="activo" class="form-check-input" value="1" id="checkActivo"
                                   <?= !isset($data['activo']) || !empty($data['activo']) ? 'checked' : '' ?>>
                            <label class="form-check-label" for="checkActivo">Activo</label>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Card 2: Configuración Financiera -->
        <div class="card mb-3">
            <div class="card-header"><strong><i class="bi bi-calculator"></i> Configuración financiera</strong></div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-3">
                        <label class="form-label">Tasa interés anual % *</label>
                        <input type="number" step="0.01" min="0" max="100" name="tasa_interes_anual"
                               class="form-control <?= isset($errors['tasa_interes_anual']) ? 'is-invalid' : '' ?>"
                               value="<?= htmlspecialchars($data['tasa_interes_anual'] ?? '6.00') ?>">
                        <div class="invalid-feedback"><?= $errors['tasa_interes_anual'] ?? '' ?></div>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Plazo mínimo (meses) *</label>
                        <input type="number" min="1" name="plazo_min_meses"
                               class="form-control <?= isset($errors['plazo_min_meses']) ? 'is-invalid' : '' ?>"
                               value="<?= htmlspecialchars($data['plazo_min_meses'] ?? '1') ?>">
                        <div class="invalid-feedback"><?= $errors['plazo_min_meses'] ?? '' ?></div>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Plazo máximo (meses) *</label>
                        <input type="number" min="1" name="plazo_max_meses"
                               class="form-control <?= isset($errors['plazo_max_meses']) ? 'is-invalid' : '' ?>"
                               value="<?= htmlspecialchars($data['plazo_max_meses'] ?? '12') ?>">
                        <div class="invalid-feedback"><?= $errors['plazo_max_meses'] ?? '' ?></div>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Monto mínimo $ *</label>
                        <input type="number" step="0.01" min="0" name="monto_min"
                               class="form-control <?= isset($errors['monto_min']) ? 'is-invalid' : '' ?>"
                               value="<?= htmlspecialchars($data['monto_min'] ?? '0') ?>">
                        <div class="invalid-feedback"><?= $errors['monto_min'] ?? '' ?></div>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Monto máximo $ *</label>
                        <input type="number" step="0.01" min="0" name="monto_max"
                               class="form-control <?= isset($errors['monto_max']) ? 'is-invalid' : '' ?>"
                               value="<?= htmlspecialchars($data['monto_max'] ?? '1000') ?>">
                        <div class="invalid-feedback"><?= $errors['monto_max'] ?? '' ?></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Card 3: Opciones de Crédito -->
        <div id="camposCredito">
            <div class="card mb-3">
                <div class="card-header"><strong><i class="bi bi-file-earmark-text"></i> Opciones de crédito</strong></div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-3">
                            <label class="form-label">Método de interés *</label>
                            <select name="metodo_interes" class="form-select <?= isset($errors['metodo_interes']) ? 'is-invalid' : '' ?>">
                                <?php foreach ($metodos as $k => $v): ?>
                                <option value="<?= $k ?>" <?= ($data['metodo_interes'] ?? 'simple') === $k ? 'selected' : '' ?>><?= $v ?></option>
                                <?php endforeach; ?>
                            </select>
                            <div class="invalid-feedback"><?= $errors['metodo_interes'] ?? '' ?></div>
                        </div>
                        <div class="col-md-3 d-flex align-items-end pb-1">
                            <div class="form-check">
                                <input type="checkbox" name="requiere_garante" class="form-check-input" value="1" id="reqGarante"
                                       <?= !empty($data['requiere_garante']) ? 'checked' : '' ?>>
                                <label class="form-check-label" for="reqGarante">Requiere garante</label>
                            </div>
                        </div>
                        <div class="col-md-3 d-flex align-items-end pb-1">
                            <div class="form-check">
                                <input type="checkbox" name="requiere_documento_firmado" class="form-check-input" value="1" id="reqDocFirmado"
                                       <?= !isset($data['requiere_documento_firmado']) || !empty($data['requiere_documento_firmado']) ? 'checked' : '' ?>>
                                <label class="form-check-label" for="reqDocFirmado">Requiere documento firmado</label>
                            </div>
                        </div>
                        <div class="col-md-3 d-flex align-items-end pb-1">
                            <div class="form-check">
                                <input type="checkbox" name="es_emergente" class="form-check-input" value="1" id="esEmergente"
                                       onchange="document.getElementById('montoEmergenteGroup').style.display=this.checked?'':'none'"
                                       <?= !empty($data['es_emergente']) ? 'checked' : '' ?>>
                                <label class="form-check-label" for="esEmergente">Crédito emergente</label>
                            </div>
                        </div>
                    </div>
                    <div class="row g-3 mt-2" id="montoEmergenteGroup" style="display:<?= !empty($data['es_emergente']) ? '' : 'none' ?>">
                        <div class="col-md-3">
                            <label class="form-label">Monto máximo emergente $</label>
                            <input type="number" step="0.01" min="0" name="monto_max_emergente" class="form-control"
                                   value="<?= htmlspecialchars($data['monto_max_emergente'] ?? '0') ?>">
                        </div>
                    </div>
                </div>
            </div>

            <!-- Card 4: Condiciones de Elegibilidad -->
            <div class="card mb-3">
                <div class="card-header"><strong><i class="bi bi-check2-square"></i> Condiciones de elegibilidad</strong></div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label">Descripcion destino</label>
                            <div class="input-group">
                                <div class="input-group-text">
                                    <input type="checkbox" name="usa_min_destino_caracteres" class="form-check-input mt-0" value="1" id="chkDestCar"
                                           onclick="document.getElementById('destCarInput').disabled=!this.checked"
                                           <?= (floatval($data['min_destino_caracteres'] ?? 0) > 0) ? 'checked' : '' ?>>
                                </div>
                                <input type="number" min="1" name="min_destino_caracteres" id="destCarInput"
                                       class="form-control <?= isset($errors['min_destino_caracteres']) ? 'is-invalid' : '' ?>"
                                       value="<?= htmlspecialchars($data['min_destino_caracteres'] ?? '0') ?>"
                                       placeholder="Caracteres"
                                       <?= (floatval($data['min_destino_caracteres'] ?? 0) > 0) ? '' : 'disabled' ?>>
                                <span class="input-group-text">caracteres</span>
                                <div class="invalid-feedback"><?= $errors['min_destino_caracteres'] ?? '' ?></div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Permanencia minima</label>
                            <div class="input-group">
                                <div class="input-group-text">
                                    <input type="checkbox" name="usa_min_permanencia" class="form-check-input mt-0" value="1" id="chkPerm"
                                           onclick="document.getElementById('permValInput').disabled=!this.checked;document.getElementById('permUnidad').disabled=!this.checked"
                                           <?= (floatval($data['min_permanencia_valor'] ?? 0) > 0) ? 'checked' : '' ?>>
                                </div>
                                <input type="number" min="1" name="min_permanencia_valor" id="permValInput"
                                       class="form-control <?= isset($errors['min_permanencia_valor']) ? 'is-invalid' : '' ?>"
                                       value="<?= htmlspecialchars($data['min_permanencia_valor'] ?? '0') ?>"
                                       placeholder="Valor"
                                       <?= (floatval($data['min_permanencia_valor'] ?? 0) > 0) ? '' : 'disabled' ?>>
                                <select name="min_permanencia_unidad" id="permUnidad" class="form-select" style="max-width:100px" <?= (floatval($data['min_permanencia_valor'] ?? 0) > 0) ? '' : 'disabled' ?>>
                                    <option value="dias" <?= ($data['min_permanencia_unidad'] ?? 'meses') === 'dias' ? 'selected' : '' ?>>Dias</option>
                                    <option value="meses" <?= ($data['min_permanencia_unidad'] ?? 'meses') === 'meses' ? 'selected' : '' ?>>Meses</option>
                                    <option value="anios" <?= ($data['min_permanencia_unidad'] ?? 'meses') === 'anios' ? 'selected' : '' ?>>Anios</option>
                                </select>
                                <div class="invalid-feedback"><?= $errors['min_permanencia_valor'] ?? '' ?></div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Ahorro minimo</label>
                            <div class="input-group">
                                <div class="input-group-text">
                                    <input type="checkbox" name="usa_min_ahorro" class="form-check-input mt-0" value="1" id="chkAhorro"
                                           onclick="var e=this.checked;document.getElementById('ahorroInput').disabled=!e;document.getElementById('ahorroUnidad').disabled=!e;if(!e){document.getElementById('ahorroHidden').value='0';document.getElementById('ahorroUnidadHidden').value='dolares'}"
                                           <?= (floatval($data['min_ahorro'] ?? 0) > 0) ? 'checked' : '' ?>>
                                </div>
                                <input type="hidden" name="min_ahorro" id="ahorroHidden" value="<?= htmlspecialchars($data['min_ahorro'] ?? '0') ?>">
                                <input type="number" step="0.01" min="0.01" name="min_ahorro_visible" id="ahorroInput"
                                       class="form-control <?= isset($errors['min_ahorro']) ? 'is-invalid' : '' ?>"
                                       value="<?= htmlspecialchars($data['min_ahorro'] ?? '0') ?>"
                                       placeholder="Valor"
                                       oninput="document.getElementById('ahorroHidden').value=this.value"
                                       <?= (floatval($data['min_ahorro'] ?? 0) > 0) ? '' : 'disabled' ?>>
                                <input type="hidden" name="min_ahorro_unidad" id="ahorroUnidadHidden" value="<?= htmlspecialchars($data['min_ahorro_unidad'] ?? 'dolares') ?>">
                                <select name="min_ahorro_unidad_visible" id="ahorroUnidad"
                                        class="form-select" style="max-width:130px"
                                        onchange="document.getElementById('ahorroUnidadHidden').value=this.value"
                                        <?= (floatval($data['min_ahorro'] ?? 0) > 0) ? '' : 'disabled' ?>>
                                    <option value="dolares" <?= ($data['min_ahorro_unidad'] ?? 'dolares') === 'dolares' ? 'selected' : '' ?>>Dolares</option>
                                    <option value="porcentaje" <?= ($data['min_ahorro_unidad'] ?? 'dolares') === 'porcentaje' ? 'selected' : '' ?>>% del credito</option>
                                </select>
                                <div class="invalid-feedback"><?= $errors['min_ahorro'] ?? '' ?></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Card 5: Opciones de Inversión -->
        <div id="camposInversion" style="display:none">
            <div class="card mb-3">
                <div class="card-header"><strong><i class="bi bi-piggy-bank"></i> Opciones de inversión</strong></div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label">Permanencia mínima (meses)</label>
                            <input type="number" min="0" name="min_permanencia_meses" class="form-control"
                                   value="<?= htmlspecialchars($data['min_permanencia_meses'] ?? '0') ?>">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Ahorro mínimo requerido $</label>
                            <input type="number" step="0.01" min="0" name="min_ahorro_inv" class="form-control"
                                   value="<?= htmlspecialchars($data['min_ahorro'] ?? '0') ?>">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Penalidad retiro anticipado %</label>
                            <input type="number" step="0.01" min="0" max="100" name="penalidad_retiro_anticipado"
                                   class="form-control"
                                   value="<?= htmlspecialchars($data['penalidad_retiro_anticipado'] ?? '0') ?>">
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Card 6: Condiciones del Producto -->
        <div class="card mb-3">
            <div class="card-header"><strong><i class="bi bi-file-text"></i> <span id="lblCondiciones">Condiciones del producto</span></strong></div>
            <div class="card-body">
                <div id="editorContainer" style="min-height:200px; border:1px solid #ccc; border-radius:4px;"></div>
                <textarea name="condiciones_html" id="condicionesHtml" class="d-none"><?= htmlspecialchars($data['condiciones_html'] ?? '') ?></textarea>
            </div>
        </div>

        <div class="mt-3">
            <button type="submit" class="btn btn-primary"><i class="bi bi-save"></i> <?= $editando ? 'Guardar cambios' : 'Crear producto' ?></button>
        </div>
    </form>
</div>

<link href="https://cdn.jsdelivr.net/npm/summernote@0.8.20/dist/summernote-bs5.min.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/jquery@3.7.1/dist/jquery.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/summernote@0.8.20/dist/summernote-bs5.min.js"></script>
<script>
function toggleTipo() {
    var tipo = document.getElementById('tipoProducto').value;
    var esCredito = tipo === 'credito';
    document.getElementById('camposCredito').style.display = esCredito ? '' : 'none';
    document.getElementById('camposInversion').style.display = esCredito ? 'none' : '';
    document.getElementById('lblCondiciones').textContent = esCredito ? 'Condiciones del crédito' : 'Condiciones de la inversión';
}

document.querySelector('form').onsubmit = function() {
    $('#condicionesHtml').val($('#editorContainer').summernote('code'));
};

toggleTipo();

$(document).ready(function() {
    $('#editorContainer').summernote({
        height: 200,
        placeholder: 'Escriba las condiciones...',
        toolbar: [
            ['style', ['bold', 'italic', 'underline', 'clear']],
            ['para', ['ul', 'ol', 'paragraph']],
            ['insert', ['link']],
            ['view', ['codeview']],
        ]
    });
    var ta = document.getElementById('condicionesHtml');
    if (ta.value) $('#editorContainer').summernote('code', ta.value);
});
</script>
