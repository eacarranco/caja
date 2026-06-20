<div class="container-fluid">
    <?php if (!empty($_SESSION['flash_socio'])): ?>
        <div class="alert alert-success alert-dismissible fade show"><?= htmlspecialchars($_SESSION['flash_socio']) ?><button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
        <?php unset($_SESSION['flash_socio']); ?>
    <?php endif; ?>
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4>Datos del socio</h4>
        <div>
            <a href="<?= BASE_URL ?>/socio/listar" class="btn btn-outline-secondary"><i class="bi bi-arrow-left"></i> Volver</a>
            <?php if (RBAC::tienePermiso($_SESSION['usuario_id'], 'socio.editar')): ?>
            <a href="<?= BASE_URL ?>/socio/editar/<?= $socio['id_socio'] ?>" class="btn btn-primary"><i class="bi bi-pencil"></i> Editar</a>
            <?php endif; ?>
            <?php if ($socio['estado'] === 'activo'): ?>
            <a href="<?= BASE_URL ?>/documento/constanciaSocio/<?= $socio['id_socio'] ?>" class="btn btn-outline-success"><i class="bi bi-file-earmark-text"></i> Constancia</a>
            <a href="<?= BASE_URL ?>/documento/libreDeuda/<?= $socio['id_socio'] ?>" class="btn btn-outline-info"><i class="bi bi-file-earmark-check"></i> Libre deuda</a>
            <a href="<?= BASE_URL ?>/documento/estadoCuenta/<?= $socio['id_socio'] ?>" class="btn btn-outline-warning"><i class="bi bi-wallet2"></i> Estado cuenta</a>
            <?php endif; ?>
        </div>
    </div>

    <div class="row">
        <div class="col-md-6">
            <div class="card card-dashboard mb-3">
                <div class="card-header"><strong>Datos personales</strong></div>
                <div class="card-body">
                    <div class="table-responsive"><table class="table table-sm">
                        <tr><th>Cédula</th><td><?= htmlspecialchars($socio['cedula']) ?></td></tr>
                        <tr><th>Apellidos</th><td><?= htmlspecialchars($socio['apellido1'] . ' ' . ($socio['apellido2'] ?? '')) ?></td></tr>
                        <tr><th>Nombres</th><td><?= htmlspecialchars($socio['nombre1'] . ' ' . ($socio['nombre2'] ?? '')) ?></td></tr>
                        <tr><th>Fecha de nacimiento</th><td><?= $socio['fecha_nacimiento'] ?></td></tr>
                        <tr><th>Género</th><td><?= ucfirst($socio['genero']) ?></td></tr>
                        <tr><th>Estado civil</th><td><?= ucfirst(str_replace('_', ' ', $socio['estado_civil'] ?? '-')) ?></td></tr>
                        <tr><th>Estado</th><td><span class="badge bg-<?= $socio['estado'] === 'activo' ? 'success' : 'warning' ?>"><?= $socio['estado'] ?></span></td></tr>
                    </table></div>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card card-dashboard mb-3">
                <div class="card-header"><strong>Contacto</strong></div>
                <div class="card-body">
                    <div class="table-responsive"><table class="table table-sm">
                        <tr><th>Dirección</th><td><?= htmlspecialchars($socio['direccion']) ?></td></tr>
                        <tr><th>Teléfono</th><td><?= htmlspecialchars($socio['telefono'] ?? '-') ?></td></tr>
                        <tr><th>Celular</th><td><?= htmlspecialchars($socio['celular']) ?></td></tr>
                        <tr><th>Correo</th><td><?= htmlspecialchars($socio['correo_electronico']) ?></td></tr>
                        <tr><th>Profesión</th><td><?= htmlspecialchars($socio['profesion'] ?? '-') ?></td></tr>
                    </table></div>
                </div>
            </div>
        </div>
    </div>

    <?php if (!empty($socio['menor_edad'])): ?>
    <div class="card card-dashboard mb-3">
        <div class="card-header"><strong>Representante legal</strong></div>
        <div class="card-body">
            <div class="table-responsive"><table class="table table-sm">
                <tr><th>Nombres</th><td><?= htmlspecialchars($socio['representante_nombres']) ?></td></tr>
                <tr><th>Cédula</th><td><?= htmlspecialchars($socio['representante_cedula']) ?></td></tr>
                <tr><th>Teléfono</th><td><?= htmlspecialchars($socio['representante_telefono']) ?></td></tr>
                <tr><th>Correo</th><td><?= htmlspecialchars($socio['representante_correo']) ?></td></tr>
            </table></div>
        </div>
    </div>
    <?php endif; ?>

    <?php if (RBAC::tienePermiso($_SESSION['usuario_id'], 'socio.editar')): ?>
    <div class="card card-dashboard mb-3">
        <div class="card-header"><strong>Documentos</strong></div>
        <div class="card-body">
            <div class="d-flex flex-wrap gap-2">
                <button class="btn btn-sm btn-outline-secondary" onclick="subirDoc('foto')"><i class="bi bi-camera"></i> Foto</button>
                <button class="btn btn-sm btn-outline-secondary" onclick="subirDoc('doc_frente')"><i class="bi bi-file-earmark"></i> Cédula frente</button>
                <button class="btn btn-sm btn-outline-secondary" onclick="subirDoc('doc_reverso')"><i class="bi bi-file-earmark"></i> Cédula reverso</button>
                <?php if (!empty($socio['menor_edad'])): ?>
                <button class="btn btn-sm btn-outline-secondary" onclick="subirDoc('doc_representante')"><i class="bi bi-file-earmark-pdf"></i> Doc. representante</button>
                <?php endif; ?>
            </div>
            <div class="mt-2 small text-muted" id="docStatus">
                <?php if ($socio['foto_url']): ?>✅ Foto: <?= basename($socio['foto_url']) ?><br><?php endif; ?>
                <?php if ($socio['documento_identidad_anverso']): ?>✅ Cédula frente<br><?php endif; ?>
                <?php if ($socio['documento_identidad_reverso']): ?>✅ Cédula reverso<br><?php endif; ?>
                <?php if (!empty($socio['representante_documento_pdf'])): ?>✅ Doc. representante<br><?php endif; ?>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <?php if (RBAC::tienePermiso($_SESSION['usuario_id'], 'socio.editar')): ?>
    <div class="card card-dashboard mb-3">
        <div class="card-header"><strong>Contraseña de acceso</strong></div>
        <div class="card-body">
            <?php if ($usuario): ?>
            <p class="mb-2">
                <i class="bi bi-envelope-at"></i> Correo: <strong><?= htmlspecialchars($usuario['correo_electronico'] ?? 'Sin correo') ?></strong><br>
                <i class="bi bi-shield-check"></i> Estado:
                <?php if ($usuario['token_activacion']): ?>
                    <span class="badge bg-warning">Pendiente de activación</span>
                <?php elseif ($usuario['fecha_contrasena']): ?>
                    <span class="badge bg-success">Contraseña establecida</span>
                <?php else: ?>
                    <span class="badge bg-secondary">Sin contraseña</span>
                <?php endif; ?>
            </p>
            <div class="d-flex flex-wrap gap-2">
                <button class="btn btn-sm btn-warning" onclick="forzarCambio()">
                    <i class="bi bi-key"></i> Forzar cambio en próximo login
                </button>
                <button class="btn btn-sm btn-danger" onclick="restablecerContrasena()">
                    <i class="bi bi-arrow-clockwise"></i> Restablecer contraseña
                </button>
            </div>
            <?php else: ?>
            <p class="text-muted mb-2">Este socio no tiene un usuario asociado. Al crear un usuario podrás gestionar su contraseña.</p>
            <a href="<?= BASE_URL ?>/usuario/registrar" class="btn btn-sm btn-primary">
                <i class="bi bi-person-plus"></i> Crear usuario
            </a>
            <?php endif; ?>
            <div id="passMsg" class="mt-2 small"></div>
        </div>
    </div>
    <?php endif; ?>

    <?php if (RBAC::tienePermiso($_SESSION['usuario_id'], 'socio.cambiar_estado')): ?>
    <div class="card card-dashboard">
        <div class="card-header"><strong>Cambiar estado</strong></div>
        <div class="card-body">
            <form id="formCambioEstado" onsubmit="return cambiarEstado('<?= $socio['id_socio'] ?>')" enctype="multipart/form-data">
                <div class="row g-2 align-items-center">
                    <div class="col-auto">
                        <select id="nuevoEstado" class="form-select" onchange="toggleActaFields()">
                            <option value="pre_activo">Pre-activo</option>
                            <option value="activo">Activo</option>
                            <option value="suspendido">Suspendido</option>
                            <option value="retiro_voluntario">Retiro voluntario</option>
                            <option value="excluido">Excluido</option>
                            <option value="fallecido">Fallecido</option>
                        </select>
                    </div>
                    <div class="col-auto">
                        <input type="text" id="motivoCambio" class="form-control" placeholder="Motivo (opcional)">
                    </div>
                    <div class="col-auto" id="actaFields" style="display:none">
                        <input type="text" id="numeroActa" class="form-control mb-1" placeholder="N° acta aprobación">
                        <input type="file" id="actaPdf" class="form-control" accept=".pdf">
                    </div>
                    <div class="col-auto">
                        <button type="submit" class="btn btn-warning">Cambiar estado</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
    <?php endif; ?>

    <?php if (isset($cuenta)): ?>
    <div class="row">
        <div class="col-md-4">
            <div class="card card-dashboard mb-3">
                <div class="card-header"><strong>Cuenta de ahorro</strong></div>
                <div class="card-body">
                    <?php if ($cuenta): ?>
                    <p>Ahorro mensual: <strong>$<?= number_format($cuenta['saldo_obligatorio'], 2) ?></strong></p>
                    <p>Excedente: <strong>$<?= number_format($cuenta['saldo_excedente'], 2) ?></strong></p>
                    <p>Disponible: <strong>$<?= number_format($cuenta['saldo_disponible'], 2) ?></strong></p>
                    <?php else: ?>
                    <p class="text-muted">Sin cuenta registrada</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card card-dashboard mb-3">
                <div class="card-header"><strong>Créditos</strong></div>
                <div class="card-body">
                    <?php if (empty($creditos)): ?>
                    <p class="text-muted">Sin creditos</p>
                    <?php else: ?>
                    <p>Total: <strong><?= count($creditos) ?></strong></p>
                    <ul class="small">
                        <?php foreach ($creditos as $cr): ?>
                        <li><?= ucfirst($cr['estado']) ?> — $<?= number_format($cr['monto_solicitado'], 2) ?></li>
                        <?php endforeach; ?>
                    </ul>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card card-dashboard mb-3">
                <div class="card-header"><strong>Inversiones</strong></div>
                <div class="card-body">
                    <?php if (empty($inversiones)): ?>
                    <p class="text-muted">Sin inversiones</p>
                    <?php else: ?>
                    <p>Total: <strong><?= count($inversiones) ?></strong></p>
                    <ul class="small">
                        <?php foreach ($inversiones as $inv): ?>
                        <li><?= ucfirst($inv['estado']) ?> — $<?= number_format($inv['monto'], 2) ?></li>
                        <?php endforeach; ?>
                    </ul>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>

