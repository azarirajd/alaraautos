<?php
// -------------------------------------------
// app/views/dashboard/credit/index.php
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h2">Solicitudes de Crédito</h1>
</div>

<!-- Estadísticas -->
<div class="row g-3 mb-4">
    <div class="col-md-3">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="flex-shrink-0">
                        <i class="bi bi-file-earmark-text text-primary" style="font-size: 2rem;"></i>
                    </div>
                    <div class="flex-grow-1 ms-3">
                        <h6 class="text-muted mb-1">Total Solicitudes</h6>
                        <h3 class="mb-0"><?= number_format($stats['enviado'] + $stats['en_revisión'] + $stats['aprobado'] + $stats['rechazado'] + $stats['documentos_pendientes']) ?></h3>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="flex-shrink-0">
                        <i class="bi bi-hourglass-split text-warning" style="font-size: 2rem;"></i>
                    </div>
                    <div class="flex-grow-1 ms-3">
                        <h6 class="text-muted mb-1">En Revisión</h6>
                        <h3 class="mb-0"><?= number_format($stats['en_revisión']) ?></h3>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="flex-shrink-0">
                        <i class="bi bi-check-circle text-success" style="font-size: 2rem;"></i>
                    </div>
                    <div class="flex-grow-1 ms-3">
                        <h6 class="text-muted mb-1">Aprobadas</h6>
                        <h3 class="mb-0"><?= number_format($stats['aprobado']) ?></h3>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="flex-shrink-0">
                        <i class="bi bi-percent text-info" style="font-size: 2rem;"></i>
                    </div>
                    <div class="flex-grow-1 ms-3">
                        <h6 class="text-muted mb-1">Tasa Aprobación</h6>
                        <h3 class="mb-0"><?= $stats['approval_rate'] ?>%</h3>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Filtros -->
<div class="card border-0 shadow-sm mb-4">
    <div class="card-body">
        <form method="GET" class="row g-3">
            <div class="col-md-4">
                <select name="status" class="form-select">
                    <option value="">Todos los estados</option>
                    <option value="Enviado" <?= $selectedStatus == 'Enviado' ? 'selected' : '' ?>>Enviado</option>
                    <option value="En Revisión" <?= $selectedStatus == 'En Revisión' ? 'selected' : '' ?>>En Revisión</option>
                    <option value="Aprobado" <?= $selectedStatus == 'Aprobado' ? 'selected' : '' ?>>Aprobado</option>
                    <option value="Rechazado" <?= $selectedStatus == 'Rechazado' ? 'selected' : '' ?>>Rechazado</option>
                    <option value="Documentos Pendientes" <?= $selectedStatus == 'Documentos Pendientes' ? 'selected' : '' ?>>Documentos Pendientes</option>
                </select>
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-secondary w-100">
                    <i class="bi bi-funnel me-1"></i>Filtrar
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Tabla de solicitudes -->
<div class="card border-0 shadow-sm">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Solicitante</th>
                        <th>Vehículo</th>
                        <th>Score</th>
                        <th>Estado</th>
                        <th>Fecha</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($applications['data'] as $app): ?>
                    <tr>
                        <td>
                            <strong>APP-<?= str_pad($app['id'], 6, '0', STR_PAD_LEFT) ?></strong>
                        </td>
                        <td>
                            <div>
                                <strong><?= e($app['applicant_name']) ?></strong><br>
                                <small class="text-muted">
                                    <?= e($app['applicant_email']) ?><br>
                                    <?= e($app['applicant_phone']) ?>
                                </small>
                            </div>
                        </td>
                        <td>
                            <?= e($app['brand'] . ' ' . $app['model'] . ' ' . $app['year']) ?><br>
                            <small class="text-muted"><?= formatPrice($app['price']) ?></small>
                        </td>
                        <td>
                            <div class="progress" style="width: 60px; height: 20px;">
                                <?php
                                $scoreColor = $app['credit_score'] >= 70 ? 'success' : 
                                            ($app['credit_score'] >= 50 ? 'warning' : 'danger');
                                ?>
                                <div class="progress-bar bg-<?= $scoreColor ?>" 
                                     style="width: <?= $app['credit_score'] ?>%">
                                    <?= $app['credit_score'] ?>
                                </div>
                            </div>
                        </td>
                        <td>
                            <?php
                            $statusColors = [
                                'Enviado' => 'secondary',
                                'En Revisión' => 'warning',
                                'Aprobado' => 'success',
                                'Rechazado' => 'danger',
                                'Documentos Pendientes' => 'info'
                            ];
                            ?>
                            <span class="badge bg-<?= $statusColors[$app['status']] ?>">
                                <?= $app['status'] ?>
                            </span>
                        </td>
                        <td><?= formatDate($app['created_at'], 'd/m/Y') ?></td>
                        <td>
                            <a href="/dashboard/credit-applications/<?= $app['id'] ?>" 
                               class="btn btn-sm btn-outline-primary">
                                <i class="bi bi-eye"></i> Ver
                            </a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>