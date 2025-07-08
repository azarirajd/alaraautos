<?php
// app/views/dashboard/users/edit.php
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h2">Editar Usuario</h1>
    <a href="/dashboard/users" class="btn btn-secondary">
        <i class="bi bi-arrow-left me-2"></i>Volver
    </a>
</div>

<div class="row">
    <div class="col-lg-8">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <form id="editUserForm" class="ajax-form" action="/api/users/<?= $editUser['id'] ?>" 
                      method="PUT" data-json-submit="true">
                    <input type="hidden" name="csrf_token" value="<?= e($csrf_token) ?>">
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Nombre completo *</label>
                            <input type="text" class="form-control" name="name" 
                                   value="<?= e($editUser['name']) ?>" required>
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Email *</label>
                            <input type="email" class="form-control" name="email" 
                                   value="<?= e($editUser['email']) ?>" required>
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Nueva contraseña</label>
                            <input type="password" class="form-control" name="password" minlength="6">
                            <small class="text-muted">Dejar vacío para mantener la actual</small>
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Confirmar nueva contraseña</label>
                            <input type="password" class="form-control" name="password_confirmation">
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Rol *</label>
                            <select class="form-select" name="role" required 
                                    <?= !$canEditRole ? 'disabled' : '' ?>>
                                <option value="admin" <?= $editUser['role'] == 'admin' ? 'selected' : '' ?>>
                                    Administrador
                                </option>
                                <option value="gerencia" <?= $editUser['role'] == 'gerencia' ? 'selected' : '' ?>>
                                    Gerencia
                                </option>
                                <option value="ventas" <?= $editUser['role'] == 'ventas' ? 'selected' : '' ?>>
                                    Ventas
                                </option>
                            </select>
                            <?php if (!$canEditRole): ?>
                            <input type="hidden" name="role" value="<?= e($editUser['role']) ?>">
                            <small class="text-danger">
                                No puedes cambiar el rol del último administrador
                            </small>
                            <?php endif; ?>
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Estado</label>
                            <div class="form-check form-switch mt-2">
                                <input class="form-check-input" type="checkbox" name="is_active" 
                                       value="1" id="active" 
                                       <?= $editUser['is_active'] ? 'checked' : '' ?>
                                       <?= $editUser['id'] == currentUser()['id'] ? 'disabled' : '' ?>>
                                <label class="form-check-label" for="active">
                                    Usuario activo
                                </label>
                            </div>
                            <?php if ($editUser['id'] == currentUser()['id']): ?>
                            <input type="hidden" name="is_active" value="1">
                            <small class="text-muted">
                                No puedes desactivar tu propio usuario
                            </small>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <div class="mt-4">
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-save me-2"></i>Guardar Cambios
                        </button>
                        <a href="/dashboard/users" class="btn btn-secondary">Cancelar</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <div class="col-lg-4">
        <!-- Información del usuario -->
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white">
                <h5 class="mb-0">Información del Usuario</h5>
            </div>
            <div class="card-body">
                <div class="text-center mb-3">
                    <img src="https://ui-avatars.com/api/?name=<?= urlencode($editUser['name']) ?>&background=6c757d&color=fff" 
                         alt="Avatar" class="rounded-circle" width="80" height="80">
                </div>
                
                <dl class="row">
                    <dt class="col-sm-5">Creado:</dt>
                    <dd class="col-sm-7"><?= formatDate($editUser['created_at'], 'd/m/Y H:i') ?></dd>
                    
                    <dt class="col-sm-5">Actualizado:</dt>
                    <dd class="col-sm-7"><?= formatDate($editUser['updated_at'], 'd/m/Y H:i') ?></dd>
                    
                    <?php if ($editUser['last_login']): ?>
                    <dt class="col-sm-5">Último acceso:</dt>
                    <dd class="col-sm-7"><?= formatDate($editUser['last_login'], 'd/m/Y H:i') ?></dd>
                    <?php endif; ?>
                </dl>
            </div>
        </div>
        
        <!-- Permisos del rol -->
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white">
                <h5 class="mb-0">Permisos del Rol</h5>
            </div>
            <div class="card-body">
                <div id="role-permissions">
                    <?php
                    $rolePermissions = [
                        'admin' => [
                            'Acceso total al sistema',
                            'Gestión de usuarios',
                            'Configuración del sistema',
                            'Ver reportes avanzados'
                        ],
                        'gerencia' => [
                            'Gestión de inventario',
                            'Aprobar créditos',
                            'Ver reportes',
                            'Gestión de contenido'
                        ],
                        'ventas' => [
                            'Ver inventario',
                            'Gestión de leads',
                            'Ver solicitudes de crédito'
                        ]
                    ];
                    
                    $currentPermissions = $rolePermissions[$editUser['role']] ?? [];
                    ?>
                    
                    <ul class="list-unstyled">
                        <?php foreach ($currentPermissions as $permission): ?>
                        <li class="mb-2">
                            <i class="bi bi-check-circle text-success me-2"></i>
                            <?= e($permission) ?>
                        </li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Validar que las contraseñas coincidan
document.getElementById('editUserForm').addEventListener('submit', function(e) {
    const password = this.querySelector('[name="password"]').value;
    const confirmation = this.querySelector('[name="password_confirmation"]').value;
    
    if (password && password !== confirmation) {
        e.preventDefault();
        showNotification('error', 'Las contraseñas no coinciden');
    }
});

// Actualizar permisos cuando cambie el rol
document.querySelector('[name="role"]').addEventListener('change', function() {
    const permissions = {
        'admin': [
            'Acceso total al sistema',
            'Gestión de usuarios',
            'Configuración del sistema',
            'Ver reportes avanzados'
        ],
        'gerencia': [
            'Gestión de inventario',
            'Aprobar créditos',
            'Ver reportes',
            'Gestión de contenido'
        ],
        'ventas': [
            'Ver inventario',
            'Gestión de leads',
            'Ver solicitudes de crédito'
        ]
    };
    
    const rolePerms = permissions[this.value] || [];
    let html = '<ul class="list-unstyled">';
    
    rolePerms.forEach(perm => {
        html += `
            <li class="mb-2">
                <i class="bi bi-check-circle text-success me-2"></i>
                ${perm}
            </li>
        `;
    });
    
    html += '</ul>';
    document.getElementById('role-permissions').innerHTML = html;
});
</script>