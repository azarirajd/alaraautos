<?php
// app/Controllers/UserController.php

namespace Controllers;

use Models\User;
use Middleware\AuthMiddleware;

/**
 * Controlador de Gestión de Usuarios
 */
class UserController extends Controller {
    
    private $userModel;
    
    public function __construct() {
        parent::__construct();
        AuthMiddleware::check();
        
        $this->userModel = new User();
    }
    
    /**
     * Listar usuarios
     */
    public function index() {
        AuthMiddleware::checkPermission('manage_users');
        
        $page = $this->input('page', 1);
        $search = $this->input('search', '');
        $role = $this->input('role', '');
        
        // Obtener usuarios
        $users = $this->searchUsers($search, $role, $page);
        
        $this->view('dashboard.users.index', [
            'title' => 'Gestión de Usuarios - ALARA Admin',
            'users' => $users,
            'search' => $search,
            'selectedRole' => $role,
            'user' => currentUser()
        ]);
    }
    
    /**
     * Formulario para crear usuario
     */
    public function create() {
        AuthMiddleware::checkPermission('manage_users');
        
        $this->view('dashboard.users.create', [
            'title' => 'Crear Usuario - ALARA Admin',
            'csrf_token' => generateCSRFToken(),
            'user' => currentUser()
        ]);
    }
    
    /**
     * Formulario para editar usuario
     */
    public function edit($id) {
        AuthMiddleware::checkPermission('manage_users');
        
        $editUser = $this->userModel->find($id);
        
        if (!$editUser) {
            http_response_code(404);
            $this->view('errors.404');
            exit;
        }
        
        // No permitir editar el propio rol si es el único admin
        $canEditRole = true;
        if ($editUser['id'] == currentUser()['id'] && $editUser['role'] == 'admin') {
            $adminCount = $this->userModel->count(['role' => 'admin', 'is_active' => 1]);
            if ($adminCount <= 1) {
                $canEditRole = false;
            }
        }
        
        $this->view('dashboard.users.edit', [
            'title' => 'Editar Usuario - ALARA Admin',
            'editUser' => $editUser,
            'canEditRole' => $canEditRole,
            'csrf_token' => generateCSRFToken(),
            'user' => currentUser()
        ]);
    }
    
    /**
     * API: Listar usuarios
     */
    public function apiList() {
        AuthMiddleware::checkPermission('manage_users');
        
        $users = $this->userModel->all('name ASC');
        
        // Remover contraseñas de la respuesta
        foreach ($users as &$user) {
            unset($user['password']);
        }
        
        $this->json([
            'success' => true,
            'data' => $users
        ]);
    }
    
    /**
     * API: Crear usuario
     */
    public function apiCreate() {
        AuthMiddleware::checkPermission('manage_users');
        
        $data = $this->jsonInput();
        
        // Validar datos requeridos
        $required = ['name', 'email', 'password', 'role'];
        foreach ($required as $field) {
            if (empty($data[$field])) {
                $this->json([
                    'success' => false,
                    'message' => "El campo $field es requerido"
                ], 400);
            }
        }
        
        // Validar email
        if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            $this->json([
                'success' => false,
                'message' => 'Email inválido'
            ], 400);
        }
        
        // Verificar que el email no esté en uso
        $existingUser = $this->userModel->findByEmail($data['email']);
        if ($existingUser) {
            $this->json([
                'success' => false,
                'message' => 'El email ya está registrado'
            ], 400);
        }
        
        // Validar contraseña
        if (strlen($data['password']) < 6) {
            $this->json([
                'success' => false,
                'message' => 'La contraseña debe tener al menos 6 caracteres'
            ], 400);
        }
        
        // Validar rol
        $validRoles = ['admin', 'gerencia', 'ventas'];
        if (!in_array($data['role'], $validRoles)) {
            $this->json([
                'success' => false,
                'message' => 'Rol inválido'
            ], 400);
        }
        
        // Crear usuario
        $userId = $this->userModel->create($data);
        
        if (!$userId) {
            $this->json([
                'success' => false,
                'message' => 'Error al crear el usuario'
            ], 500);
        }
        
        // Registrar actividad
        $this->logActivity('user_created', 'user', $userId, 
            "Usuario creado: {$data['name']}");
        
