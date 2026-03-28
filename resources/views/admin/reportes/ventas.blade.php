@extends('layouts.admin')

@section('title', 'Reporte de Ventas')

@section('actions')
<button onclick="window.print()" class="btn btn-outline-secondary fw-bold rounded-pill px-4">
    <i class="fas fa-print me-2"></i> Imprimir Reporte
</button>
@endsection

@section('content')
<div class="row g-4 mb-4">
    <div class="col-md-12">
        <div class="card border-0 shadow-sm rounded-4">
            <div class="card-header bg-white py-3 border-bottom d-flex align-items-center">
                <i class="fas fa-chart-bar text-primary fs-5 me-2"></i>
                <h6 class="mb-0 fw-bold m-0">Ventas por Mes (Año Actual: {{ date('Y') }})</h6>
            </div>
            <div class="card-body p-4">
                <div class="table-responsive">
                    <table class="table table-bordered table-hover mt-3">
                        <thead class="table-light">
                            <tr>
                                <th>Mes</th>
                                <th class="text-center">Total Pedidos</th>
                                <th class="text-end">Ingresos Totales</th>
                            </tr>
                        </thead>
                        <tbody>
                            @php
                                $meses_nombres = ['', 'Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio', 'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre'];
                                $total_anual_pedidos = 0;
                                $total_anual_ingresos = 0;
                            @endphp
                            
                            @forelse($ventasMes as $venta)
                                @php
                                    $total_anual_pedidos += $venta->total_pedidos;
                                    $total_anual_ingresos += $venta->total_ingresos;
                                @endphp
                                <tr>
                                    <td class="fw-bold">{{ $meses_nombres[$venta->mes] }}</td>
                                    <td class="text-center">{{ $venta->total_pedidos }}</td>
                                    <td class="text-end text-success fw-bold">${{ number_format($venta->total_ingresos, 2) }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="3" class="text-center py-4 text-muted">No hay registros de ventas este año.</td>
                                </tr>
                            @endforelse
                        </tbody>
                        @if($ventasMes->isNotEmpty())
                        <tfoot class="table-primary fw-bold">
                            <tr>
                                <td>TOTAL ANUAL</td>
                                <td class="text-center">{{ $total_anual_pedidos }}</td>
                                <td class="text-end text-success">${{ number_format($total_anual_ingresos, 2) }}</td>
                            </tr>
                        </tfoot>
                        @endif
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="card border-0 shadow-sm rounded-4 mt-4">
    <div class="card-header bg-white py-3 border-bottom d-flex align-items-center">
        <i class="fas fa-history text-info fs-5 me-2"></i>
        <h6 class="mb-0 fw-bold m-0">Últimos 10 Pedidos</h6>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="ps-4">No. Orden</th>
                        <th>Fecha</th>
                        <th>Cliente</th>
                        <th class="text-end pe-4">Total</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($pedidosRecientes as $pedido)
                        <tr>
                            <td class="ps-4 fw-bold">#{{ str_pad($pedido->pedido_id, 5, '0', STR_PAD_LEFT) }}</td>
                            <td class="text-muted"><i class="far fa-calendar-alt me-1"></i> {{ $pedido->fecha_pedido->format('d/m/Y H:i') }}</td>
                            <td>{{ $pedido->usuario->nombre_completo ?? 'Cliente Eliminado' }}</td>
                            <td class="text-end pe-4 fw-bold text-success">${{ number_format($pedido->total, 2) }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="text-center py-4 text-muted">No hay pedidos recientes.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
