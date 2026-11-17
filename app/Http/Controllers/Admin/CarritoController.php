<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Carrito;

class CarritoController extends Controller
{
    public function index()
    {
        $carritos = Carrito::with(['usuario', 'producto.categoria', 'producto.unidadVenta'])
            ->orderBy('fecha_agregado', 'desc')
            ->get();

        $valorTotal = $carritos->sum(fn ($item) => $item->producto
            ? $item->cantidad * (float) $item->producto->precio
            : 0);

        return view('admin.carritos.index', compact('carritos', 'valorTotal'));
    }
}
