// public/js/admin.js - JavaScript del panel de administración

document.addEventListener('DOMContentLoaded', function() {
    // Inicializar tooltips de Bootstrap
    const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });
    
    // Manejar formularios AJAX
    initAjaxForms();
    
    // Inicializar editores de texto enriquecido si existen
    initRichTextEditors();
    
    // Manejar carga de imágenes con preview
    initImageUploads();
    
    // Confirmación para acciones peligrosas
    initDeleteConfirmations();
});

/**
 * Inicializar formularios AJAX
 */
function initAjaxForms() {
    const ajaxForms = document.querySelectorAll('.ajax-form');
    
    ajaxForms.forEach(form => {
        form.addEventListener('submit', async (e) => {
            e.preventDefault();
            
            const submitBtn = form.querySelector('[type="submit"]');
            const originalText = submitBtn.innerHTML;
            const formData = new FormData(form);
            
            // Mostrar loading
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Procesando...';
            
            try {
                const response = await fetch(form.action, {
                    method: form.method || 'POST',
                    body: form.dataset.jsonSubmit ? JSON.stringify(Object.fromEntries(formData)) : formData,
                    headers: form.dataset.jsonSubmit ? {'Content-Type': 'application/json'} : {}
                });
                
                const data = await response.json();
                
                if (data.success) {
                    showNotification('success', data.message || 'Operación exitosa');
                    
                    if (data.redirect) {
                        setTimeout(() => {
                            window.location.href = data.redirect;
                        }, 1000);
                    }
                } else {
                    showNotification('error', data.message || 'Error al procesar la solicitud');
                }
            } catch (error) {
                showNotification('error', 'Error de conexión');
            } finally {
                submitBtn.disabled = false;
                submitBtn.innerHTML = originalText;
            }
        });
    });
}

/**
 * Mostrar notificaciones
 */
function showNotification(type, message) {
    const alertClass = type === 'success' ? 'alert-success' : 'alert-danger';
    const icon = type === 'success' ? 'check-circle' : 'exclamation-circle';
    
    const alert = document.createElement('div');
    alert.className = `alert ${alertClass} alert-dismissible fade show position-fixed top-0 end-0 m-3`;
    alert.style.zIndex = '9999';
    alert.innerHTML = `
        <i class="bi bi-${icon} me-2"></i>
        ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    `;
    
    document.body.appendChild(alert);
    
    // Auto-cerrar después de 5 segundos
    setTimeout(() => {
        alert.remove();
    }, 5000);
}

/**
 * Inicializar editores de texto enriquecido
 */
function initRichTextEditors() {
    const textareas = document.querySelectorAll('.rich-editor');
    
    textareas.forEach(textarea => {
        // Aquí se podría integrar TinyMCE, CKEditor, etc.
        // Por ahora solo agregamos un contador de caracteres
        const counter = document.createElement('small');
        counter.className = 'text-muted';
        textarea.parentElement.appendChild(counter);
        
        const updateCounter = () => {
            counter.textContent = `${textarea.value.length} caracteres`;
        };
        
        textarea.addEventListener('input', updateCounter);
        updateCounter();
    });
}

/**
 * Inicializar carga de imágenes con preview
 */
function initImageUploads() {
    const imageInputs = document.querySelectorAll('.image-upload');
    
    imageInputs.forEach(input => {
        const preview = document.getElementById(input.dataset.preview);
        
        input.addEventListener('change', (e) => {
            const files = e.target.files;
            
            if (files && files[0] && preview) {
                const reader = new FileReader();
                
                reader.onload = (e) => {
                    preview.innerHTML = `<img src="${e.target.result}" class="img-fluid rounded" alt="Preview">`;
                };
                
                reader.readAsDataURL(files[0]);
            }
        });
    });
}

/**
 * Confirmaciones para eliminar
 */
function initDeleteConfirmations() {
    document.addEventListener('click', (e) => {
        if (e.target.matches('.delete-confirm') || e.target.closest('.delete-confirm')) {
            e.preventDefault();
            
            const button = e.target.matches('.delete-confirm') ? e.target : e.target.closest('.delete-confirm');
            const message = button.dataset.message || '¿Estás seguro de eliminar este elemento?';
            
            if (confirm(message)) {
                if (button.dataset.action) {
                    deleteItem(button.dataset.action, button.dataset.method || 'DELETE');
                }
            }
        }
    });
}

/**
 * Eliminar elemento vía AJAX
 */
async function deleteItem(url, method) {
    try {
        const response = await fetch(url, {
            method: method,
            headers: {
                'Content-Type': 'application/json'
            }
        });
        
        const data = await response.json();
        
        if (data.success) {
            showNotification('success', data.message || 'Elemento eliminado');
            setTimeout(() => location.reload(), 1000);
        } else {
            showNotification('error', data.message || 'Error al eliminar');
        }
    } catch (error) {
        showNotification('error', 'Error de conexión');
    }
}

/**
 * Funciones auxiliares para vehículos
 */
