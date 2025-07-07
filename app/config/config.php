<?php
// app/config/config.php

/**
 * Configuración general de la aplicación
 */

// Configuración del sitio
define('SITE_NAME', 'ALARA');
define('SITE_URL', 'http://localhost');
define('SITE_DESCRIPTION', 'Venta de Autos de Lujo Seminuevos');

// Configuración de la base de datos
define('DB_HOST', 'localhost');
define('DB_NAME', 'alara_db');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_CHARSET', 'utf8mb4');

// Configuración de seguridad
define('SECRET_KEY', 'tu_clave_secreta_aqui_cambiar_en_produccion');
define('SESSION_LIFETIME', 3600); // 1 hora

// Configuración de email
define('MAIL_FROM', 'noreply@alara.com');
define('MAIL_FROM_NAME', 'ALARA');

// Configuración de uploads
define('MAX_FILE_SIZE', 5 * 1024 * 1024); // 5MB
define('ALLOWED_IMAGE_TYPES', ['jpg', 'jpeg', 'png', 'webp']);

// Modo de desarrollo
define('DEBUG_MODE', true);

// Zona horaria
date_default_timezone_set('America/Mexico_City');