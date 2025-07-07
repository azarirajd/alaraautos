<?php
// app/Controllers/CalculatorController.php

namespace Controllers;

/**
 * Controlador para la calculadora de presupuesto
 */
class CalculatorController extends Controller {
    
    /**
     * API: Calcular presupuesto y opciones de financiamiento
     */
    public function apiCalculate() {
        $data = $this->jsonInput();
        
        // Validar entrada
        if (empty($data['budget']) || !is_numeric($data['budget'])) {
            $this->json([
                'success' => false,
                'message' => 'Por favor ingrese un presupuesto válido'
            ], 400);
        }
        
        $budget = floatval($data['budget']);
        $downPaymentPercent = floatval($data['down_payment'] ?? 20);
        $term = intval($data['term'] ?? 48); // meses
        
        // Validar rangos
        if ($budget < 100000) {
            $this->json([
                'success' => false,
                'message' => 'El presupuesto mínimo es de $100,000 MXN'
            ], 400);
        }
        
        if ($downPaymentPercent < 10 || $downPaymentPercent > 50) {
            $this->json([
                'success' => false,
                'message' => 'El enganche debe estar entre 10% y 50%'
            ], 400);
        }
        
        if (!in_array($term, [12, 24, 36, 48, 60])) {
            $this->json([
                'success' => false,
                'message' => 'Plazo de financiamiento inválido'
            ], 400);
        }
        
        // Calcular financiamiento
        $calculations = $this->calculateFinancing($budget, $downPaymentPercent, $term);
        
        // Buscar vehículos que coincidan con el presupuesto
        $vehicles = $this->findVehiclesInBudget($budget);
        
        $this->json([
            'success' => true,
            'data' => [
                'calculations' => $calculations,
                'vehicles' => $vehicles,
                'recommendations' => $this->getRecommendations($budget, $calculations)
            ]
        ]);
    }
    
    /**
     * Calcular detalles de financiamiento
     */
    private function calculateFinancing($totalPrice, $downPaymentPercent, $termMonths) {
        $downPayment = $totalPrice * ($downPaymentPercent / 100);
        $loanAmount = $totalPrice - $downPayment;
        
        // Tasas de interés aproximadas (esto debería venir de configuración)
        $interestRates = [
            12 => 12.5,  // 12.5% anual para 12 meses
            24 => 13.5,  // 13.5% anual para 24 meses
            36 => 14.5,  // 14.5% anual para 36 meses
            48 => 15.5,  // 15.5% anual para 48 meses
            60 => 16.5   // 16.5% anual para 60 meses
        ];
        
        $annualRate = $interestRates[$termMonths] ?? 15;
        $monthlyRate = $annualRate / 12 / 100;
        
        // Calcular pago mensual usando la fórmula de amortización
        if ($monthlyRate > 0) {
            $monthlyPayment = $loanAmount * 
                ($monthlyRate * pow(1 + $monthlyRate, $termMonths)) / 
                (pow(1 + $monthlyRate, $termMonths) - 1);
        } else {
            $monthlyPayment = $loanAmount / $termMonths;
        }
        
        $totalInterest = ($monthlyPayment * $termMonths) - $loanAmount;
        $totalPayment = $downPayment + ($monthlyPayment * $termMonths);
        
        return [
            'total_price' => $totalPrice,
            'down_payment' => $downPayment,
            'down_payment_percent' => $downPaymentPercent,
            'loan_amount' => $loanAmount,
            'term_months' => $termMonths,
            'interest_rate' => $annualRate,
            'monthly_payment' => round($monthlyPayment, 2),
            'total_interest' => round($totalInterest, 2),
            'total_payment' => round($totalPayment, 2)
        ];
    }
    
    /**
     * Buscar vehículos dentro del presupuesto
     */
    private function findVehiclesInBudget($budget) {
        // Rango de búsqueda: -10% a +5% del presupuesto
        $minPrice = $budget * 0.9;
        $maxPrice = $budget * 1.05;
        
        $sql = "SELECT id, brand, model, year, price, mileage, main_image, is_featured
                FROM cars 
                WHERE is_available = 1 
                AND price BETWEEN :min_price AND :max_price
                ORDER BY ABS(price - :budget) ASC
                LIMIT 6";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            'min_price' => $minPrice,
            'max_price' => $maxPrice,
            'budget' => $budget
        ]);
        
        $vehicles = $stmt->fetchAll();
        
        // Formatear para la respuesta
        foreach ($vehicles as &$vehicle) {
            $vehicle['formatted_price'] = '$' . number_format($vehicle['price'], 0, '.', ',');
            $vehicle['formatted_mileage'] = number_format($vehicle['mileage']) . ' km';
            $vehicle['match_percentage'] = 100 - round(abs($vehicle['price'] - $budget) / $budget * 100);
        }
        
        return $vehicles;
    }
    
    /**
     * Generar recomendaciones basadas en el presupuesto
     */
    private function getRecommendations($budget, $calculations) {
        $recommendations = [];
        
        // Recomendación sobre el pago mensual
        $monthlyIncome = $budget / 5; // Asumiendo que el presupuesto es 5x el ingreso mensual
        $recommendedPayment = $monthlyIncome * 0.3; // 30% del ingreso para auto
        
        if ($calculations['monthly_payment'] > $recommendedPayment) {
            $recommendations[] = [
                'type' => 'warning',
                'message' => 'El pago mensual supera el 30% recomendado de tu ingreso. Considera aumentar el enganche o extender el plazo.'
            ];
        }
        
        // Recomendación sobre el plazo
        if ($calculations['term_months'] > 48) {
            $recommendations[] = [
                'type' => 'info',
                'message' => 'Un plazo mayor a 48 meses incrementa significativamente los intereses. Si es posible, considera un plazo menor.'
            ];
        }
        
        // Recomendación sobre el enganche
        if ($calculations['down_payment_percent'] < 20) {
            $recommendations[] = [
                'type' => 'info',
                'message' => 'Un enganche mayor al 20% puede ayudarte a obtener mejores tasas de interés y reducir el pago mensual.'
            ];
        }
        
        // Recomendación positiva
        if ($calculations['down_payment_percent'] >= 30 && $calculations['term_months'] <= 36) {
            $recommendations[] = [
                'type' => 'success',
                'message' => '¡Excelente plan de financiamiento! Con este enganche y plazo obtendrás las mejores condiciones.'
            ];
        }
        
        return $recommendations;
    }
}