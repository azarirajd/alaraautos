<?php
// app/Models/CarImage.php

namespace Models;

/**
 * Modelo de Imágenes de Autos
 */
class CarImage extends Model {
    
    protected $table = 'car_images';
    
    protected $fillable = [
        'car_id', 'filename', 'caption', 'is_primary', 'sort_order'
    ];
    
    /**
     * Obtener imágenes de un auto
     */
    public function getByCarId($carId) {
        return $this->where(['car_id' => $carId], 'sort_order ASC');
    }
    
    /**
     * Establecer imagen principal
     */
    public function setPrimary($carId, $imageId) {
        // Quitar la marca de principal de todas las imágenes del auto
        $sql = "UPDATE {$this->table} SET is_primary = 0 WHERE car_id = :car_id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['car_id' => $carId]);
        
        // Marcar la nueva imagen como principal
        return $this->update($imageId, ['is_primary' => 1]);
    }
}