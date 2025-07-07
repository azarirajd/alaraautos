<?php
// app/routes/web.php

/**
 * Definición de rutas web
 */

return [
    // Sitio público
    '#^/$#' => 'static',
    '#^/index\.html$#' => 'static',
    '#^/inventario\.html$#' => 'static',
    '#^/financiamiento\.html$#' => 'static',
    '#^/nosotros\.html$#' => 'static',
    '#^/contacto\.html$#' => 'static',
    '#^/terminos\.html$#' => 'static',
    '#^/aviso-privacidad\.html$#' => 'static',
    
    // Assets estáticos
    '#^/css/.*$#' => 'static',
    '#^/js/.*$#' => 'static',
    '#^/assets/.*$#' => 'static',
    
    // Login
    '#^/login$#' => 'AuthController@loginForm',
    '#^/logout$#' => 'AuthController@logout',
    
    // Dashboard - Panel de administración
    '#^/dashboard$#' => 'DashboardController@index',
    '#^/dashboard/crm$#' => 'CRMController@index',
    '#^/dashboard/inventory$#' => 'InventoryController@index',
    '#^/dashboard/inventory/add$#' => 'InventoryController@create',
    '#^/dashboard/inventory/edit/(\d+)$#' => 'InventoryController@edit',
    '#^/dashboard/credit-applications$#' => 'CreditController@index',
    '#^/dashboard/credit-applications/(\d+)$#' => 'CreditController@show',
    '#^/dashboard/content$#' => 'ContentController@index',
    '#^/dashboard/users$#' => 'UserController@index',
    '#^/dashboard/users/add$#' => 'UserController@create',
    '#^/dashboard/users/edit/(\d+)$#' => 'UserController@edit',
];