<?php

namespace App\Services;

use App\Models\Carrito;
use App\Models\Configuracion;
use App\Models\Pedido;
use App\Models\PedidoProducto;
use App\Models\Producto;
use App\Models\Usuario;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class OrderService
{
    public function __construct(private PricingService $pricing)
    {
    }

    /**
     * Procesa la creación de un pedido a partir del carrito del usuario.
     *
     * Todo el trabajo ocurre en una transacción: o se crea el pedido completo
     * con su stock descontado, o no cambia nada.
     *
     * @throws \RuntimeException con un mensaje apto para el cliente.
     */
    public function processOrder(Usuario $usuario): Pedido
    {
        return DB::transaction(function () use ($usuario) {
            $items = Carrito::where('usuario_id', $usuario->usuario_id)->get();

            if ($items->isEmpty()) {
                throw new RuntimeException('El carrito está vacío.');
            }

            // Bloquea las filas para que dos checkouts simultáneos no sobrevendan.
            $productos = Producto::whereIn('producto_id', $items->pluck('producto_id'))
                ->lockForUpdate()
                ->get()
                ->keyBy('producto_id');

            $subtotal = 0.0;

            foreach ($items as $item) {
                $producto = $productos->get($item->producto_id);

                if (!$producto) {
                    throw new RuntimeException('Uno de los productos del carrito ya no está disponible.');
                }

                if ($item->cantidad > $producto->stockVenta + 1e-9) {
                    throw new RuntimeException(
                        "Stock insuficiente para {$producto->nombre}: disponible {$producto->stockVenta}, solicitado {$item->cantidad}."
                    );
                }

                $subtotal += $item->cantidad * (float) $producto->precio;
            }

            $totales = $this->pricing->desglosar($subtotal);

            $pedido = Pedido::create([
                'usuario_id' => $usuario->usuario_id,
                'subtotal' => $totales['subtotal'],
                'iva' => $totales['iva'],
                'iva_pct' => $totales['iva_pct'],
                'total' => $totales['total'],
                'estado' => 'Pendiente',
            ]);

            // Secuencial a partir del ID insertado: seguro ante concurrencia.
            $serie = Configuracion::obtenerSerieComprobante();
            $pedido->numero_comprobante = $serie . '-' . str_pad($pedido->pedido_id, 9, '0', STR_PAD_LEFT);
            $pedido->save();

            foreach ($items as $item) {
                $producto = $productos->get($item->producto_id);
                $precio = (float) $producto->precio;

                PedidoProducto::create([
                    'pedido_id' => $pedido->pedido_id,
                    'producto_id' => $producto->producto_id,
                    'cantidad' => $item->cantidad,
                    'precio_unitario' => $precio,
                    'subtotal' => round($item->cantidad * $precio, 2),
                ]);

                $producto->descontarStock((float) $item->cantidad);
            }

            Carrito::where('usuario_id', $usuario->usuario_id)->delete();

            return $pedido->fresh();
        });
    }
}
