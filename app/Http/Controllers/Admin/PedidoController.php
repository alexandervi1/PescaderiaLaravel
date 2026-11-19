<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Pedido;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class PedidoController extends Controller
{
    public function index(Request $request)
    {
        $estados = Pedido::ESTADOS;
        $estado = $request->query('estado');
        $estado = in_array($estado, $estados, true) ? $estado : null;

        $pedidos = Pedido::with('usuario')
                    ->when($estado, fn ($query) => $query->where('estado', $estado))
                    ->orderBy('fecha_pedido', 'desc')
                    ->get();

        $conteos = Pedido::selectRaw('estado, count(*) as pedidos')
                    ->groupBy('estado')
                    ->pluck('pedidos', 'estado');

        return view('admin.pedidos.index', compact('pedidos', 'estado', 'estados', 'conteos'));
    }

    public function show($id)
    {
        $pedido = Pedido::with(['usuario', 'productos.unidadVenta'])->findOrFail($id);
        return view('admin.pedidos.show', compact('pedido'));
    }

    public function update(Request $request, $id)
    {
        $data = $request->validate([
            'estado' => ['required', Rule::in(Pedido::ESTADOS)],
        ]);

        $pedido = Pedido::with('productos')->findOrFail($id);
        $nuevoEstado = $data['estado'];

        if (! $pedido->puedeCambiarA($nuevoEstado)) {
            return back()->with('error', "No se puede pasar un pedido de {$pedido->estado} a {$nuevoEstado}.");
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
