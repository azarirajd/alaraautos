// public/js/forms-integration.js - Integración de formularios del sitio público con PHP

/**
 * Configuración global
 */
const API_ENDPOINT = '/api';
const CSRF_TOKEN = document.querySelector('meta[name="csrf-token"]')?.content || '';

/**
 * Utilidades
 */
const showMessage = (type, message, targetId = null) => {
    const alertClass = type === 'success' ? 'alert-success' : 'alert-error';
    const alertHTML = `
        <div class="alert ${alertClass} alert-dismissible" role="alert">
            <span>${message}</span>
            <button type="button" class="close" onclick="this.parentElement.remove()">×</button>
        </div>
    `;
    
    if (targetId) {
        const target = document.getElementById(targetId);
        if (target) {
            target.innerHTML = alertHTML;
        }
    } else {
        // Mostrar como notificación flotante
        const notification = document.createElement('div');
        notification.className = 'notification-float';
        notification.innerHTML = alertHTML;
        document.body.appendChild(notification);
        setTimeout(() => notification.remove(), 5000);
    }
};

const formatCurrency = (amount) => {
    return new Intl.NumberFormat('es-MX', {
        style: 'currency',
        currency: 'MXN'
    }).format(amount);
};

/**
 * Formulario de Contacto Principal
 */
const initContactForm = () => {
    const form = document.getElementById('main-contact-form');
    if (!form) return;
    
    form.addEventListener('submit', async (e) => {
        e.preventDefault();
        
        const submitBtn = form.querySelector('button[type="submit"]');
        const originalText = submitBtn.innerHTML;
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<span class="spinner"></span> Enviando...';
        
        const formData = new FormData(form);
        const data = {
            name: formData.get('name'),
            email: formData.get('email'),
            phone: formData.get('phone'),
            message: formData.get('message'),
            car_id: formData.get('car_id') || null,
            source: 'web',
            form_source: 'contact_page'
        };
        
        try {
            const response = await fetch(`${API_ENDPOINT}/contact`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-Token': CSRF_TOKEN
                },
                body: JSON.stringify(data)
            });
            
            const result = await response.json();
            
            if (result.success) {
                showMessage('success', result.message || '¡Gracias por contactarnos! Te responderemos pronto.');
                form.reset();
                
                // Tracking de conversión
                if (typeof gtag !== 'undefined') {
                    gtag('event', 'form_submission', {
                        'event_category': 'Contact',
                        'event_label': 'Main Contact Form'
                    });
                }
            } else {
                showMessage('error', result.message || 'Error al enviar el formulario');
            }
        } catch (error) {
            showMessage('error', 'Error de conexión. Por favor intenta nuevamente.');
        } finally {
            submitBtn.disabled = false;
            submitBtn.innerHTML = originalText;
        }
    });
};

/**
 * Formulario de Solicitud de Crédito
 */
