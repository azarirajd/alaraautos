<?php
// app/views/dashboard/inventory/create.php
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h2">Agregar Vehículo</h1>
    <a href="/dashboard/inventory" class="btn btn-secondary">
        <i class="bi bi-arrow-left me-2"></i>Volver al inventario
    </a>
</div>

<form id="vehicleForm" action="/api/inventory" method="POST" enctype="multipart/form-data" 
      class="ajax-form" data-json-submit="true">
    <input type="hidden" name="csrf_token" value="<?= e($csrf_token) ?>">
    
    <div class="row">
        <div class="col-lg-8">
            <!-- Información básica -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white">
                    <h5 class="mb-0">Información Básica</h5>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label">Marca *</label>
                            <input type="text" class="form-control" id="brand" name="brand" 
                                   list="brands-list" required>
                            <datalist id="brands-list">
                                <option value="Mercedes-Benz">
                                <option value="BMW">
                                <option value="Audi">
                                <option value="Porsche">
                                <option value="Lexus">
                                <option value="Jaguar">
                                <option value="Land Rover">
                                <option value="Volvo">
                                <option value="Tesla">
                                <option value="Maserati">
                            </datalist>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Modelo *</label>
                            <input type="text" class="form-control" name="model" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Año *</label>
                            <input type="number" class="form-control" id="year" name="year" 
                                   min="2000" max="<?= date('Y') + 1 ?>" required
                                   onchange="vehicleManager.calculateSuggestedPrice()">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Precio (MXN) *</label>
                            <input type="number" class="form-control" name="price" 
                                   min="0" step="1000" required>
                            <small class="text-muted">Sugerido: <span id="suggested-price">-</span></small>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Kilometraje *</label>
                            <input type="number" class="form-control" id="mileage" name="mileage" 
                                   min="0" required onchange="vehicleManager.calculateSuggestedPrice()">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">VIN</label>
                            <input type="text" class="form-control" name="vin" maxlength="17">
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Especificaciones -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white">
                    <h5 class="mb-0">Especificaciones</h5>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-3">
                            <label class="form-label">Color</label>
                            <input type="text" class="form-control" name="color">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Tipo de Combustible</label>
                            <select class="form-select" name="fuel_type">
                                <option value="gasolina">Gasolina</option>
                                <option value="diesel">Diesel</option>
                                <option value="hibrido">Híbrido</option>
                                <option value="electrico">Eléctrico</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Transmisión</label>
                            <select class="form-select" name="transmission">
                                <option value="automatica">Automática</option>
                                <option value="manual">Manual</option>
                                <option value="cvt">CVT</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Motor</label>
                            <input type="text" class="form-control" name="engine" 
                                   placeholder="ej: 2.0L Turbo">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Puertas</label>
                            <select class="form-select" name="doors">
                                <option value="2">2</option>
                                <option value="4" selected>4</option>
                                <option value="5">5</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Asientos</label>
                            <select class="form-select" name="seats">
                                <option value="2">2</option>
                                <option value="4">4</option>
                                <option value="5" selected>5</option>
                                <option value="7">7</option>
                                <option value="8">8</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Condición</label>
                            <select class="form-select" name="condition">
                                <option value="nuevo">Nuevo</option>
                                <option value="seminuevo" selected>Seminuevo</option>
                                <option value="usado">Usado</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Ubicación</label>
                            <input type="text" class="form-control" name="location" 
                                   value="Temixco, Morelos">
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Descripción -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white">
                    <h5 class="mb-0">Descripción</h5>
                </div>
                <div class="card-body">
                    <textarea class="form-control rich-editor" name="description" rows="5" 
                              placeholder="Describe el vehículo, su estado, historia, características especiales..."></textarea>
                </div>
            </div>
            
            <!-- Características -->
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white">
                    <h5 class="mb-0">Características</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <h6>Seguridad</h6>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="features[]" 
                                       value="ABS" id="feat-abs">
                                <label class="form-check-label" for="feat-abs">ABS</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="features[]" 
                                       value="Airbags frontales" id="feat-airbags">
                                <label class="form-check-label" for="feat-airbags">Airbags frontales</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="features[]" 
                                       value="Control de estabilidad" id="feat-esc">
                                <label class="form-check-label" for="feat-esc">Control de estabilidad</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="features[]" 
                                       value="Cámara de reversa" id="feat-camera">
                                <label class="form-check-label" for="feat-camera">Cámara de reversa</label>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <h6>Confort</h6>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="features[]" 
                                       value="Aire acondicionado" id="feat-ac">
                                <label class="form-check-label" for="feat-ac">Aire acondicionado</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="features[]" 
                                       value="Asientos de cuero" id="feat-leather">
                                <label class="form-check-label" for="feat-leather">Asientos de cuero</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="features[]" 
                                       value="Techo panorámico" id="feat-sunroof">
                                <label class="form-check-label" for="feat-sunroof">Techo panorámico</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="features[]" 
                                       value="Sistema de navegación" id="feat-nav">
                                <label class="form-check-label" for="feat-nav">Sistema de navegación</label>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-lg-4">
            <!-- Opciones -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white">
                    <h5 class="mb-0">Opciones</h5>
                </div>
                <div class="card-body">
                    <div class="form-check form-switch mb-3">
                        <input class="form-check-input" type="checkbox" name="is_featured" 
                               value="1" id="featured">
                        <label class="form-check-label" for="featured">
                            Destacar vehículo
                        </label>
                    </div>
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" name="is_available" 
                               value="1" id="available" checked>
                        <label class="form-check-label" for="available">
                            Disponible para venta
                        </label>
                    </div>
                </div>
            </div>
            
            <!-- Imágenes -->
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white">
                    <h5 class="mb-0">Imágenes</h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label class="form-label">Imagen principal</label>
                        <input type="file" class="form-control image-upload" name="main_image" 
                               accept="image/*" data-preview="main-preview">
                        <div id="main-preview" class="mt-2"></div>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Galería</label>
                        <input type="file" class="form-control" id="gallery-input" 
                               accept="image/*" multiple>
                        <button type="button" class="btn btn-sm btn-secondary mt-2" 
                                onclick="vehicleManager.addImage('gallery-input')">
                            <i class="bi bi-plus me-1"></i>Agregar imagen
                        </button>
                    </div>
                    
                    <div id="image-gallery" class="row g-2"></div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="mt-4">
        <button type="submit" class="btn btn-primary">
            <i class="bi bi-save me-2"></i>Guardar Vehículo
        </button>
        <a href="/dashboard/inventory" class="btn btn-secondary">Cancelar</a>
    </div>
</form>