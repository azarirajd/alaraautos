/**
 * ALARA - Scripts principales del sitio público
 * Funcionalidades interactivas y animaciones
 */

// Esperar a que el DOM esté listo
document.addEventListener('DOMContentLoaded', function() {
    // Inicializar todas las funcionalidades
    initNavigation();
    initAnimations();
    initWhatsAppFloat();
    initLegalModals();
    initSmoothScroll();
    initLazyLoading();
    initCarousel();
    initInventoryInteractions();
});

/**
 * Navegación móvil y efectos del header
 */
function initNavigation() {
    const header = document.querySelector('.header');
    const hamburger = document.querySelector('.hamburger');
    const navMenu = document.querySelector('.nav-menu');
    
    // Header sticky con efecto
    let lastScroll = 0;
    window.addEventListener('scroll', () => {
        const currentScroll = window.pageYOffset;
        
        if (currentScroll > 100) {
            header.classList.add('scrolled');
        } else {
            header.classList.remove('scrolled');
        }
        
        // Ocultar/mostrar header al hacer scroll
        if (currentScroll > lastScroll && currentScroll > 300) {
            header.style.transform = 'translateY(-100%)';
        } else {
            header.style.transform = 'translateY(0)';
        }
        
        lastScroll = currentScroll;
    });
    
    // Menú móvil
    if (hamburger) {
        hamburger.addEventListener('click', () => {
            hamburger.classList.toggle('active');
            navMenu.classList.toggle('active');
            document.body.classList.toggle('menu-open');
        });
        
        // Cerrar menú al hacer clic en un enlace
        document.querySelectorAll('.nav-link').forEach(link => {
            link.addEventListener('click', () => {
                hamburger.classList.remove('active');
                navMenu.classList.remove('active');
                document.body.classList.remove('menu-open');
            });
        });
    }
}

/**
 * Animaciones on scroll
 */
function initAnimations() {
    const observerOptions = {
        threshold: 0.1,
        rootMargin: '0px 0px -100px 0px'
    };
    
    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.classList.add('visible');
                
                // Animar elementos hijos con delay
                const children = entry.target.querySelectorAll('.animate-child');
                children.forEach((child, index) => {
                    setTimeout(() => {
                        child.classList.add('visible');
                    }, index * 100);
                });
            }
        });
    }, observerOptions);
    
    // Observar todos los elementos animables
    document.querySelectorAll('.animate-on-scroll').forEach(el => {
        observer.observe(el);
    });
    
    // Animación de números (contador)
    const countElements = document.querySelectorAll('.count-up');
    countElements.forEach(element => {
        const countObserver = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting && !entry.target.classList.contains('counted')) {
                    animateCount(entry.target);
                    entry.target.classList.add('counted');
                }
            });
        }, observerOptions);
        
        countObserver.observe(element);
    });
}

/**
 * Animación de contador
 */
function animateCount(element) {
    const target = parseInt(element.getAttribute('data-count'));
    const duration = 2000; // 2 segundos
    const step = target / (duration / 16); // 60 FPS
    let current = 0;
    
    const timer = setInterval(() => {
        current += step;
        if (current >= target) {
            current = target;
            clearInterval(timer);
        }
        element.textContent = Math.floor(current).toLocaleString();
    }, 16);
}

/**
 * WhatsApp flotante
 */
function initWhatsAppFloat() {
    const whatsappFloat = document.querySelector('.whatsapp-float');
    if (!whatsappFloat) return;
    
    // Mostrar después de 3 segundos
    setTimeout(() => {
        whatsappFloat.style.opacity = '1';
        whatsappFloat.style.transform = 'scale(1)';
    }, 3000);
    
    // Click handler
    whatsappFloat.addEventListener('click', () => {
        const phone = whatsappFloat.getAttribute('data-phone') || '521234567890';
        const message = 'Hola, me interesa conocer más sobre los vehículos disponibles en ALARA.';
        const url = `https://wa.me/${phone}?text=${encodeURIComponent(message)}`;
        window.open(url, '_blank');
    });
}

/**
 * Modales legales
 */
function initLegalModals() {
    const legalLinks = document.querySelectorAll('[data-legal]');
    
    legalLinks.forEach(link => {
        link.addEventListener('click', (e) => {
            e.preventDefault();
            const modalId = link.getAttribute('data-legal');
            const modal = document.getElementById(modalId);
            
            if (modal) {
                modal.style.display = 'flex';
                document.body.style.overflow = 'hidden';
            }
        });
    });
    
    // Cerrar modales
    document.querySelectorAll('.close-legal, .legal-modal').forEach(element => {
        element.addEventListener('click', (e) => {
            if (e.target === element || element.classList.contains('close-legal')) {
                const modal = element.closest('.legal-modal') || element;
                modal.style.display = 'none';
                document.body.style.overflow = '';
            }
        });
    });
}

/**
 * Scroll suave
 */
function initSmoothScroll() {
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function(e) {
            e.preventDefault();
            const target = document.querySelector(this.getAttribute('href'));
            
            if (target) {
                const headerOffset = 80;
                const elementPosition = target.getBoundingClientRect().top;
                const offsetPosition = elementPosition + window.pageYOffset - headerOffset;
                
                window.scrollTo({
                    top: offsetPosition,
                    behavior: 'smooth'
                });
            }
        });
    });
}

