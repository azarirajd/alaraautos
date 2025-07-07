<?php
// app/routes/api.php

/**
 * Definición de rutas API
 */

return [
    // Autenticación
    '#^/api/auth/login$#' => [
        'method' => 'POST',
        'handler' => 'AuthController@login'
    ],
    
    // Inventario
    '#^/api/inventory$#' => [
        'method' => 'GET',
        'handler' => 'InventoryController@apiList'
    ],
    '#^/api/inventory$#' => [
        'method' => 'POST',
        'handler' => 'InventoryController@apiStore'
    ],
    '#^/api/inventory/(\d+)$#' => [
        'method' => 'PUT',
        'handler' => 'InventoryController@apiUpdate'
    ],
    '#^/api/inventory/(\d+)$#' => [
        'method' => 'DELETE',
        'handler' => 'InventoryController@apiDelete'
    ],
    
    // CRM
    '#^/api/crm/leads$#' => [
        'method' => 'GET',
        'handler' => 'CRMController@apiLeads'
    ],
    '#^/api/crm/leads$#' => [
        'method' => 'POST',
        'handler' => 'CRMController@apiCreateLead'
    ],
    '#^/api/crm/leads/(\d+)$#' => [
        'method' => 'PUT',
        'handler' => 'CRMController@apiUpdateLead'
    ],
    
    // Solicitudes de crédito
    '#^/api/credit-applications$#' => [
        'method' => 'GET',
        'handler' => 'CreditController@apiList'
    ],
    '#^/api/credit-applications$#' => [
        'method' => 'POST',
        'handler' => 'CreditController@apiCreate'
    ],
    '#^/api/credit-applications/(\d+)/status$#' => [
        'method' => 'PUT',
        'handler' => 'CreditController@apiUpdateStatus'
    ],
    
    // Generación de contenido con IA
    '#^/api/content/generate$#' => [
        'method' => 'POST',
        'handler' => 'ContentController@apiGenerate'
    ],
    
    // Usuarios
    '#^/api/users$#' => [
        'method' => 'GET',
        'handler' => 'UserController@apiList'
    ],
    '#^/api/users$#' => [
        'method' => 'POST',
        'handler' => 'UserController@apiCreate'
    ],
    '#^/api/users/(\d+)$#' => [
        'method' => 'PUT',
        'handler' => 'UserController@apiUpdate'
    ],
    '#^/api/users/(\d+)$#' => [
        'method' => 'DELETE',
        'handler' => 'UserController@apiDelete'
    ],
    
    // Estadísticas del dashboard
    '#^/api/dashboard/stats$#' => [
        'method' => 'GET',
        'handler' => 'DashboardController@apiStats'
    ],
    
    // Formulario de contacto
    '#^/api/contact$#' => [
        'method' => 'POST',
        'handler' => 'ContactController@apiSubmit'
    ],
    
    // Calculadora de presupuesto
    '#^/api/budget-calculator$#' => [
        'method' => 'POST',
        'handler' => 'CalculatorController@apiCalculate'
    ],
];