<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4><?= $editando ? 'Editar usuario' : 'Registrar usuario' ?></h4>
        <a href="<?= BASE_URL ?>/usuario/listar" class="btn btn-outline-secondary"><i class="bi bi-arrow-left"></i> Volver</a>
    </div>

    <div class="card card-dashboard">
        <div class="card-body">
            <?php if (!empty($errors['general'])): ?>
                <div class="alert alert-danger"><?= htmlspecialchars($errors['general']) ?></div>
            <?php endif; ?>
            <form method="POST">
                <?= CSRFMiddleware::campoHTML() ?>
                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label">Cédula *</label>
                        <input type="text" name="cedula" class="form-control <?= isset($errors['cedula']) ? 'is-invalid' : '' ?>"
                               value="<?= htmlspecialchars($data['cédula'] ?? $data['cedula'] ?? '') ?>" <?= $editando ? 'readonly' : 'required' ?> maxlength="10">
                        <?php if (isset($errors['cedula'])): ?><div class="invalid-feedback"><?= $errors['cedula'] ?></div><?php endif; ?>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Nombres *</label>
                        <input type="text" name="nombres" class="form-control <?= isset($errors['nombres']) ? 'is-invalid' : '' ?>"
                               value="<?= htmlspecialchars($data['nombres'] ?? '') ?>" required>
                        <?php if (isset($errors['nombres'])): ?><div class="invalid-feedback"><?= $errors['nombres'] ?></div><?php endif; ?>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Apellidos *</label>
                        <input type="text" name="apellidos" class="form-control <?= isset($errors['apellidos']) ? 'is-invalid' : '' ?>"
                               value="<?= htmlspecialchars($data['apellidos'] ?? '') ?>" required>
                        <?php if (isset($errors['apellidos'])): ?><div class="invalid-feedback"><?= $errors['apellidos'] ?></div><?php endif; ?>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Correo electrónico *</label>
                        <input type="email" name="correo" class="form-control <?= isset($errors['correo']) ? 'is-invalid' : '' ?>"
                               value="<?= htmlspecialchars($data['correo_electrónico'] ?? $data['correo'] ?? '') ?>" required>
                        <?php if (isset($errors['correo'])): ?><div class="invalid-feedback"><?= $errors['correo'] ?></div><?php endif; ?>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Teléfono</label>
                        <input type="text" name="telefono" class="form-control" value="<?= htmlspecialchars($data['teléfono'] ?? '') ?>">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Nombre de usuario *</label>
                        <input type="text" name="username" class="form-control <?= isset($errors['username']) ? 'is-invalid' : '' ?>"
                               value="<?= htmlspecialchars($data['nombre_usuario'] ?? $data['username'] ?? '') ?>" <?= $editando ? 'readonly' : 'required' ?>>
                        <?php if (isset($errors['username'])): ?><div class="invalid-feedback"><?= $errors['username'] ?></div><?php endif; ?>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Contraseña <?= $editando ? '(dejar vacío para mantener)' : '*' ?></label>
                        <input type="password" name="password" class="form-control <?= isset($errors['password']) ? 'is-invalid' : '' ?>"
                               <?= $editando ? '' : 'required' ?> minlength="6">
                        <?php if (isset($errors['password'])): ?><div class="invalid-feedback"><?= $errors['password'] ?></div><?php endif; ?>
                    </div>
                </div>

                <div class="row g-3 mt-3">
                    <h6 class="text-muted">Roles</h6>
                    <div class="col-12">
                        <div class="row">
                            <?php foreach ($roles as $r): ?>
                            <div class="col-md-4">
                                <div class="form-check">
                                    <input type="checkbox" name="roles[]" class="form-check-input"
                                           value="<?= $r['id_rol'] ?>"
                                           id="rol_<?= $r['id_rol'] ?>"
                                           <?= (isset($rolesUsuario) && in_array($r['id_rol'], $rolesUsuario)) ? 'checked' : '' ?>>
                                    <label class="form-check-label" for="rol_<?= $r['id_rol'] ?>">
                                        <?= htmlspecialchars($r['nombre']) ?>
                                        <small class="text-muted"><?= htmlspecialchars($r['endosable'] ? ' (endosable)' : '') ?></small>
                                    </label>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>

                <div class="row g-3 mt-3">
                    <h6 class="text-muted">Configuración</h6>
                    <div class="col-md-4">
                        <div class="form-check">
                            <input type="checkbox" name="activo" class="form-check-input" value="1" id="activo"
                                   <?= (!isset($data['activo']) || $data['activo']) ? 'checked' : '' ?>>
                            <label class="form-check-label" for="activo">Usuario activo</label>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-check">
                            <input type="checkbox" name="_2fa_obligatorio" class="form-check-input" value="1" id="tfa"
                                   <?= !empty($data['_2fa_obligatorio']) ? 'checked' : '' ?>>
                            <label class="form-check-label" for="tfa">2FA obligatorio</label>
                        </div>
                    </div>
                </div>

                <div class="mt-4">
                    <button type="submit" class="btn btn-primary"><i class="bi bi-save"></i> <?= $editando ? 'Guardar cambios' : 'Crear usuario' ?></button>
                </div>
            </form>
        </div>
    </div>
</div>
