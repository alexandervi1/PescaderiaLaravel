@extends('layouts.admin')

@section('title', 'Administradores')

@section('actions')
<a href="{{ route('admin.administradores.create') }}" class="btn btn-primary fw-bold shadow-sm rounded-pill px-4">
    <i class="fas fa-user-shield me-2"></i> Nuevo Administrador
</a>
@endsection

@section('content')
<div class="card border-0 shadow-sm rounded-4">
    <div class="card-header bg-white py-3 border-bottom d-flex justify-content-between align-items-center">
        <h5 class="mb-0 fw-bold"><i class="fas fa-user-shield text-primary me-2"></i> Cuentas de Administrador</h5>
        <span class="badge bg-light text-muted fw-normal">{{ $administradores->count() }} {{ \Illuminate\Support\Str::plural('registro', $administradores->count()) }}</span>
    </div>

    <div class="card-body p-4">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0 datatable">
                <thead class="table-light">
                    <tr>
                        <th class="ps-4">Usuario</th>
                        <th>Nombre completo</th>
                        <th>Contacto</th>
                        <th class="text-center">Contraseña</th>
                        <th class="text-end pe-4">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($administradores as $administrador)
                        <tr>
                            <td class="ps-4 fw-bold text-primary">
                                <i class="fas fa-user-shield me-2 text-secondary"></i>{{ $administrador->nombre_usuario }}
                                @if($administrador->usuario_id === auth()->id())
                                    <span class="badge bg-light text-muted fw-normal ms-1">Tú</span>
                                @endif
                            </td>
                            <td class="fw-bold">{{ $administrador->nombre_completo }}</td>
                            <td>
                                <div class="small"><i class="fas fa-envelope text-muted me-1"></i> {{ $administrador->correo ?: '—' }}</div>
                                <div class="small"><i class="fas fa-phone-alt text-muted me-1"></i> {{ $administrador->telefono ?: '—' }}</div>
                            </td>
                            <td class="text-center">
                                @if($administrador->password_changed_at)
                                    <span class="badge bg-info rounded-pill px-3">Rotada</span>
                                @else
                                    <span class="badge bg-danger rounded-pill px-3">Inicial</span>
                                @endif
                            </td>
                            <td class="text-center pe-4">
                                <div class="d-flex justify-content-center gap-2">
                                    <a href="{{ route('admin.administradores.edit', $administrador->usuario_id) }}" class="btn btn-sm btn-outline-primary btn-action" title="Editar administrador">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <form action="{{ route('admin.administradores.destroy', $administrador->usuario_id) }}" method="POST" class="m-0 p-0">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-outline-danger btn-action" title="Eliminar administrador" onclick="return confirm('¿Eliminar esta cuenta de administrador?')">
                                            <i class="fas fa-trash-alt"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center py-5">
                                <i class="fas fa-user-shield fa-3x text-muted mb-3 opacity-50"></i>
                                <h5 class="text-muted">No hay administradores registrados.</h5>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
