@extends('layouts.admin')

@section('title', 'Gestión de Clientes')

@section('actions')
<a href="{{ route('admin.clientes.create') }}" class="btn btn-primary fw-bold shadow-sm rounded-pill px-4">
    <i class="fas fa-user-plus me-2"></i> Añadir Cliente
</a>
@endsection

@section('content')
<div class="card border-0 shadow-sm rounded-4">
    <div class="card-header bg-white py-3 border-bottom d-flex justify-content-between align-items-center">
        <h5 class="mb-0 fw-bold"><i class="fas fa-users text-primary me-2"></i> Clientes Registrados</h5>
        <span class="badge bg-light text-muted fw-normal">{{ $clientes->count() }} {{ \Illuminate\Support\Str::plural('registro', $clientes->count()) }}</span>
    </div>
    <div class="card-body p-4">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0 datatable">
                <thead class="table-light">
                    <tr>
                        <th scope="col" class="ps-4">Usuario</th>
                        <th scope="col">Nombre Completo</th>
                        <th scope="col">Contacto</th>
                        <th scope="col">Dirección</th>
                        <th scope="col" class="text-center pe-4">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($clientes as $cliente)
                        <tr>
                            <td class="ps-4 py-3 fw-bold text-primary">
                                <i class="fas fa-user-circle me-2 text-secondary"></i>
                                {{ $cliente->nombre_usuario }}
                            </td>
                            <td class="fw-bold">
                                {{ $cliente->nombre_completo }}
                            </td>
                            <td>
                                @if($cliente->correo)
                                    <div class="small"><i class="fas fa-envelope text-muted me-1"></i> {{ $cliente->correo }}</div>
                                @endif
                                @if($cliente->telefono)
                                    <div class="small"><i class="fas fa-phone text-muted me-1"></i> {{ $cliente->telefono }}</div>
                                @endif
                            </td>
                            <td>
                                <span class="small text-truncate d-inline-block" style="max-width: 250px;" title="{{ $cliente->direccion }}">
                                    <i class="fas fa-map-marker-alt text-muted me-1"></i> {{ $cliente->direccion ?? 'N/A' }}
                                </span>
                            </td>
                            <td class="text-center pe-4">
                                <div class="d-flex justify-content-center gap-2">
                                    <a href="{{ route('admin.clientes.edit', $cliente->usuario_id) }}" class="btn btn-sm btn-outline-primary btn-action" title="Editar">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <form action="{{ route('admin.clientes.destroy', $cliente->usuario_id) }}" method="POST" class="m-0 p-0">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-outline-danger btn-action" title="Eliminar" onclick="return confirm('¿Confirma la eliminación?')">
                                            <i class="fas fa-trash-alt"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center py-5">
                                <i class="fas fa-users-slash fa-3x text-muted mb-3 opacity-50"></i>
                                <h5 class="text-muted">No se han registrado clientes.</h5>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
