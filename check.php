<?php
/**
 * ALARA - Script de Verificación del Sistema
 * Ejecuta este archivo para verificar que todo esté configurado correctamente
 */

// Mostrar todos los errores
error_reporting(E_ALL);
ini_set('display_errors', 1);

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verificación del Sistema - ALARA</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            max-width: 800px;
            margin: 50px auto;
            padding: 20px;
            background: #f5f5f5;
        }
        .container {
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        h1 {
            color: #333;
            border-bottom: 3px solid #dc3545;
            padding-bottom: 10px;
        }
        .check-item {
            padding: 10px;
            margin: 10px 0;
            border-radius: 5px;
        }
        .success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        .warning {
            background: #fff3cd;
            color: #856404;
            border: 1px solid #ffeeba;
        }
        .error {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        .info {
            background: #d1ecf1;
            color: #0c5460;
            border: 1px solid #bee5eb;
        }
        code {
            background: #f8f9fa;
            padding: 2px 5px;
            border-radius: 3px;
            font-family: monospace;
        }
        .section {
            margin: 30px 0;
        }
        .btn {
            display: inline-block;
            padding: 10px 20px;
            background: #dc3545;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            margin-top: 20px;
        }
        .btn:hover {
            background: #c92333;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>🔍 Verificación del Sistema ALARA</h1>
        
        <div class="section">
            <h2>Información del Servidor</h2>
            <?php
            $serverInfo = [
                'Sistema Operativo' => PHP_OS,
                'Versión PHP' => PHP_VERSION,
                'Servidor Web' => $_SERVER['SERVER_SOFTWARE'] ?? 'Desconocido',
                'Ruta del Documento' => $_SERVER['DOCUMENT_ROOT'] ?? 'Desconocido',
                'Ruta del Script' => __DIR__,
                'URL Base Detectada' => 'http://' . $_SERVER['HTTP_HOST'] . dirname($_SERVER['SCRIPT_NAME'])
            ];
            
            foreach ($serverInfo as $key => $value) {
                echo "<div class='check-item info'><strong>$key:</strong> $value</div>";
            }
            ?>
        </div>
        
        <div class="section">
            <h2>Verificación de PHP</h2>
            <?php
            // Versión de PHP
            if (version_compare(PHP_VERSION, '7.4.0', '>=')) {
                echo "<div class='check-item success'>✅ PHP " . PHP_VERSION . " (7.4+ requerido)</div>";
            } else {
                echo "<div class='check-item error'>❌ PHP " . PHP_VERSION . " (Se requiere 7.4 o superior)</div>";
            }
            
            // Extensiones requeridas
            $extensions = [
                'pdo' => 'PDO',
                'pdo_mysql' => 'PDO MySQL',
                'json' => 'JSON',
                'fileinfo' => 'FileInfo',
                'mbstring' => 'Mbstring',
                'openssl' => 'OpenSSL'
            ];
            
            foreach ($extensions as $ext => $name) {
                if (extension_loaded($ext)) {
                    echo "<div class='check-item success'>✅ Extensión $name instalada</div>";
                } else {
                    echo "<div class='check-item error'>❌ Extensión $name NO instalada</div>";
                }
            }
            ?>
        </div>
        
        <div class="section">
            <h2>Verificación de Apache</h2>
            <?php
            if (function_exists('apache_get_modules')) {
                $modules = apache_get_modules();
                
                // mod_rewrite
                if (in_array('mod_rewrite', $modules)) {
                    echo "<div class='check-item success'>✅ mod_rewrite habilitado</div>";
                } else {
                    echo "<div class='check-item error'>❌ mod_rewrite NO habilitado - Las URLs amigables no funcionarán</div>";
                }
                
                // Otros módulos útiles
                $optional_modules = ['mod_headers', 'mod_deflate', 'mod_expires'];
                foreach ($optional_modules as $mod) {
                    if (in_array($mod, $modules)) {
                        echo "<div class='check-item success'>✅ $mod habilitado (opcional)</div>";
                    } else {
                        echo "<div class='check-item warning'>⚠️ $mod no habilitado (opcional, mejora el rendimiento)</div>";
                    }
                }
            } else {
                echo "<div class='check-item warning'>⚠️ No se puede verificar los módulos de Apache</div>";
            }
            ?>
        </div>
        
        <div class="section">
            <h2>Verificación de MySQL</h2>
            <?php
            try {
                $pdo = new PDO('mysql:host=localhost', 'root', '');
                $version = $pdo->query('SELECT VERSION()')->fetchColumn();
                echo "<div class='check-item success'>✅ MySQL/MariaDB conectado - Versión: $version</div>";
                
                // Verificar si la base de datos existe
                $result = $pdo->query("SHOW DATABASES LIKE 'alara_db'");
                if ($result->rowCount() > 0) {
                    echo "<div class='check-item success'>✅ Base de datos 'alara_db' existe</div>";
                } else {
                    echo "<div class='check-item warning'>⚠️ Base de datos 'alara_db' no existe - Se creará durante la instalación</div>";
                }
            } catch (PDOException $e) {
                echo "<div class='check-item error'>❌ No se puede conectar a MySQL - Asegúrate de que esté ejecutándose en XAMPP</div>";
                echo "<div class='check-item info'>Error: " . $e->getMessage() . "</div>";
            }
            ?>
        </div>
        
        <div class="section">
            <h2>Verificación de Archivos y Carpetas</h2>
            <?php
            // Archivos importantes
            $files = [
                '.htaccess' => 'Archivo de configuración Apache',
                'index.php' => 'Archivo principal',
                'install.php' => 'Instalador',
                'database/schema.sql' => 'Esquema de base de datos'
            ];
            
            foreach ($files as $file => $desc) {
                if (file_exists($file)) {
                    echo "<div class='check-item success'>✅ $desc encontrado</div>";
                } else {
                    echo "<div class='check-item error'>❌ $desc NO encontrado</div>";
                }
            }
            
            // Carpetas con permisos de escritura
            $folders = [
                'logs' => 'Carpeta de logs',
                'public/uploads' => 'Carpeta de uploads',
                'public/uploads/cars' => 'Carpeta de imágenes de autos',
                'public/uploads/documents' => 'Carpeta de documentos'
            ];
            
            foreach ($folders as $folder => $desc) {
                if (!file_exists($folder)) {
                    // Intentar crear
                    @mkdir($folder, 0777, true);
                }
                
                if (file_exists($folder)) {
                    if (is_writable($folder)) {
                        echo "<div class='check-item success'>✅ $desc - Permisos OK</div>";
                    } else {
                        // En Windows normalmente todo es escribible
                        if (stripos(PHP_OS, 'WIN') === 0) {
                            echo "<div class='check-item success'>✅ $desc - OK (Windows)</div>";
                        } else {
                            echo "<div class='check-item warning'>⚠️ $desc - Sin permisos de escritura</div>";
                        }
                    }
                } else {
                    echo "<div class='check-item error'>❌ $desc - No existe</div>";
                }
            }
            ?>
        </div>
        
        <div class="section">
            <h2>Verificación de Configuración</h2>
            <?php
            // Verificar .env
            if (file_exists('.env')) {
                echo "<div class='check-item success'>✅ Archivo .env encontrado</div>";
                
                // Verificar contenido básico
                $env = parse_ini_file('.env');
                if (isset($env['DB_HOST']) && isset($env['APP_URL'])) {
                    echo "<div class='check-item success'>✅ Configuración .env parece correcta</div>";
                } else {
                    echo "<div class='check-item warning'>⚠️ El archivo .env puede estar incompleto</div>";
                }
            } else {
                echo "<div class='check-item warning'>⚠️ Archivo .env no encontrado - Se creará durante la instalación</div>";
            }
            
            // Verificar límites de PHP
            $upload_max = ini_get('upload_max_filesize');
            $post_max = ini_get('post_max_size');
            $memory_limit = ini_get('memory_limit');
            
            echo "<div class='check-item info'>📊 Límite de subida: $upload_max</div>";
            echo "<div class='check-item info'>📊 Límite POST: $post_max</div>";
            echo "<div class='check-item info'>📊 Límite de memoria: $memory_limit</div>";
            ?>
        </div>
        
        <div class="section">
            <h2>Resumen</h2>
            <?php
            $ready = true;
            
            // Verificaciones críticas
            if (version_compare(PHP_VERSION, '7.4.0', '<')) $ready = false;
            if (!extension_loaded('pdo_mysql')) $ready = false;
            if (!file_exists('.htaccess')) $ready = false;
            if (!file_exists('index.php')) $ready = false;
            
            if ($ready) {
                echo "<div class='check-item success'>";
                echo "<h3>✅ ¡El sistema está listo para instalar!</h3>";
                echo "<p>Todos los requisitos críticos están cumplidos.</p>";
                echo "</div>";
                
                if (file_exists('install.php')) {
                    $install_url = 'http://' . $_SERVER['HTTP_HOST'] . dirname($_SERVER['SCRIPT_NAME']) . '/install.php';
                    echo "<a href='$install_url' class='btn'>Ir al Instalador →</a>";
                } else {
                    echo "<div class='check-item error'>❌ Falta el archivo install.php</div>";
                }
            } else {
                echo "<div class='check-item error'>";
                echo "<h3>❌ El sistema NO está listo</h3>";
                echo "<p>Por favor, corrige los errores marcados arriba antes de continuar.</p>";
                echo "</div>";
            }
            ?>
        </div>
        
        <div class="section">
            <p style="text-align: center; color: #666; margin-top: 40px;">
                <strong>Nota:</strong> Elimina este archivo después de verificar el sistema por seguridad.
            </p>
        </div>
    </div>
</body>
</html>