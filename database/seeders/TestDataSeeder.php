<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Usuario;
use App\Models\Producto;
use App\Models\Pedido;
use App\Models\PedidoProducto;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

class TestDataSeeder extends Seeder
{
    public function run()
    {
        // 1. Agregar Nuevos Productos con las im\u00e1genes generadas
        $productosNuevos = [
            [
                'nombre' => 'Salmón Premium',
                'descripcion' => 'Filetes de salmón fresco importado. Ideal para preparaciones crudas, sushi o a la plancha.',
                'precio' => 12.50,
                'stock' => 50.00,
                'imagen_url' => 'img/salmon.png',
                'categoria_id' => 1,
                'unidad_compra_id' => 1,
                'unidad_venta_id' => 2,
                'factor_conversion' => 2204.62
            ],
            [
                'nombre' => 'Cangrejo Rojo',
                'descripcion' => 'Cangrejo rojo vivo seleccionado, tamaño garantizado especial para eventos y parrilladas familiares.',
                'precio' => 18.00,
                'stock' => 100.00,
                'imagen_url' => 'img/cangrejo.png',
                'categoria_id' => 2,
                'unidad_compra_id' => 3,
                'unidad_venta_id' => 3,
                'factor_conversion' => 1.0000
            ],
            [
                'nombre' => 'Langosta Viva Premium',
                'descripcion' => 'Sensacional langosta entera de mar, excelente tamaño y calidad para los paladares más exigentes.',
                'precio' => 25.00,
                'stock' => 30.00,
                'imagen_url' => 'img/langosta.png',
                'categoria_id' => 2,
                'unidad_compra_id' => 3,
                'unidad_venta_id' => 3,
                'factor_conversion' => 1.0000
            ],
        ];

        foreach ($productosNuevos as $prod) {
            Producto::updateOrCreate(
                ['nombre' => $prod['nombre']], // Evita duplicados si se corre de nuevo
                $prod
            );
        }

        // 2. Agregar Usuarios de Prueba (Clientes) de manera segura
        $clientes = [];
        for ($i = 1; $i <= 5; $i++) {
            $clientes[] = Usuario::updateOrCreate(
                ['nombre_usuario' => "cliente_test_$i"],
                [
                    'nombre_completo' => "Cliente de Prueba Num $i",
                    'identificacion'  => "130000000$i",
                    'password'        => Hash::make('test1234'),
                    'rol_id'          => 2, // Cliente
                    'direccion'       => "Av. Ficticia $i, Zona Norte, Manta",
                    'correo'          => "cliente_test_$i@test.com",
                    'telefono'        => "099999999$i",
                ]
            );
        }

        // 3. Crear pedidos de prueba
        $prods = Producto::whereIn('nombre', ['Salmón Premium', 'Cangrejo Rojo', 'Langosta Viva Premium'])->get();

        foreach ($clientes as $cliente) {
            // un pedido por cliente
            $pedido = Pedido::create([
                'usuario_id' => $cliente->usuario_id,
            ]);

            // Generar n\u00famero
            $numeroComprobante = '001-001-' . str_pad($pedido->pedido_id, 9, '0', STR_PAD_LEFT);
            $pedido->numero_comprobante = $numeroComprobante;
            $pedido->save();

            // Agregar productos al pedido
            $cantidadItems = rand(1, 2);
            $seleccionados = $prods->random($cantidadItems);

            foreach ($seleccionados as $p) {
                $cant = rand(1, 4);
                PedidoProducto::create([
                    'pedido_id'   => $pedido->pedido_id,
                    'producto_id' => $p->producto_id,
                    'cantidad'    => $cant,
                ]);
                $p->descontarStock($cant);
            }
            
            $pedido->calcularTotal();
        }
    }
}
