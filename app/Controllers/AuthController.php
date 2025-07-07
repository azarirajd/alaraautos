<?php
// app/Controllers/AuthController.php

namespace Controllers;

use Models\User;

/**
 * Controlador de Autenticación
 */
class AuthController extends Controller {
    
    private $userModel;
    
    public function __construct() {
        parent::__construct();
        $this->userModel = new User();
    }
    
    /**
     * Mostrar formulario de login
     */
    public function loginForm() {
        if (isAuthenticated()) {
            $this->redirect('/dashboard');
        }
        
        $this->view('auth.login', [
            'title' => 'Iniciar Sesión - ALARA Admin',
            'csrf_token' => generateCSRFToken()
        ]);
    }
    
    /**
     * Procesar login
     */
    public function login() {
        $this->validateCSRF();
        
        $email = $this->input('email');
        $password = $this->input('password');
        
        // Validar campos
        if (empty($email) || empty($password)) {
            $this->json([
                'success' => false,
                'message' => 'Por favor complete todos los campos'
            ], 400);
        }
        
        // Buscar usuario
        $user = $this->userModel->findByEmail($email);
        
        if (!$user || !$this->userModel->verifyPassword($password, $user['password'])) {
            $this->json([
                'success' => false,
                'message' => 'Credenciales inválidas'
            ], 401);
        }
        
        // Verificar si el usuario está activo
        if (!$user['is_active']) {
            $this->json([
                'success' => false,
                'message' => 'Tu cuenta está desactivada'
            ], 403);
        }
        
        // Crear sesión
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user'] = [
            'id' => $user['id'],
            'name' => $user['name'],
            'email' => $user['email'],
            'role' => $user['role'],
            'avatar' => $user['avatar']
        ];
        
        // Establecer permisos según el rol
        $this->setPermissions($user['role']);
        
        $this->json([
            'success' => true,
            'message' => 'Inicio de sesión exitoso',
            'redirect' => '/dashboard'
        ]);
    }
    
    /**
     * Cerrar sesión
     */
    public function logout() {
        session_destroy();
        $this->redirect('/login');
    }
    
    /**
     * Establecer permisos según el rol
     */
    private function setPermissions($role) {
        $permissions = [];
        
        switch ($role) {
            case 'admin':
                $permissions = [
                    'view_dashboard', 'manage_inventory', 'manage_crm',
                    'manage_credit', 'manage_content', 'manage_users',
                    'view_reports', 'system_settings'
                ];
                break;
                
            case 'gerencia':
                $permissions = [
                    'view_dashboard', 'manage_inventory', 'manage_crm',
                    'manage_credit', 'manage_content', 'view_reports'
                ];
                break;
                
            case 'ventas':
                $permissions = [
                    'view_dashboard', 'view_inventory', 'manage_crm',
                    'view_credit'
                ];
                break;
        }
        
        $_SESSION['user']['permissions'] = $permissions;
    }
}