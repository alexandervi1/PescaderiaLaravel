<?php

namespace App\Http\View\Composers;

use App\Models\Pedido;
use App\Models\Producto;
use Illuminate\View\View;

/**
 * Contadores operativos que alimentan los badges del menú de administración.
 */
class AdminMenuComposer
{
    public function compose(View $view): void
    {
        $view->with([
            'pendingOrders' => Pedido::where('estado', 'Pendiente')->count(),
            'lowStockCount' => Producto::whereRaw('(stock * factor_conversion) < 50')->count(),
        ]);
    }
}
