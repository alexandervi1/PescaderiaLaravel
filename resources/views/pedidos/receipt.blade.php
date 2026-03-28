@extends('layouts.app')

@section('title', 'Recibo de Compra - ' . $pedido->numero_comprobante)

@push('styles')
<style>
    @media print {
        .no-print, nav, footer, .btn, .mb-4.d-flex {
            display: none !important;
        }
        .container {
            width: 100% !important;
            max-width: 100% !important;
            padding: 0 !important;
            margin: 0 !important;
        }
        .receipt-container {
            box-shadow: none !important;
            border: 1px solid #ddd !important;
        }
    }
    .receipt-container {
        max-width: 800px;
        margin: 20px auto;
        padding: 40px;
        background: white;
        border-radius: 8px;
        box-shadow: 0 0 20px rgba(0,0,0,0.1);
        font-family: 'Courier New', Courier, monospace;
    }
    .receipt-header {
        border-bottom: 2px solid #333;
        margin-bottom: 20px;
        padding-bottom: 20px;
    }
    .business-name {
        font-size: 24px;
        font-weight: bold;
        color: #1A519D;
    }
    .receipt-title {
        background: #f8f9fa;
        padding: 10px;
        border: 1px solid #ddd;
        text-align: center;
        margin-bottom: 20px;
    }
    .info-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 20px;
        margin-bottom: 30px;
    }
    .table-receipt th {
        background-color: #f8f9fa;
        border-bottom: 2px solid #dee2e6;
    }
    .totals-box {
        width: 300px;
        margin-left: auto;
    }
</style>
@endpush

@section('content')
<div class="no-print text-center mb-4">
    <button onclick="window.print()" class="btn btn-primary btn-lg shadow-sm">
        <i class="fas fa-print me-2"></i> Imprimir Recibo
    </button>
</div>

<div class="receipt-container shadow">
    <div class="receipt-header row">
        <div class="col-8">
            <div class="business-name">{{ $negocio['nombre'] }}</div>
            <div class="mt-2 small">
                <strong>RUC:</strong> {{ $negocio['ruc'] }}<br>
                <strong>Dirección:</strong> {{ $negocio['direccion'] }}<br>
                <strong>Teléfono:</strong> {{ $negocio['telefono'] }}<br>
                <strong>Matriz:</strong> MANTA - ECUADOR
            </div>
        </div>
        <div class="col-4 text-center">
            <img src="{{ asset('img/Pescaderia Don Walter logo.png') }}" alt="Logo" style="max-width: 150px;">
        </div>
    </div>

    <div class="receipt-title">
        <h4 class="mb-1 fw-bold">NOTA DE VENTA</h4>
        <div class="text-danger fw-bold">№ {{ $pedido->numero_comprobante }}</div>
    </div>

    <div class="info-grid small">
        <div class="border p-3 rounded">
            <h6 class="fw-bold border-bottom pb-2 mb-2">DATOS DEL CLIENTE</h6>
            <strong>Nombre:</strong> {{ $pedido->usuario->nombre_completo }}<br>
            <strong>C.I./RUC:</strong> {{ $pedido->usuario->identificacion ?? 'S/N' }}<br>
            <strong>Dirección:</strong> {{ $pedido->usuario->direccion }}<br>
            <strong>Correo:</strong> {{ $pedido->usuario->correo }}
        </div>
        <div class="border p-3 rounded">
            <h6 class="fw-bold border-bottom pb-2 mb-2">DETALLES DE EMISIÓN</h6>
            <strong>Fecha:</strong> {{ $pedido->fecha_pedido->format('d/m/Y') }}<br>
            <strong>Hora:</strong> {{ $pedido->fecha_pedido->format('H:i:s') }}<br>
            <strong>Forma Pago:</strong> Transferencia/Efectivo<br>
            <strong>Estado:</strong> PAGADO
        </div>
    </div>

    <table class="table table-bordered table-receipt small">
        <thead>
            <tr>
                <th style="width: 10%;">Cant.</th>
                <th>Descripción</th>
                <th style="width: 15%;" class="text-end">P. Unit</th>
                <th style="width: 15%;" class="text-end">V. Total</th>
            </tr>
        </thead>
        <tbody>
            @foreach($pedido->productos as $producto)
            <tr>
                <td>{{ number_format($producto->pivot->cantidad, 2) }}</td>
                <td>{{ $producto->nombre }} ({{ $producto->unidadVenta->nombre ?? 'Unidad' }})</td>
                <td class="text-end">${{ number_format($producto->precio, 2) }}</td>
                <td class="text-end">${{ number_format($producto->precio * $producto->pivot->cantidad, 2) }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <div class="row mt-4">
        <div class="col-7">
            <div class="border p-2 rounded small bg-light" style="min-height: 100px;">
                <strong>Observaciones:</strong><br>
                {{ $negocio['leyenda'] }}<br>
                Gracias por preferir Pescadería Don Walter. "Del mar a tu mesa".
            </div>
        </div>
        <div class="col-5">
            <table class="table table-sm table-borderless fw-bold small mb-0">
                <thead class="visually-hidden">
                    <tr>
                        <th>Concepto</th>
                        <th>Valor</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>SUBTOTAL 0%:</td>
                        <td class="text-end">$0.00</td>
                    </tr>
                    <tr>
                        <td>SUBTOTAL 15%:</td>
                        <td class="text-end">${{ number_format($pedido->total / 1.15, 2) }}</td>
                    </tr>
                    <tr>
                        <td>IVA 15%:</td>
                        <td class="text-end">${{ number_format($pedido->total - ($pedido->total / 1.15), 2) }}</td>
                    </tr>
                    <tr class="border-top table-secondary">
                        <td class="fs-6">VALOR TOTAL:</td>
                        <td class="text-end fs-5">${{ number_format($pedido->total, 2) }}</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-5 row text-center small">
        <div class="col-6">
            <div class="border-top mx-4 pt-2">FIRMA AUTORIZADA</div>
        </div>
        <div class="col-6">
            <div class="border-top mx-4 pt-2">RECIBÍ CONFORME</div>
        </div>
    </div>
</div>
@endsection
