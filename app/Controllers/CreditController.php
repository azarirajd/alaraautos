<?php
// app/Controllers/CreditController.php

namespace Controllers;

use Models\CreditApplication;
use Models\Car;
use Middleware\AuthMiddleware;

/**
 * Controlador de Solicitudes de Crédito
 */
class CreditController extends Controller {
    
    private $creditModel;
    private $carModel;
    
    public function __construct() {
        parent::__construct();
        AuthMiddleware::check();
        
        $this->creditModel = new CreditApplication();
        $this->carModel = new Car();
    }
    
    /**
     * Listar solicitudes de crédito
     */
    public function index() {
        AuthMiddleware::checkPermission('view_credit');
        
        $status = $this->input('status', '');
        $page = $this->input('page', 1);
        
        // Obtener solicitudes con detalles
        $applications = $this->getApplicationsWithDetails($status, $page);
        
        // Estadísticas
        $stats = $this->getCreditStats();
        
        $this->view('dashboard.credit.index', [
            'title' => 'Solicitudes de Crédito - ALARA Admin',
            'applications' => $applications,
            'stats' => $stats,
            'selectedStatus' => $status,
            'user' => currentUser()
        ]);
    }
    
    /**
     * Ver detalle de solicitud
     */
    public function show($id) {
        AuthMiddleware::checkPermission('view_credit');
        
        $sql = "SELECT ca.*, 
                       c.brand, c.model, c.year, c.price, c.main_image,
                       u.name as reviewer_name
                FROM credit_applications ca
                JOIN cars c ON ca.car_id = c.id
                LEFT JOIN users u ON ca.reviewed_by = u.id
                WHERE ca.id = :id";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['id' => $id]);
        $application = $stmt->fetch();
        
        if (!$application) {
            http_response_code(404);
            $this->view('errors.404');
            exit;
        }
        
        // Calcular score crediticio
        $creditScore = $this->creditModel->calculateCreditScore($application);
        
        // Decodificar documentos si existen
        if ($application['documents']) {
            $application['documents'] = json_decode($application['documents'], true);
        }
        
        $this->view('dashboard.credit.show', [
            'title' => 'Detalle de Solicitud - ALARA Admin',
            'application' => $application,
            'creditScore' => $creditScore,
            'csrf_token' => generateCSRFToken(),
            'user' => currentUser()
        ]);
    }
    
    /**
     * API: Listar solicitudes
     */
    public function apiList() {
        AuthMiddleware::checkPermission('view_credit');
        
        $filters = $this->jsonInput();
        $conditions = [];
        
        if (!empty($filters['status'])) {
            $conditions['status'] = $filters['status'];
        }
        
        $applications = $this->creditModel->where($conditions, 'created_at DESC');
        
        $this->json([
            'success' => true,
            'data' => $applications
        ]);
    }
    
    /**
     * API: Crear solicitud (desde el sitio público)
     */
    public function apiCreate() {
        $data = $this->jsonInput();
        
        // Validar datos requeridos
        $required = [
            'applicant_name', 'applicant_email', 'applicant_phone',
            'monthly_income', 'employment_type', 'car_id', 'down_payment', 'requested_term'
        ];
        
        foreach ($required as $field) {
            if (empty($data[$field])) {
                $this->json([
                    'success' => false,
                    'message' => "El campo $field es requerido"
                ], 400);
            }
        }
        
        // Validar email
        if (!filter_var($data['applicant_email'], FILTER_VALIDATE_EMAIL)) {
            $this->json([
                'success' => false,
                'message' => 'Email inválido'
            ], 400);
        }
        
        // Validar que el auto existe y está disponible
        $car = $this->carModel->find($data['car_id']);
        if (!$car || !$car['is_available']) {
            $this->json([
                'success' => false,
                'message' => 'El vehículo seleccionado no está disponible'
            ], 400);
        }
        
        // Crear solicitud
        $applicationId = $this->creditModel->create($data);
        
        if (!$applicationId) {
            $this->json([
                'success' => false,
                'message' => 'Error al crear la solicitud'
            ], 500);
        }
        
        // Enviar email de confirmación
        $this->sendConfirmationEmail($applicationId, $data);
        
        $this->json([
            'success' => true,
            'message' => 'Solicitud enviada exitosamente',
            'data' => ['id' => $applicationId, 'reference' => 'APP-' . str_pad($applicationId, 6, '0', STR_PAD_LEFT)]
        ]);
    }
    
