<?php
// app/Controllers/Controller.php

namespace Controllers;

/**
 * Controlador base del que heredan todos los demás
 */
abstract class Controller {
    
    protected $db;
    
    public function __construct() {
        $this->db = \Database::getInstance()->getConnection();
    }
    
    /**
     * Renderizar una vista
     */
    protected function view($view, $data = []) {
        view($view, $data);
    }
    
    /**
     * Responder con JSON
     */
    protected function json($data, $status = 200) {
        json($data, $status);
    }
    
    /**
     * Redireccionar
     */
    protected function redirect($url) {
        redirect($url);
    }
    
    /**
     * Obtener datos del request
     */
    protected function input($key = null, $default = null) {
        $data = array_merge($_GET, $_POST);
        
        if ($key === null) {
            return $data;
        }
        
        return $data[$key] ?? $default;
    }
    
    /**
     * Obtener datos JSON del body
     */
    protected function jsonInput() {
        $json = file_get_contents('php://input');
        return json_decode($json, true) ?? [];
    }
    
    /**
     * Validar token CSRF
     */
    protected function validateCSRF() {
        $token = $this->input('csrf_token');
        
        if (!verifyCSRFToken($token)) {
            if (strpos($_SERVER['REQUEST_URI'], '/api/') === 0) {
                $this->json(['error' => 'Token CSRF inválido'], 403);
            } else {
                die('Token CSRF inválido');
            }
        }
    }
}