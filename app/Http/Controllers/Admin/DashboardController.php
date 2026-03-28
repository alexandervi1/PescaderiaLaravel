<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Producto;
use App\Models\Usuario;
use App\Models\Pedido;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index()
    {
        // 1. Estadísticas Generales Clave
        $totalProductos = Producto::count();
        $totalClientes = Usuario::where('rol_id', 2)->count();
        $totalPedidos = Pedido::count();
        $ingresosTotales = Pedido::sum('total');

        // 2. Últimos Pedidos Recientes para acción rápida
        $pedidosRecientes = Pedido::with('usuario')
            ->orderBy('fecha_pedido', 'desc')
            ->take(5)
            ->get();

        // 3. Productos Críticos con Bajo Stock (Ventas Menores a 50 unidades de inventario)
        $productosBajoStock = Producto::with(['categoria', 'unidadVenta'])
            ->whereRaw('(stock * factor_conversion) < 50')
            ->take(5)
            ->get();

        // 4. Analítica Avanzada: Ingresos de los últimos 6 meses para Chart.js
        $ventasMesesRaw = Pedido::select(
            DB::raw('MONTH(fecha_pedido) as mes'),
            DB::raw('SUM(total) as ingresos')
        )
        ->where('fecha_pedido', '>=', Carbon::now()->subMonths(6)->startOfMonth())
        ->groupBy('mes')
        ->orderBy('mes')
        ->pluck('ingresos', 'mes')
        ->toArray();

        // Variables para llenar la gráfica con ceros en meses sin ventas
        $labelsGrafico = [];
        $datosGrafico = [];
        $mesesNombres = [
            1=>'Ene', 2=>'Feb', 3=>'Mar', 4=>'Abr', 5=>'May', 6=>'Jun',
            7=>'Jul', 8=>'Ago', 9=>'Sep', 10=>'Oct', 11=>'Nov', 12=>'Dic'
        ];

        // Iteramos los últimos 6 meses dinámicamente hasta el mes en curso
        for ($i = 5; $i >= 0; $i--) {
            $fecha = Carbon::now()->subMonths($i);
            $mesNum = $fecha->month;
            $labelsGrafico[] = $mesesNombres[$mesNum];
            $datosGrafico[] = $ventasMesesRaw[$mesNum] ?? 0;
        }

        return view('admin.dashboard', compact(
            'totalProductos', 'totalClientes', 'totalPedidos', 'ingresosTotales',
            'pedidosRecientes', 'productosBajoStock', 'labelsGrafico', 'datosGrafico'
        ));
    }
}
