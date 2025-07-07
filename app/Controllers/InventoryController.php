<?php
// app/Controllers/InventoryController.php

namespace Controllers;

use Models\Car;
use Models\CarImage;
use Middleware\AuthMiddleware;

/**
 * Controlador de Inventario de Vehículos
 */
class InventoryController extends Controller {
    
    private $carModel;
    private $imageModel;
    
    public function __construct() {
        parent::__construct();
        AuthMiddleware::check();
        
        $this->carModel = new Car();
        $this->imageModel = new CarImage();
    }
    
    /**
     * Listar inventario
     */
    public function index() {
        AuthMiddleware::checkPermission('view_inventory');
        
        $page = $this->input('page', 1);
        $search = $this->input('search', '');
        $brand = $this->input('brand', '');
        $status = $this->input('status', '');
        
        // Construir condiciones de búsqueda
        $conditions = [];
        if ($status === 'available') {
            $conditions['is_available'] = 1;
        } elseif ($status === 'sold') {
            $conditions['is_available'] = 0;
        }
        
        // Búsqueda compleja con SQL personalizado
        if ($search || $brand) {
            $cars = $this->searchCars($search, $brand, $conditions, $page);
        } else {
            $cars = $this->carModel->paginate($page, 20, $conditions, 'created_at DESC');
        }
        
        $brands = $this->carModel->getBrands();
        
        $this->view('dashboard.inventory.index', [
            'title' => 'Inventario - ALARA Admin',
            'cars' => $cars,
            'brands' => $brands,
            'search' => $search,
            'selectedBrand' => $brand,
            'selectedStatus' => $status,
            'user' => currentUser()
        ]);
    }
    
    /**
     * Formulario para agregar vehículo
     */
    public function create() {
        AuthMiddleware::checkPermission('manage_inventory');
        
        $this->view('dashboard.inventory.create', [
            'title' => 'Agregar Vehículo - ALARA Admin',
            'csrf_token' => generateCSRFToken(),
            'user' => currentUser()
        ]);
    }
    
    /**
     * Formulario para editar vehículo
     */
    public function edit($id) {
        AuthMiddleware::checkPermission('manage_inventory');
        
        $car = $this->carModel->find($id);
        
        if (!$car) {
            http_response_code(404);
            $this->view('errors.404');
            exit;
        }
        
        $images = $this->imageModel->getByCarId($id);
        
        $this->view('dashboard.inventory.edit', [
            'title' => 'Editar Vehículo - ALARA Admin',
            'car' => $car,
            'images' => $images,
            'csrf_token' => generateCSRFToken(),
            'user' => currentUser()
        ]);
    }
    
    /**
     * API: Listar vehículos
     */
    public function apiList() {
        AuthMiddleware::checkPermission('view_inventory');
        
        $filters = $this->jsonInput();
        $cars = $this->carModel->getAvailable($filters);
        
        $this->json([
            'success' => true,
            'data' => $cars
        ]);
    }
    
    /**
     * API: Guardar nuevo vehículo
     */
    public function apiStore() {
        AuthMiddleware::checkPermission('manage_inventory');
        
        $data = $this->jsonInput();
        
        // Validar datos requeridos
        $required = ['brand', 'model', 'year', 'price', 'mileage'];
        foreach ($required as $field) {
            if (empty($data[$field])) {
                $this->json([
                    'success' => false,
                    'message' => "El campo $field es requerido"
                ], 400);
            }
        }
        
        // Generar número de stock único
        $data['stock_number'] = $this->generateStockNumber();
        
        // Procesar características (array a JSON)
        if (isset($data['features']) && is_array($data['features'])) {
            $data['features'] = json_encode($data['features']);
        }
        
        // Crear vehículo
        $carId = $this->carModel->create($data);
        
        if (!$carId) {
            $this->json([
                'success' => false,
                'message' => 'Error al guardar el vehículo'
            ], 500);
        }
        
        // Procesar imágenes si se enviaron
        if (!empty($_FILES['images'])) {
            $this->processImages($carId, $_FILES['images']);
        }
        
        $this->json([
            'success' => true,
            'message' => 'Vehículo agregado exitosamente',
            'data' => ['id' => $carId]
        ]);
    }
    
    /**
     * API: Actualizar vehículo
     */
    public function apiUpdate($id) {
        AuthMiddleware::checkPermission('manage_inventory');
        
        $data = $this->jsonInput();
        
        // Verificar que el vehículo existe
        $car = $this->carModel->find($id);
        if (!$car) {
            $this->json([
                'success' => false,
                'message' => 'Vehículo no encontrado'
            ], 404);
        }
        
        // Procesar características
        if (isset($data['features']) && is_array($data['features'])) {
            $data['features'] = json_encode($data['features']);
        }
        
        // Actualizar
        $result = $this->carModel->update($id, $data);
        
        if (!$result) {
            $this->json([
                'success' => false,
                'message' => 'Error al actualizar el vehículo'
            ], 500);
        }
        
        $this->json([
            'success' => true,
            'message' => 'Vehículo actualizado exitosamente'
        ]);
    }
    
