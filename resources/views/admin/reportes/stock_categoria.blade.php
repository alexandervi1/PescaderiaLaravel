@extends('layouts.admin')

@section('title', 'Inventario por Categoría')

@section('actions')
<button onclick="window.print()" class="btn btn-outline-secondary fw-bold rounded-pill px-4">
    <i class="fas fa-print me-2"></i> Imprimir Reporte
</button>
@endsection

@section('content')
<div class="row g-4 mb-4">
    <div class="col-md-5">
        <div class="card border-0 shadow-sm rounded-4 h-100">
            <div class="card-header bg-white py-3 border-bottom d-flex align-items-center">
                <i class="fas fa-tags text-primary fs-5 me-2"></i>
                <h6 class="mb-0 fw-bold m-0">Distribución de Productos</h6>
            </div>
            <div class="card-body p-0">
                <ul class="list-group list-group-flush rounded-bottom-4 mt-2">
                    @forelse($categorias as $categoria)
                        <li class="list-group-item d-flex justify-content-between align-items-center py-3">
                            <span class="fw-bold text-dark"><i class="fas fa-folder text-warning me-2"></i> {{ $categoria->nombre }}</span>
                            <span class="badge bg-primary rounded-pill fs-6">{{ $categoria->productos_count }} ítems</span>
                        </li>
                    @empty
                        <li class="list-group-item text-center py-4 text-muted">No se han registrado categorías.</li>
                    @endforelse
                </ul>
            </div>
        </div>
    </div>

    <div class="col-md-7">
        <div class="card border-0 shadow-sm rounded-4 h-100">
            <div class="card-header bg-white py-3 border-bottom d-flex align-items-center">
                <i class="fas fa-cubes text-info fs-5 me-2"></i>
                <h6 class="mb-0 fw-bold m-0">Volumen Físico Estimado (Unidades de Venta)</h6>
            </div>
            <div class="card-body">
                <div class="mt-2">
                    @php
                        // Determinar el max para los porcentajes de la barra una sola vez (fuera del bucle)
                        $max = $stockCategorias->max('total_stock');
                    @endphp
                    @forelse($stockCategorias as $index => $item)
                        @php
                            // Colores rotativos simples para barras
                            $colores = ['bg-primary', 'bg-success', 'bg-info', 'bg-warning', 'bg-danger'];
                            $color = $colores[$index % count($colores)];
                            
                            $pct = $max > 0 ? round(($item->total_stock / $max) * 100, 2) : 0;
                        @endphp
                        
                        <div class="mb-4">
                            <div class="d-flex justify-content-between mb-1">
                                <span class="fw-bold">{{ $item->nombre }}</span>
                                <span class="fw-bold text-muted">{{ number_format($item->total_stock, 0) }} u.</span>
                            </div>
                            <div class="progress" style="height: 12px; border-radius: 10px;">
                                <div class="progress-bar {{ $color }} progress-bar-striped progress-bar-animated" role="progressbar" style="width: {{ $pct }}%;" aria-valuenow="{{ $pct }}" aria-valuemin="0" aria-valuemax="100"></div>
                            </div>
                        </div>
                    @empty
                        <div class="text-center py-4 text-muted">No hay datos de inventario.</div>
                    @endforelse
                </div>
                
                <div class="alert alert-light mt-4 mb-0 border rounded-3 text-muted small">
                    <i class="fas fa-info-circle me-1 text-primary"></i> 
                    El reporte suma el stock de todos los productos y los convierte a su respectiva <strong>Unidad de Venta</strong> usando el factor de conversión predefinido, agrupando los resultados por categoría.
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
