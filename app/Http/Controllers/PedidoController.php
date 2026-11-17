<?php

namespace App\Http\Controllers;

use App\Models\Pedido;

class PedidoController extends Controller
{
    public function index()
    {
        $pedidos = Pedido::with('productos')->where('usuario_id', auth()->id())->orderBy('fecha_pedido', 'desc')->get();
        return view('pedidos.index', compact('pedidos'));
    }

    public function show($id)
    {
        $pedido = Pedido::with(['productos.unidadVenta'])->where('usuario_id', auth()->id())->findOrFail($id);
        $ivaPct = \App\Models\Configuracion::obtenerIva();

        return view('pedidos.show', compact('pedido', 'ivaPct'));
    }

    public function process(\App\Services\OrderService $orderService)
    {
        try {
            $pedido = $orderService->processOrder(auth()->user());

            return redirect()->route('pedidos.show', $pedido->pedido_id)
                ->with('success', 'Pedido procesado con éxito. Gracias por tu compra.');
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    public function receipt($id)
    {
        $pedido = Pedido::with(['productos.unidadVenta', 'usuario'])->where('usuario_id', auth()->id())->findOrFail($id);
        
        // Datos del negocio (SRI) leídos desde la Base de Datos
        $negocio = [
            'nombre' => \App\Models\Configuracion::obtenerValor('negocio_nombre', 'COMERCIAL DE MARISCOS DON WALTER'),
            'ruc' => \App\Models\Configuracion::obtenerValor('negocio_ruc', '1301234567001'),
            'direccion' => \App\Models\Configuracion::obtenerValor('negocio_direccion', 'Manta, Ecuador'),
            'telefono' => \App\Models\Configuracion::obtenerValor('negocio_telefono', '0999999999'),
            'leyenda' => \App\Models\Configuracion::obtenerValor('negocio_leyenda', 'CONTRIBUYENTE RÉGIMEN RIMPE - NEGOCIO POPULAR'),
            'iva_pct' => \App\Models\Configuracion::obtenerIva(),
        ];

        return view('pedidos.receipt', compact('pedido', 'negocio'));
    }
}
