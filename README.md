# ALARA - Sistema de Venta de Autos de Lujo (PHP)

Sistema web completo para la gestión de venta de autos de lujo seminuevos, migrado de Next.js a PHP puro con arquitectura MVC.

# Estructura Completa del Proyecto ALARA en PHP

```
alara-php/
│
├── index.php                    # Punto de entrada principal
├── .htaccess                    # Configuración de Apache
├── README.md                    # Documentación del proyecto
│
├── app/                         # Código de la aplicación
│   ├── config/                  # Configuración
│   │   ├── config.php          # Configuración general
│   │   └── database.php        # Clase de conexión a DB
│   │
│   ├── Controllers/            # Controladores MVC
│   │   ├── Controller.php      # Controlador base
│   │   ├── AuthController.php  # Autenticación
│   │   ├── DashboardController.php
│   │   ├── InventoryController.php
│   │   ├── CRMController.php
│   │   ├── CreditController.php
│   │   ├── ContentController.php
│   │   ├── UserController.php
│   │   ├── ContactController.php
│   │   ├── CalculatorController.php
│   │   └── PublicController.php
│   │
│   ├── Models/                 # Modelos de datos
│   │   ├── Model.php          # Modelo base
│   │   ├── User.php
│   │   ├── Car.php
│   │   ├── Lead.php
│   │   ├── CreditApplication.php
│   │   └── CarImage.php
│   │
│   ├── views/                  # Vistas
│   │   ├── layouts/           # Layouts
│   │   │   └── dashboard.php
│   │   ├── auth/              # Vistas de autenticación
│   │   │   └── login.php
│   │   ├── dashboard/         # Vistas del dashboard
│   │   │   ├── index.php      # Panel principal
│   │   │   ├── inventory/     # Inventario
│   │   │   │   ├── index.php
│   │   │   │   ├── create.php
│   │   │   │   └── edit.php
│   │   │   ├── crm/          # CRM
│   │   │   │   └── index.php
│   │   │   ├── credit/        # Créditos
│   │   │   │   ├── index.php
│   │   │   │   └── show.php
│   │   │   ├── content/       # Contenido
│   │   │   │   └── index.php
│   │   │   └── users/         # Usuarios
│   │   │       ├── index.php
│   │   │       ├── create.php
│   │   │       └── edit.php
│   │   └── errors/            # Páginas de error
│   │       ├── 404.php
│   │       └── 403.php
│   │
│   ├── middleware/            # Middleware
│   │   └── AuthMiddleware.php
│   │
│   ├── helpers/               # Funciones auxiliares
│   │   └── functions.php
│   │
│   └── routes/                # Definición de rutas
│       ├── web.php           # Rutas web
│       └── api.php           # Rutas API
│
├── public/                    # Archivos públicos accesibles
│   ├── static/               # Sitio web estático (HTML original)
│   │   ├── index.html
│   │   ├── inventario.html
│   │   ├── financiamiento.html
│   │   ├── nosotros.html
│   │   ├── contacto.html
│   │   ├── terminos.html
│   │   └── aviso-privacidad.html
│   │
│   ├── css/                  # Hojas de estilo
│   │   ├── styles.css       # Estilos del sitio público
│   │   └── admin.css        # Estilos del panel admin
│   │
│   ├── js/                   # JavaScript
│   │   ├── scripts.js       # JS del sitio público
│   │   └── admin.js         # JS del panel admin
│   │
│   ├── assets/              # Recursos estáticos
│   │   ├── logo.svg
│   │   ├── logo-white.svg
│   │   ├── hero-bg.jpg
│   │   └── ...
│   │
│   └── uploads/             # Archivos subidos (con permisos de escritura)
│       ├── cars/           # Imágenes de vehículos
│       ├── documents/      # Documentos de crédito
│       └── temp/          # Archivos temporales
│
├── database/                # Scripts de base de datos
│   ├── schema.sql          # Estructura de tablas
│   ├── migrations/         # Migraciones (opcional)
│   └── seeds/             # Datos de prueba (opcional)
│
└── logs/                   # Logs de la aplicación
    ├── php_errors.log
    └── app.log
```

## Archivos Adicionales Importantes

### 1. Archivo de entorno (.env.example)

```env
# Configuración de Base de Datos
DB_HOST=localhost
DB_NAME=alara_db
DB_USER=root
DB_PASS=
DB_CHARSET=utf8mb4

# Configuración de la Aplicación
APP_NAME=ALARA
APP_URL=http://localhost
APP_ENV=development
DEBUG_MODE=true
SECRET_KEY=tu_clave_secreta_aqui

# Configuración de Email
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USER=tu-email@gmail.com
MAIL_PASS=tu-contraseña
MAIL_FROM=noreply@alara.com
MAIL_FROM_NAME=ALARA

# APIs Externas (opcional)
OPENAI_API_KEY=
CLAUDE_API_KEY=
GOOGLE_MAPS_KEY=

# WhatsApp
WHATSAPP_NUMBER=521234567890
```

### 2. Integración con el sitio público (public/static/js/api-integration.js)