const initCreditApplicationForm = () => {
    const form = document.getElementById('credit-application-form');
    if (!form) return;
    
    // Calcular enganche automáticamente
    const downPaymentSlider = form.querySelector('#down-payment-slider');
    const downPaymentInput = form.querySelector('#down-payment-amount');
    const carPrice = parseFloat(form.dataset.carPrice || 0);
    
    if (downPaymentSlider && downPaymentInput && carPrice) {
        downPaymentSlider.addEventListener('input', (e) => {
            const percentage = e.target.value;
            const amount = carPrice * (percentage / 100);
            downPaymentInput.value = Math.round(amount);
            document.getElementById('down-payment-display').textContent = formatCurrency(amount);
            updateMonthlyPayment();
        });
    }
    
    // Actualizar pago mensual estimado
    const updateMonthlyPayment = () => {
        const downPayment = parseFloat(downPaymentInput.value || 0);
        const term = parseInt(form.querySelector('#loan-term').value || 48);
        const amount = carPrice - downPayment;
        
        // Cálculo simple de pago mensual (sin interés exacto)
        const interestRate = 0.15 / 12; // 15% anual aproximado
        const monthlyPayment = amount * (interestRate * Math.pow(1 + interestRate, term)) / 
                              (Math.pow(1 + interestRate, term) - 1);
        
        document.getElementById('monthly-payment-estimate').textContent = 
            formatCurrency(monthlyPayment);
    };
    
    // Envío del formulario
    form.addEventListener('submit', async (e) => {
        e.preventDefault();
        
        const submitBtn = form.querySelector('button[type="submit"]');
        const originalText = submitBtn.innerHTML;
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<span class="spinner"></span> Procesando solicitud...';
        
        const formData = new FormData(form);
        const data = {
            applicant_name: formData.get('name'),
            applicant_email: formData.get('email'),
            applicant_phone: formData.get('phone'),
            applicant_rfc: formData.get('rfc'),
            monthly_income: parseFloat(formData.get('monthly_income')),
            employment_type: formData.get('employment_type'),
            employment_years: parseInt(formData.get('employment_years')),
            car_id: parseInt(formData.get('car_id')),
            down_payment: parseFloat(formData.get('down_payment')),
            requested_term: parseInt(formData.get('loan_term'))
        };
        
        try {
            const response = await fetch(`${API_ENDPOINT}/credit-applications`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-Token': CSRF_TOKEN
                },
                body: JSON.stringify(data)
            });
            
            const result = await response.json();
            
            if (result.success) {
                // Mostrar mensaje de éxito
                const successHTML = `
                    <div class="credit-success">
                        <i class="icon-check-circle"></i>
                        <h3>¡Solicitud Enviada Exitosamente!</h3>
                        <p>Tu número de referencia es: <strong>${result.data.reference}</strong></p>
                        <p>Nos pondremos en contacto contigo en las próximas 24-48 horas.</p>
                        <button class="btn btn-primary" onclick="location.reload()">
                            Enviar otra solicitud
                        </button>
                    </div>
                `;
                form.parentElement.innerHTML = successHTML;
                
                // Tracking
                if (typeof gtag !== 'undefined') {
                    gtag('event', 'credit_application', {
                        'event_category': 'Conversion',
                        'event_label': 'Credit Application Submitted',
                        'value': carPrice
                    });
                }
            } else {
                showMessage('error', result.message || 'Error al procesar la solicitud');
            }
        } catch (error) {
            showMessage('error', 'Error de conexión. Por favor intenta nuevamente.');
        } finally {
            submitBtn.disabled = false;
            submitBtn.innerHTML = originalText;
        }
    });
};

/**
 * Calculadora de Presupuesto
 */
const initBudgetCalculator = () => {
    const calculator = document.getElementById('budget-calculator');
    if (!calculator) return;
    
    const calculateBtn = calculator.querySelector('#calculate-btn');
    const resultsDiv = calculator.querySelector('#calculator-results');
    
    calculateBtn.addEventListener('click', async () => {
        const budget = parseFloat(calculator.querySelector('#budget-amount').value);
        const downPayment = parseFloat(calculator.querySelector('#down-payment-percentage').value);
        const term = parseInt(calculator.querySelector('#loan-term-calc').value);
        
        if (!budget || budget < 100000) {
            showMessage('error', 'Por favor ingresa un presupuesto válido (mínimo $100,000)');
            return;
        }
        
        calculateBtn.disabled = true;
        calculateBtn.innerHTML = '<span class="spinner"></span> Calculando...';
        
        try {
            const response = await fetch(`${API_ENDPOINT}/budget-calculator`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-Token': CSRF_TOKEN
                },
                body: JSON.stringify({
                    budget: budget,
                    down_payment: downPayment,
                    term: term
                })
            });
            
            const result = await response.json();
            
            if (result.success) {
                displayCalculatorResults(result.data, resultsDiv);
                
                // Scroll a resultados
                resultsDiv.scrollIntoView({ behavior: 'smooth', block: 'start' });
            } else {
                showMessage('error', result.message);
            }
        } catch (error) {
            showMessage('error', 'Error al calcular. Por favor intenta nuevamente.');
        } finally {
            calculateBtn.disabled = false;
            calculateBtn.innerHTML = 'Calcular';
        }
    });
};

