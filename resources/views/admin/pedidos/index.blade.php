@extends('layouts.admin')

@section('title', 'Gestión de Pedidos')

@section('content')
<div class="card border-0 shadow-sm rounded-4">
    <div class="card-body p-4">
        <div class="table-responsive">
            <table class="table table-hover align-middle datatable">
                <thead class="table-light">
                    <tr>
                        <th>N° Comprobante</th>
                        <th>Fecha</th>
                        <th>Cliente</th>
                        <th>Total</th>
                        <th>Estado</th>
                        <th>Acción</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($pedidos as $pedido)
                    <tr>
                        <td class="fw-bold">{{ $pedido->numero_comprobante ?? 'N/A' }}</td>
                        <td>{{ $pedido->fecha_pedido->format('d/m/Y H:i') }}</td>
                        <td>{{ $pedido->usuario->nombre_completo ?? 'N/A' }}</td>
                        <td>${{ number_format($pedido->total, 2) }}</td>
                        <td>
                            @if($pedido->estado == 'Entregado')
                                <span class="badge bg-success rounded-pill px-3 py-2">Entregado</span>
                            @elseif($pedido->estado == 'Enviado')
                                <span class="badge bg-info rounded-pill px-3 py-2 text-dark">Enviado</span>
                            @elseif($pedido->estado == 'Cancelado')
                                <span class="badge bg-danger rounded-pill px-3 py-2">Cancelado</span>
                            @else
                                <span class="badge bg-warning rounded-pill px-3 py-2 text-dark">Pendiente</span>
                            @endif
                        </td>
                        <td class="text-center pe-4">
                            <a href="{{ route('admin.pedidos.show', $pedido->pedido_id) }}" class="btn btn-sm btn-outline-primary btn-action" title="Ver Detalle">
                                <i class="fas fa-eye"></i>
                            </a>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