    /**
     * API: Eliminar vehículo
     */
    public function apiDelete($id) {
        AuthMiddleware::checkPermission('manage_inventory');
        
        // Verificar que el vehículo existe
        $car = $this->carModel->find($id);
        if (!$car) {
            $this->json([
                'success' => false,
                'message' => 'Vehículo no encontrado'
            ], 404);
        }
        
        // Eliminar imágenes asociadas
        $images = $this->imageModel->getByCarId($id);
        foreach ($images as $image) {
            $this->deleteImageFile($image['filename']);
            $this->imageModel->delete($image['id']);
        }
        
        // Eliminar vehículo
        $result = $this->carModel->delete($id);
        
        if (!$result) {
            $this->json([
                'success' => false,
                'message' => 'Error al eliminar el vehículo'
            ], 500);
        }
        
        $this->json([
            'success' => true,
            'message' => 'Vehículo eliminado exitosamente'
        ]);
    }
    
    /**
     * Buscar vehículos con criterios complejos
     */
    private function searchCars($search, $brand, $conditions, $page) {
        $sql = "SELECT * FROM cars WHERE 1=1";
        $countSql = "SELECT COUNT(*) as total FROM cars WHERE 1=1";
        $params = [];
        
        // Condiciones base
        foreach ($conditions as $key => $value) {
            $sql .= " AND $key = :$key";
            $countSql .= " AND $key = :$key";
            $params[$key] = $value;
        }
        
        // Búsqueda de texto
        if ($search) {
            $sql .= " AND (brand LIKE :search OR model LIKE :search OR description LIKE :search)";
            $countSql .= " AND (brand LIKE :search OR model LIKE :search OR description LIKE :search)";
            $params['search'] = "%$search%";
        }
        
        // Filtro de marca
        if ($brand) {
            $sql .= " AND brand = :brand";
            $countSql .= " AND brand = :brand";
            $params['brand'] = $brand;
        }
        
        // Paginación
        $perPage = 20;
        $offset = ($page - 1) * $perPage;
        $sql .= " ORDER BY created_at DESC LIMIT $perPage OFFSET $offset";
        
        // Ejecutar consultas
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        $data = $stmt->fetchAll();
        
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
     * Generar número de stock único
     */
    private function generateStockNumber() {
        $prefix = 'ALR';
        $year = date('y');
        
        // Obtener el último número
        $sql = "SELECT stock_number FROM cars 
                WHERE stock_number LIKE :prefix 
                ORDER BY id DESC LIMIT 1";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['prefix' => "$prefix$year%"]);
        $result = $stmt->fetch();
        
        if ($result) {
            $lastNumber = intval(substr($result['stock_number'], -4));
            $newNumber = $lastNumber + 1;
        } else {
            $newNumber = 1;
        }
        
        return sprintf("%s%s%04d", $prefix, $year, $newNumber);
    }
    
    /**
     * Procesar y guardar imágenes
     */
    private function processImages($carId, $files) {
        $uploadedImages = [];
        
        for ($i = 0; $i < count($files['name']); $i++) {
            if ($files['error'][$i] === UPLOAD_ERR_OK) {
                $file = [
                    'name' => $files['name'][$i],
                    'type' => $files['type'][$i],
                    'tmp_name' => $files['tmp_name'][$i],
                    'error' => $files['error'][$i],
                    'size' => $files['size'][$i]
                ];
                
                $result = uploadFile($file, 'cars');
                
                if ($result['success']) {
                    $imageData = [
                        'car_id' => $carId,
                        'filename' => $result['filename'],
                        'is_primary' => empty($uploadedImages) ? 1 : 0,
                        'sort_order' => $i
                    ];
                    
                    $this->imageModel->create($imageData);
                    $uploadedImages[] = $result['filename'];
                    
                    // Actualizar imagen principal del auto
                    if ($imageData['is_primary']) {
                        $this->carModel->update($carId, ['main_image' => $result['filename']]);
                    }
                }
            }
        }
        
        return $uploadedImages;
    }
    
    /**
     * Eliminar archivo de imagen
     */
    private function deleteImageFile($filename) {
        $filepath = UPLOADS_PATH . '/cars/' . $filename;
        if (file_exists($filepath)) {
            unlink($filepath);
        }
    }
}