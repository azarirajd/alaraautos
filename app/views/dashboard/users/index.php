<?php
// -------------------------------------------
// app/views/dashboard/users/index.php
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h2">Gestión de Usuarios</h1>
    <a href="/dashboard/users/add" class="btn btn-primary">
        <i class="bi bi-person-plus me-2"></i>Crear Usuario
    </a>
</div>

<!-- Filtros -->
<div class="card border-0 shadow-sm mb-4">
    <div class="card-body">
        <form method="GET" class="row g-3">
            <div class="col-md-6">
                <input type="text" name="search" class="form-control" 
                       placeholder="Buscar por nombre o email..." value="<?= e($search) ?>">
            </div>
            <div class="col-md-4">
                <select name="role" class="form-select">
                    <option value="">Todos los roles</option>
                    <option value="admin" <?= $selectedRole == 'admin' ? 'selected' : '' ?>>Admin</option>
                    <option value="gerencia" <?= $selectedRole == 'gerencia' ? 'selected' : '' ?>>Gerencia</option>
                    <option value="ventas" <?= $selectedRole == 'ventas' ? 'selected' : '' ?>>Ventas</option>
                </select>
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-secondary w-100">
                    <i class="bi bi-search"></i> Buscar
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Tabla de usuarios -->
<div class="card border-0 shadow-sm">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>Usuario</th>
                        <th>Email</th>
                        <th>Rol</th>
                        <th>Estado</th>
                        <th>Último acceso</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($users['data'] as $user): ?>
                    <tr>
                        <td>
                            <div class="d-flex align-items-center">
                                <img src="https://ui-avatars.com/api/?name=<?= urlencode($user['name']) ?>&background=6c757d&color=fff" 
                                     alt="Avatar" class="rounded-circle me-2" width="40" height="40">
                                <div>
                                    <strong><?= e($user['name']) ?></strong>
                                    <?php if ($user['id'] == currentUser()['id']): ?>
                                    <span class="badge bg-info ms-1">Tú</span>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </td>
                        <td><?= e($user['email']) ?></td>
                        <td>
                            <?php
                            $roleLabels = [
                                'admin' => 'Administrador',
                                'gerencia' => 'Gerencia',
                                'ventas' => 'Ventas'
                            ];
                            $roleColors = [
                                'admin' => 'danger',
                                'gerencia' => 'warning',
                                'ventas' => 'info'
                            ];
                            ?>
                            <span class="badge bg-<?= $roleColors[$user['role']] ?>">
                                <?= $roleLabels[$user['role']] ?>
                            </span>
                        </td>
                        <td>
                            <?php if ($user['is_active']): ?>
                                <span class="badge bg-success">Activo</span>
                            <?php else: ?>
                                <span class="badge bg-secondary">Inactivo</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?= $user['last_login'] ? formatDate($user['last_login'], 'd/m/Y H:i') : 'Nunca' ?>
                        </td>
                        <td>
                            <div class="btn-group btn-group-sm">
                                <a href="/dashboard/users/edit/<?= $user['id'] ?>" 
                                   class="btn btn-outline-primary" title="Editar">
                                    <i class="bi bi-pencil"></i>
                                </a>
                                <?php if ($user['id'] != currentUser()['id']): ?>
                                <button type="button" class="btn btn-outline-danger delete-confirm" 
                                        data-action="/api/users/<?= $user['id'] ?>"
                                        data-message="¿Eliminar este usuario?" title="Eliminar">
                                    <i class="bi bi-trash"></i>
                                </button>
                                <?php endif; ?>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        
        <!-- Paginación -->
        <?php if ($users['total_pages'] > 1): ?>
        <nav class="mt-4">
            <ul class="pagination justify-content-center">
                <?php for ($i = 1; $i <= $users['total_pages']; $i++): ?>
                <li class="page-item <?= $i == $users['page'] ? 'active' : '' ?>">
                    <a class="page-link" href="?page=<?= $i ?>&search=<?= urlencode($search) ?>&role=<?= urlencode($selectedRole) ?>">
                        <?= $i ?>
                    </a>
                </li>
                <?php endfor; ?>
            </ul>
        </nav>
        <?php endif; ?>
    </div>
</div>
