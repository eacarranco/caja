<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4>Registrar nuevo socio</h4>
        <a href="<?= BASE_URL ?>/socio/listar" class="btn btn-outline-secondary"><i class="bi bi-arrow-left"></i> Volver</a>
    </div>

    <div class="card card-dashboard">
        <div class="card-body">
            <?php if (!empty($errors['general'])): ?>
                <div class="alert alert-danger"><?= htmlspecialchars($errors['general']) ?></div>
            <?php endif; ?>
            <form method="POST" action="<?= BASE_URL ?>/socio/registrar">
                <?= CSRFMiddleware::campoHTML() ?>
                <div class="row g-3">
                    <h6 class="text-muted">Datos personales</h6>
                    <div class="col-md-3">
                        <label class="form-label">Cédula *</label>
                        <input type="text" name="cedula" class="form-control <?= isset($errors['cédula']) ? 'is-invalid' : '' ?>"
                               value="<?= htmlspecialchars($data['cedula'] ?? '') ?>" required maxlength="10">
                        <?php if (isset($errors['cédula'])): ?><div class="invalid-feedback"><?= $errors['cédula'] ?></div><?php endif; ?>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Primer apellido *</label>
                        <input type="text" name="apellido1" class="form-control <?= isset($errors['apellido1']) ? 'is-invalid' : '' ?>"
                               value="<?= htmlspecialchars($data['apellido1'] ?? '') ?>" required>
                        <?php if (isset($errors['apellido1'])): ?><div class="invalid-feedback"><?= $errors['apellido1'] ?></div><?php endif; ?>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Segundo apellido</label>
                        <input type="text" name="apellido2" class="form-control" value="<?= htmlspecialchars($data['apellido2'] ?? '') ?>">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Primer nombre *</label>
                        <input type="text" name="nombre1" class="form-control <?= isset($errors['nombre1']) ? 'is-invalid' : '' ?>"
                               value="<?= htmlspecialchars($data['nombre1'] ?? '') ?>" required>
                        <?php if (isset($errors['nombre1'])): ?><div class="invalid-feedback"><?= $errors['nombre1'] ?></div><?php endif; ?>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Segundo nombre</label>
                        <input type="text" name="nombre2" class="form-control" value="<?= htmlspecialchars($data['nombre2'] ?? '') ?>">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Fecha de nacimiento *</label>
                        <input type="date" name="fecha_nacimiento" class="form-control <?= isset($errors['fecha_nacimiento']) ? 'is-invalid' : '' ?>"
                               value="<?= htmlspecialchars($data['fecha_nacimiento'] ?? '') ?>" required>
                        <?php if (isset($errors['fecha_nacimiento'])): ?><div class="invalid-feedback"><?= $errors['fecha_nacimiento'] ?></div><?php endif; ?>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Género *</label>
                        <select name="genero" class="form-select">
                            <option value="masculino" <?= ($data['genero'] ?? '') === 'masculino' ? 'selected' : '' ?>>Masculino</option>
                            <option value="femenino" <?= ($data['genero'] ?? '') === 'femenino' ? 'selected' : '' ?>>Femenino</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Estado civil</label>
                        <select name="estado_civil" class="form-select">
                            <option value="">Seleccionar...</option>
                            <option value="soltero">Soltero/a</option>
                            <option value="casado">Casado/a</option>
                            <option value="divorciado">Divorciado/a</option>
                            <option value="viudo">Viudo/a</option>
                            <option value="unión_libre">Unión libre</option>
                        </select>
                    </div>
                </div>

                <div class="row g-3 mt-2">
                    <h6 class="text-muted">Contacto</h6>
                    <div class="col-md-4">
                        <label class="form-label">Dirección *</label>
                        <input type="text" name="direccion" class="form-control <?= isset($errors['dirección']) ? 'is-invalid' : '' ?>"
                               value="<?= htmlspecialchars($data['direccion'] ?? '') ?>" required>
                        <?php if (isset($errors['dirección'])): ?><div class="invalid-feedback"><?= $errors['dirección'] ?></div><?php endif; ?>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Teléfono</label>
                        <input type="text" name="telefono" class="form-control" value="<?= htmlspecialchars($data['telefono'] ?? '') ?>">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Celular *</label>
                        <input type="text" name="celular" class="form-control" value="<?= htmlspecialchars($data['celular'] ?? '') ?>" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Correo electrónico *</label>
                        <input type="email" name="correo" class="form-control <?= isset($errors['correo_electrónico']) ? 'is-invalid' : '' ?>"
                               value="<?= htmlspecialchars($data['correo'] ?? '') ?>" required>
                        <?php if (isset($errors['correo_electrónico'])): ?><div class="invalid-feedback"><?= $errors['correo_electrónico'] ?></div><?php endif; ?>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Profesión</label>
                        <input type="text" name="profesion" class="form-control" value="<?= htmlspecialchars($data['profesion'] ?? '') ?>">
                    </div>
                </div>

                <div class="row g-3 mt-2">
                    <h6 class="text-muted">Menor de edad</h6>
                    <div class="col-md-12">
                        <div class="form-check">
                            <input type="checkbox" name="menor_edad" class="form-check-input" value="1" id="menorCheck"
                                   onchange="document.getElementById('repSection').style.display = this.checked ? 'block' : 'none'">
                            <label class="form-check-label" for="menorCheck">El socio es menor de edad</label>
                        </div>
                    </div>
                    <div id="repSection" style="display:none">
                        <div class="col-md-3">
                            <label class="form-label">Nombres del representante</label>
                            <input type="text" name="representante_nombres" class="form-control">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Cédula del representante</label>
                            <input type="text" name="representante_cedula" class="form-control" maxlength="10">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Teléfono del representante</label>
                            <input type="text" name="representante_telefono" class="form-control">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Correo del representante</label>
                            <input type="email" name="representante_correo" class="form-control">
                        </div>
                    </div>
                </div>

                <div class="mt-4">
                    <button type="submit" class="btn btn-primary"><i class="bi bi-save"></i> Registrar socio</button>
                </div>
            </form>
        </div>
    </div>
</div>
