<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Pedido;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

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
        $data = $request->validate([
            'estado' => 'required|in:Pendiente,Enviado,Entregado,Cancelado'
        ]);

        $pedido = Pedido::with('productos')->findOrFail($id);
        $nuevoEstado = $data['estado'];

        if ($pedido->estado === 'Cancelado' && $nuevoEstado !== 'Cancelado') {
            return back()->with('error', 'Un pedido cancelado no puede reactivarse.');
        }

        if ($nuevoEstado === 'Cancelado' && $pedido->estado !== 'Cancelado') {
            DB::transaction(function () use ($pedido) {
                foreach ($pedido->productos as $producto) {
                    $producto->reponerStock((float) $producto->pivot->cantidad);
                }

                $pedido->estado = 'Cancelado';
                $pedido->save();
            });

            return redirect()->route('admin.pedidos.show', $pedido->pedido_id)
                             ->with('success', 'Pedido cancelado y stock repuesto.');
        }

        $pedido->estado = $nuevoEstado;
        $pedido->save();

        return redirect()->route('admin.pedidos.show', $pedido->pedido_id)
                         ->with('success', 'Estado del pedido actualizado correctamente.');
    }
}
