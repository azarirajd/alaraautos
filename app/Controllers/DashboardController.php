<?php
// app/Controllers/DashboardController.php

namespace Controllers;

use Models\Car;
use Models\Lead;
use Models\CreditApplication;
use Models\User;
use Middleware\AuthMiddleware;

/**
 * Controlador del Dashboard
 */
class DashboardController extends Controller {
    
    private $carModel;
    private $leadModel;
    private $creditModel;
    private $userModel;
    
    public function __construct() {
        parent::__construct();
        AuthMiddleware::check();
        
        $this->carModel = new Car();
        $this->leadModel = new Lead();
        $this->creditModel = new CreditApplication();
        $this->userModel = new User();
    }
    
    /**
     * Página principal del dashboard
     */
    public function index() {
        $stats = $this->getStats();
        $recentActivities = $this->getRecentActivities();
        $salesChart = $this->getSalesChartData();
        
        $this->view('dashboard.index', [
            'title' => 'Dashboard - ALARA Admin',
            'stats' => $stats,
            'activities' => $recentActivities,
            'salesChart' => $salesChart,
            'user' => currentUser()
        ]);
    }
    
    /**
     * API: Obtener estadísticas
     */
    public function apiStats() {
        AuthMiddleware::check();
        
        $stats = $this->getStats();
        $this->json(['success' => true, 'data' => $stats]);
    }
    
    /**
     * Obtener estadísticas generales
     */
    private function getStats() {
        // Estadísticas de inventario
        $totalCars = $this->carModel->count();
        $availableCars = $this->carModel->count(['is_available' => 1]);
        
        // Valor total del inventario
        $sql = "SELECT SUM(price) as total FROM cars WHERE is_available = 1";
        $stmt = $this->db->query($sql);
        $result = $stmt->fetch();
        $inventoryValue = $result['total'] ?? 0;
        
        // Estadísticas de leads
        $totalLeads = $this->leadModel->count();
        $newLeads = $this->leadModel->count(['status' => 'new']);
        $contactedLeads = $this->leadModel->count(['status' => 'contacted']);
        
        // Estadísticas de créditos
        $totalApplications = $this->creditModel->count();
        $pendingApplications = $this->creditModel->count(['status' => 'En Revisión']);
        $approvedApplications = $this->creditModel->count(['status' => 'Aprobado']);
        
        // Ventas del mes
        $sql = "SELECT COUNT(*) as total, SUM(sale_price) as revenue 
                FROM sales 
                WHERE MONTH(sale_date) = MONTH(CURRENT_DATE) 
                AND YEAR(sale_date) = YEAR(CURRENT_DATE)";
        $stmt = $this->db->query($sql);
        $salesData = $stmt->fetch();
        
        return [
            'inventory' => [
                'total' => $totalCars,
                'available' => $availableCars,
                'value' => $inventoryValue
            ],
            'leads' => [
                'total' => $totalLeads,
                'new' => $newLeads,
                'contacted' => $contactedLeads,
                'conversion_rate' => $totalLeads > 0 ? 
                    round(($contactedLeads / $totalLeads) * 100, 1) : 0
            ],
            'credit' => [
                'total' => $totalApplications,
                'pending' => $pendingApplications,
                'approved' => $approvedApplications,
                'approval_rate' => $totalApplications > 0 ? 
                    round(($approvedApplications / $totalApplications) * 100, 1) : 0
            ],
            'sales' => [
                'monthly_count' => $salesData['total'] ?? 0,
                'monthly_revenue' => $salesData['revenue'] ?? 0
            ]
        ];
    }
    
    /**
     * Obtener actividades recientes
     */
    private function getRecentActivities() {
        $activities = [];
        
        // Últimos leads
        $sql = "SELECT 'lead' as type, name as title, created_at, 
                'Nuevo lead registrado' as description 
                FROM leads 
                ORDER BY created_at DESC LIMIT 5";
        $stmt = $this->db->query($sql);
        $leads = $stmt->fetchAll();
        
        // Últimas solicitudes de crédito
        $sql = "SELECT 'credit' as type, applicant_name as title, created_at,
                CONCAT('Solicitud de crédito: ', status) as description
                FROM credit_applications 
                ORDER BY created_at DESC LIMIT 5";
        $stmt = $this->db->query($sql);
        $credits = $stmt->fetchAll();
        
        // Combinar y ordenar por fecha
        $activities = array_merge($leads, $credits);
        usort($activities, function($a, $b) {
            return strtotime($b['created_at']) - strtotime($a['created_at']);
        });
        
        return array_slice($activities, 0, 10);
    }
    
    /**
     * Obtener datos para gráfico de ventas
     */
    private function getSalesChartData() {
        $sql = "SELECT 
                    DATE_FORMAT(sale_date, '%Y-%m') as month,
                    COUNT(*) as sales,
                    SUM(sale_price) as revenue
                FROM sales 
                WHERE sale_date >= DATE_SUB(CURRENT_DATE, INTERVAL 6 MONTH)
                GROUP BY month
                ORDER BY month";
        
        $stmt = $this->db->query($sql);
        $data = $stmt->fetchAll();
        
        $labels = [];
        $salesData = [];
        $revenueData = [];
        
        foreach ($data as $row) {
            $labels[] = date('M Y', strtotime($row['month'] . '-01'));
            $salesData[] = $row['sales'];
            $revenueData[] = $row['revenue'];
        }
        
        return [
            'labels' => $labels,
            'datasets' => [
                [
                    'label' => 'Ventas',
                    'data' => $salesData,
                    'borderColor' => 'rgb(75, 192, 192)',
                    'tension' => 0.1
                ],
                [
                    'label' => 'Ingresos',
                    'data' => $revenueData,
                    'borderColor' => 'rgb(255, 99, 132)',
                    'tension' => 0.1,
                    'yAxisID' => 'y1'
                ]
            ]
        ];
    }
}