```javascript
// Integración del sitio estático con las APIs PHP

// Configuración base
const API_BASE = '/api';

// Formulario de contacto
document.addEventListener('DOMContentLoaded', function() {
    const contactForm = document.getElementById('contact-form');
    if (contactForm) {
        contactForm.addEventListener('submit', async (e) => {
            e.preventDefault();
            
            const formData = new FormData(contactForm);
            const data = Object.fromEntries(formData);
            
            try {
                const response = await fetch(`${API_BASE}/contact`, {
                    method: 'POST',
                    headers: {'Content-Type': 'application/json'},
                    body: JSON.stringify(data)
                });
                
                const result = await response.json();
                
                if (result.success) {
                    alert('¡Gracias por contactarnos! Nos comunicaremos contigo pronto.');
                    contactForm.reset();
                } else {
                    alert(result.message || 'Error al enviar el formulario');
                }
            } catch (error) {
                alert('Error de conexión. Por favor intenta nuevamente.');
            }
        });
    }
    
    // Cargar inventario dinámicamente
    const inventoryGrid = document.getElementById('inventory-grid');
    if (inventoryGrid) {
        loadInventory();
    }
});

// Cargar inventario
async function loadInventory(filters = {}) {
    try {
        const response = await fetch(`${API_BASE}/inventory/public`, {
            method: 'POST',
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify(filters)
        });
        
        const result = await response.json();
        
        if (result.success) {
            displayVehicles(result.data);
        }
    } catch (error) {
        console.error('Error al cargar inventario:', error);
    }
}

// Mostrar vehículos
function displayVehicles(vehicles) {
    const grid = document.getElementById('inventory-grid');
    grid.innerHTML = '';
    
    vehicles.forEach(car => {
        const card = document.createElement('div');
        card.className = 'car-card';
        card.innerHTML = `
            <div class="car-image">
                <img src="/uploads/cars/${car.main_image || 'placeholder.jpg'}" 
                     alt="${car.brand} ${car.model}">
                ${car.is_featured ? '<span class="badge-featured">Destacado</span>' : ''}
            </div>
            <div class="car-info">
                <h3>${car.brand} ${car.model}</h3>
                <p class="car-year">${car.year}</p>
                <p class="car-price">${car.formatted_price}</p>
                <p class="car-mileage">${car.formatted_mileage}</p>
                <button class="btn-primary" onclick="viewVehicle(${car.id})">
                    Ver Detalles
                </button>
            </div>
        `;
        grid.appendChild(card);
    });
}

// Ver detalles del vehículo
function viewVehicle(id) {
    window.location.href = `/vehiculo.html?id=${id}`;
}

// Calculadora de financiamiento
async function calculateFinancing() {
    const budget = document.getElementById('budget').value;
    const downPayment = document.getElementById('down_payment').value;
    const term = document.getElementById('term').value;
    
    try {
        const response = await fetch(`${API_BASE}/budget-calculator`, {
            method: 'POST',
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify({
                budget: parseFloat(budget),
                down_payment: parseFloat(downPayment),
                term: parseInt(term)
            })
        });
        
        const result = await response.json();
        
        if (result.success) {
            displayCalculatorResults(result.data);
        } else {
            alert(result.message);
        }
    } catch (error) {
        alert('Error al calcular. Por favor intenta nuevamente.');
    }
}
```

### 3. Script de instalación (install.php)

```php
<?php
// install.php - Script de instalación inicial

// Verificar requisitos
$errors = [];

// PHP Version
if (version_compare(PHP_VERSION, '7.4.0', '<')) {
    $errors[] = 'PHP 7.4 o superior es requerido';
}

// Extensiones requeridas
$required_extensions = ['pdo', 'pdo_mysql', 'json', 'fileinfo'];
foreach ($required_extensions as $ext) {
    if (!extension_loaded($ext)) {
        $errors[] = "La extensión PHP '$ext' es requerida";
    }
}

// Verificar permisos de escritura
$writable_dirs = ['public/uploads', 'public/uploads/cars', 'public/uploads/documents', 'logs'];
foreach ($writable_dirs as $dir) {
    if (!is_writable($dir)) {
        $errors[] = "El directorio '$dir' debe tener permisos de escritura";
    }
}

if (!empty($errors)) {
    echo "<h2>Errores de instalación:</h2>";
    echo "<ul>";
    foreach ($errors as $error) {
        echo "<li>$error</li>";
    }
    echo "</ul>";
    exit;
}

// Si no hay errores, proceder con la instalación
echo "<h2>Instalación de ALARA</h2>";
echo "<p>Todos los requisitos han sido verificados. El sistema está listo para ser configurado.</p>";
echo "<p>Por favor:</p>";
echo "<ol>";
echo "<li>Crea la base de datos ejecutando el archivo database/schema.sql</li>";
echo "<li>Copia .env.example a .env y configura tus credenciales</li>";
echo "<li>Asegúrate de que Apache tenga mod_rewrite habilitado</li>";
echo "<li>Accede a /login con las credenciales: admin@alara.com / admin123</li>";
echo "</ol>";
echo "<p><strong>IMPORTANTE:</strong> Elimina este archivo después de la instalación.</p>";
```

## Notas de Migración

### Cambios principales respecto a Next.js:

1. **Routing**: De Next.js App Router a sistema MVC con regex en PHP
2. **Componentes**: De React a plantillas PHP con includes
3. **Estado**: De React hooks a sesiones PHP y JavaScript vanilla
4. **API**: De Next.js API routes a controladores PHP con JSON
5. **Base de datos**: De Firebase a MySQL (aunque se puede mantener Firebase con el SDK de PHP)
6. **Autenticación**: De NextAuth a sistema de sesiones PHP
7. **Estilos**: Se mantiene Tailwind CSS y estilos personalizados

### Funcionalidades migradas:

✅ Sitio web público completo  
✅ Panel de administración  
✅ Sistema de autenticación y roles  
✅ Gestión de inventario con imágenes  
✅ CRM para leads  
✅ Sistema de solicitudes de crédito  
✅ Generador de contenido con IA  
✅ Gestión de usuarios  
✅ API REST para integraciones  
✅ Formularios de contacto  
✅ Calculadora de financiamiento  

### Pendientes opcionales:

- Sistema de notificaciones por email (PHPMailer)
- Integración con WhatsApp Business API
- Sistema de reportes PDF
- Dashboard analytics más avanzado
- Sistema de backups automáticos
- API para aplicación móvil# alaraautos