    /**
     * API: Actualizar estado de solicitud
     */
    public function apiUpdateStatus($id) {
        AuthMiddleware::checkPermission('manage_credit');
        
        $data = $this->jsonInput();
        
        if (empty($data['status'])) {
            $this->json([
                'success' => false,
                'message' => 'Estado requerido'
            ], 400);
        }
        
        $validStatuses = ['Enviado', 'En Revisión', 'Aprobado', 'Rechazado', 'Documentos Pendientes'];
        if (!in_array($data['status'], $validStatuses)) {
            $this->json([
                'success' => false,
                'message' => 'Estado inválido'
            ], 400);
        }
        
        // Verificar que la solicitud existe
        $application = $this->creditModel->find($id);
        if (!$application) {
            $this->json([
                'success' => false,
                'message' => 'Solicitud no encontrada'
            ], 404);
        }
        
        // Actualizar estado
        $result = $this->creditModel->updateStatus(
            $id, 
            $data['status'], 
            currentUser()['id'],
            $data['notes'] ?? null
        );
        
        if (!$result) {
            $this->json([
                'success' => false,
                'message' => 'Error al actualizar el estado'
            ], 500);
        }
        
        // Registrar actividad
        $this->logActivity('credit_status_updated', 'credit_application', $id, 
            "Estado actualizado a: {$data['status']}");
        
        // Notificar al solicitante si se aprobó o rechazó
        if (in_array($data['status'], ['Aprobado', 'Rechazado'])) {
            $this->notifyApplicant($id, $data['status']);
        }
        
        $this->json([
            'success' => true,
            'message' => 'Estado actualizado exitosamente'
        ]);
    }
    
    /**
     * Obtener solicitudes con detalles
     */
    private function getApplicationsWithDetails($status, $page) {
        $perPage = 20;
        $offset = ($page - 1) * $perPage;
        
        $sql = "SELECT ca.*, 
                       c.brand, c.model, c.year, c.price,
                       u.name as reviewer_name
                FROM credit_applications ca
                JOIN cars c ON ca.car_id = c.id
                LEFT JOIN users u ON ca.reviewed_by = u.id
                WHERE 1=1";
        
        $params = [];
        if ($status) {
            $sql .= " AND ca.status = :status";
            $params['status'] = $status;
        }
        
        $sql .= " ORDER BY ca.created_at DESC LIMIT $perPage OFFSET $offset";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        $data = $stmt->fetchAll();
        
        // Calcular score para cada solicitud
        foreach ($data as &$app) {
            $app['credit_score'] = $this->creditModel->calculateCreditScore($app);
        }
        
        // Total
        $conditions = $status ? ['status' => $status] : [];
        $total = $this->creditModel->count($conditions);
        
        return [
            'data' => $data,
            'total' => $total,
            'page' => $page,
            'per_page' => $perPage,
            'total_pages' => ceil($total / $perPage)
        ];
    }
    
    /**
     * Obtener estadísticas de créditos
     */
    private function getCreditStats() {
        $stats = [];
        
        // Total por estado
        $statuses = ['Enviado', 'En Revisión', 'Aprobado', 'Rechazado', 'Documentos Pendientes'];
        foreach ($statuses as $status) {
            $stats[strtolower(str_replace(' ', '_', $status))] = 
                $this->creditModel->count(['status' => $status]);
        }
        
        // Tasa de aprobación
        $total = array_sum(array_values($stats));
        $stats['approval_rate'] = $total > 0 ? 
            round(($stats['aprobado'] / $total) * 100, 1) : 0;
        
        // Solicitudes del mes
        $sql = "SELECT COUNT(*) as total FROM credit_applications 
                WHERE MONTH(created_at) = MONTH(CURRENT_DATE) 
                AND YEAR(created_at) = YEAR(CURRENT_DATE)";
        $stmt = $this->db->query($sql);
        $result = $stmt->fetch();
        $stats['monthly'] = $result['total'];
        
        return $stats;
    }
    
    /**
     * Enviar email de confirmación
     */
    private function sendConfirmationEmail($applicationId, $data) {
        // Por ahora solo registramos la actividad
        // En producción, implementar envío real de email
        $this->logActivity('credit_application_created', 'credit_application', $applicationId,
            "Nueva solicitud de crédito de {$data['applicant_name']}");
    }
    
    /**
     * Notificar al solicitante sobre el estado
     */
    private function notifyApplicant($applicationId, $status) {
        // Implementar notificación por email
        $this->logActivity('credit_applicant_notified', 'credit_application', $applicationId,
            "Solicitante notificado: $status");
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