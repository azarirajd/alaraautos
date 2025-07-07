<?php
// -------------------------------------------
// app/views/dashboard/inventory/edit.php
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h2">Editar Vehículo</h1>
    <a href="/dashboard/inventory" class="btn btn-secondary">
        <i class="bi bi-arrow-left me-2"></i>Volver al inventario
    </a>
</div>

<form id="vehicleForm" action="/api/inventory/<?= $car['id'] ?>" method="PUT" 
      class="ajax-form" data-json-submit="true">
    <input type="hidden" name="csrf_token" value="<?= e($csrf_token) ?>">
    
    <div class="row">
        <div class="col-lg-8">
            <!-- Información básica -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white">
                    <h5 class="mb-0">Información Básica</h5>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label">Stock #</label>
                            <input type="text" class="form-control" value="<?= e($car['stock_number']) ?>" readonly>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Marca *</label>
                            <input type="text" class="form-control" name="brand" 
                                   value="<?= e($car['brand']) ?>" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Modelo *</label>
                            <input type="text" class="form-control" name="model" 
                                   value="<?= e($car['model']) ?>" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Año *</label>
                            <input type="number" class="form-control" name="year" 
                                   value="<?= e($car['year']) ?>" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Precio (MXN) *</label>
                            <input type="number" class="form-control" name="price" 
                                   value="<?= e($car['price']) ?>" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Kilometraje *</label>
                            <input type="number" class="form-control" name="mileage" 
                                   value="<?= e($car['mileage']) ?>" required>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Resto del formulario similar a create.php pero con valores precargados -->
            
            <!-- Imágenes existentes -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white">
                    <h5 class="mb-0">Imágenes Actuales</h5>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <?php foreach ($images as $image): ?>
                        <div class="col-md-3">
                            <div class="position-relative">
                                <img src="/uploads/cars/<?= e($image['filename']) ?>" 
                                     class="img-fluid rounded" alt="Imagen">
                                <div class="position-absolute top-0 end-0 m-1">
                                    <?php if ($image['is_primary']): ?>
                                    <span class="badge bg-primary">Principal</span>
                                    <?php else: ?>
                                    <button type="button" class="btn btn-sm btn-success" 
                                            onclick="setPrimaryImage(<?= $image['id'] ?>)">
                                        <i class="bi bi-star"></i>
                                    </button>
                                    <?php endif; ?>
                                    <button type="button" class="btn btn-sm btn-danger" 
                                            onclick="deleteImage(<?= $image['id'] ?>)">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-lg-4">
            <!-- Información adicional -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white">
                    <h5 class="mb-0">Información</h5>
                </div>
                <div class="card-body">
                    <p class="mb-2">
                        <strong>Creado:</strong> <?= formatDate($car['created_at'], 'd/m/Y H:i') ?>
                    </p>
                    <p class="mb-2">
                        <strong>Actualizado:</strong> <?= formatDate($car['updated_at'], 'd/m/Y H:i') ?>
                    </p>
                    <p class="mb-0">
                        <strong>Vistas:</strong> <?= number_format($car['views']) ?>
                    </p>
                </div>
            </div>
            
            <!-- Opciones -->
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white">
                    <h5 class="mb-0">Opciones</h5>
                </div>
                <div class="card-body">
                    <div class="form-check form-switch mb-3">
                        <input class="form-check-input" type="checkbox" name="is_featured" 
                               value="1" id="featured" <?= $car['is_featured'] ? 'checked' : '' ?>>
                        <label class="form-check-label" for="featured">
                            Destacar vehículo
                        </label>
                    </div>
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" name="is_available" 
                               value="1" id="available" <?= $car['is_available'] ? 'checked' : '' ?>>
                        <label class="form-check-label" for="available">
                            Disponible para venta
                        </label>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="mt-4">
        <button type="submit" class="btn btn-primary">
            <i class="bi bi-save me-2"></i>Guardar Cambios
        </button>
        <a href="/dashboard/inventory" class="btn btn-secondary">Cancelar</a>
    </div>
</form>

<script>
function setPrimaryImage(imageId) {
    // Implementar llamada AJAX para establecer imagen principal
}

function deleteImage(imageId) {
    if (confirm('¿Eliminar esta imagen?')) {
        // Implementar llamada AJAX para eliminar imagen
    }
}
</script>