/**
 * Mostrar resultados de la calculadora
 */
const displayCalculatorResults = (data, container) => {
    const { calculations, vehicles, recommendations } = data;
    
    let html = `
        <div class="calculator-results-content">
            <h3>Resultados de tu Cálculo</h3>
            
            <div class="calculation-summary">
                <div class="summary-item">
                    <span class="label">Precio del vehículo:</span>
                    <span class="value">${formatCurrency(calculations.total_price)}</span>
                </div>
                <div class="summary-item">
                    <span class="label">Enganche (${calculations.down_payment_percent}%):</span>
                    <span class="value">${formatCurrency(calculations.down_payment)}</span>
                </div>
                <div class="summary-item">
                    <span class="label">Monto a financiar:</span>
                    <span class="value">${formatCurrency(calculations.loan_amount)}</span>
                </div>
                <div class="summary-item highlight">
                    <span class="label">Pago mensual estimado:</span>
                    <span class="value">${formatCurrency(calculations.monthly_payment)}</span>
                </div>
                <div class="summary-item">
                    <span class="label">Plazo:</span>
                    <span class="value">${calculations.term_months} meses</span>
                </div>
                <div class="summary-item">
                    <span class="label">Tasa de interés:</span>
                    <span class="value">${calculations.interest_rate}% anual</span>
                </div>
            </div>
    `;
    
    // Recomendaciones
    if (recommendations && recommendations.length > 0) {
        html += '<div class="recommendations">';
        recommendations.forEach(rec => {
            const iconClass = rec.type === 'success' ? 'check-circle' : 
                             rec.type === 'warning' ? 'alert-circle' : 'info-circle';
            html += `
                <div class="recommendation recommendation-${rec.type}">
                    <i class="icon-${iconClass}"></i>
                    <p>${rec.message}</p>
                </div>
            `;
        });
        html += '</div>';
    }
    
    // Vehículos sugeridos
    if (vehicles && vehicles.length > 0) {
        html += `
            <div class="suggested-vehicles">
                <h4>Vehículos dentro de tu presupuesto</h4>
                <div class="vehicles-grid">
        `;
        
        vehicles.forEach(car => {
            html += `
                <div class="vehicle-card">
                    <div class="vehicle-image">
                        <img src="/uploads/cars/${car.main_image || 'placeholder.jpg'}" 
                             alt="${car.brand} ${car.model}">
                        <span class="match-badge">${car.match_percentage}% compatible</span>
                    </div>
                    <div class="vehicle-info">
                        <h5>${car.brand} ${car.model} ${car.year}</h5>
                        <p class="price">${car.formatted_price}</p>
                        <p class="mileage">${car.formatted_mileage}</p>
                        <a href="/vehiculo.html?id=${car.id}" class="btn btn-primary btn-sm">
                            Ver detalles
                        </a>
                    </div>
                </div>
            `;
        });
        
        html += `
                </div>
                <div class="text-center mt-4">
                    <a href="/inventario.html?budget=${calculations.total_price}" 
                       class="btn btn-secondary">
                        Ver más vehículos en este rango
                    </a>
                </div>
            </div>
        `;
    }
    
    html += '</div>';
    container.innerHTML = html;
    container.style.display = 'block';
};

/**
 * Newsletter/Pop-up de salida
 */
const initExitIntent = () => {
    let exitIntentShown = sessionStorage.getItem('exitIntentShown');
    if (exitIntentShown) return;
    
    const showExitPopup = () => {
        const popup = document.getElementById('exit-popup');
        if (popup) {
            popup.style.display = 'flex';
            sessionStorage.setItem('exitIntentShown', 'true');
        }
    };
    
    // Detectar intención de salida (mouse sale del viewport)
    document.addEventListener('mouseout', (e) => {
        if (e.clientY <= 0 && e.relatedTarget == null) {
            showExitPopup();
        }
    });
    
    // También mostrar después de 30 segundos si no se ha mostrado
    setTimeout(() => {
        if (!sessionStorage.getItem('exitIntentShown')) {
            showExitPopup();
        }
    }, 30000);
};

