<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4>Listado de socios</h4>
        <?php if (RBAC::tienePermiso($_SESSION['usuario_id'], 'socio.registrar')): ?>
        <a href="<?= BASE_URL ?>/socio/registrar" class="btn btn-primary"><i class="bi bi-plus-circle"></i> Nuevo socio</a>
        <?php endif; ?>
    </div>

    <div class="card card-dashboard mb-4">
        <div class="card-body">
            <form method="GET" class="row g-2">
                <div class="col-md-6">
                    <input type="text" name="search" class="form-control" placeholder="Buscar por cédula, apellido o nombre..."
                           value="<?= htmlspecialchars($search ?? '') ?>">
                </div>
                <div class="col-md-3">
                    <select name="estado" class="form-select">
                        <option value="">Todos los estados</option>
                        <option value="pendiente" <?= ($estado ?? '') === 'pendiente' ? 'selected' : '' ?>>Pendiente</option>
                        <option value="pre_activo" <?= ($estado ?? '') === 'pre_activo' ? 'selected' : '' ?>>Pre-activo</option>
                        <option value="activo" <?= ($estado ?? '') === 'activo' ? 'selected' : '' ?>>Activo</option>
                        <option value="suspendido" <?= ($estado ?? '') === 'suspendido' ? 'selected' : '' ?>>Suspendido</option>
                        <option value="retiro_voluntario" <?= ($estado ?? '') === 'retiro_voluntario' ? 'selected' : '' ?>>Retiro voluntario</option>
                        <option value="excluido" <?= ($estado ?? '') === 'excluido' ? 'selected' : '' ?>>Excluido</option>
                        <option value="fallecido" <?= ($estado ?? '') === 'fallecido' ? 'selected' : '' ?>>Fallecido</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <button type="submit" class="btn btn-outline-primary w-100"><i class="bi bi-search"></i> Buscar</button>
                </div>
            </form>
        </div>
    </div>

    <div class="card card-dashboard">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Cédula</th>
                            <th>Apellidos</th>
                            <th>Nombres</th>
                            <th>Estado</th>
                            <th>Celular</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($socios['data'])): ?>
                        <tr><td colspan="6" class="text-center text-muted py-4">No se encontraron socios</td></tr>
                        <?php else: ?>
                        <?php foreach ($socios['data'] as $s): ?>
                        <tr>
                            <td><?= htmlspecialchars($s['cédula']) ?></td>
                            <td><?= htmlspecialchars($s['apellido1'] . ' ' . ($s['apellido2'] ?? '')) ?></td>
                            <td><?= htmlspecialchars($s['nombre1'] . ' ' . ($s['nombre2'] ?? '')) ?></td>
                            <td><span class="badge bg-<?= $s['estado'] === 'activo' ? 'success' : ($s['estado'] === 'pendiente' ? 'warning' : ($s['estado'] === 'suspendido' ? 'danger' : 'secondary')) ?>"><?= $s['estado'] ?></span></td>
                            <td><?= htmlspecialchars($s['celular']) ?></td>
                            <td>
                                <a href="<?= BASE_URL ?>/socio/ver/<?= $s['id_socio'] ?>" class="btn btn-sm btn-outline-info"><i class="bi bi-eye"></i></a>
                                <?php if (RBAC::tienePermiso($_SESSION['usuario_id'], 'socio.editar')): ?>
                                <a href="<?= BASE_URL ?>/socio/editar/<?= $s['id_socio'] ?>" class="btn btn-sm btn-outline-primary"><i class="bi bi-pencil"></i></a>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <?php if (($socios['totalPages'] ?? 1) > 1): ?>
    <nav class="mt-3">
        <ul class="pagination justify-content-center">
            <?php for ($i = 1; $i <= $socios['totalPages']; $i++): ?>
            <li class="page-item <?= $i === $socios['page'] ? 'active' : '' ?>">
                <a class="page-link" href="?page=<?= $i ?>&search=<?= urlencode($search ?? '') ?>&estado=<?= urlencode($estado ?? '') ?>"><?= $i ?></a>
            </li>
            <?php endfor; ?>
        </ul>
    </nav>
    <?php endif; ?>
</div>
