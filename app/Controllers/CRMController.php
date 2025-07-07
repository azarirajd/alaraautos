<?php
// app/Controllers/CRMController.php

namespace Controllers;

use Models\Lead;
use Models\User;
use Models\Car;
use Middleware\AuthMiddleware;

/**
 * Controlador de CRM (Customer Relationship Management)
 */
class CRMController extends Controller {
    
    private $leadModel;
    private $userModel;
    private $carModel;
    
    public function __construct() {
        parent::__construct();
        AuthMiddleware::check();
        
        $this->leadModel = new Lead();
        $this->userModel = new User();
        $this->carModel = new Car();
    }
    
    /**
     * Página principal del CRM
     */
    public function index() {
        AuthMiddleware::checkPermission('manage_crm');
        
        $status = $this->input('status', '');
        $assignedTo = $this->input('assigned_to', '');
        $page = $this->input('page', 1);
        
        // Construir condiciones
        $conditions = [];
        if ($status) {
            $conditions['status'] = $status;
        }
        if ($assignedTo) {
            $conditions['assigned_to'] = $assignedTo;
        }
        
        // Obtener leads con información adicional
        $leads = $this->getLeadsWithDetails($conditions, $page);
        
        // Obtener estadísticas
        $stats = $this->getLeadStats();
        
        // Obtener usuarios para asignación
        $users = $this->userModel->where(['is_active' => 1], 'name ASC');
        
        $this->view('dashboard.crm.index', [
            'title' => 'Gestión de CRM - ALARA Admin',
            'leads' => $leads,
            'stats' => $stats,
            'users' => $users,
            'filters' => [
                'status' => $status,
                'assigned_to' => $assignedTo
            ],
            'user' => currentUser()
        ]);
    }
    
    /**
     * API: Obtener leads
     */
    public function apiLeads() {
        AuthMiddleware::checkPermission('manage_crm');
        
        $filters = $this->jsonInput();
        $conditions = [];
        
        if (!empty($filters['status'])) {
            $conditions['status'] = $filters['status'];
        }
        
        if (!empty($filters['assigned_to'])) {
            $conditions['assigned_to'] = $filters['assigned_to'];
        }
        
        $leads = $this->leadModel->where($conditions, 'created_at DESC');
        
        $this->json([
            'success' => true,
            'data' => $leads
        ]);
    }
    
    /**
     * API: Crear nuevo lead
     */
    public function apiCreateLead() {
        $data = $this->jsonInput();
        
        // Validar datos requeridos
        $required = ['name', 'email'];
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
        
        // Asignar automáticamente si hay un usuario de ventas disponible
        if (empty($data['assigned_to'])) {
            $data['assigned_to'] = $this->getNextAvailableSalesperson();
        }
        
        $leadId = $this->leadModel->create($data);
        
        if (!$leadId) {
            $this->json([
                'success' => false,
                'message' => 'Error al crear el lead'
            ], 500);
        }
        
        // Enviar notificación por email al vendedor asignado
        if (!empty($data['assigned_to'])) {
            $this->notifySalesperson($leadId, $data['assigned_to']);
        }
        
        $this->json([
            'success' => true,
            'message' => 'Lead creado exitosamente',
            'data' => ['id' => $leadId]
        ]);
    }
    
    /**
     * API: Actualizar lead
     */
    public function apiUpdateLead($id) {
        AuthMiddleware::checkPermission('manage_crm');
        
        $data = $this->jsonInput();
        
        // Verificar que el lead existe
        $lead = $this->leadModel->find($id);
        if (!$lead) {
            $this->json([
                'success' => false,
                'message' => 'Lead no encontrado'
            ], 404);
        }
        
        // Si se está cambiando el estado a contactado
        if (!empty($data['status']) && $data['status'] === 'contacted' && $lead['status'] !== 'contacted') {
            $data['contacted_at'] = date('Y-m-d H:i:s');
        }
        
        // Si se está convirtiendo el lead
        if (!empty($data['status']) && $data['status'] === 'converted' && $lead['status'] !== 'converted') {
            $data['converted_at'] = date('Y-m-d H:i:s');
        }
        
        $result = $this->leadModel->update($id, $data);
        
        if (!$result) {
            $this->json([
                'success' => false,
                'message' => 'Error al actualizar el lead'
            ], 500);
        }
        
        // Registrar actividad
        $this->logActivity('lead_updated', 'lead', $id, "Lead actualizado: {$lead['name']}");
        
        $this->json([
            'success' => true,
            'message' => 'Lead actualizado exitosamente'
        ]);
    }
    
