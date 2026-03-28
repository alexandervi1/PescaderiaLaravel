<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Pedido;
use Illuminate\Http\Request;

class PedidoController extends Controller
{
    public function index()
    {
        $pedidos = Pedido::with('usuario')
                    ->orderBy('fecha_pedido', 'desc')
                    ->get();
        return view('admin.pedidos.index', compact('pedidos'));
    }

    public function show($id)
    {
        $pedido = Pedido::with(['usuario', 'productos.unidadVenta'])->findOrFail($id);
        return view('admin.pedidos.show', compact('pedido'));
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'estado' => 'required|in:Pendiente,Enviado,Entregado,Cancelado'
        ]);

        $pedido = Pedido::findOrFail($id);
        $pedido->estado = $request->estado;
        $pedido->save();

        return redirect()->route('admin.pedidos.show', $pedido->pedido_id)
                         ->with('success', 'Estado del pedido actualizado correctamente.');
    }
}
