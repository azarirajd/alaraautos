<?php
// app/views/errors/403.php
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>403 - Acceso Denegado</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f8f9fa;
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .error-container {
            text-align: center;
            max-width: 500px;
        }
        .error-code {
            font-size: 120px;
            font-weight: 700;
            color: #dc3545;
            line-height: 1;
            margin-bottom: 20px;
        }
        .error-icon {
            font-size: 80px;
            color: #ffc107;
            margin-bottom: 20px;
        }
        .error-message {
            font-size: 24px;
            color: #333;
            margin-bottom: 20px;
        }
        .error-description {
            color: #6c757d;
            margin-bottom: 30px;
        }
        .btn-group-vertical .btn {
            margin-bottom: 10px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="error-container">
            <div class="error-icon">🚫</div>
            <div class="error-code">403</div>
            <h1 class="error-message">Acceso Denegado</h1>
            <p class="error-description">
                No tienes permisos para acceder a esta página. 
                Si crees que esto es un error, contacta al administrador del sistema.
            </p>
            <div class="btn-group-vertical d-inline-flex">
                <?php if (isAuthenticated()): ?>
                    <a href="/dashboard" class="btn btn-primary">
                        <i class="bi bi-house me-2"></i>Ir al Panel Principal
                    </a>
                    <button onclick="history.back()" class="btn btn-secondary">
                        <i class="bi bi-arrow-left me-2"></i>Volver Atrás
                    </button>
                <?php else: ?>
                    <a href="/login" class="btn btn-primary">
                        <i class="bi bi-box-arrow-in-right me-2"></i>Iniciar Sesión
                    </a>
                    <a href="/" class="btn btn-secondary">
                        <i class="bi bi-house me-2"></i>Ir al Sitio Web
                    </a>
                <?php endif; ?>
            </div>
            
            <div class="mt-5">
                <small class="text-muted">
                    Código de error: 403 | <?= date('Y-m-d H:i:s') ?>
                </small>
            </div>
        </div>
    </div>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css" rel="stylesheet">
</body>
</html>