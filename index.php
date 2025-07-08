<?php
/**
 * ALARA - Sistema de Venta de Autos de Lujo
 * Punto de entrada principal
 */

// Configuración de errores para desarrollo
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Definir constantes del sistema
define('ROOT_PATH', __DIR__);
define('APP_PATH', ROOT_PATH . '/app');
define('PUBLIC_PATH', ROOT_PATH . '/public');
define('VIEWS_PATH', APP_PATH . '/views');
define('UPLOADS_PATH', PUBLIC_PATH . '/uploads');

// Autoloader simple
spl_autoload_register(function ($class) {
    $file = APP_PATH . '/' . str_replace('\\', '/', $class) . '.php';
    if (file_exists($file)) {
        require_once $file;
    }
});

// Iniciar sesión
session_start();

// Cargar configuración
require_once APP_PATH . '/config/config.php';
require_once APP_PATH . '/config/database.php';

// Cargar funciones auxiliares
require_once APP_PATH . '/helpers/functions.php';

// Router principal
$request_uri = $_SERVER['REQUEST_URI'];
$request_method = $_SERVER['REQUEST_METHOD'];

// Eliminar query string de la URI
$request_uri = strtok($request_uri, '?');

// Si estamos en un subdirectorio, ajustar la URI
$script_name = $_SERVER['SCRIPT_NAME'];
$base_path = dirname($script_name);
if ($base_path !== '/' && $base_path !== '\\') {
    // Eliminar el path base de la URI
    $request_uri = substr($request_uri, strlen($base_path));
    if (empty($request_uri)) {
        $request_uri = '/';
    }
}

// Router simple
$routes = require APP_PATH . '/routes/web.php';

// Verificar si es una ruta de API
if (strpos($request_uri, '/api/') === 0) {
    header('Content-Type: application/json');
    $api_routes = require APP_PATH . '/routes/api.php';
    $route_found = false;
    
    foreach ($api_routes as $route => $handler) {
        if (preg_match($route, $request_uri, $matches)) {
            $route_found = true;
            array_shift($matches); // Eliminar la coincidencia completa
            
            // Verificar método HTTP si está especificado
            if (is_array($handler) && isset($handler['method'])) {
                if ($handler['method'] !== $request_method) {
                    http_response_code(405);
                    echo json_encode(['error' => 'Método no permitido']);
                    exit;
                }
                $handler = $handler['handler'];
            }
            
            // Ejecutar el controlador
            if (is_callable($handler)) {
                call_user_func_array($handler, $matches);
            } else {
                list($controller, $method) = explode('@', $handler);
                $controller_class = "Controllers\\$controller";
                $controller_instance = new $controller_class();
                call_user_func_array([$controller_instance, $method], $matches);
            }
            exit;
        }
    }
    
    if (!$route_found) {
        http_response_code(404);
        echo json_encode(['error' => 'Ruta no encontrada']);
        exit;
    }
}

// Rutas web
$route_found = false;

foreach ($routes as $route => $handler) {
    if (preg_match($route, $request_uri, $matches)) {
        $route_found = true;
        array_shift($matches); // Eliminar la coincidencia completa
        
        // Si es el sitio público estático
        if ($handler === 'static') {
            $file = PUBLIC_PATH . '/static' . $request_uri;
            if ($request_uri === '/') {
                $file = PUBLIC_PATH . '/static/index.html';
            }
            
            if (file_exists($file) && !is_dir($file)) {
                // Determinar el tipo MIME
                $mime_types = [
                    'html' => 'text/html',
                    'css' => 'text/css',
                    'js' => 'application/javascript',
                    'jpg' => 'image/jpeg',
                    'jpeg' => 'image/jpeg',
                    'png' => 'image/png',
                    'gif' => 'image/gif',
                    'svg' => 'image/svg+xml',
                    'webp' => 'image/webp'
                ];
                
                $ext = pathinfo($file, PATHINFO_EXTENSION);
                $mime_type = $mime_types[$ext] ?? 'application/octet-stream';
                
                header('Content-Type: ' . $mime_type);
                readfile($file);
                exit;
            }
        }
        
        // Ejecutar el controlador
        if (is_callable($handler)) {
            call_user_func_array($handler, $matches);
        } else {
            list($controller, $method) = explode('@', $handler);
            $controller_class = "Controllers\\$controller";
            $controller_instance = new $controller_class();
            call_user_func_array([$controller_instance, $method], $matches);
        }
        exit;
    }
}

// Si no se encontró ninguna ruta, mostrar 404
if (!$route_found) {
    http_response_code(404);
    require VIEWS_PATH . '/errors/404.php';
}