/**
 * Lazy loading de imágenes
 */
function initLazyLoading() {
    const lazyImages = document.querySelectorAll('img[data-src]');
    
    const imageObserver = new IntersectionObserver((entries, observer) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                const img = entry.target;
                img.src = img.dataset.src;
                img.removeAttribute('data-src');
                img.classList.add('loaded');
                observer.unobserve(img);
            }
        });
    });
    
    lazyImages.forEach(img => imageObserver.observe(img));
}

/**
 * Carrusel de testimonios
 */
function initCarousel() {
    const carousel = document.querySelector('.testimonials-carousel');
    if (!carousel) return;
    
    const track = carousel.querySelector('.testimonial-track');
    const slides = carousel.querySelectorAll('.testimonial-slide');
    const prevBtn = carousel.querySelector('.carousel-prev');
    const nextBtn = carousel.querySelector('.carousel-next');
    
    let currentSlide = 0;
    const totalSlides = slides.length;
    
    function updateCarousel() {
        const offset = -currentSlide * 100;
        track.style.transform = `translateX(${offset}%)`;
    }
    
    function nextSlide() {
        currentSlide = (currentSlide + 1) % totalSlides;
        updateCarousel();
    }
    
    function prevSlide() {
        currentSlide = (currentSlide - 1 + totalSlides) % totalSlides;
        updateCarousel();
    }
    
    if (nextBtn) nextBtn.addEventListener('click', nextSlide);
    if (prevBtn) prevBtn.addEventListener('click', prevSlide);
    
    // Auto-play
    setInterval(nextSlide, 5000);
    
    // Touch/swipe support
    let touchStartX = 0;
    let touchEndX = 0;
    
    carousel.addEventListener('touchstart', (e) => {
        touchStartX = e.changedTouches[0].screenX;
    });
    
    carousel.addEventListener('touchend', (e) => {
        touchEndX = e.changedTouches[0].screenX;
        handleSwipe();
    });
    
    function handleSwipe() {
        if (touchEndX < touchStartX - 50) nextSlide();
        if (touchEndX > touchStartX + 50) prevSlide();
    }
}

/**
 * Interacciones del inventario
 */
function initInventoryInteractions() {
    // Hover effects en tarjetas
    const carCards = document.querySelectorAll('.car-card');
    
    carCards.forEach(card => {
        card.addEventListener('mouseenter', function() {
            this.style.transform = 'translateY(-5px)';
        });
        
        card.addEventListener('mouseleave', function() {
            this.style.transform = 'translateY(0)';
        });
    });
    
    // Vista rápida
    window.showQuickView = function(carId) {
        // Aquí iría la lógica para mostrar un modal con detalles del vehículo
        console.log('Vista rápida del vehículo:', carId);
    };
}

/**
 * Utilidades globales
 */

// Formatear números como moneda
window.formatCurrency = function(amount) {
    return new Intl.NumberFormat('es-MX', {
        style: 'currency',
        currency: 'MXN',
        minimumFractionDigits: 0,
        maximumFractionDigits: 0
    }).format(amount);
};

// Mostrar notificaciones
window.showNotification = function(message, type = 'success') {
    const notification = document.createElement('div');
    notification.className = `notification notification-${type}`;
    notification.innerHTML = `
        <i class="bi bi-${type === 'success' ? 'check-circle' : 'exclamation-circle'}"></i>
        <span>${message}</span>
    `;
    
    document.body.appendChild(notification);
    
    // Animar entrada
    setTimeout(() => {
        notification.classList.add('show');
    }, 10);
    
    // Remover después de 3 segundos
    setTimeout(() => {
        notification.classList.remove('show');
        setTimeout(() => {
            notification.remove();
        }, 300);
    }, 3000);
};

// Validar formularios
window.validateForm = function(formElement) {
    const inputs = formElement.querySelectorAll('[required]');
    let isValid = true;
    
    inputs.forEach(input => {
        if (!input.value.trim()) {
            input.classList.add('error');
            isValid = false;
        } else {
            input.classList.remove('error');
        }
        
        // Validación específica para email
        if (input.type === 'email' && input.value) {
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if (!emailRegex.test(input.value)) {
                input.classList.add('error');
                isValid = false;
            }
        }
        
        // Validación para teléfono
        if (input.type === 'tel' && input.value) {
            const phoneRegex = /^[\d\s\-\+\(\)]+$/;
            if (!phoneRegex.test(input.value) || input.value.length < 10) {
                input.classList.add('error');
                isValid = false;
            }
        }
    });
    
    return isValid;
};

// Debounce para optimizar eventos
window.debounce = function(func, wait) {
    let timeout;
    return function executedFunction(...args) {
        const later = () => {
            clearTimeout(timeout);
            func(...args);
        };
        clearTimeout(timeout);
        timeout = setTimeout(later, wait);
    };
};

// Throttle para limitar la frecuencia de ejecución
window.throttle = function(func, limit) {
    let inThrottle;
    return function(...args) {
        if (!inThrottle) {
            func.apply(this, args);
            inThrottle = true;
            setTimeout(() => inThrottle = false, limit);
        }
    };
};

// Manejo de errores global
window.addEventListener('error', function(e) {
    console.error('Error global:', e.error);
    // En producción, enviar a servicio de logging
});