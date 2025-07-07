<?php
// app/views/dashboard/index.php

// Layout se incluye automáticamente desde el controlador
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h2">Panel de Control</h1>
    <span class="text-muted">Bienvenido, <?= e($user['name']) ?></span>
</div>

<!-- Tarjetas de estadísticas -->
<div class="row g-4 mb-4">
    <!-- Inventario -->
    <div class="col-md-6 col-lg-3">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="flex-shrink-0">
                        <div class="bg-primary bg-opacity-10 p-3 rounded">
                            <i class="bi bi-car-front text-primary" style="font-size: 1.5rem;"></i>
                        </div>
                    </div>
                    <div class="flex-grow-1 ms-3">
                        <h6 class="text-muted mb-1">Inventario Total</h6>
                        <h3 class="mb-0"><?= number_format($stats['inventory']['total']) ?></h3>
                        <small class="text-success">
                            <?= number_format($stats['inventory']['available']) ?> disponibles
                        </small>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Valor del Inventario -->
    <div class="col-md-6 col-lg-3">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="flex-shrink-0">
                        <div class="bg-success bg-opacity-10 p-3 rounded">
                            <i class="bi bi-currency-dollar text-success" style="font-size: 1.5rem;"></i>
                        </div>
                    </div>
                    <div class="flex-grow-1 ms-3">
                        <h6 class="text-muted mb-1">Valor Inventario</h6>
                        <h3 class="mb-0"><?= formatPrice($stats['inventory']['value']) ?></h3>
                        <small class="text-muted">MXN</small>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Leads -->
    <div class="col-md-6 col-lg-3">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="flex-shrink-0">
                        <div class="bg-info bg-opacity-10 p-3 rounded">
                            <i class="bi bi-people text-info" style="font-size: 1.5rem;"></i>
                        </div>
                    </div>
                    <div class="flex-grow-1 ms-3">
                        <h6 class="text-muted mb-1">Leads Totales</h6>
                        <h3 class="mb-0"><?= number_format($stats['leads']['total']) ?></h3>
                        <small class="<?= $stats['leads']['new'] > 0 ? 'text-warning' : 'text-muted' ?>">
                            <?= number_format($stats['leads']['new']) ?> nuevos
                        </small>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Solicitudes de Crédito -->
    <div class="col-md-6 col-lg-3">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="flex-shrink-0">
                        <div class="bg-warning bg-opacity-10 p-3 rounded">
                            <i class="bi bi-file-text text-warning" style="font-size: 1.5rem;"></i>
                        </div>
                    </div>
                    <div class="flex-grow-1 ms-3">
                        <h6 class="text-muted mb-1">Solicitudes Crédito</h6>
                        <h3 class="mb-0"><?= number_format($stats['credit']['total']) ?></h3>
                        <small class="text-warning">
                            <?= number_format($stats['credit']['pending']) ?> pendientes
                        </small>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Métricas adicionales -->
<div class="row g-4 mb-4">
    <div class="col-md-4">
        <div class="card border-0 shadow-sm">
            <div class="card-body text-center">
                <h5 class="text-muted">Tasa de Conversión</h5>
                <div class="display-4 text-primary"><?= $stats['leads']['conversion_rate'] ?>%</div>
                <small class="text-muted">Leads convertidos</small>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card border-0 shadow-sm">
            <div class="card-body text-center">
                <h5 class="text-muted">Tasa de Aprobación</h5>
                <div class="display-4 text-success"><?= $stats['credit']['approval_rate'] ?>%</div>
                <small class="text-muted">Créditos aprobados</small>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card border-0 shadow-sm">
            <div class="card-body text-center">
                <h5 class="text-muted">Ventas del Mes</h5>
                <div class="display-4 text-info"><?= $stats['sales']['monthly_count'] ?></div>
                <small class="text-muted"><?= formatPrice($stats['sales']['monthly_revenue']) ?> MXN</small>
            </div>
        </div>
    </div>
</div>

<div class="row g-4">
    <!-- Gráfico de ventas -->
    <div class="col-lg-8">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white">
                <h5 class="mb-0">Ventas - Últimos 6 Meses</h5>
            </div>
            <div class="card-body">
                <canvas id="salesChart" height="100"></canvas>
            </div>
        </div>
    </div>
    
    <!-- Actividad reciente -->
    <div class="col-lg-4">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white">
                <h5 class="mb-0">Actividad Reciente</h5>
            </div>
            <div class="card-body">
                <div class="activity-feed">
                    <?php foreach ($activities as $activity): ?>
                    <div class="activity-item d-flex align-items-start mb-3">
                        <div class="activity-icon me-3">
                            <?php if ($activity['type'] == 'lead'): ?>
                                <i class="bi bi-person-plus text-primary"></i>
                            <?php else: ?>
                                <i class="bi bi-file-earmark-text text-warning"></i>
                            <?php endif; ?>
                        </div>
                        <div class="activity-content flex-grow-1">
                            <p class="mb-1">
                                <strong><?= e($activity['title']) ?></strong>
                            </p>
                            <small class="text-muted"><?= e($activity['description']) ?></small>
                            <br>
                            <small class="text-muted">
                                <?= date('d/m/Y H:i', strtotime($activity['created_at'])) ?>
                            </small>
                        </div>
                    </div>
                    <?php endforeach; ?>
                    
                    <?php if (empty($activities)): ?>
                    <p class="text-muted text-center">No hay actividad reciente</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Datos para el gráfico
const salesData = <?= json_encode($salesChart) ?>;

// Configurar gráfico de ventas
const ctx = document.getElementById('salesChart').getContext('2d');
new Chart(ctx, {
    type: 'line',
    data: {
        labels: salesData.labels,
        datasets: salesData.datasets
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: {
                display: true,
                position: 'top'
            }
        },
        scales: {
            y: {
                beginAtZero: true,
                ticks: {
                    callback: function(value) {
                        return value.toLocaleString();
                    }
                }
            },
            y1: {
                type: 'linear',
                display: true,
                position: 'right',
                beginAtZero: true,
                grid: {
                    drawOnChartArea: false
                },
                ticks: {
                    callback: function(value) {
                        return '$' + value.toLocaleString();
                    }
                }
            }
        }
    }
});

// Auto-actualizar cada 5 minutos
setInterval(() => {
    location.reload();
}, 300000);
</script>