<?php
/**
 * ALARA - Script de Instalación
 * Este archivo debe ser eliminado después de la instalación
 */

// Verificar que no esté ya instalado
if (file_exists('.env')) {
    die('⚠️ El sistema ya está instalado. Por seguridad, elimina este archivo.');
}

$errors = [];
$warnings = [];
$success = [];

// Verificar versión de PHP
if (version_compare(PHP_VERSION, '7.4.0', '<')) {
    $errors[] = 'PHP 7.4 o superior es requerido. Versión actual: ' . PHP_VERSION;
} else {
    $success[] = 'PHP ' . PHP_VERSION . ' ✓';
}

// Verificar extensiones requeridas
$required_extensions = ['pdo', 'pdo_mysql', 'json', 'fileinfo', 'mbstring', 'openssl'];
foreach ($required_extensions as $ext) {
    if (!extension_loaded($ext)) {
        $errors[] = "La extensión PHP '$ext' es requerida";
    } else {
        $success[] = "Extensión '$ext' ✓";
    }
}

// Verificar y crear directorios
$writable_dirs = [
    'public/uploads',
    'public/uploads/cars',
    'public/uploads/documents',
    'public/uploads/temp',
    'logs'
];

foreach ($writable_dirs as $dir) {
    $fullPath = __DIR__ . '/' . $dir;
    
    if (!file_exists($fullPath)) {
        // Intentar crear el directorio
        $oldmask = @umask(0);
        $created = @mkdir($fullPath, 0777, true);
        @umask($oldmask);
        
        if (!$created) {
            $warnings[] = "No se pudo crear '$dir' - créalo manualmente vía FTP con permisos 777";
        } else {
            $success[] = "Directorio '$dir' creado ✓";
            // En Windows/XAMPP, intentar dar permisos
            @chmod($fullPath, 0777);
        }
    }
    
    // Verificar permisos
    if (file_exists($fullPath)) {
        if (!is_writable($fullPath)) {
            // En XAMPP local normalmente todo es escribible
            if (stripos(PHP_OS, 'WIN') === 0) {
                $success[] = "Directorio '$dir' OK (Windows) ✓";
            } else {
                $warnings[] = "El directorio '$dir' necesita permisos 777";
            }
        } else {
            $success[] = "Directorio '$dir' con permisos correctos ✓";
        }
    }
}

// Crear archivo .htaccess en las carpetas de uploads para seguridad
$htaccessContent = "Options -Indexes\nOptions -ExecCGI\nAddHandler cgi-script .php .pl .py .jsp .asp .htm .shtml .sh .cgi";
@file_put_contents(__DIR__ . '/public/uploads/.htaccess', $htaccessContent);
@file_put_contents(__DIR__ . '/logs/.htaccess', "Deny from all");

// Detectar entorno
$serverInfo = [];
$serverInfo['PHP Version'] = PHP_VERSION;
$serverInfo['Sistema'] = PHP_OS;
$serverInfo['Servidor'] = $_SERVER['SERVER_SOFTWARE'] ?? 'Desconocido';
$serverInfo['XAMPP'] = (stripos($serverInfo['Servidor'], 'Apache') !== false && stripos(PHP_OS, 'WIN') === 0) ? 'Sí' : 'No';

// Verificar mod_rewrite
if (function_exists('apache_get_modules')) {
    if (!in_array('mod_rewrite', apache_get_modules())) {
        $errors[] = "mod_rewrite no está habilitado en Apache. Es necesario para las URLs amigables.";
    } else {
        $success[] = "mod_rewrite habilitado ✓";
    }
} else {
    $warnings[] = "No se pudo verificar mod_rewrite. Asegúrate de que esté habilitado.";
}

// Verificar versión de MySQL/MariaDB
try {
    $testPdo = new PDO("mysql:host=localhost", 'root', '');
    $version = $testPdo->query('SELECT VERSION()')->fetchColumn();
    $serverInfo['MySQL/MariaDB'] = $version;
    $success[] = "MySQL/MariaDB $version detectado ✓";
} catch (Exception $e) {
    $warnings[] = "No se pudo conectar a MySQL. Asegúrate de que XAMPP MySQL esté ejecutándose.";
}

