<?php
// app/Models/Lead.php

namespace Models;

/**
 * Modelo de Lead/Prospecto (CRM)
 */
class Lead extends Model {
    
    protected $table = 'leads';
    
    protected $fillable = [
        'name', 'email', 'phone', 'message', 'source', 'status',
        'assigned_to', 'car_id', 'budget', 'notes', 'contacted_at',
        'converted_at'
    ];
    
    /**
     * Obtener leads por estado
     */
    public function getByStatus($status) {
        return $this->where(['status' => $status], 'created_at DESC');
    }
    
    /**
     * Obtener leads asignados a un usuario
     */
    public function getAssignedTo($userId) {
        return $this->where(['assigned_to' => $userId], 'created_at DESC');
    }
    
    /**
     * Marcar como contactado
     */
    public function markAsContacted($id) {
        return $this->update($id, [
            'status' => 'contacted',
            'contacted_at' => date('Y-m-d H:i:s')
        ]);
    }
    
    /**
     * Convertir lead a cliente
     */
    public function convert($id) {
        return $this->update($id, [
            'status' => 'converted',
            'converted_at' => date('Y-m-d H:i:s')
        ]);
    }
}