window.vehicleManager = {
    // Agregar imagen a la galería
    addImage: function(inputId) {
        const input = document.getElementById(inputId);
        const gallery = document.getElementById('image-gallery');
        
        if (input.files && input.files[0]) {
            const reader = new FileReader();
            
            reader.onload = function(e) {
                const imageItem = document.createElement('div');
                imageItem.className = 'col-md-3 mb-3';
                imageItem.innerHTML = `
                    <div class="position-relative">
                        <img src="${e.target.result}" class="img-fluid rounded" alt="Nueva imagen">
                        <button type="button" class="btn btn-sm btn-danger position-absolute top-0 end-0 m-1" 
                                onclick="this.closest('.col-md-3').remove()">
                            <i class="bi bi-x"></i>
                        </button>
                    </div>
                `;
                gallery.appendChild(imageItem);
            };
            
            reader.readAsDataURL(input.files[0]);
        }
    },
    
    // Calcular precio sugerido
    calculateSuggestedPrice: function() {
        const year = document.getElementById('year').value;
        const mileage = document.getElementById('mileage').value;
        const brand = document.getElementById('brand').value;
        
        if (year && mileage) {
            // Fórmula simple de ejemplo
            let basePrice = 500000;
            const currentYear = new Date().getFullYear();
            const age = currentYear - parseInt(year);
            
            // Ajustar por edad
            basePrice -= (age * 50000);
            
            // Ajustar por kilometraje
            basePrice -= (parseInt(mileage) * 2);
            
            // Ajustar por marca (premium)
            const premiumBrands = ['Mercedes-Benz', 'BMW', 'Audi', 'Porsche'];
            if (premiumBrands.includes(brand)) {
                basePrice *= 1.3;
            }
            
            // No permitir precios negativos
            basePrice = Math.max(basePrice, 100000);
            
            document.getElementById('suggested-price').textContent = 
                '$' + basePrice.toLocaleString('es-MX');
        }
    }
};

/**
 * Funciones para el generador de contenido
 */
window.contentGenerator = {
    generate: async function(formId) {
        const form = document.getElementById(formId);
        const formData = new FormData(form);
        const resultDiv = document.getElementById('generated-content');
        const generateBtn = form.querySelector('[type="submit"]');
        
        generateBtn.disabled = true;
        generateBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Generando...';
        resultDiv.innerHTML = '<div class="text-center"><div class="spinner-border"></div></div>';
        
        try {
            const response = await fetch('/api/content/generate', {
                method: 'POST',
                headers: {'Content-Type': 'application/json'},
                body: JSON.stringify(Object.fromEntries(formData))
            });
            
            const data = await response.json();
            
            if (data.success) {
                resultDiv.innerHTML = `
                    <div class="card">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h5 class="mb-0">${data.data.title}</h5>
                            <button type="button" class="btn btn-sm btn-outline-primary" 
                                    onclick="contentGenerator.copyToClipboard('${data.data.title}', '${data.data.content}')">
                                <i class="bi bi-clipboard me-1"></i>Copiar
                            </button>
                        </div>
                        <div class="card-body">
                            ${data.data.content}
                        </div>
                    </div>
                `;
            } else {
                resultDiv.innerHTML = `
                    <div class="alert alert-danger">
                        ${data.message || 'Error al generar contenido'}
                    </div>
                `;
            }
        } catch (error) {
            resultDiv.innerHTML = `
                <div class="alert alert-danger">
                    Error de conexión. Por favor intenta nuevamente.
                </div>
            `;
        } finally {
            generateBtn.disabled = false;
            generateBtn.innerHTML = '<i class="bi bi-magic me-2"></i>Generar Contenido';
        }
    },
    
    copyToClipboard: function(title, content) {
        const text = `${title}\n\n${content.replace(/<[^>]*>/g, '')}`;
        navigator.clipboard.writeText(text).then(() => {
            showNotification('success', 'Contenido copiado al portapapeles');
        });
    }
};

/**
 * Gráficos y estadísticas
 */
window.statsManager = {
    updateDashboard: async function() {
        try {
            const response = await fetch('/api/dashboard/stats');
            const data = await response.json();
            
            if (data.success) {
                // Actualizar números en las tarjetas
                Object.keys(data.data).forEach(section => {
                    Object.keys(data.data[section]).forEach(key => {
                        const element = document.querySelector(`[data-stat="${section}-${key}"]`);
                        if (element) {
                            element.textContent = data.data[section][key].toLocaleString();
                        }
                    });
                });
            }
        } catch (error) {
            console.error('Error al actualizar estadísticas:', error);
        }
    }
};

// Funciones globales para compatibilidad
window.deleteVehicle = function(id) {
    if (confirm('¿Estás seguro de eliminar este vehículo?')) {
        deleteItem(`/api/inventory/${id}`, 'DELETE');
    }
};

window.updateLeadStatus = function(id, status) {
    fetch(`/api/crm/leads/${id}`, {
        method: 'PUT',
        headers: {'Content-Type': 'application/json'},
        body: JSON.stringify({status: status})
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showNotification('success', 'Estado actualizado');
            setTimeout(() => location.reload(), 1000);
        } else {
            showNotification('error', data.message);
        }
    });
};