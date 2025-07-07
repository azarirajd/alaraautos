<?php
// -------------------------------------------
// app/views/dashboard/inventory/index.php
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h2">Inventario de Vehículos</h1>
    <?php if (hasPermission('manage_inventory')): ?>
    <a href="/dashboard/inventory/add" class="btn btn-primary">
        <i class="bi bi-plus-circle me-2"></i>Agregar Vehículo
    </a>
    <?php endif; ?>
</div>

<!-- Filtros -->
<div class="card border-0 shadow-sm mb-4">
    <div class="card-body">
        <form method="GET" class="row g-3">
            <div class="col-md-4">
                <label class="form-label">Buscar</label>
                <input type="text" name="search" class="form-control" placeholder="Marca, modelo, descripción..." 
                       value="<?= e($search) ?>">
            </div>
            <div class="col-md-3">
                <label class="form-label">Marca</label>
                <select name="brand" class="form-select">
                    <option value="">Todas las marcas</option>
                    <?php foreach ($brands as $brand): ?>
                    <option value="<?= e($brand) ?>" <?= $selectedBrand == $brand ? 'selected' : '' ?>>
                        <?= e($brand) ?>
                    </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label">Estado</label>
                <select name="status" class="form-select">
                    <option value="">Todos</option>
                    <option value="available" <?= $selectedStatus == 'available' ? 'selected' : '' ?>>Disponibles</option>
                    <option value="sold" <?= $selectedStatus == 'sold' ? 'selected' : '' ?>>Vendidos</option>
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label">&nbsp;</label>
                <button type="submit" class="btn btn-secondary w-100">
                    <i class="bi bi-search me-1"></i>Filtrar
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Tabla de vehículos -->
<div class="card border-0 shadow-sm">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>Stock #</th>
                        <th>Imagen</th>
                        <th>Vehículo</th>
                        <th>Año</th>
                        <th>Precio</th>
                        <th>Kilometraje</th>
                        <th>Estado</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($cars['data'] as $car): ?>
                    <tr>
                        <td><?= e($car['stock_number']) ?></td>
                        <td>
                            <?php if ($car['main_image']): ?>
                            <img src="/uploads/cars/<?= e($car['main_image']) ?>" 
                                 alt="<?= e($car['brand'] . ' ' . $car['model']) ?>"
                                 class="rounded" style="width: 60px; height: 40px; object-fit: cover;">
                            <?php else: ?>
                            <div class="bg-secondary rounded d-flex align-items-center justify-content-center" 
                                 style="width: 60px; height: 40px;">
                                <i class="bi bi-car-front text-white"></i>
                            </div>
                            <?php endif; ?>
                        </td>
                        <td>
                            <strong><?= e($car['brand'] . ' ' . $car['model']) ?></strong>
                            <?php if ($car['is_featured']): ?>
                            <span class="badge bg-warning text-dark ms-1">Destacado</span>
                            <?php endif; ?>
                        </td>
                        <td><?= e($car['year']) ?></td>
                        <td><?= formatPrice($car['price']) ?></td>
                        <td><?= number_format($car['mileage']) ?> km</td>
                        <td>
                            <?php if ($car['is_available']): ?>
                            <span class="badge bg-success">Disponible</span>
                            <?php else: ?>
                            <span class="badge bg-secondary">Vendido</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <div class="btn-group btn-group-sm">
                                <a href="/dashboard/inventory/edit/<?= $car['id'] ?>" 
                                   class="btn btn-outline-primary" title="Editar">
                                    <i class="bi bi-pencil"></i>
                                </a>
                                <button type="button" class="btn btn-outline-danger" 
                                        onclick="deleteVehicle(<?= $car['id'] ?>)" title="Eliminar">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    
                    <?php if (empty($cars['data'])): ?>
                    <tr>
                        <td colspan="8" class="text-center py-4 text-muted">
                            No se encontraron vehículos
                        </td>
                    </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        
        <!-- Paginación -->
        <?php if ($cars['total_pages'] > 1): ?>
        <nav class="mt-4">
            <ul class="pagination justify-content-center">
                <?php for ($i = 1; $i <= $cars['total_pages']; $i++): ?>
                <li class="page-item <?= $i == $cars['page'] ? 'active' : '' ?>">
                    <a class="page-link" href="?page=<?= $i ?>&search=<?= urlencode($search) ?>&brand=<?= urlencode($selectedBrand) ?>&status=<?= urlencode($selectedStatus) ?>">
                        <?= $i ?>
                    </a>
                </li>
                <?php endfor; ?>
            </ul>
        </nav>
        <?php endif; ?>
    </div>
</div>

<script>
function deleteVehicle(id) {
    if (!confirm('¿Estás seguro de eliminar este vehículo?')) {
        return;
    }
    
    fetch(`/api/inventory/${id}`, {
        method: 'DELETE',
        headers: {
            'Content-Type': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            location.reload();
        } else {
            alert(data.message || 'Error al eliminar el vehículo');
        }
    })
    .catch(error => {
        alert('Error de conexión');
    });
}
</script>