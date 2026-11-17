@extends('layouts.app')

@section('title', 'Iniciar Sesión')

@section('content')
<div class="row justify-content-center my-5">
    <div class="col-md-5">
        <div class="card shadow-lg border-0 rounded-4 overflow-hidden">
            <div class="card-header border-0 text-center py-4 bg-white">
                <img src="{{ asset($negocio['logo']) }}" alt="Logo" class="img-fluid mb-3" style="max-height: 80px;">
                <hr class="mx-5">
                <h3 class="mb-0 fw-bold text-brand">Iniciar Sesión</h3>
                <hr class="mx-5">
            </div>
            <div class="card-body p-4 pt-0">
                @if($errors->has('login_error'))
                    <div class="alert alert-danger py-2 small">{{ $errors->first('login_error') }}</div>
                @endif
                
                <form action="{{ route('login') }}" method="POST">
                    @csrf
                    <div class="mb-3">
                        <label for="nombre_usuario" class="form-label fw-bold">Usuario o correo</label>
                        <input type="text" class="form-control py-2" id="nombre_usuario" name="nombre_usuario" value="{{ old('nombre_usuario') }}" required autofocus placeholder="Tu usuario o correo electrónico" style="border-radius: 8px;">
                    </div>
                    
                    <div class="mb-4">
                        <label for="password" class="form-label fw-bold">Contraseña</label>
                        <input type="password" class="form-control py-2" id="password" name="password" required placeholder="Tu contraseña" style="border-radius: 8px;">
                    </div>

                    <div class="d-grid shadow-sm">
                        <button type="submit" class="btn btn-primary py-3 fw-bold">
                            <i class="fas fa-sign-in-alt me-2"></i> Iniciar Sesión
                        </button>
                    </div>
                </form>
            </div>
            <div class="card-footer bg-light text-center py-3 border-0">
                <p class="mb-0 text-muted">¿No tienes una cuenta? <a href="{{ route('register') }}" class="fw-bold text-decoration-none text-brand">Regístrate aquí</a></p>
            </div>
        </div>
        <div class="text-center mt-3">
            <a href="{{ route('home') }}" class="text-secondary text-decoration-none small"><i class="fas fa-arrow-left me-1"></i> Volver al Inicio</a>
        </div>
    </div>
</div>
@endsection
