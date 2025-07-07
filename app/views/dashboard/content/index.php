<?php
// app/views/dashboard/content/index.php
?>

<div class="flex flex-col gap-6">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h2">Control de Contenido del Sitio Web</h1>
    </div>

    <div class="row">
        <div class="col-lg-6">
            <!-- Generador de contenido -->
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white">
                    <h5 class="mb-0">
                        <i class="bi bi-magic text-primary me-2"></i>
                        Generador de Contenido con IA
                    </h5>
                </div>
                <div class="card-body">
                    <p class="text-muted mb-4">
                        Genera contenido optimizado para SEO usando inteligencia artificial.
                    </p>
                    
                    <form id="contentGeneratorForm" onsubmit="event.preventDefault(); contentGenerator.generate('contentGeneratorForm');">
                        <input type="hidden" name="csrf_token" value="<?= e($csrf_token) ?>">
                        
                        <div class="mb-3">
                            <label class="form-label">Tema de la página *</label>
                            <input type="text" class="form-control" name="pageTopic" 
                                   placeholder="ej: Financiamiento de autos de lujo" required>
                            <small class="text-muted">¿Sobre qué quieres generar contenido?</small>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Tono del contenido *</label>
                            <select class="form-select" name="tone" required>
                                <option value="Profesional y emocionante">Profesional y emocionante</option>
                                <option value="Informativo y educativo">Informativo y educativo</option>
                                <option value="Persuasivo y orientado a ventas">Persuasivo y orientado a ventas</option>
                                <option value="Casual y amigable">Casual y amigable</option>
                                <option value="Técnico y detallado">Técnico y detallado</option>
                            </select>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Palabras clave *</label>
                            <input type="text" class="form-control" name="keywords" 
                                   placeholder="ej: autos de lujo, financiamiento, seminuevos" required>
                            <small class="text-muted">Separa las palabras clave con comas</small>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Tipo de contenido</label>
                            <select class="form-select" name="contentType">
                                <option value="landing">Página de aterrizaje</option>
                                <option value="blog">Artículo de blog</option>
                                <option value="product">Descripción de producto</option>
                                <option value="service">Descripción de servicio</option>
                                <option value="about">Página "Acerca de"</option>
                            </select>
                        </div>
                        
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="bi bi-magic me-2"></i>Generar Contenido
                        </button>
                    </form>
                </div>
            </div>
            
            <!-- Plantillas rápidas -->
            <div class="card border-0 shadow-sm mt-4">
                <div class="card-header bg-white">
                    <h5 class="mb-0">Plantillas Rápidas</h5>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <button class="btn btn-outline-secondary text-start" 
                                onclick="loadTemplate('luxury-suvs')">
                            <i class="bi bi-file-text me-2"></i>
                            SUVs de Lujo - Página de categoría
                        </button>
                        <button class="btn btn-outline-secondary text-start" 
                                onclick="loadTemplate('financing-guide')">
                            <i class="bi bi-file-text me-2"></i>
                            Guía de Financiamiento Automotriz
                        </button>
                        <button class="btn btn-outline-secondary text-start" 
                                onclick="loadTemplate('maintenance-tips')">
                            <i class="bi bi-file-text me-2"></i>
                            Tips de Mantenimiento para Autos de Lujo
                        </button>
                        <button class="btn btn-outline-secondary text-start" 
                                onclick="loadTemplate('why-choose-us')">
                            <i class="bi bi-file-text me-2"></i>
                            Por qué elegir ALARA
                        </button>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-lg-6">
            <!-- Resultado generado -->
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white">
                    <h5 class="mb-0">Contenido Generado</h5>
                </div>
                <div class="card-body" id="generated-content">
                    <div class="text-center text-muted py-5">
                        <i class="bi bi-file-text" style="font-size: 3rem;"></i>
                        <p class="mt-3">El contenido generado aparecerá aquí</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Configuración SEO -->
    <div class="card border-0 shadow-sm mt-4">
        <div class="card-header bg-white">
            <h5 class="mb-0">Configuración SEO Global</h5>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <div class="mb-3">
                        <label class="form-label">Título del sitio</label>
                        <input type="text" class="form-control" value="ALARA | Venta de Autos de Lujo Seminuevos">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Meta descripción</label>
                        <textarea class="form-control" rows="3">En ALARA, redefinimos la compra de autos de lujo seminuevos. Descubre nuestro inventario exclusivo y vive una experiencia de compra inigualable.</textarea>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="mb-3">
                        <label class="form-label">Palabras clave principales</label>
                        <input type="text" class="form-control" value="autos de lujo, seminuevos, financiamiento, mercedes benz, bmw, audi">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Google Analytics ID</label>
                        <input type="text" class="form-control" placeholder="UA-XXXXXXXXX-X">
                    </div>
                </div>
            </div>
            <button class="btn btn-primary">Guardar Configuración SEO</button>
        </div>
    </div>
</div>

<script>
function loadTemplate(template) {
    const templates = {
        'luxury-suvs': {
            topic: 'SUVs de lujo disponibles en ALARA',
            tone: 'Profesional y emocionante',
            keywords: 'SUV de lujo, Range Rover, BMW X5, Mercedes GLE, Porsche Cayenne'
        },
        'financing-guide': {
            topic: 'Guía completa de financiamiento automotriz',
            tone: 'Informativo y educativo',
            keywords: 'financiamiento auto, crédito automotriz, enganche, mensualidades'
        },
        'maintenance-tips': {
            topic: 'Mantenimiento preventivo para autos de lujo',
            tone: 'Informativo y educativo',
            keywords: 'mantenimiento auto lujo, servicio preventivo, cuidado vehículo premium'
        },
        'why-choose-us': {
            topic: 'Por qué ALARA es tu mejor opción',
            tone: 'Persuasivo y orientado a ventas',
            keywords: 'comprar auto seminuevo, garantía, servicio personalizado, mejor precio'
        }
    };
    
    const template = templates[template];
    if (template) {
        document.querySelector('[name="pageTopic"]').value = template.topic;
        document.querySelector('[name="tone"]').value = template.tone;
        document.querySelector('[name="keywords"]').value = template.keywords;
    }
}
</script>