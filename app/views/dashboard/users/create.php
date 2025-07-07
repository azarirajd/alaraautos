<?php
// -------------------------------------------
// app/views/dashboard/users/create.php
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h2">Crear Usuario</h1>
    <a href="/dashboard/users" class="btn btn-secondary">
        <i class="bi bi-arrow-left me-2"></i>Volver
    </a>
</div>

<div class="row">
    <div class="col-lg-8">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <form id="createUserForm" class="ajax-form" action="/api/users" method="POST" data-json-submit="true">
                    <input type="hidden" name="csrf_token" value="<?= e($csrf_token) ?>">
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Nombre completo *</label>
                            <input type="text" class="form-control" name="name" required>
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Email *</label>
                            <input type="email" class="form-control" name="email" required>
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Contraseña *</label>
                            <input type="password" class="form-control" name="password" required minlength="6">
                            <small class="text-muted">Mínimo 6 caracteres</small>
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Confirmar contraseña *</label>
                            <input type="password" class="form-control" name="password_confirmation" required>
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Rol *</label>
                            <select class="form-select" name="role" required>
                                <option value="">Seleccionar rol</option>
                                <option value="admin">Administrador</option>
                                <option value="gerencia">Gerencia</option>
                                <option value="ventas">Ventas</option>
                            </select>
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Estado</label>
                            <div class="form-check form-switch mt-2">
                                <input class="form-check-input" type="checkbox" name="is_active" 
                                       value="1" id="active" checked>
                                <label class="form-check-label" for="active">
                                    Usuario activo
                                </label>
                            </div>
                        </div>
                    </div>
                    
                    <div class="mt-4">
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-save me-2"></i>Crear Usuario
                        </button>
                        <a href="/dashboard/users" class="btn btn-secondary">Cancelar</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <div class="col-lg-4">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white">
                <h5 class="mb-0">Permisos por Rol</h5>
            </div>
            <div class="card-body">
                <h6 class="text-danger">Administrador</h6>
                <ul class="small">
                    <li>Acceso total al sistema</li>
                    <li>Gestión de usuarios</li>
                    <li>Configuración del sistema</li>
                </ul>
                
                <h6 class="text-warning mt-3">Gerencia</h6>
                <ul class="small">
                    <li>Gestión de inventario</li>
                    <li>Aprobar créditos</li>
                    <li>Ver reportes</li>
                    <li>Gestión de contenido</li>
                </ul>
                
                <h6 class="text-info mt-3">Ventas</h6>
                <ul class="small">
                    <li>Ver inventario</li>
                    <li>Gestión de leads</li>
                    <li>Ver solicitudes de crédito</li>
                </ul>
            </div>
        </div>
    </div>
</div>

<script>
// Validar que las contraseñas coincidan
document.getElementById('createUserForm').addEventListener('submit', function(e) {
    const password = this.querySelector('[name="password"]').value;
    const confirmation = this.querySelector('[name="password_confirmation"]').value;
    
    if (password !== confirmation) {
        e.preventDefault();
        showNotification('error', 'Las contraseñas no coinciden');
    }
});
</script>