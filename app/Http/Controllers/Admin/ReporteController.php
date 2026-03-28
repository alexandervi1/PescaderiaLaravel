<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Pedido;
use App\Models\Producto;
use App\Models\Categoria;
use Illuminate\Support\Facades\DB;

class ReporteController extends Controller
{
    public function ventas()
    {
        $ventasMes = Pedido::select(
            DB::raw('MONTH(fecha_pedido) as mes'),
            DB::raw('COUNT(*) as total_pedidos'),
            DB::raw('SUM(total) as total_ingresos')
        )
        ->groupBy('mes')
        ->orderBy('mes')
        ->get();

        $pedidosRecientes = Pedido::with('usuario')->orderBy('fecha_pedido', 'desc')->take(10)->get();

        return view('admin.reportes.ventas', compact('ventasMes', 'pedidosRecientes'));
    }

    public function masVendidos()
    {
        $productos = Producto::withCount(['pedidos as cantidad_vendida' => function($query) {
            $query->select(DB::raw('SUM(pedidoproducto.cantidad)'));
        }])
        ->orderBy('cantidad_vendida', 'desc')
        ->take(10)
        ->get();

        return view('admin.reportes.mas_vendidos', compact('productos'));
    }

    public function bajoStock()
    {
        $productos = Producto::whereRaw('(stock * factor_conversion) < 50')->get();
        return view('admin.reportes.bajo_stock', compact('productos'));
    }

    public function stockPorCategoria()
    {
        $categorias = Categoria::withCount('productos')->get();
        
        $stockCategorias = DB::table('producto')
            ->join('categoria', 'producto.categoria_id', '=', 'categoria.categoria_id')
            ->select('categoria.nombre', DB::raw('SUM(producto.stock * producto.factor_conversion) as total_stock'))
            ->groupBy('categoria.categoria_id', 'categoria.nombre')
            ->get();

        return view('admin.reportes.stock_categoria', compact('categorias', 'stockCategorias'));
    }
}
