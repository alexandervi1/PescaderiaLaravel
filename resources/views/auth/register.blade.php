@extends('layouts.app')

@section('title', 'Registro de Cliente')

@section('content')
<div class="row justify-content-center my-5">
    <div class="col-md-7">
        <div class="card shadow-lg border-0 rounded-4 overflow-hidden">
            <div class="card-header border-0 text-center py-4 bg-white">
                <img src="{{ asset('img/Pescaderia Don Walter logo.png') }}" alt="Logo" class="img-fluid mb-3" style="max-height: 80px;">
                <hr class="mx-5">
                <h3 class="mb-0 fw-bold" style="color: #1A519D;">Registro de Clientes</h3>
                <hr class="mx-5">
            </div>
            <div class="card-body p-4 pt-0">
                @if ($errors->any())
                    <div class="alert alert-danger py-2 small">
                        <ul class="mb-0 ps-3">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form action="{{ route('register') }}" method="POST">
                    @csrf
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label for="nombre_completo" class="form-label fw-bold">Nombre Completo</label>
                            <input type="text" class="form-control py-2" id="nombre_completo" name="nombre_completo" value="{{ old('nombre_completo') }}" required placeholder="Ej. Juan Pérez" style="border-radius: 8px;">
                        </div>
                        
                        <div class="col-md-6">
                            <label for="nombre_usuario" class="form-label fw-bold">Nombre de Usuario</label>
                            <input type="text" class="form-control py-2" id="nombre_usuario" name="nombre_usuario" value="{{ old('nombre_usuario') }}" required placeholder="Para iniciar sesión" style="border-radius: 8px;">
                        </div>

                        <div class="col-md-6">
                            <label for="correo" class="form-label fw-bold">Correo Electrónico</label>
                            <input type="email" class="form-control py-2" id="correo" name="correo" value="{{ old('correo') }}" required placeholder="ejemplo@correo.com" style="border-radius: 8px;">
                        </div>

                        <div class="col-md-6">
                            <label for="telefono" class="form-label fw-bold">Teléfono</label>
                            <input type="text" class="form-control py-2" id="telefono" name="telefono" value="{{ old('telefono') }}" maxlength="10" placeholder="099xxxxxxx" style="border-radius: 8px;">
                        </div>

                        <div class="col-12">
                            <label for="direccion" class="form-label fw-bold">Dirección Completa</label>
                            <input type="text" class="form-control py-2" id="direccion" name="direccion" value="{{ old('direccion') }}" required placeholder="Ej. Av. Principal 123 y Calle Secundaria" style="border-radius: 8px;">
                        </div>

                        <div class="col-md-6">
                            <label for="password" class="form-label fw-bold">Contraseña</label>
                            <input type="password" class="form-control py-2" id="password" name="password" required minlength="6" placeholder="Mínimo 6 caracteres" style="border-radius: 8px;">
                        </div>

                        <div class="col-md-6">
                            <label for="password_confirmation" class="form-label fw-bold">Confirmar Contraseña</label>
                            <input type="password" class="form-control py-2" id="password_confirmation" name="password_confirmation" required minlength="6" placeholder="Repite la contraseña" style="border-radius: 8px;">
                        </div>
                    </div>

                    <div class="mt-4 text-center">
                        <button type="submit" class="btn btn-lg px-5 text-white fw-bold shadow-sm" style="background-color: #002E5D; border-radius: 8px; transition: all 0.3s ease;">
                            <i class="fas fa-user-plus me-2"></i> Registrarse
                        </button>
                    </div>
                </form>
            </div>
            <div class="card-footer bg-light text-center py-3 border-0">
                <p class="mb-0 text-muted">¿Ya tienes cuenta? <a href="{{ route('login') }}" class="fw-bold text-decoration-none" style="color: #1A519D;">Inicia sesión aquí</a></p>
            </div>
        </div>
        <div class="text-center mt-3">
            <a href="{{ route('home') }}" class="text-secondary text-decoration-none small"><i class="fas fa-arrow-left me-1"></i> Volver al Inicio</a>
        </div>
    </div>
</div>
@endsection
