<?php
// app/helpers/functions.php

/**
 * Funciones auxiliares globales
 */

// Función para escapar HTML
function e($string) {
    return htmlspecialchars($string, ENT_QUOTES, 'UTF-8');
}

// Función para redireccionar
function redirect($url) {
    header("Location: " . $url);
    exit;
}

// Función para verificar si el usuario está autenticado
function isAuthenticated() {
    return isset($_SESSION['user_id']);
}

// Función para obtener el usuario actual
function currentUser() {
    if (isAuthenticated()) {
        return $_SESSION['user'] ?? null;
    }
    return null;
}

// Función para verificar permisos
function hasPermission($permission) {
    $user = currentUser();
    if (!$user) return false;
    
    // Implementar lógica de permisos según roles
    return in_array($permission, $user['permissions'] ?? []);
}

// Función para generar token CSRF
function generateCSRFToken() {
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

// Función para verificar token CSRF
function verifyCSRFToken($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

// Función para cargar una vista
function view($view, $data = []) {
    extract($data);
    $viewFile = VIEWS_PATH . '/' . str_replace('.', '/', $view) . '.php';
    
    if (file_exists($viewFile)) {
        require $viewFile;
    } else {
        die("Vista no encontrada: $view");
    }
}

// Función para respuesta JSON
function json($data, $status = 200) {
    http_response_code($status);
    header('Content-Type: application/json');
    echo json_encode($data);
    exit;
}

// Función para subir archivos
function uploadFile($file, $directory) {
    $targetDir = UPLOADS_PATH . '/' . $directory;
    
    if (!file_exists($targetDir)) {
        mkdir($targetDir, 0777, true);
    }
    
    $fileName = uniqid() . '_' . basename($file['name']);
    $targetFile = $targetDir . '/' . $fileName;
    $fileType = strtolower(pathinfo($targetFile, PATHINFO_EXTENSION));
    
    // Verificar tipo de archivo
    if (!in_array($fileType, ALLOWED_IMAGE_TYPES)) {
        return ['success' => false, 'error' => 'Tipo de archivo no permitido'];
    }
    
    // Verificar tamaño
    if ($file['size'] > MAX_FILE_SIZE) {
        return ['success' => false, 'error' => 'El archivo es demasiado grande'];
    }
    
    if (move_uploaded_file($file['tmp_name'], $targetFile)) {
        return ['success' => true, 'filename' => $fileName];
    }
    
    return ['success' => false, 'error' => 'Error al subir el archivo'];
}

// Función para formatear fecha
function formatDate($date, $format = 'd/m/Y') {
    return date($format, strtotime($date));
}

// Función para formatear precio
function formatPrice($price) {
    return '$' . number_format($price, 2, '.', ',');
}

// Función para crear slug
function createSlug($string) {
    $string = preg_replace('/[^A-Za-z0-9-]+/', '-', $string);
    $string = strtolower(trim($string, '-'));
    return $string;
}