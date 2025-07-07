<?php
// app/views/dashboard/crm/index.php
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h2">Gestión de CRM</h1>
    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addLeadModal">
        <i class="bi bi-person-plus me-2"></i>Agregar Lead
    </button>
</div>

<!-- Estadísticas -->
<div class="row g-3 mb-4">
    <div class="col-md-2">
        <div class="card border-0 shadow-sm text-center">
            <div class="card-body">
                <h3 class="mb-0"><?= $stats['new'] ?></h3>
                <small class="text-muted">Nuevos</small>
            </div>
        </div>
    </div>
    <div class="col-md-2">
        <div class="card border-0 shadow-sm text-center">
            <div class="card-body">
                <h3 class="mb-0"><?= $stats['contacted'] ?></h3>
                <small class="text-muted">Contactados</small>
            </div>
        </div>
    </div>
    <div class="col-md-2">
        <div class="card border-0 shadow-sm text-center">
            <div class="card-body">
                <h3 class="mb-0"><?= $stats['qualified'] ?></h3>
                <small class="text-muted">Calificados</small>
            </div>
        </div>
    </div>
    <div class="col-md-2">
        <div class="card border-0 shadow-sm text-center">
            <div class="card-body">
                <h3 class="mb-0"><?= $stats['converted'] ?></h3>
                <small class="text-muted">Convertidos</small>
            </div>
        </div>
    </div>
    <div class="col-md-2">
        <div class="card border-0 shadow-sm text-center">
            <div class="card-body">
                <h3 class="mb-0"><?= $stats['lost'] ?></h3>
                <small class="text-muted">Perdidos</small>
            </div>
        </div>
    </div>
    <div class="col-md-2">
        <div class="card border-0 shadow-sm text-center">
            <div class="card-body">
                <h3 class="mb-0 text-success"><?= $stats['conversion_rate'] ?>%</h3>
                <small class="text-muted">Conversión</small>
            </div>
        </div>
    </div>
</div>

