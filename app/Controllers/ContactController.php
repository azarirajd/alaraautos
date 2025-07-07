<?php
// app/Controllers/ContactController.php

namespace Controllers;

use Models\Lead;

/**
 * Controlador para el formulario de contacto del sitio público
 */
class ContactController extends Controller {
    
    private $leadModel;
    
    public function __construct() {
        parent::__construct();
        $this->leadModel = new Lead();
    }
    
    /**
     * API: Procesar formulario de contacto
     */
    public function apiSubmit() {
        $data = $this->jsonInput();
        
        // Validar datos requeridos
        if (empty($data['name']) || empty($data['email'])) {
            $this->json([
                'success' => false,
                'message' => 'Por favor complete todos los campos requeridos'
            ], 400);
        }
        
        // Validar email
        if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            $this->json([
                'success' => false,
                'message' => 'Por favor ingrese un email válido'
            ], 400);
        }
        
        // Validar teléfono si se proporciona
        if (!empty($data['phone'])) {
            $data['phone'] = preg_replace('/[^0-9]/', '', $data['phone']);
            if (strlen($data['phone']) < 10) {
                $this->json([
                    'success' => false,
                    'message' => 'Por favor ingrese un teléfono válido'
                ], 400);
            }
        }
        
        // Determinar la fuente según el formulario
        $data['source'] = $data['form_source'] ?? 'web';
        unset($data['form_source']);
        
        // Crear el lead
        try {
            $leadId = $this->leadModel->create($data);
            
            if (!$leadId) {
                throw new \Exception('Error al guardar la información');
            }
            
            // Enviar notificación por email
            $this->sendNotificationEmail($leadId, $data);
            
            // Si el usuario se suscribió al newsletter
            if (!empty($data['newsletter']) && $data['newsletter'] == '1') {
                $this->subscribeToNewsletter($data['email'], $data['name']);
            }
            
            $this->json([
                'success' => true,
                'message' => '¡Gracias por contactarnos! Nos pondremos en contacto contigo pronto.',
                'data' => ['id' => $leadId]
            ]);
            
        } catch (\Exception $e) {
            $this->json([
                'success' => false,
                'message' => 'Hubo un error al procesar tu solicitud. Por favor intenta nuevamente.'
            ], 500);
        }
    }
    
    /**
     * Enviar email de notificación
     */
    private function sendNotificationEmail($leadId, $data) {
        // Obtener configuración de email
        $to = $this->getSettingValue('contact_email', 'contacto@alara.com');
        $subject = 'Nuevo contacto desde el sitio web - ALARA';
        
        $message = "Se ha recibido un nuevo contacto desde el sitio web:\n\n";
        $message .= "Nombre: {$data['name']}\n";
        $message .= "Email: {$data['email']}\n";
        $message .= "Teléfono: " . ($data['phone'] ?? 'No proporcionado') . "\n";
        $message .= "Mensaje: " . ($data['message'] ?? 'No proporcionado') . "\n\n";
        $message .= "Fuente: {$data['source']}\n";
        $message .= "Fecha: " . date('d/m/Y H:i:s') . "\n\n";
        $message .= "Ver en el panel: " . SITE_URL . "/dashboard/crm";
        
        $headers = "From: " . MAIL_FROM . "\r\n";
        $headers .= "Reply-To: {$data['email']}\r\n";
        $headers .= "Content-Type: text/plain; charset=UTF-8\r\n";
        
        // Enviar email
        @mail($to, $subject, $message, $headers);
        
        // También registrar en el log
        $this->logActivity('contact_form_submitted', 'lead', $leadId, 
            "Nuevo contacto: {$data['name']} ({$data['email']})");
    }
    
    /**
     * Suscribir al newsletter (placeholder)
     */
    private function subscribeToNewsletter($email, $name) {
        // Aquí se integraría con Mailchimp, SendGrid, etc.
        // Por ahora solo guardamos en la base de datos
        $sql = "INSERT INTO newsletter_subscribers (email, name, subscribed_at) 
                VALUES (:email, :name, NOW())
                ON DUPLICATE KEY UPDATE name = :name";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['email' => $email, 'name' => $name]);
    }
    
    /**
     * Obtener valor de configuración
     */
    private function getSettingValue($key, $default = null) {
        $sql = "SELECT setting_value FROM site_settings WHERE setting_key = :key";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['key' => $key]);
        $result = $stmt->fetch();
        
        return $result ? $result['setting_value'] : $default;
    }
    
    /**
     * Registrar actividad
     */
    private function logActivity($action, $entityType, $entityId, $description) {
        $sql = "INSERT INTO activity_logs (action, entity_type, entity_id, description, ip_address, user_agent) 
                VALUES (:action, :entity_type, :entity_id, :description, :ip, :agent)";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            'action' => $action,
            'entity_type' => $entityType,
            'entity_id' => $entityId,
            'description' => $description,
            'ip' => $_SERVER['REMOTE_ADDR'] ?? '',
            'agent' => $_SERVER['HTTP_USER_AGENT'] ?? ''
        ]);
    }
}