/**
 * Formulario de Newsletter
 */
const initNewsletterForm = () => {
    const forms = document.querySelectorAll('.newsletter-form');
    
    forms.forEach(form => {
        form.addEventListener('submit', async (e) => {
            e.preventDefault();
            
            const email = form.querySelector('input[type="email"]').value;
            const submitBtn = form.querySelector('button[type="submit"]');
            const originalText = submitBtn.innerHTML;
            
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<span class="spinner"></span>';
            
            try {
                const response = await fetch(`${API_ENDPOINT}/contact`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-Token': CSRF_TOKEN
                    },
                    body: JSON.stringify({
                        name: 'Suscriptor Newsletter',
                        email: email,
                        message: 'Suscripción a newsletter',
                        source: 'newsletter',
                        newsletter: '1'
                    })
                });
                
                const result = await response.json();
                
                if (result.success) {
                    form.innerHTML = `
                        <div class="newsletter-success">
                            <i class="icon-check"></i>
                            <p>¡Gracias por suscribirte! Recibirás nuestras mejores ofertas.</p>
                        </div>
                    `;
                    
                    // Cerrar popup si existe
                    const popup = form.closest('.popup');
                    if (popup) {
                        setTimeout(() => {
                            popup.style.display = 'none';
                        }, 3000);
                    }
                } else {
                    showMessage('error', result.message || 'Error al suscribirse');
                }
            } catch (error) {
                showMessage('error', 'Error de conexión');
            } finally {
                submitBtn.disabled = false;
                submitBtn.innerHTML = originalText;
            }
        });
    });
};

/**
 * Filtros de inventario
 */
const initInventoryFilters = () => {
    const filtersForm = document.getElementById('inventory-filters');
    if (!filtersForm) return;
    
    const applyFilters = async () => {
        const formData = new FormData(filtersForm);
        const filters = {
            brand: formData.get('brand'),
            min_price: parseFloat(formData.get('min_price')) || null,
            max_price: parseFloat(formData.get('max_price')) || null,
            year: parseInt(formData.get('year')) || null,
            fuel_type: formData.get('fuel_type'),
            order_by: formData.get('order_by') || 'created_at DESC'
        };
        
        // Limpiar filtros vacíos
        Object.keys(filters).forEach(key => {
            if (!filters[key]) delete filters[key];
        });
        
        const inventoryGrid = document.getElementById('inventory-grid');
        inventoryGrid.innerHTML = '<div class="loading">Cargando vehículos...</div>';
        
        try {
            const response = await fetch(`${API_ENDPOINT}/inventory/public`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-Token': CSRF_TOKEN
                },
                body: JSON.stringify(filters)
            });
            
            const result = await response.json();
            
            if (result.success) {
                displayVehicles(result.data, inventoryGrid);
                
                // Actualizar contador
                const counter = document.getElementById('results-count');
                if (counter) {
                    counter.textContent = `${result.total} vehículos encontrados`;
                }
            }
        } catch (error) {
            inventoryGrid.innerHTML = '<p class="error">Error al cargar vehículos</p>';
        }
    };
    
    // Aplicar filtros al cambiar cualquier input
    filtersForm.addEventListener('change', applyFilters);
    
    // Botón de limpiar filtros
    const clearBtn = filtersForm.querySelector('#clear-filters');
    if (clearBtn) {
        clearBtn.addEventListener('click', () => {
            filtersForm.reset();
            applyFilters();
        });
    }
    
    // Cargar inventario inicial
    applyFilters();
};

/**
 * Mostrar vehículos en el grid
 */
