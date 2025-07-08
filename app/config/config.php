<?php
// app/config/config.php - Configuración optimizada para XAMPP

/**
 * Auto-detectar la URL base y rutas
 */
function detectBaseUrl() {
    $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
    
    // Detectar si estamos en un subdirectorio
    $scriptName = $_SERVER['SCRIPT_NAME'] ?? '';
    $scriptDir = dirname($scriptName);
    
    // Si no es la raíz, agregar el directorio
    $basePath = ($scriptDir !== '/' && $scriptDir !== '\\') ? $scriptDir : '';
    
    // Limpiar barras extras
    $basePath = rtrim($basePath, '/\\');
    
    return $protocol . '://' . $host . $basePath;
}

// Detectar si existe archivo .env
if (file_exists(ROOT_PATH . '/.env')) {
    // Cargar configuración desde .env
    $envFile = parse_ini_file(ROOT_PATH . '/.env');
    foreach ($envFile as $key => $value) {
        putenv("$key=$value");
        $_ENV[$key] = $value;
    }
}

// Configuración del sitio
define('SITE_NAME', $_ENV['APP_NAME'] ?? 'ALARA');
define('SITE_URL', $_ENV['APP_URL'] ?? detectBaseUrl());
define('SITE_DESCRIPTION', 'Venta de Autos de Lujo Seminuevos');

// Detectar subdirectorio para assets
$baseUri = parse_url(SITE_URL, PHP_URL_PATH);
define('BASE_PATH', $baseUri ? rtrim($baseUri, '/') : '');

// Configuración de la base de datos
define('DB_HOST', $_ENV['DB_HOST'] ?? 'localhost');
define('DB_NAME', $_ENV['DB_NAME'] ?? 'alara_db');
define('DB_USER', $_ENV['DB_USER'] ?? 'root');
define('DB_PASS', $_ENV['DB_PASS'] ?? '');
define('DB_CHARSET', $_ENV['DB_CHARSET'] ?? 'utf8mb4');

// Configuración de seguridad
define('SECRET_KEY', $_ENV['SECRET_KEY'] ?? 'cambiar_esta_clave_' . uniqid());
define('SESSION_LIFETIME', 3600); // 1 hora

// Configuración de email
define('MAIL_FROM', $_ENV['MAIL_FROM'] ?? 'noreply@' . ($_SERVER['HTTP_HOST'] ?? 'localhost'));
define('MAIL_FROM_NAME', $_ENV['MAIL_FROM_NAME'] ?? 'ALARA');

// Configuración de uploads
define('MAX_FILE_SIZE', 5 * 1024 * 1024); // 5MB
define('ALLOWED_IMAGE_TYPES', ['jpg', 'jpeg', 'png', 'webp']);

// Modo de desarrollo
define('DEBUG_MODE', $_ENV['DEBUG_MODE'] ?? true);
define('ENVIRONMENT', $_ENV['APP_ENV'] ?? 'development');

// Zona horaria
date_default_timezone_set('America/Mexico_City');

// Configuración de errores según el entorno
if (DEBUG_MODE) {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
} else {
    error_reporting(E_ALL & ~E_NOTICE & ~E_DEPRECATED);
    ini_set('display_errors', 0);
}

// Asegurar que los directorios necesarios existan
$requiredDirs = [
    ROOT_PATH . '/logs',
    ROOT_PATH . '/public/uploads',
    ROOT_PATH . '/public/uploads/cars',
    ROOT_PATH . '/public/uploads/documents',
    ROOT_PATH . '/public/uploads/temp'
];

foreach ($requiredDirs as $dir) {
    if (!is_dir($dir)) {
        @mkdir($dir, 0777, true);
    }
}

// Función helper para URLs
function asset($path) {
    return BASE_PATH . '/' . ltrim($path, '/');
}

function url($path = '') {
    return SITE_URL . '/' . ltrim($path, '/');
}