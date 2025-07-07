<?php
// app/views/layouts/dashboard.php
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($title ?? 'ALARA Admin') ?></title>
    
    <!-- CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css" rel="stylesheet">
    <link href="/css/admin.css" rel="stylesheet">
    
    <!-- Fuentes -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;700&display=swap" rel="stylesheet">
</head>
<body>
    <div class="d-flex">
        <!-- Sidebar -->
        <nav class="sidebar bg-dark text-white p-3" style="width: 280px; min-height: 100vh;">
            <div class="mb-4">
                <div class="d-flex align-items-center gap-3">
                    <img src="/assets/logo-white.svg" alt="ALARA" style="height: 40px;">
                    <div>
                        <h5 class="mb-0">ALARA</h5>
                        <small class="text-white-50">Panel de Administración</small>
                    </div>
                </div>
            </div>
            
            <ul class="nav nav-pills flex-column mb-auto">
                <li class="nav-item">
                    <a href="/dashboard" class="nav-link <?= $_SERVER['REQUEST_URI'] == '/dashboard' ? 'active' : 'text-white' ?>">
                        <i class="bi bi-speedometer2 me-2"></i>
                        Panel
                    </a>
                </li>
                <li class="nav-item">
                    <a href="/dashboard/crm" class="nav-link <?= strpos($_SERVER['REQUEST_URI'], '/dashboard/crm') === 0 ? 'active' : 'text-white' ?>">
                        <i class="bi bi-clipboard-data me-2"></i>
                        Gestión de CRM
                    </a>
                </li>
                <li class="nav-item">
                    <a href="/dashboard/inventory" class="nav-link <?= strpos($_SERVER['REQUEST_URI'], '/dashboard/inventory') === 0 ? 'active' : 'text-white' ?>">
                        <i class="bi bi-car-front me-2"></i>
                        Inventario
                    </a>
                </li>
                <li class="nav-item">
                    <a href="/dashboard/credit-applications" class="nav-link <?= strpos($_SERVER['REQUEST_URI'], '/dashboard/credit-applications') === 0 ? 'active' : 'text-white' ?>">
                        <i class="bi bi-file-text me-2"></i>
                        Solicitudes de Crédito
                    </a>
                </li>
                <li class="nav-item">
                    <a href="/dashboard/content" class="nav-link <?= strpos($_SERVER['REQUEST_URI'], '/dashboard/content') === 0 ? 'active' : 'text-white' ?>">
                        <i class="bi bi-magic me-2"></i>
                        Control de Contenido
                    </a>
                </li>
                <?php if (hasPermission('manage_users')): ?>
                <li class="nav-item">
                    <a href="/dashboard/users" class="nav-link <?= strpos($_SERVER['REQUEST_URI'], '/dashboard/users') === 0 ? 'active' : 'text-white' ?>">
                        <i class="bi bi-people me-2"></i>
                        Gestión de Usuarios
                    </a>
                </li>
                <?php endif; ?>
            </ul>
            
            <hr>
            
            <div class="dropdown">
                <a href="#" class="d-flex align-items-center text-white text-decoration-none dropdown-toggle" data-bs-toggle="dropdown">
                    <img src="https://ui-avatars.com/api/?name=<?= urlencode($user['name']) ?>&background=6c757d&color=fff" alt="" width="32" height="32" class="rounded-circle me-2">
                    <strong><?= e($user['name']) ?></strong>
                </a>
                <ul class="dropdown-menu dropdown-menu-dark text-small shadow">
                    <li><a class="dropdown-item" href="/dashboard/profile">Perfil</a></li>
                    <li><a class="dropdown-item" href="/dashboard/settings">Configuración</a></li>
                    <li><hr class="dropdown-divider"></li>
                    <li><a class="dropdown-item" href="/logout">Cerrar sesión</a></li>
                </ul>
            </div>
        </nav>
        
        <!-- Main content -->
        <main class="flex-grow-1">
            <!-- Top bar -->
            <nav class="navbar navbar-light bg-white border-bottom px-4">
                <div class="container-fluid">
                    <button class="navbar-toggler d-md-none" type="button" data-bs-toggle="collapse" data-bs-target="#sidebarMenu">
                        <span class="navbar-toggler-icon"></span>
                    </button>
                    
                    <div class="ms-auto">
                        <button class="btn btn-outline-secondary btn-sm me-2">
                            <i class="bi bi-bell"></i>
                        </button>
                        <a href="/" target="_blank" class="btn btn-outline-primary btn-sm">
                            <i class="bi bi-globe me-1"></i>
                            Ver sitio
                        </a>
                    </div>
                </div>
            </nav>
            
            <!-- Page content -->
            <div class="container-fluid p-4">
                <?= $content ?? '' ?>
            </div>
        </main>
    </div>
    
    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="/js/admin.js"></script>
</body>
</html>