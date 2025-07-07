<?php
// app/Controllers/PublicController.php

namespace Controllers;

use Models\Car;

/**
 * Controlador para páginas públicas dinámicas
 */
class PublicController extends Controller {
    
    private $carModel;
    
    public function __construct() {
        parent::__construct();
        $this->carModel = new Car();
    }
    
    /**
     * API: Obtener inventario filtrado para el sitio público
     */
    public function apiInventory() {
        $filters = $this->jsonInput();
        
        // Aplicar filtros
        $cars = $this->carModel->getAvailable($filters);
        
        // Incrementar contador de vistas si se solicita un auto específico
        if (!empty($filters['id'])) {
            $this->carModel->incrementViews($filters['id']);
        }
        
        // Formatear para el frontend
        foreach ($cars as &$car) {
            $car['formatted_price'] = formatPrice($car['price']);
            $car['formatted_mileage'] = number_format($car['mileage']) . ' km';
            
            // Decodificar características
            if ($car['features']) {
                $car['features'] = json_decode($car['features'], true);
            }
        }
        
        $this->json([
            'success' => true,
            'data' => $cars,
            'total' => count($cars)
        ]);
    }
    
    /**
     * API: Obtener marcas disponibles
     */
    public function apiBrands() {
        $brands = $this->carModel->getBrands();
        
        $this->json([
            'success' => true,
            'data' => $brands
        ]);
    }
    
    /**
     * API: Obtener vehículos destacados
     */
    public function apiFeatured() {
        $limit = $this->input('limit', 3);
        $featured = $this->carModel->getFeatured($limit);
        
        // Formatear
        foreach ($featured as &$car) {
            $car['formatted_price'] = formatPrice($car['price']);
            $car['formatted_mileage'] = number_format($car['mileage']) . ' km';
        }
        
        $this->json([
            'success' => true,
            'data' => $featured
        ]);
    }
    
    /**
     * API: Obtener detalles de un vehículo
     */
    public function apiVehicleDetails($id) {
        $car = $this->carModel->find($id);
        
        if (!$car || !$car['is_available']) {
            $this->json([
                'success' => false,
                'message' => 'Vehículo no encontrado'
            ], 404);
        }
        
        // Incrementar vistas
        $this->carModel->incrementViews($id);
        
        // Obtener imágenes
        $sql = "SELECT * FROM car_images WHERE car_id = :id ORDER BY sort_order";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['id' => $id]);
        $images = $stmt->fetchAll();
        
        // Formatear datos
        $car['formatted_price'] = formatPrice($car['price']);
        $car['formatted_mileage'] = number_format($car['mileage']) . ' km';
        $car['features'] = json_decode($car['features'], true) ?? [];
        $car['images'] = $images;
        
        // Obtener vehículos similares
        $similar = $this->getSimilarVehicles($car);
        
        $this->json([
            'success' => true,
            'data' => [
                'vehicle' => $car,
                'similar' => $similar
            ]
        ]);
    }
    
    /**
     * Obtener vehículos similares
     */
    private function getSimilarVehicles($car) {
        $sql = "SELECT id, brand, model, year, price, mileage, main_image
                FROM cars 
                WHERE is_available = 1 
                AND id != :id
                AND (
                    brand = :brand 
                    OR ABS(price - :price) < :price_range
                    OR ABS(year - :year) <= 2
                )
                ORDER BY 
                    CASE WHEN brand = :brand THEN 0 ELSE 1 END,
                    ABS(price - :price)
                LIMIT 4";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            'id' => $car['id'],
            'brand' => $car['brand'],
            'price' => $car['price'],
            'price_range' => $car['price'] * 0.2, // 20% de diferencia
            'year' => $car['year']
        ]);
        
        $similar = $stmt->fetchAll();
        
        // Formatear
        foreach ($similar as &$vehicle) {
            $vehicle['formatted_price'] = formatPrice($vehicle['price']);
            $vehicle['formatted_mileage'] = number_format($vehicle['mileage']) . ' km';
        }
        
        return $similar;
    }
}