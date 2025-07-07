<?php
// app/middleware/AuthMiddleware.php

namespace Middleware;

/**
 * Middleware para verificar autenticación
 */
class AuthMiddleware {
    
    public static function check() {
        if (!isAuthenticated()) {
            if (self::isApiRequest()) {
                json(['error' => 'No autorizado'], 401);
            } else {
                redirect('/login');
            }
        }
    }
    
    public static function checkPermission($permission) {
        self::check(); // Primero verificar autenticación
        
        if (!hasPermission($permission)) {
            if (self::isApiRequest()) {
                json(['error' => 'Sin permisos suficientes'], 403);
            } else {
                http_response_code(403);
                view('errors.403');
                exit;
            }
        }
    }
    
    private static function isApiRequest() {
        return strpos($_SERVER['REQUEST_URI'], '/api/') === 0;
    }
}