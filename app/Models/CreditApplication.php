<?php
// app/Models/CreditApplication.php

namespace Models;

/**
 * Modelo de Solicitud de Crédito
 */
class CreditApplication extends Model {
    
    protected $table = 'credit_applications';
    
    protected $fillable = [
        'applicant_name', 'applicant_email', 'applicant_phone', 'applicant_rfc',
        'monthly_income', 'employment_type', 'employment_years', 'car_id',
        'down_payment', 'requested_term', 'status', 'reviewed_by',
        'reviewed_at', 'notes', 'documents'
    ];
    
    /**
     * Obtener solicitudes por estado
     */
    public function getByStatus($status) {
        return $this->where(['status' => $status], 'created_at DESC');
    }
    
    /**
     * Actualizar estado de solicitud
     */
    public function updateStatus($id, $status, $reviewerId = null, $notes = null) {
        $data = ['status' => $status];
        
        if ($reviewerId) {
            $data['reviewed_by'] = $reviewerId;
            $data['reviewed_at'] = date('Y-m-d H:i:s');
        }
        
        if ($notes) {
            $data['notes'] = $notes;
        }
        
        return $this->update($id, $data);
    }
    
    /**
     * Calcular score crediticio básico
     */
    public function calculateCreditScore($application) {
        $score = 0;
        
        // Ingreso mensual
        if ($application['monthly_income'] >= 50000) $score += 30;
        elseif ($application['monthly_income'] >= 30000) $score += 20;
        elseif ($application['monthly_income'] >= 15000) $score += 10;
        
        // Tipo de empleo
        if ($application['employment_type'] == 'empleado_fijo') $score += 20;
        elseif ($application['employment_type'] == 'negocio_propio') $score += 15;
        
        // Años de empleo
        if ($application['employment_years'] >= 5) $score += 20;
        elseif ($application['employment_years'] >= 2) $score += 10;
        
        // Enganche
        $carPrice = 500000; // Precio promedio, se debe obtener del auto real
        $downPaymentPercent = ($application['down_payment'] / $carPrice) * 100;
        
        if ($downPaymentPercent >= 30) $score += 30;
        elseif ($downPaymentPercent >= 20) $score += 20;
        elseif ($downPaymentPercent >= 10) $score += 10;
        
        return $score;
    }
}