const displayVehicles = (vehicles, container) => {
    if (vehicles.length === 0) {
        container.innerHTML = `
            <div class="no-results">
                <i class="icon-car"></i>
                <p>No se encontraron vehículos con los filtros seleccionados</p>
                <button class="btn btn-secondary" onclick="document.getElementById('clear-filters').click()">
                    Limpiar filtros
                </button>
            </div>
        `;
        return;
    }
    
    let html = '';
    vehicles.forEach(car => {
        html += `
            <div class="car-card" data-id="${car.id}">
                <div class="car-image">
                    <img src="/uploads/cars/${car.main_image || 'placeholder.jpg'}" 
                         alt="${car.brand} ${car.model}" loading="lazy">
                    ${car.is_featured ? '<span class="badge-featured">Destacado</span>' : ''}
                    <div class="car-overlay">
                        <button class="btn-icon" onclick="quickView(${car.id})" title="Vista rápida">
                            <i class="icon-eye"></i>
                        </button>
                        <button class="btn-icon" onclick="addToCompare(${car.id})" title="Comparar">
                            <i class="icon-compare"></i>
                        </button>
                    </div>
                </div>
                <div class="car-info">
                    <h3>${car.brand} ${car.model}</h3>
                    <p class="car-year">${car.year} • ${car.formatted_mileage}</p>
                    <p class="car-price">${car.formatted_price}</p>
                    <div class="car-features">
                        ${car.fuel_type ? `<span><i class="icon-fuel"></i> ${car.fuel_type}</span>` : ''}
                        ${car.transmission ? `<span><i class="icon-gear"></i> ${car.transmission}</span>` : ''}
                    </div>
                    <div class="car-actions">
                        <a href="/vehiculo.html?id=${car.id}" class="btn btn-primary">
                            Ver detalles
                        </a>
                        <button class="btn btn-secondary" onclick="openCreditForm(${car.id})">
                            Solicitar crédito
                        </button>
                    </div>
                </div>
            </div>
        `;
    });
    
    container.innerHTML = html;
    
    // Animación de entrada
    const cards = container.querySelectorAll('.car-card');
    cards.forEach((card, index) => {
        setTimeout(() => {
            card.classList.add('fade-in');
        }, index * 50);
    });
};

/**
 * Vista rápida de vehículo
 */
window.quickView = async (carId) => {
    // Implementar modal de vista rápida
    console.log('Vista rápida del vehículo:', carId);
};

/**
 * Comparador de vehículos
 */
let compareList = JSON.parse(localStorage.getItem('compareList')) || [];

window.addToCompare = (carId) => {
    if (compareList.includes(carId)) {
        compareList = compareList.filter(id => id !== carId);
        showMessage('info', 'Vehículo removido de la comparación');
    } else if (compareList.length >= 3) {
        showMessage('error', 'Máximo 3 vehículos para comparar');
        return;
    } else {
        compareList.push(carId);
        showMessage('success', 'Vehículo agregado a la comparación');
    }
    
    localStorage.setItem('compareList', JSON.stringify(compareList));
    updateCompareButton();
};

const updateCompareButton = () => {
    const btn = document.getElementById('compare-button');
    if (btn) {
        btn.innerHTML = `Comparar (${compareList.length})`;
        btn.style.display = compareList.length > 0 ? 'block' : 'none';
    }
};

/**
 * Inicialización principal
 */
document.addEventListener('DOMContentLoaded', () => {
    // Formularios
    initContactForm();
    initCreditApplicationForm();
    initBudgetCalculator();
    initNewsletterForm();
    
    // Funcionalidades del inventario
    initInventoryFilters();
    updateCompareButton();
    
    // Pop-ups y extras
    initExitIntent();
    
    // Lazy loading de imágenes
    if ('IntersectionObserver' in window) {
        const imageObserver = new IntersectionObserver((entries, observer) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    const img = entry.target;
                    img.src = img.dataset.src;
                    img.classList.add('loaded');
                    observer.unobserve(img);
                }
            });
        });
        
        document.querySelectorAll('img[data-src]').forEach(img => {
            imageObserver.observe(img);
        });
    }
});