</div>

<script>
function subirDoc(tipo) {
    var input = document.createElement('input');
    input.type = 'file';
    input.accept = tipo === 'doc_representante' ? '.pdf' : 'image/*,.pdf';
    input.onchange = function() {
        var formData = new FormData();
        formData.append('csrf_token', '<?= $csrfToken ?? '' ?>');
        formData.append('tipo_documento', tipo);
        formData.append('archivo', input.files[0]);
        fetch('<?= BASE_URL ?>/socio/subirDocumento/<?= $socio['id_socio'] ?>', {
            method: 'POST', body: formData
        }).then(function(r) { return r.json(); }).then(function(d) {
            if (d.error) { alert(d.error); } else { alert(d.mensaje); location.reload(); }
        });
    };
    input.click();
}
function toggleActaFields() {
    document.getElementById('actaFields').style.display = document.getElementById('nuevoEstado').value === 'activo' ? 'inline-block' : 'none';
}
function cambiarEstado(id) {
    var estado = document.getElementById('nuevoEstado').value;
    var motivo = document.getElementById('motivoCambio').value;
    if (!confirm('¿Cambiar estado a ' + estado + '?')) return false;
    var formData = new FormData();
    formData.append('csrf_token', '<?= $csrfToken ?? '' ?>');
    formData.append('estado', estado);
    formData.append('motivo', motivo);
    formData.append('numero_acta', document.getElementById('numeroActa') ? document.getElementById('numeroActa').value : '');
    var actaFile = document.getElementById('actaPdf');
    if (actaFile && actaFile.files[0]) formData.append('acta_pdf', actaFile.files[0]);
    fetch('<?= BASE_URL ?>/socio/cambiarEstado/' + id, {
        method: 'POST',
        body: formData
    }).then(function(r) { return r.json(); }).then(function(d) {
        if (d.error) { alert(d.error); } else { alert(d.mensaje); location.reload(); }
    });
    return false;
}
function forzarCambio() {
    if (!confirm('¿Forzar cambio de contrasena en el proximo login del socio?')) return;
    var msg = document.getElementById('passMsg');
    msg.innerHTML = '<span class="text-muted">Procesando...</span>';
    var formData = new FormData();
    formData.append('csrf_token', '<?= $csrfToken ?? '' ?>');
    fetch('<?= BASE_URL ?>/socio/forzarCambioContrasena/<?= $socio['id_socio'] ?>', {
        method: 'POST', body: formData
    }).then(function(r) { return r.json(); }).then(function(d) {
        if (d.error) { msg.innerHTML = '<span class="text-danger">' + d.error + '</span>'; }
        else { msg.innerHTML = '<span class="text-success">' + d.mensaje + '</span>'; }
    }).catch(function() { msg.innerHTML = '<span class="text-danger">Error de red</span>'; });
}
function restablecerContrasena() {
    if (!confirm('Se generara una contrasena temporal y se enviara al correo del socio. ¿Continuar?')) return;
    var msg = document.getElementById('passMsg');
    msg.innerHTML = '<span class="text-muted">Generando y enviando...</span>';
    var formData = new FormData();
    formData.append('csrf_token', '<?= $csrfToken ?? '' ?>');
    fetch('<?= BASE_URL ?>/socio/restablecerContrasena/<?= $socio['id_socio'] ?>', {
        method: 'POST', body: formData
    }).then(function(r) { return r.json(); }).then(function(d) {
        if (d.error) { msg.innerHTML = '<span class="text-danger">' + d.error + '</span>'; }
        else { msg.innerHTML = '<span class="text-success">' + d.mensaje + '</span>'; }
    }).catch(function() { msg.innerHTML = '<span class="text-danger">Error de red</span>'; });
}
</script>
