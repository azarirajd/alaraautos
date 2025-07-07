-- Base de datos para ALARA - Sistema de Venta de Autos de Lujo
-- Ejecutar este script en MySQL para crear las tablas necesarias

CREATE DATABASE IF NOT EXISTS alara_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE alara_db;

-- Tabla de usuarios del sistema
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    role ENUM('admin', 'gerencia', 'ventas') DEFAULT 'ventas',
    avatar VARCHAR(255) DEFAULT NULL,
    is_active BOOLEAN DEFAULT TRUE,
    last_login DATETIME DEFAULT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_email (email),
    INDEX idx_role (role)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Tabla de vehículos
CREATE TABLE cars (
    id INT AUTO_INCREMENT PRIMARY KEY,
    stock_number VARCHAR(20) UNIQUE NOT NULL,
    brand VARCHAR(50) NOT NULL,
    model VARCHAR(50) NOT NULL,
    year INT NOT NULL,
    price DECIMAL(12, 2) NOT NULL,
    mileage INT NOT NULL,
    color VARCHAR(30),
    fuel_type ENUM('gasolina', 'diesel', 'hibrido', 'electrico') DEFAULT 'gasolina',
    transmission ENUM('manual', 'automatica', 'cvt') DEFAULT 'automatica',
    engine VARCHAR(50),
    doors INT DEFAULT 4,
    seats INT DEFAULT 5,
    description TEXT,
    features JSON,
    condition ENUM('nuevo', 'seminuevo', 'usado') DEFAULT 'seminuevo',
    vin VARCHAR(17),
    location VARCHAR(100),
    is_featured BOOLEAN DEFAULT FALSE,
    is_available BOOLEAN DEFAULT TRUE,
    views INT DEFAULT 0,
    main_image VARCHAR(255),
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_brand (brand),
    INDEX idx_year (year),
    INDEX idx_price (price),
    INDEX idx_available (is_available),
    INDEX idx_featured (is_featured)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Tabla de imágenes de vehículos
CREATE TABLE car_images (
    id INT AUTO_INCREMENT PRIMARY KEY,
    car_id INT NOT NULL,
    filename VARCHAR(255) NOT NULL,
    caption VARCHAR(255),
    is_primary BOOLEAN DEFAULT FALSE,
    sort_order INT DEFAULT 0,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (car_id) REFERENCES cars(id) ON DELETE CASCADE,
    INDEX idx_car_id (car_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Tabla de leads/prospectos (CRM)
CREATE TABLE leads (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL,
    phone VARCHAR(20),
    message TEXT,
    source ENUM('web', 'whatsapp', 'facebook', 'instagram', 'referido', 'otro') DEFAULT 'web',
    status ENUM('new', 'contacted', 'qualified', 'converted', 'lost') DEFAULT 'new',
    assigned_to INT,
    car_id INT,
    budget DECIMAL(12, 2),
    notes TEXT,
    contacted_at DATETIME,
    converted_at DATETIME,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (assigned_to) REFERENCES users(id) ON DELETE SET NULL,
    FOREIGN KEY (car_id) REFERENCES cars(id) ON DELETE SET NULL,
    INDEX idx_status (status),
    INDEX idx_assigned (assigned_to),
    INDEX idx_created (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Tabla de solicitudes de crédito
CREATE TABLE credit_applications (
    id INT AUTO_INCREMENT PRIMARY KEY,
    applicant_name VARCHAR(100) NOT NULL,
    applicant_email VARCHAR(100) NOT NULL,
    applicant_phone VARCHAR(20) NOT NULL,
    applicant_rfc VARCHAR(13),
    monthly_income DECIMAL(12, 2) NOT NULL,
    employment_type ENUM('empleado_fijo', 'empleado_temporal', 'negocio_propio', 'freelance', 'otro') NOT NULL,
    employment_years INT,
    car_id INT NOT NULL,
    down_payment DECIMAL(12, 2) NOT NULL,
    requested_term INT NOT NULL, -- En meses
    status ENUM('Enviado', 'En Revisión', 'Aprobado', 'Rechazado', 'Documentos Pendientes') DEFAULT 'Enviado',
    reviewed_by INT,
    reviewed_at DATETIME,
    notes TEXT,
    documents JSON, -- Almacena rutas de documentos subidos
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (car_id) REFERENCES cars(id) ON DELETE CASCADE,
    FOREIGN KEY (reviewed_by) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_status (status),
    INDEX idx_created (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Tabla de ventas
CREATE TABLE sales (
    id INT AUTO_INCREMENT PRIMARY KEY,
    car_id INT NOT NULL,
    customer_name VARCHAR(100) NOT NULL,
    customer_email VARCHAR(100),
    customer_phone VARCHAR(20),
    sale_price DECIMAL(12, 2) NOT NULL,
    payment_method ENUM('contado', 'credito', 'financiamiento') NOT NULL,
    salesperson_id INT NOT NULL,
    commission DECIMAL(10, 2),
    sale_date DATE NOT NULL,
    notes TEXT,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (car_id) REFERENCES cars(id),
    FOREIGN KEY (salesperson_id) REFERENCES users(id),
    INDEX idx_sale_date (sale_date),
    INDEX idx_salesperson (salesperson_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Tabla de actividad/logs
CREATE TABLE activity_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    action VARCHAR(100) NOT NULL,
    entity_type VARCHAR(50),
    entity_id INT,
    description TEXT,
    ip_address VARCHAR(45),
    user_agent VARCHAR(255),
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_user (user_id),
    INDEX idx_created (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Tabla de configuración del sitio
CREATE TABLE site_settings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    setting_key VARCHAR(50) UNIQUE NOT NULL,
    setting_value TEXT,
    setting_type ENUM('text', 'number', 'boolean', 'json') DEFAULT 'text',
    description VARCHAR(255),
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Insertar usuario administrador por defecto (contraseña: admin123)
INSERT INTO users (name, email, password, role) VALUES 
('Administrador', 'admin@alara.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin');

-- Insertar configuración inicial
INSERT INTO site_settings (setting_key, setting_value, description) VALUES
('site_name', 'ALARA', 'Nombre del sitio'),
('contact_email', 'contacto@alara.com', 'Email de contacto'),
('contact_phone', '+52 777 123 4567', 'Teléfono de contacto'),
('whatsapp_number', '527771234567', 'Número de WhatsApp'),
('address', 'Av. Principal 123, Temixco, Morelos', 'Dirección física'),
('facebook_url', 'https://facebook.com/alara', 'URL de Facebook'),
('instagram_url', 'https://instagram.com/alara', 'URL de Instagram'),
('business_hours', '{"lunes-viernes": "9:00 - 19:00", "sabado": "10:00 - 14:00", "domingo": "Cerrado"}', 'Horario de atención');

-- Insertar algunos vehículos de ejemplo
INSERT INTO cars (stock_number, brand, model, year, price, mileage, color, fuel_type, transmission, engine, description, features, main_image, is_featured) VALUES
('ALR24001', 'Mercedes-Benz', 'C300', 2022, 850000, 15000, 'Negro', 'gasolina', 'automatica', '2.0L Turbo', 'Mercedes-Benz C300 en excelente estado, un solo dueño.', '["Asientos de cuero", "Techo panorámico", "Sistema de sonido Burmester", "Apple CarPlay", "Android Auto"]', 'mercedes-c300-1.jpg', 1),
('ALR24002', 'BMW', 'X3', 2021, 780000, 25000, 'Blanco', 'gasolina', 'automatica', '2.0L TwinPower', 'BMW X3 xDrive30i con paquete M Sport.', '["Paquete M Sport", "Navegación", "Cámara 360", "Asientos deportivos", "Head-up display"]', 'bmw-x3-1.jpg', 1),
('ALR24003', 'Audi', 'A4', 2023, 720000, 8000, 'Gris', 'gasolina', 'automatica', '2.0L TFSI', 'Audi A4 prácticamente nuevo con garantía de fábrica.', '["Virtual Cockpit", "Matrix LED", "Bang & Olufsen", "Asistente de estacionamiento", "Keyless"]', 'audi-a4-1.jpg', 0);

-- Insertar algunos leads de ejemplo
INSERT INTO leads (name, email, phone, message, source, car_id, budget) VALUES
('Juan Pérez', 'juan.perez@email.com', '777-123-4567', 'Me interesa el Mercedes C300', 'web', 1, 800000),
('María García', 'maria.garcia@email.com', '777-234-5678', 'Quisiera agendar una prueba de manejo del BMW X3', 'whatsapp', 2, 750000),
('Carlos López', 'carlos.lopez@email.com', '777-345-6789', '¿Tienen opciones de financiamiento?', 'facebook', NULL, 600000);

-- Crear vistas útiles

-- Vista de inventario con resumen
CREATE VIEW v_inventory_summary AS
SELECT 
    c.id,
    c.stock_number,
    c.brand,
    c.model,
    c.year,
    c.price,
    c.mileage,
    c.is_available,
    c.is_featured,
    c.views,
    c.main_image,
    COUNT(DISTINCT ci.id) as image_count,
    COUNT(DISTINCT l.id) as lead_count
FROM cars c
LEFT JOIN car_images ci ON c.id = ci.car_id
LEFT JOIN leads l ON c.id = l.car_id
GROUP BY c.id;

-- Vista de leads con información del vehículo
CREATE VIEW v_leads_with_cars AS
SELECT 
    l.*,
    c.brand as car_brand,
    c.model as car_model,
    c.year as car_year,
    c.price as car_price,
    u.name as assigned_to_name
FROM leads l
LEFT JOIN cars c ON l.car_id = c.id
LEFT JOIN users u ON l.assigned_to = u.id;

-- Vista de solicitudes de crédito con detalles
CREATE VIEW v_credit_applications_detail AS
SELECT 
    ca.*,
    c.brand as car_brand,
    c.model as car_model,
    c.year as car_year,
    c.price as car_price,
    u.name as reviewer_name
FROM credit_applications ca
JOIN cars c ON ca.car_id = c.id
LEFT JOIN users u ON ca.reviewed_by = u.id;