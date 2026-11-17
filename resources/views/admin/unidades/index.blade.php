@extends('layouts.admin')

@section('title', 'Unidades de Medida')

@section('actions')
<button type="button" class="btn btn-primary fw-bold shadow-sm rounded-pill px-4" data-bs-toggle="modal" data-bs-target="#modalUnidad">
    <i class="fas fa-plus-circle me-2"></i> Nueva Unidad
</button>
@endsection

@section('content')
<div class="card border-0 shadow-sm rounded-4">
    <div class="card-header bg-white py-3 border-bottom d-flex justify-content-between align-items-center">
        <h5 class="mb-0 fw-bold"><i class="fas fa-ruler-combined text-primary me-2"></i> Unidades de Medida</h5>
        <span class="badge bg-light text-muted fw-normal">{{ $unidades->count() }} {{ \Illuminate\Support\Str::plural('registro', $unidades->count()) }}</span>
    </div>

    <div class="card-body p-4">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0 datatable">
                <thead class="table-light">
                    <tr>
                        <th class="ps-4">Unidad</th>
                        <th class="text-center">Abreviatura</th>
                        <th class="text-center">Productos que la usan</th>
                        <th class="text-end pe-4">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($unidades as $unidad)
                        @php $usos = $unidad->productos_compra_count + $unidad->productos_venta_count; @endphp
                        <tr>
                            <td class="ps-4 fw-bold">{{ $unidad->nombre }}</td>
                            <td class="text-center"><span class="badge bg-light text-dark">{{ $unidad->abreviatura }}</span></td>
                            <td class="text-center">
                                <span class="badge {{ $usos > 0 ? 'bg-info' : 'bg-light text-muted' }} rounded-pill px-3">{{ $usos }}</span>
                            </td>
                            <td class="text-center pe-4">
                                <div class="d-flex justify-content-center gap-2">
                                    <button type="button" class="btn btn-sm btn-outline-primary btn-action btn-edit"
                                            data-id="{{ $unidad->unidad_id }}"
                                            data-nombre="{{ $unidad->nombre }}"
                                            data-abreviatura="{{ $unidad->abreviatura }}"
                                            data-bs-toggle="modal" data-bs-target="#modalUnidad"
                                            title="Editar unidad">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <form action="{{ route('admin.unidades.destroy', $unidad->unidad_id) }}" method="POST" class="m-0 p-0">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-outline-danger btn-action" title="Eliminar unidad" onclick="return confirm('¿Eliminar esta unidad de medida?')">
                                            <i class="fas fa-trash-alt"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="text-center py-5">
                                <i class="fas fa-ruler-combined fa-3x text-muted mb-3 opacity-50"></i>
                                <h5 class="text-muted">No hay unidades registradas.</h5>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="modal fade" id="modalUnidad" tabindex="-1" aria-labelledby="modalUnidadLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded-4 border-0 shadow">
            <div class="modal-header border-bottom bg-light rounded-top-4">
                <h5 class="modal-title fw-bold text-primary" id="modalUnidadLabel"><i class="fas fa-ruler me-2"></i> <span id="unidad-titulo">Nueva Unidad</span></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <form action="{{ route('admin.unidades.store') }}" method="POST" id="formUnidad">
                @csrf
                <div id="unidad-method"></div>

                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label for="unidad-nombre" class="form-label fw-bold">Nombre *</label>
                        <input type="text" class="form-control" id="unidad-nombre" name="nombre" required placeholder="Ej. Tonelada">
                    </div>
                    <div class="mb-0">
                        <label for="unidad-abreviatura" class="form-label fw-bold">Abreviatura *</label>
                        <input type="text" class="form-control" id="unidad-abreviatura" name="abreviatura" required maxlength="5" placeholder="Ej. Tn">
                        <div class="form-text mt-2"><i class="fas fa-info-circle me-1"></i> Se usa en el catálogo y en los comprobantes. Máximo 5 caracteres.</div>
                    </div>
                </div>
                <div class="modal-footer border-top bg-light rounded-bottom-4">
                    <button type="button" class="btn btn-outline-secondary fw-bold px-4" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary fw-bold px-4 shadow-sm" id="unidad-guardar">Registrar Unidad</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const modal = document.getElementById('modalUnidad');
        const form = document.getElementById('formUnidad');
        const titulo = document.getElementById('unidad-titulo');
        const inputNombre = document.getElementById('unidad-nombre');
        const inputAbreviatura = document.getElementById('unidad-abreviatura');
        const btnGuardar = document.getElementById('unidad-guardar');
        const methodContainer = document.getElementById('unidad-method');
        const storeRoute = "{{ route('admin.unidades.store') }}";

        modal.addEventListener('hidden.bs.modal', function () {
            form.action = storeRoute;
            methodContainer.innerHTML = '';
            inputNombre.value = '';
            inputAbreviatura.value = '';
            titulo.textContent = 'Nueva Unidad';
            btnGuardar.textContent = 'Registrar Unidad';
        });

        document.querySelectorAll('.btn-edit').forEach(button => {
            button.addEventListener('click', function () {
                const id = this.getAttribute('data-id');
                form.action = `{{ url('/admin/unidades') }}/${id}`;
                methodContainer.innerHTML = '@method("PUT")';
                inputNombre.value = this.getAttribute('data-nombre');
                inputAbreviatura.value = this.getAttribute('data-abreviatura');
                titulo.textContent = 'Editar Unidad';
                btnGuardar.textContent = 'Guardar Cambios';
            });
        });
    });
</script>
@endpush