// Procesar instalación si se envió el formulario
if ($_SERVER['REQUEST_METHOD'] === 'POST' && empty($errors)) {
    $db_host = $_POST['db_host'] ?? 'localhost';
    $db_name = $_POST['db_name'] ?? '';
    $db_user = $_POST['db_user'] ?? '';
    $db_pass = $_POST['db_pass'] ?? '';
    $admin_email = $_POST['admin_email'] ?? '';
    $admin_pass = $_POST['admin_pass'] ?? '';
    $site_url = $_POST['site_url'] ?? '';
    
    // Validar datos
    if (empty($db_name) || empty($db_user) || empty($admin_email) || empty($admin_pass) || empty($site_url)) {
        $errors[] = 'Todos los campos son requeridos';
    }
    
    if (!filter_var($admin_email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Email de administrador inválido';
    }
    
    if (strlen($admin_pass) < 6) {
        $errors[] = 'La contraseña debe tener al menos 6 caracteres';
    }
    
    if (empty($errors)) {
        try {
            // Probar conexión a la base de datos
            $pdo = new PDO("mysql:host=$db_host", $db_user, $db_pass);
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            
            // Crear base de datos si no existe
            $pdo->exec("CREATE DATABASE IF NOT EXISTS `$db_name` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
            $pdo->exec("USE `$db_name`");
            
            // Ejecutar schema.sql
            $schema = file_get_contents('database/schema.sql');
            
            // Reemplazar palabras reservadas problemáticas
            $schema = str_replace('condition ENUM', '`condition` ENUM', $schema);
            
            // Dividir por declaraciones (mejorado para manejar delimitadores dentro de strings)
            $statements = preg_split('/;\s*$/m', $schema, -1, PREG_SPLIT_NO_EMPTY);
            
            foreach ($statements as $statement) {
                $statement = trim($statement);
                if (!empty($statement)) {
                    try {
                        $pdo->exec($statement);
                    } catch (PDOException $e) {
                        // Si falla con 'condition', intentar sin ese campo
                        if (strpos($e->getMessage(), 'condition') !== false) {
                            // Usar schema alternativo sin el campo problemático
                            if (file_exists('database/schema-fixed.sql')) {
                                $schema = file_get_contents('database/schema-fixed.sql');
                                $statements = preg_split('/;\s*$/m', $schema, -1, PREG_SPLIT_NO_EMPTY);
                                foreach ($statements as $stmt) {
                                    if (!empty(trim($stmt))) {
                                        $pdo->exec($stmt);
                                    }
                                }
                                break;
                            }
                        } else {
                            throw $e;
                        }
                    }
                }
            }
            
            // Actualizar usuario admin
            $hash = password_hash($admin_pass, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("UPDATE users SET email = ?, password = ? WHERE id = 1");
            $stmt->execute([$admin_email, $hash]);
            
            // Detectar URL base automáticamente
            $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
            $host = $_SERVER['HTTP_HOST'];
            $scriptPath = dirname($_SERVER['SCRIPT_NAME']);
            $baseUrl = $protocol . '://' . $host . $scriptPath;
            $baseUrl = rtrim($baseUrl, '/');
            
            // Si el usuario proporcionó una URL, usarla
            if (!empty($site_url)) {
                $baseUrl = rtrim($site_url, '/');
            }
            
            // Crear archivo .env
            $env_content = "# Configuración de Base de Datos
DB_HOST=$db_host
DB_NAME=$db_name
DB_USER=$db_user
DB_PASS=$db_pass
DB_CHARSET=utf8mb4

# Configuración de la Aplicación
APP_NAME=ALARA
APP_URL=$baseUrl
APP_ENV=production
DEBUG_MODE=false
SECRET_KEY=" . bin2hex(random_bytes(32)) . "

# Configuración de Email
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USER=
MAIL_PASS=
MAIL_FROM=noreply@" . parse_url($baseUrl, PHP_URL_HOST) . "
MAIL_FROM_NAME=ALARA

# WhatsApp
WHATSAPP_NUMBER=

# APIs (opcional)
OPENAI_API_KEY=
CLAUDE_API_KEY=
";
            
            file_put_contents('.env', $env_content);
            
            // Crear archivo de log
            @file_put_contents('logs/app.log', date('Y-m-d H:i:s') . " - Sistema instalado\n");
            @file_put_contents('logs/php_errors.log', '');
            
            // Crear archivo index.html en uploads para prevenir listado
            $indexContent = '<!DOCTYPE html><html><head><title>403 Forbidden</title></head><body><h1>Forbidden</h1></body></html>';
            @file_put_contents('public/uploads/index.html', $indexContent);
            @file_put_contents('public/uploads/cars/index.html', $indexContent);
            @file_put_contents('public/uploads/documents/index.html', $indexContent);
            
            $install_success = true;
            
        } catch (Exception $e) {
            $errors[] = 'Error de base de datos: ' . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Instalación - ALARA</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: #f5f7fa;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        
        .container {
            background: white;
            border-radius: 12px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
            max-width: 600px;
            width: 100%;
            overflow: hidden;
        }
        
        .header {
            background: linear-gradient(135deg, #dc3545, #c92333);
            color: white;
            padding: 40px;
            text-align: center;
        }
        
        .header h1 {
            font-size: 32px;
            margin-bottom: 10px;
        }
        
        .content {
            padding: 40px;
        }
        
        .alert {
            padding: 15px 20px;
            border-radius: 8px;
            margin-bottom: 20px;
        }
        
        .alert-error {
            background: #fee;
            color: #c33;
            border: 1px solid #fcc;
        }
        
        .alert-warning {
            background: #ffc;
            color: #660;
            border: 1px solid #fc9;
        }
        
        .alert-success {
            background: #efe;
            color: #060;
            border: 1px solid #cfc;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        .form-group label {
            display: block;
            font-weight: 600;
            margin-bottom: 8px;
            color: #333;
        }
        
        .form-control {
            width: 100%;
            padding: 12px 16px;
            border: 2px solid #e1e4e8;
            border-radius: 8px;
            font-size: 16px;
            transition: border-color 0.3s;
        }
        
        .form-control:focus {
            outline: none;
            border-color: #dc3545;
        }
        
        .form-text {
            font-size: 14px;
            color: #666;
            margin-top: 5px;
        }
        
        .btn {
            background: #dc3545;
            color: white;
            padding: 14px 30px;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            width: 100%;
            transition: background 0.3s;
        }
        
        .btn:hover {
            background: #c92333;
        }
        
        .btn:disabled {
            background: #ccc;
            cursor: not-allowed;
        }
        
        .requirements {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 30px;
        }
        
        .requirements h3 {
            margin-bottom: 15px;
            color: #333;
        }
        
        .requirements ul {
            list-style: none;
            padding: 0;
        }
        
        .requirements li {
            padding: 5px 0;
            color: #666;
        }
        
        .requirements li::before {
            margin-right: 8px;
        }
        
        .requirements .success::before {
            content: "✓";
            color: #28a745;
        }
        
        .requirements .error::before {
            content: "✗";
            color: #dc3545;
        }
        
        .success-page {
            text-align: center;
            padding: 60px 40px;
        }
        
        .success-icon {
            width: 80px;
            height: 80px;
            background: #28a745;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 30px;
            font-size: 40px;
            color: white;
        }
        
        .next-steps {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 8px;
            text-align: left;
            margin-top: 30px;
        }
        
        .next-steps h4 {
            margin-bottom: 15px;
        }
        
        .next-steps ol {
            margin-left: 20px;
            color: #666;
        }
        
        .next-steps li {
            margin-bottom: 10px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🚗 ALARA</h1>
            <p>Instalación del Sistema</p>
        </div>
        
        <div class="content">
            <?php if (isset($install_success) && $install_success): ?>
                <div class="success-page">
                    <div class="success-icon">✓</div>
                    <h2>¡Instalación Exitosa!</h2>
                    <p>El sistema ALARA ha sido instalado correctamente.</p>
                    
                    <div class="next-steps">
                        <h4>Próximos pasos:</h4>
                        <ol>
                            <li><strong>IMPORTANTE:</strong> Elimina este archivo (install.php) por seguridad</li>
                            <li>Accede al panel de administración: <a href="/login">/login</a></li>
                            <li>Usa las credenciales:
                                <ul>
                                    <li>Email: <?= htmlspecialchars($admin_email) ?></li>
                                    <li>Contraseña: La que configuraste</li>
                                </ul>
                            </li>
                            <li>Configura el email SMTP en el archivo .env</li>
                            <li>Personaliza el contenido del sitio desde el panel</li>
                        </ol>
                    </div>
                </div>
            <?php else: ?>
                <?php if (!empty($errors)): ?>
                    <?php foreach ($errors as $error): ?>
                        <div class="alert alert-error">⚠️ <?= htmlspecialchars($error) ?></div>
                    <?php endforeach; ?>
                <?php endif; ?>
                
                <?php if (!empty($warnings)): ?>
                    <?php foreach ($warnings as $warning): ?>
                        <div class="alert alert-warning">⚡ <?= htmlspecialchars($warning) ?></div>
                    <?php endforeach; ?>
                <?php endif; ?>
                
                <div class="requirements">
                    <h3>Verificación de Requisitos</h3>
                    <ul>
                        <?php foreach ($success as $item): ?>
                            <li class="success"><?= htmlspecialchars($item) ?></li>
                        <?php endforeach; ?>
                        <?php foreach ($errors as $error): ?>
                            <li class="error"><?= htmlspecialchars($error) ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
                
                <?php if (empty($errors)): ?>
                    <form method="POST">
                        <h3>Configuración de la Base de Datos</h3>
                        
                        <div class="form-group">
                            <label>Host de Base de Datos</label>
                            <input type="text" name="db_host" class="form-control" value="localhost" required>
                            <div class="form-text">Generalmente es 'localhost'</div>
                        </div>
                        
                        <div class="form-group">
                            <label>Nombre de Base de Datos</label>
                            <input type="text" name="db_name" class="form-control" value="alara_db" required>
                            <div class="form-text">Se creará si no existe</div>
                        </div>
                        
                        <div class="form-group">
                            <label>Usuario de Base de Datos</label>
                            <input type="text" name="db_user" class="form-control" required>
                        </div>
                        
                        <div class="form-group">
                            <label>Contraseña de Base de Datos</label>
                            <input type="password" name="db_pass" class="form-control">
                            <div class="form-text">Deja vacío si no tiene contraseña</div>
                        </div>
                        
                        <hr style="margin: 30px 0;">
                        
                        <h3>Configuración del Administrador</h3>
                        
                        <div class="form-group">
                            <label>Email del Administrador</label>
                            <input type="email" name="admin_email" class="form-control" required>
                            <div class="form-text">Usarás este email para iniciar sesión</div>
                        </div>
                        
                        <div class="form-group">
                            <label>Contraseña del Administrador</label>
                            <input type="password" name="admin_pass" class="form-control" required minlength="6">
                            <div class="form-text">Mínimo 6 caracteres</div>
                        </div>
                        
                        <div class="form-group">
                            <label>URL del Sitio</label>
                            <input type="url" name="site_url" class="form-control" 
                                   value="<?= 'http://' . $_SERVER['HTTP_HOST'] . dirname($_SERVER['SCRIPT_NAME']) ?>" required>
                            <div class="form-text">Se detectó automáticamente. Ajusta si es necesario (sin barra al final)</div>
                        </div>
                        
                        <div class="alert alert-warning" style="margin-top: 20px;">
                            <strong>📌 Nota para XAMPP:</strong>
                            <ul style="margin: 10px 0 0 20px; padding: 0;">
                                <li>Asegúrate de que Apache y MySQL estén ejecutándose</li>
                                <li>La base de datos se creará automáticamente</li>
                                <li>Usuario por defecto de MySQL en XAMPP: <code>root</code> sin contraseña</li>
                                <li>Después de instalar, los archivos subidos irán a <code>public/uploads/</code></li>
                            </ul>
                        </div>
                        
                        <button type="submit" class="btn">
                            🚀 Instalar ALARA
                        </button>
                    </form>
                <?php else: ?>
                    <div class="alert alert-error">
                        Por favor, corrige los errores antes de continuar con la instalación.
                    </div>
                <?php endif; ?>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>