        $this->json([
            'success' => true,
            'message' => 'Usuario creado exitosamente',
            'data' => ['id' => $userId]
        ]);
    }
    
    /**
     * API: Actualizar usuario
     */
    public function apiUpdate($id) {
        AuthMiddleware::checkPermission('manage_users');
        
        $data = $this->jsonInput();
        
        // Verificar que el usuario existe
        $user = $this->userModel->find($id);
        if (!$user) {
            $this->json([
                'success' => false,
                'message' => 'Usuario no encontrado'
            ], 404);
        }
        
        // Validar email si se está cambiando
        if (!empty($data['email']) && $data['email'] !== $user['email']) {
            if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
                $this->json([
                    'success' => false,
                    'message' => 'Email inválido'
                ], 400);
            }
            
            $existingUser = $this->userModel->findByEmail($data['email']);
            if ($existingUser) {
                $this->json([
                    'success' => false,
                    'message' => 'El email ya está registrado'
                ], 400);
            }
        }
        
        // Validar contraseña si se está cambiando
        if (!empty($data['password']) && strlen($data['password']) < 6) {
            $this->json([
                'success' => false,
                'message' => 'La contraseña debe tener al menos 6 caracteres'
            ], 400);
        }
        
        // No permitir que el último admin se desactive o cambie de rol
        if ($user['role'] == 'admin' && 
            ((!empty($data['role']) && $data['role'] != 'admin') || 
             (isset($data['is_active']) && !$data['is_active']))) {
            
            $adminCount = $this->userModel->count(['role' => 'admin', 'is_active' => 1]);
            if ($adminCount <= 1) {
                $this->json([
                    'success' => false,
                    'message' => 'No se puede modificar el último administrador activo'
                ], 400);
            }
        }
        
        // Actualizar usuario
        $result = $this->userModel->update($id, $data);
        
        if (!$result) {
            $this->json([
                'success' => false,
                'message' => 'Error al actualizar el usuario'
            ], 500);
        }
        
        // Registrar actividad
        $this->logActivity('user_updated', 'user', $id, 
            "Usuario actualizado: {$user['name']}");
        
        $this->json([
            'success' => true,
            'message' => 'Usuario actualizado exitosamente'
        ]);
    }
    
    /**
     * API: Eliminar usuario
     */
    public function apiDelete($id) {
        AuthMiddleware::checkPermission('manage_users');
        
        // Verificar que el usuario existe
        $user = $this->userModel->find($id);
        if (!$user) {
            $this->json([
                'success' => false,
                'message' => 'Usuario no encontrado'
            ], 404);
        }
        
        // No permitir eliminar el propio usuario
        if ($id == currentUser()['id']) {
            $this->json([
                'success' => false,
                'message' => 'No puedes eliminar tu propio usuario'
            ], 400);
        }
        
        // No permitir eliminar el último admin
        if ($user['role'] == 'admin') {
            $adminCount = $this->userModel->count(['role' => 'admin', 'is_active' => 1]);
            if ($adminCount <= 1) {
                $this->json([
                    'success' => false,
                    'message' => 'No se puede eliminar el último administrador'
                ], 400);
            }
        }
        
        // En lugar de eliminar, desactivar el usuario
        $result = $this->userModel->update($id, ['is_active' => 0]);
        
        if (!$result) {
            $this->json([
                'success' => false,
                'message' => 'Error al eliminar el usuario'
            ], 500);
        }
        
        // Registrar actividad
        $this->logActivity('user_deleted', 'user', $id, 
            "Usuario eliminado: {$user['name']}");
        
        $this->json([
            'success' => true,
            'message' => 'Usuario eliminado exitosamente'
        ]);
    }
    
    /**
     * Buscar usuarios
     */
    private function searchUsers($search, $role, $page) {
        $perPage = 20;
        $offset = ($page - 1) * $perPage;
        
        $sql = "SELECT * FROM users WHERE 1=1";
        $countSql = "SELECT COUNT(*) as total FROM users WHERE 1=1";
        $params = [];
        
        if ($search) {
            $sql .= " AND (name LIKE :search OR email LIKE :search)";
            $countSql .= " AND (name LIKE :search OR email LIKE :search)";
            $params['search'] = "%$search%";
        }
        
        if ($role) {
            $sql .= " AND role = :role";
            $countSql .= " AND role = :role";
            $params['role'] = $role;
        }
        
        $sql .= " ORDER BY created_at DESC LIMIT $perPage OFFSET $offset";
        
        // Ejecutar consultas
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        $data = $stmt->fetchAll();
        
        // Remover contraseñas
        foreach ($data as &$user) {
            unset($user['password']);
        }
        
        $stmt = $this->db->prepare($countSql);
        $stmt->execute($params);
        $result = $stmt->fetch();
        $total = $result['total'];
        
        return [
            'data' => $data,
            'total' => $total,
            'page' => $page,
            'per_page' => $perPage,
            'total_pages' => ceil($total / $perPage)
        ];
    }
    
    /**
     * Registrar actividad
     */
    private function logActivity($action, $entityType, $entityId, $description) {
        $sql = "INSERT INTO activity_logs (user_id, action, entity_type, entity_id, description, ip_address) 
                VALUES (:user_id, :action, :entity_type, :entity_id, :description, :ip)";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            'user_id' => $_SESSION['user_id'] ?? null,
            'action' => $action,
            'entity_type' => $entityType,
            'entity_id' => $entityId,
            'description' => $description,
            'ip' => $_SERVER['REMOTE_ADDR'] ?? ''
        ]);
    }
}