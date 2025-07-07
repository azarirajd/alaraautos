<?php
// -------------------------------------------
// app/views/dashboard/credit/show.php
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h2">Solicitud de Crédito #APP-<?= str_pad($application['id'], 6, '0', STR_PAD_LEFT) ?></h1>
    <a href="/dashboard/credit-applications" class="btn btn-secondary">
        <i class="bi bi-arrow-left me-2"></i>Volver
    </a>
</div>

<div class="row">
    <div class="col-lg-8">
        <!-- Información del solicitante -->
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white">
                <h5 class="mb-0">Información del Solicitante</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <p><strong>Nombre:</strong> <?= e($application['applicant_name']) ?></p>
                        <p><strong>Email:</strong> <?= e($application['applicant_email']) ?></p>
                        <p><strong>Teléfono:</strong> <?= e($application['applicant_phone']) ?></p>
                        <p><strong>RFC:</strong> <?= e($application['applicant_rfc'] ?: 'No proporcionado') ?></p>
                    </div>
                    <div class="col-md-6">
                        <p><strong>Ingreso mensual:</strong> <?= formatPrice($application['monthly_income']) ?></p>
                        <p><strong>Tipo de empleo:</strong> <?= ucfirst(str_replace('_', ' ', $application['employment_type'])) ?></p>
                        <p><strong>Años en empleo:</strong> <?= $application['employment_years'] ?></p>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Información del vehículo -->
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white">
                <h5 class="mb-0">Vehículo Solicitado</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-3">
                        <?php if ($application['main_image']): ?>
                        <img src="/uploads/cars/<?= e($application['main_image']) ?>" 
                             class="img-fluid rounded" alt="<?= e($application['brand'] . ' ' . $application['model']) ?>">
                        <?php else: ?>
                        <div class="bg-secondary rounded d-flex align-items-center justify-content-center" style="height: 100px;">
                            <i class="bi bi-car-front text-white" style="font-size: 2rem;"></i>
                        </div>
                        <?php endif; ?>
                    </div>
                    <div class="col-md-9">
                        <h6><?= e($application['brand'] . ' ' . $application['model'] . ' ' . $application['year']) ?></h6>
                        <p class="mb-2"><strong>Precio:</strong> <?= formatPrice($application['price']) ?></p>
                        <p class="mb-2"><strong>Enganche:</strong> <?= formatPrice($application['down_payment']) ?> 
                            (<?= round(($application['down_payment'] / $application['price']) * 100) ?>%)</p>
                        <p class="mb-2"><strong>Monto a financiar:</strong> 
                            <?= formatPrice($application['price'] - $application['down_payment']) ?></p>
                        <p class="mb-0"><strong>Plazo solicitado:</strong> <?= $application['requested_term'] ?> meses</p>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Notas y comentarios -->
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white">
                <h5 class="mb-0">Notas y Comentarios</h5>
            </div>
            <div class="card-body">
                <?php if ($application['notes']): ?>
                    <div class="alert alert-info">
                        <strong>Notas del revisor:</strong><br>
                        <?= nl2br(e($application['notes'])) ?>
                    </div>
                <?php endif; ?>
                
                <form id="updateNotesForm" class="ajax-form" 
                      action="/api/credit-applications/<?= $application['id'] ?>/status" 
                      method="PUT" data-json-submit="true">
                    <input type="hidden" name="csrf_token" value="<?= e($csrf_token) ?>">
                    <input type="hidden" name="status" value="<?= e($application['status']) ?>">
                    
                    <div class="mb-3">
                        <label class="form-label">Agregar nota</label>
                        <textarea class="form-control" name="notes" rows="3"></textarea>
                    </div>
                    
                    <button type="submit" class="btn btn-secondary">
                        <i class="bi bi-chat-dots me-2"></i>Guardar Nota
                    </button>
                </form>
            </div>
        </div>
    </div>
    
    <div class="col-lg-4">
        <!-- Estado y acciones -->
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white">
                <h5 class="mb-0">Estado de la Solicitud</h5>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <label class="form-label">Estado actual</label>
                    <?php
                    $statusColors = [
                        'Enviado' => 'secondary',
                        'En Revisión' => 'warning',
                        'Aprobado' => 'success',
                        'Rechazado' => 'danger',
                        'Documentos Pendientes' => 'info'
                    ];
                    ?>
                    <h4>
                        <span class="badge bg-<?= $statusColors[$application['status']] ?>">
                            <?= $application['status'] ?>
                        </span>
                    </h4>
                </div>
                
                <?php if ($application['reviewer_name']): ?>
                <p class="mb-2">
                    <strong>Revisado por:</strong> <?= e($application['reviewer_name']) ?>
                </p>
                <p class="mb-3">
                    <strong>Fecha revisión:</strong> <?= formatDate($application['reviewed_at'], 'd/m/Y H:i') ?>
                </p>
                <?php endif; ?>
                
                <div class="d-grid gap-2">
                    <?php if ($application['status'] != 'Aprobado'): ?>
                    <button class="btn btn-success" onclick="updateStatus('Aprobado')">
                        <i class="bi bi-check-circle me-2"></i>Aprobar
                    </button>
                    <?php endif; ?>
                    
                    <?php if ($application['status'] != 'Rechazado'): ?>
                    <button class="btn btn-danger" onclick="updateStatus('Rechazado')">
                        <i class="bi bi-x-circle me-2"></i>Rechazar
                    </button>
                    <?php endif; ?>
                    
                    <?php if ($application['status'] != 'Documentos Pendientes'): ?>
                    <button class="btn btn-info" onclick="updateStatus('Documentos Pendientes')">
                        <i class="bi bi-file-earmark-arrow-down me-2"></i>Solicitar Documentos
                    </button>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        
        <!-- Score crediticio -->
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white">
                <h5 class="mb-0">Score Crediticio</h5>
            </div>
            <div class="card-body text-center">
                <div class="circular-progress mb-3" style="position: relative; display: inline-block;">
                    <svg width="120" height="120">
                        <circle cx="60" cy="60" r="50" fill="none" stroke="#e0e0e0" stroke-width="10"/>
                        <circle cx="60" cy="60" r="50" fill="none" 
                                stroke="<?= $creditScore >= 70 ? '#28a745' : ($creditScore >= 50 ? '#ffc107' : '#dc3545') ?>" 
                                stroke-width="10"
                                stroke-dasharray="<?= 314 * ($creditScore / 100) ?> 314"
                                transform="rotate(-90 60 60)"/>
                    </svg>
                    <div style="position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%);">
                        <h2 class="mb-0"><?= $creditScore ?></h2>
                    </div>
                </div>
                
                <p class="mb-0">
                    <?php if ($creditScore >= 70): ?>
                        <span class="text-success">Excelente perfil crediticio</span>
                    <?php elseif ($creditScore >= 50): ?>
                        <span class="text-warning">Perfil crediticio aceptable</span>
                    <?php else: ?>
                        <span class="text-danger">Perfil crediticio bajo</span>
                    <?php endif; ?>
                </p>
            </div>
        </div>
        
        <!-- Documentos -->
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white">
                <h5 class="mb-0">Documentos</h5>
            </div>
            <div class="card-body">
                <?php if ($application['documents']): ?>
                    <?php foreach ($application['documents'] as $doc): ?>
                    <div class="d-flex align-items-center mb-2">
                        <i class="bi bi-file-earmark-pdf text-danger me-2"></i>
                        <a href="/uploads/documents/<?= e($doc) ?>" target="_blank">
                            <?= e(basename($doc)) ?>
                        </a>
                    </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p class="text-muted mb-0">No se han subido documentos</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<script>
function updateStatus(newStatus) {
    if (!confirm(`¿Cambiar el estado a "${newStatus}"?`)) return;
    
    fetch('/api/credit-applications/<?= $application['id'] ?>/status', {
        method: 'PUT',
        headers: {'Content-Type': 'application/json'},
        body: JSON.stringify({
            status: newStatus,
            csrf_token: '<?= e($csrf_token) ?>'
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            location.reload();
        } else {
            alert(data.message || 'Error al actualizar el estado');
        }
    });
}
</script>