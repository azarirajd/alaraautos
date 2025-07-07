<?php
// -------------------------------------------
// app/views/auth/login.php
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($title) ?></title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;700&display=swap" rel="stylesheet">
    
    <style>
        body {
            font-family: 'DM Sans', sans-serif;
            background-color: #f8f9fa;
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
        }
        .login-card {
            max-width: 400px;
            width: 100%;
            box-shadow: 0 0 20px rgba(0,0,0,0.1);
        }
        .logo {
            width: 60px;
            height: 60px;
            background-color: #dc3545;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 20px;
            color: white;
            font-weight: bold;
            font-size: 24px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col">
                <div class="card login-card">
                    <div class="card-body p-5">
                        <div class="logo">A</div>
                        <h2 class="text-center mb-2">Acceso de Administrador ALARA</h2>
                        <p class="text-center text-muted mb-4">Introduce tus credenciales para acceder al panel de administración</p>
                        
                        <form id="loginForm">
                            <input type="hidden" name="csrf_token" value="<?= e($csrf_token) ?>">
                            
                            <div class="mb-3">
                                <label for="email" class="form-label">Correo electrónico</label>
                                <input type="email" class="form-control" id="email" name="email" required autofocus>
                            </div>
                            
                            <div class="mb-3">
                                <label for="password" class="form-label">Contraseña</label>
                                <input type="password" class="form-control" id="password" name="password" required>
                            </div>
                            
                            <div class="mb-3 form-check">
                                <input type="checkbox" class="form-check-input" id="remember" name="remember">
                                <label class="form-check-label" for="remember">
                                    Recordarme
                                </label>
                            </div>
                            
                            <div class="d-grid">
                                <button type="submit" class="btn btn-danger" id="submitBtn">
                                    Iniciar Sesión
                                </button>
                            </div>
                        </form>
                        
                        <div id="errorAlert" class="alert alert-danger mt-3 d-none" role="alert"></div>
                        
                        <hr class="my-4">
                        
                        <p class="text-center text-muted mb-0">
                            <small>¿Olvidaste tu contraseña? Contacta al administrador del sistema.</small>
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <script>
    document.getElementById('loginForm').addEventListener('submit', async (e) => {
        e.preventDefault();
        
        const submitBtn = document.getElementById('submitBtn');
        const errorAlert = document.getElementById('errorAlert');
        
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Iniciando sesión...';
        errorAlert.classList.add('d-none');
        
        try {
            const formData = new FormData(e.target);
            const response = await fetch('/api/auth/login', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify(Object.fromEntries(formData))
            });
            
            const data = await response.json();
            
            if (data.success) {
                window.location.href = data.redirect || '/dashboard';
            } else {
                errorAlert.textContent = data.message || 'Error al iniciar sesión';
                errorAlert.classList.remove('d-none');
            }
        } catch (error) {
            errorAlert.textContent = 'Error de conexión. Por favor intenta nuevamente.';
            errorAlert.classList.remove('d-none');
        } finally {
            submitBtn.disabled = false;
            submitBtn.innerHTML = 'Iniciar Sesión';
        }
    });
    </script>
</body>
</html>