    /**
     * Obtener leads con detalles adicionales
     */
    private function getLeadsWithDetails($conditions, $page) {
        $perPage = 20;
        $offset = ($page - 1) * $perPage;
        
        $sql = "SELECT l.*, 
                       c.brand as car_brand, 
                       c.model as car_model, 
                       c.year as car_year,
                       u.name as assigned_to_name
                FROM leads l
                LEFT JOIN cars c ON l.car_id = c.id
                LEFT JOIN users u ON l.assigned_to = u.id
                WHERE 1=1";
        
        $params = [];
        foreach ($conditions as $key => $value) {
            $sql .= " AND l.$key = :$key";
            $params[$key] = $value;
        }
        
        $sql .= " ORDER BY l.created_at DESC LIMIT $perPage OFFSET $offset";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        $data = $stmt->fetchAll();
        
        // Obtener total
        $total = $this->leadModel->count($conditions);
        
        return [
            'data' => $data,
            'total' => $total,
            'page' => $page,
            'per_page' => $perPage,
            'total_pages' => ceil($total / $perPage)
        ];
    }
    
    /**
     * Obtener estadísticas de leads
     */
    private function getLeadStats() {
        $stats = [];
        
        // Total por estado
        $statuses = ['new', 'contacted', 'qualified', 'converted', 'lost'];
        foreach ($statuses as $status) {
            $stats[$status] = $this->leadModel->count(['status' => $status]);
        }
        
        // Tasa de conversión
        $total = array_sum($stats);
        $stats['conversion_rate'] = $total > 0 ? 
            round(($stats['converted'] / $total) * 100, 1) : 0;
        
        // Leads del mes actual
        $sql = "SELECT COUNT(*) as total FROM leads 
                WHERE MONTH(created_at) = MONTH(CURRENT_DATE) 
                AND YEAR(created_at) = YEAR(CURRENT_DATE)";
        $stmt = $this->db->query($sql);
        $result = $stmt->fetch();
        $stats['monthly'] = $result['total'];
        
        return $stats;
    }
    
    /**
     * Obtener siguiente vendedor disponible (round-robin)
     */
    private function getNextAvailableSalesperson() {
        $sql = "SELECT u.id 
                FROM users u
                LEFT JOIN (
                    SELECT assigned_to, COUNT(*) as lead_count 
                    FROM leads 
                    WHERE status IN ('new', 'contacted') 
                    GROUP BY assigned_to
                ) l ON u.id = l.assigned_to
                WHERE u.role = 'ventas' AND u.is_active = 1
                ORDER BY COALESCE(l.lead_count, 0) ASC
                LIMIT 1";
        
        $stmt = $this->db->query($sql);
        $result = $stmt->fetch();
        
        return $result ? $result['id'] : null;
    }
    
    /**
     * Notificar al vendedor sobre nuevo lead
     */
    private function notifySalesperson($leadId, $userId) {
        // Implementar notificación por email
        // Por ahora solo registramos la actividad
        $this->logActivity('lead_assigned', 'lead', $leadId, 
            "Nuevo lead asignado al usuario $userId");
    }
    
    /**
     * Registrar actividad
     */
    private function logActivity($action, $entityType, $entityId, $description) {
        $sql = "INSERT INTO activity_logs (user_id, action, entity_type, entity_id, description, ip_address, user_agent) 
                VALUES (:user_id, :action, :entity_type, :entity_id, :description, :ip, :agent)";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            'user_id' => $_SESSION['user_id'] ?? null,
            'action' => $action,
            'entity_type' => $entityType,
            'entity_id' => $entityId,
            'description' => $description,
            'ip' => $_SERVER['REMOTE_ADDR'] ?? '',
            'agent' => $_SERVER['HTTP_USER_AGENT'] ?? ''
        ]);
    }
}