<!-- Filtros -->
<div class="card border-0 shadow-sm mb-4">
    <div class="card-body">
        <form method="GET" class="row g-3">
            <div class="col-md-4">
                <label class="form-label">Estado</label>
                <select name="status" class="form-select">
                    <option value="">Todos los estados</option>
                    <option value="new" <?= $filters['status'] == 'new' ? 'selected' : '' ?>>Nuevo</option>
                    <option value="contacted" <?= $filters['status'] == 'contacted' ? 'selected' : '' ?>>Contactado</option>
                    <option value="qualified" <?= $filters['status'] == 'qualified' ? 'selected' : '' ?>>Calificado</option>
                    <option value="converted" <?= $filters['status'] == 'converted' ? 'selected' : '' ?>>Convertido</option>
                    <option value="lost" <?= $filters['status'] == 'lost' ? 'selected' : '' ?>>Perdido</option>
                </select>
            </div>
            <div class="col-md-4">
                <label class="form-label">Asignado a</label>
                <select name="assigned_to" class="form-select">
                    <option value="">Todos</option>
                    <?php foreach ($users as $user): ?>
                    <option value="<?= $user['id'] ?>" <?= $filters['assigned_to'] == $user['id'] ? 'selected' : '' ?>>
                        <?= e($user['name']) ?>
                    </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-4">
                <label class="form-label">&nbsp;</label>
                <button type="submit" class="btn btn-secondary w-100">
                    <i class="bi bi-funnel me-1"></i>Filtrar
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Tabla de leads -->
<div class="card border-0 shadow-sm">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>Contacto</th>
                        <th>Vehículo Interesado</th>
                        <th>Estado</th>
                        <th>Asignado a</th>
                        <th>Fuente</th>
                        <th>Fecha</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($leads['data'] as $lead): ?>
                    <tr>
                        <td>
                            <div>
                                <strong><?= e($lead['name']) ?></strong><br>
                                <small class="text-muted">
                                    <?= e($lead['email']) ?><br>
                                    <?= e($lead['phone']) ?>
                                </small>
                            </div>
                        </td>
                        <td>
                            <?php if ($lead['car_brand']): ?>
                                <?= e($lead['car_brand'] . ' ' . $lead['car_model'] . ' ' . $lead['car_year']) ?>
                            <?php else: ?>
                                <span class="text-muted">-</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php
                            $statusColors = [
                                'new' => 'primary',
                                'contacted' => 'info',
                                'qualified' => 'warning',
                                'converted' => 'success',
                                'lost' => 'secondary'
                            ];
                            $statusLabels = [
                                'new' => 'Nuevo',
                                'contacted' => 'Contactado',
                                'qualified' => 'Calificado',
                                'converted' => 'Convertido',
                                'lost' => 'Perdido'
                            ];
                            ?>
                            <span class="badge bg-<?= $statusColors[$lead['status']] ?>">
                                <?= $statusLabels[$lead['status']] ?>
                            </span>
                        </td>
                        <td><?= e($lead['assigned_to_name'] ?? 'Sin asignar') ?></td>
                        <td>
                            <span class="badge bg-light text-dark">
                                <?= ucfirst($lead['source']) ?>
                            </span>
                        </td>
                        <td><?= formatDate($lead['created_at'], 'd/m/Y') ?></td>
                        <td>
                            <div class="dropdown">
                                <button class="btn btn-sm btn-outline-secondary dropdown-toggle" 
                                        data-bs-toggle="dropdown">
                                    <i class="bi bi-three-dots"></i>
                                </button>
                                <ul class="dropdown-menu dropdown-menu-end">
                                    <li>
                                        <a class="dropdown-item" href="#" 
                                           onclick="editLead(<?= $lead['id'] ?>)">
                                            <i class="bi bi-pencil me-2"></i>Editar
                                        </a>
                                    </li>
                                    <li>
                                        <a class="dropdown-item" href="#" 
                                           onclick="updateLeadStatus(<?= $lead['id'] ?>, 'contacted')">
                                            <i class="bi bi-telephone me-2"></i>Marcar como contactado
                                        </a>
                                    </li>
                                    <li>
                                        <a class="dropdown-item" href="#" 
                                           onclick="updateLeadStatus(<?= $lead['id'] ?>, 'converted')">
                                            <i class="bi bi-check-circle me-2"></i>Convertir
                                        </a>
                                    </li>
                                    <li><hr class="dropdown-divider"></li>
                                    <li>
                                        <a class="dropdown-item text-danger" href="#" 
                                           onclick="updateLeadStatus(<?= $lead['id'] ?>, 'lost')">
                                            <i class="bi bi-x-circle me-2"></i>Marcar como perdido
                                        </a>
                                    </li>
                                </ul>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        
        <!-- Paginación -->
        <?php if ($leads['total_pages'] > 1): ?>
        <nav class="mt-4">
            <ul class="pagination justify-content-center">
                <?php for ($i = 1; $i <= $leads['total_pages']; $i++): ?>
                <li class="page-item <?= $i == $leads['page'] ? 'active' : '' ?>">
                    <a class="page-link" href="?page=<?= $i ?>&status=<?= urlencode($filters['status']) ?>&assigned_to=<?= urlencode($filters['assigned_to']) ?>">
                        <?= $i ?>
                    </a>
                </li>
                <?php endfor; ?>
            </ul>
        </nav>
        <?php endif; ?>
    </div>
</div>

<!-- Modal para agregar lead -->
<div class="modal fade" id="addLeadModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Agregar Nuevo Lead</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="addLeadForm" class="ajax-form" action="/api/crm/leads" method="POST" data-json-submit="true">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Nombre *</label>
                        <input type="text" class="form-control" name="name" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Email *</label>
                        <input type="email" class="form-control" name="email" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Teléfono</label>
                        <input type="tel" class="form-control" name="phone">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Asignar a</label>
                        <select class="form-select" name="assigned_to">
                            <option value="">Asignación automática</option>
                            <?php foreach ($users as $user): ?>
                            <option value="<?= $user['id'] ?>"><?= e($user['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Notas</label>
                        <textarea class="form-control" name="notes" rows="3"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Guardar Lead</button>
                </div>
            </form>
        </div>
    </div>
</div>