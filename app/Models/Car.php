<?php
// app/Models/Car.php

namespace Models;

/**
 * Modelo de Auto/Vehículo
 */
class Car extends Model {
    
    protected $table = 'cars';
    
    protected $fillable = [
        'brand', 'model', 'year', 'price', 'mileage', 'color', 'fuel_type',
        'transmission', 'engine', 'doors', 'seats', 'description', 'features',
        'condition', 'vin', 'stock_number', 'location', 'is_featured',
        'is_available', 'views', 'main_image'
    ];
    
    /**
     * Obtener autos disponibles
     */
    public function getAvailable($filters = []) {
        $sql = "SELECT * FROM {$this->table} WHERE is_available = 1";
        $params = [];
        
        // Aplicar filtros
        if (!empty($filters['brand'])) {
            $sql .= " AND brand = :brand";
            $params['brand'] = $filters['brand'];
        }
        
        if (!empty($filters['min_price'])) {
            $sql .= " AND price >= :min_price";
            $params['min_price'] = $filters['min_price'];
        }
        
        if (!empty($filters['max_price'])) {
            $sql .= " AND price <= :max_price";
            $params['max_price'] = $filters['max_price'];
        }
        
        if (!empty($filters['year'])) {
            $sql .= " AND year = :year";
            $params['year'] = $filters['year'];
        }
        
        // Ordenamiento
        $orderBy = $filters['order_by'] ?? 'created_at DESC';
        $sql .= " ORDER BY $orderBy";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }
    
    /**
     * Obtener autos destacados
     */
    public function getFeatured($limit = 3) {
        $sql = "SELECT * FROM {$this->table} 
                WHERE is_featured = 1 AND is_available = 1 
                ORDER BY created_at DESC 
                LIMIT :limit";
        
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':limit', $limit, \PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }
    
    /**
     * Incrementar vistas
     */
    public function incrementViews($id) {
        $sql = "UPDATE {$this->table} SET views = views + 1 WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute(['id' => $id]);
    }
    
    /**
     * Obtener marcas únicas
     */
    public function getBrands() {
        $sql = "SELECT DISTINCT brand FROM {$this->table} ORDER BY brand";
        $stmt = $this->db->query($sql);
        return array_column($stmt->fetchAll(), 'brand');
    }
}
