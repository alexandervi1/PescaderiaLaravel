<?php

namespace App\Http\Controllers;

use App\Models\Carrito;
use App\Models\Producto;
use Illuminate\Http\Request;

class CarritoController extends Controller
{
    public function index()
    {
        $items = Carrito::with('producto')
            ->where('usuario_id', auth()->id())
            ->get();

        $total = $items->sum(function ($item) {
            return $item->cantidad * $item->producto->precio;
        });

        return view('carrito.index', compact('items', 'total'));
    }

    public function agregar(Request $request)
    {
        $request->validate([
            'producto_id' => 'required|exists:producto,producto_id',
            'cantidad'    => 'nullable|integer|min:1',
        ]);

        $cantidad = $request->input('cantidad', 1);
        $usuario_id = auth()->user()->usuario_id;

        $item = Carrito::where('usuario_id', $usuario_id)
            ->where('producto_id', $request->producto_id)
            ->first();

        if ($item) {
            $item->cantidad += $cantidad;
            $item->save();
        } else {
            Carrito::create([
                'usuario_id'  => $usuario_id,
                'producto_id' => $request->producto_id,
                'cantidad'    => $cantidad,
            ]);
        }

        return back()->with('success', 'Producto agregado al carrito.');
    }

    public function actualizar(Request $request)
    {
        $request->validate([
            'carrito_id' => 'required|exists:carrito,carrito_id',
            'cantidad'   => 'required|integer|min:0',
        ]);

        $item = Carrito::where('carrito_id', $request->carrito_id)
            ->where('usuario_id', auth()->user()->usuario_id)
            ->firstOrFail();

        if ($request->cantidad <= 0) {
            $item->delete();
        } else {
            $item->cantidad = $request->cantidad;
            $item->save();
        }

        return back()->with('success', 'Carrito actualizado.');
    }

    public function eliminar(Request $request)
    {
        $request->validate([
            'carrito_id' => 'required|exists:carrito,carrito_id',
        ]);

        Carrito::where('carrito_id', $request->carrito_id)
            ->where('usuario_id', auth()->user()->usuario_id)
            ->delete();

        return back()->with('success', 'Producto eliminado del carrito.');
    }
}
