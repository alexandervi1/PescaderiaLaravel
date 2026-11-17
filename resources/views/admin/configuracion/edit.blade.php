@extends('layouts.admin')

@section('title', 'Configuración del Sistema')

@section('content')
<div class="row mt-4">
    <div class="col-md-9 mx-auto">
        <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
            <div class="card-header bg-white pt-4 pb-0 border-bottom-0">
                <ul class="nav nav-tabs border-bottom-0" id="configTabs" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link {{ $tab === 'negocio' ? 'active fw-bold text-primary px-4 py-3 border-0 border-bottom border-primary border-3' : 'fw-bold text-secondary px-4 py-3 border-0' }}" id="negocio-tab" data-bs-toggle="tab" data-bs-target="#negocio" type="button" role="tab" aria-controls="negocio" aria-selected="{{ $tab === 'negocio' ? 'true' : 'false' }}">
                            <i class="fas fa-store me-2"></i> Datos del Negocio
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link {{ $tab === 'seguridad' ? 'active fw-bold text-primary px-4 py-3 border-0 border-bottom border-primary border-3' : 'fw-bold text-secondary px-4 py-3 border-0' }}" id="seguridad-tab" data-bs-toggle="tab" data-bs-target="#seguridad" type="button" role="tab" aria-controls="seguridad" aria-selected="{{ $tab === 'seguridad' ? 'true' : 'false' }}">
                            <i class="fas fa-shield-alt me-2"></i> Seguridad y Acceso
                        </button>
                    </li>
                </ul>
            </div>
            
            <div class="card-body p-0 bg-light">
                <div class="tab-content" id="configTabsContent">
                    
                    <!-- Pestaña Negocio -->
                    <div class="tab-pane fade {{ $tab === 'negocio' ? 'show active' : '' }} p-4 p-md-5 bg-white" id="negocio" role="tabpanel" aria-labelledby="negocio-tab">
                        <form action="{{ route('admin.configuracion.update') }}" method="POST">
                            @csrf
                            @method('PUT')
                            
                            <h6 class="fw-bold mb-4 text-primary"><i class="fas fa-file-invoice-dollar me-2"></i> Información Fiscal Tributaria</h6>
                            <div class="row g-4 mb-5">
                                <div class="col-md-6">
                                    <label class="form-label fw-bold text-secondary">Razón Social</label>
                                    <input type="text" class="form-control" name="negocio_nombre" value="{{ old('negocio_nombre', $configuraciones['negocio_nombre']) }}" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-bold text-secondary">Identificación (RUC)</label>
                                    <input type="text" class="form-control" name="negocio_ruc" value="{{ old('negocio_ruc', $configuraciones['negocio_ruc']) }}" required>
                                </div>
                                <div class="col-md-12">
                                    <label class="form-label fw-bold text-secondary">Leyenda (Ej. Contribuyente Régimen RIMPE)</label>
                                    <input type="text" class="form-control" name="negocio_leyenda" value="{{ old('negocio_leyenda', $configuraciones['negocio_leyenda']) }}">
                                </div>
                            </div>

                            <h6 class="fw-bold mb-4 text-primary"><i class="fas fa-map-marked-alt me-2"></i> Local y Contactos</h6>
                            <div class="row g-4 mb-5">
                                <div class="col-md-7">
                                    <label class="form-label fw-bold text-secondary">Dirección del Local</label>
                                    <input type="text" class="form-control" name="negocio_direccion" value="{{ old('negocio_direccion', $configuraciones['negocio_direccion']) }}" required>
                                </div>
                                <div class="col-md-5">
                                    <label class="form-label fw-bold text-secondary">Teléfono de Soporte</label>
                                    <input type="text" class="form-control" name="negocio_telefono" value="{{ old('negocio_telefono', $configuraciones['negocio_telefono']) }}" required>
                                </div>
                            </div>

                            <h6 class="fw-bold mb-4 text-primary"><i class="fas fa-calculator me-2"></i> Cálculos Comerciales</h6>
                            <div class="row g-4 mb-4">
                                <div class="col-md-5">
                                    <label for="iva_actual" class="form-label fw-bold text-secondary">Porcentaje de IVA (%)</label>
                                    <div class="input-group border rounded">
                                        <span class="input-group-text bg-light border-0"><i class="fas fa-percent text-primary"></i></span>
                                        <input type="number" step="0.01" min="0" max="100" class="form-control" id="iva_actual" name="iva_actual" value="{{ old('iva_actual', $configuraciones['iva_actual']) }}" required>
                                    </div>
                                </div>
                            </div>

                            <div class="d-flex justify-content-end mt-5 pt-3 border-top">
                                <button type="submit" class="btn btn-primary btn-lg fw-bold shadow-sm rounded-pill px-5">
                                    Guardar Negocio <i class="fas fa-save ms-2"></i>
                                </button>
                            </div>
                        </form>
                    </div>
                    
                    <!-- Pestaña Seguridad -->
                    <div class="tab-pane fade {{ $tab === 'seguridad' ? 'show active' : '' }} p-4 p-md-5 bg-white" id="seguridad" role="tabpanel" aria-labelledby="seguridad-tab">
                        <div class="alert alert-info rounded-3 mb-5 border-0 shadow-sm d-flex align-items-center">
                            <i class="fas fa-info-circle fa-2x me-3 text-info"></i>
                            <div>
                                <h6 class="fw-bold mb-1">Protección de Cuenta</h6>
                                <p class="mb-0 small text-dark">Modifique su usuario y contraseña maestra aquí. Al guardar, el sistema requerirá que inicie sesión nuevamente.</p>
                            </div>
                        </div>

                        <form action="{{ route('admin.configuracion.seguridad') }}" method="POST">
                            @csrf
                            @method('PUT')
                            
                            <div class="row g-4">
                                <div class="col-md-12 mb-2">
                                    <label class="form-label fw-bold text-secondary">Nombre Completo del Administrador</label>
                                    <div class="input-group border rounded">
                                        <span class="input-group-text bg-light border-0"><i class="fas fa-id-badge text-primary"></i></span>
                                        <input type="text" class="form-control" name="nombre_completo" value="{{ old('nombre_completo', auth()->user()->nombre_completo) }}" required autocomplete="name">
                                    </div>
                                    <div class="form-text mt-2"><i class="fas fa-info-circle me-1"></i> Este es tu nombre real que verán los clientes en algunas constancias o recibos.</div>
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label class="form-label fw-bold text-secondary">Nombre de Usuario (Para Iniciar Sesión)</label>
                                    <div class="input-group border rounded">
                                        <span class="input-group-text bg-light border-0"><i class="fas fa-user-shield text-primary"></i></span>
                                        <input type="text" class="form-control" name="nombre_usuario" value="{{ old('nombre_usuario', auth()->user()->nombre_usuario) }}" required autocomplete="username">
                                    </div>
                                </div>
                                
                                <div class="col-md-6 mb-3">
                                    <label class="form-label fw-bold text-secondary">Nueva Contraseña</label>
                                    <div class="input-group border rounded">
                                        <span class="input-group-text bg-light border-0"><i class="fas fa-key text-primary"></i></span>
                                        <input type="password" class="form-control" name="password" placeholder="Dejar en blanco para no cambiar" autocomplete="new-password">
                                    </div>
                                    <div class="form-text mt-2"><i class="fas fa-shield-alt me-1"></i> Mínimo 8 caracteres.</div>
                                </div>
                            </div>

                            <div class="d-flex justify-content-end mt-5 pt-3 border-top">
                                <button type="submit" class="btn btn-dark btn-lg fw-bold shadow-sm rounded-pill px-5">
                                    Actualizar Seguridad <i class="fas fa-lock ms-2"></i>
                                </button>
                            </div>
                        </form>
                    </div>

                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Handle tab persistence or deep-linking via URL hash
        if (window.location.hash === '#seguridad') {
            var tab = new bootstrap.Tab(document.querySelector('#seguridad-tab'));
            tab.show();
        }
        
        // Stylish tab transitions
        const tabs = document.querySelectorAll('.nav-link');
        tabs.forEach(tab => {
            tab.addEventListener('shown.bs.tab', function(e) {
                // Reset all tabs
                tabs.forEach(t => {
                    t.classList.remove('text-primary', 'border-bottom', 'border-primary', 'border-3');
                    t.classList.add('text-secondary', 'border-0');
                });
                // Style active tab
                e.target.classList.remove('text-secondary', 'border-0');
                e.target.classList.add('text-primary', 'border-bottom', 'border-primary', 'border-3');
            });
        });
    });
</script>
@endpush
