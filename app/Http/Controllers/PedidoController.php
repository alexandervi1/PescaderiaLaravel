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

        return view('pedidos.show', compact('pedido'));
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

        return view('pedidos.receipt', compact('pedido'));
    }
}
