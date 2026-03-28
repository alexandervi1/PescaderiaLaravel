<?php

namespace App\Services;

use App\Models\Carrito;
use App\Models\Pedido;
use App\Models\PedidoProducto;
use App\Models\Usuario;
use Illuminate\Support\Facades\DB;
use Exception;

class OrderService
{
    /**
     * Procesa la creación de un pedido a partir del carrito del usuario.
     *
     * @param Usuario $usuario
     * @return Pedido
     * @throws \RuntimeException
     */
    public function processOrder(Usuario $usuario): Pedido
    {
        $items = Carrito::with('producto')->where('usuario_id', $usuario->usuario_id)->get();

        if ($items->isEmpty()) {
            throw new \RuntimeException('El carrito está vacío.');
        }

        DB::beginTransaction();

        try {
            // 1. Crear el pedido inicialmente sin comprobante
            $pedido = Pedido::create([
                'usuario_id' => $usuario->usuario_id,
            ]);

            // 2. Generar número de comprobante secuencial a partir del ID insertado (seguro contra condiciones de carrera)
            $numeroComprobante = '001-001-' . str_pad($pedido->pedido_id, 9, '0', STR_PAD_LEFT);
            $pedido->numero_comprobante = $numeroComprobante;
            $pedido->save();

            // 3. Transferir items al pedido y descontar stock
            foreach ($items as $item) {
                // Agregar al pivote
                PedidoProducto::create([
                    'pedido_id'   => $pedido->pedido_id,
                    'producto_id' => $item->producto_id,
                    'cantidad'    => $item->cantidad,
                ]);

                // Descontar stock usando el helper del modelo Producto
                $item->producto->descontarStock($item->cantidad);
            }

            // 4. Calcular total del pedido
            $pedido->calcularTotal();

            // 5. Vaciar el carrito
            Carrito::where('usuario_id', $usuario->usuario_id)->delete();

            DB::commit();

            return $pedido;
        } catch (Exception $e) {
            DB::rollBack();
            throw new \RuntimeException('Error al procesar el pedido. Inténtalo de nuevo.');
        }
    }
}
