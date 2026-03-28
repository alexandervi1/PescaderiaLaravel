<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Producto;

class ExtraProductosSeeder extends Seeder
{
    public function run()
    {
        $productosExtra = [
            [
                'nombre' => 'Filete de Corvina',
                'descripcion' => 'Filetes limpios de corvina de alta profundidad. Sabor suave y textura firme, perfecto para cebiches.',
                'precio' => 10.50,
                'stock' => 80.00,
                'imagen_url' => 'img/placeholder.jpg',
                'categoria_id' => 1, // Pescados
                'unidad_compra_id' => 1,
                'unidad_venta_id' => 2,
                'factor_conversion' => 2204.62
            ],
            [
                'nombre' => 'Camarones Jumbo exportación',
                'descripcion' => 'Camarones pelados y desvenados calibre U-15. La mejor selección para tus parrilladas.',
                'precio' => 15.00,
                'stock' => 200.00,
                'imagen_url' => 'img/placeholder.jpg',
                'categoria_id' => 2, // Mariscos
                'unidad_compra_id' => 3, // Asignando libras o kilos por defecto
                'unidad_venta_id' => 3,
                'factor_conversion' => 1.0000
            ],
            [
                'nombre' => 'Atún Rojo (Lomo)',
                'descripcion' => 'Corte premium de atún aleta amarilla. Grado Sashimi.',
                'precio' => 28.00,
                'stock' => 15.00,
                'imagen_url' => 'img/placeholder.jpg',
                'categoria_id' => 1,
                'unidad_compra_id' => 1,
                'unidad_venta_id' => 2,
                'factor_conversion' => 2204.62
            ],
            [
                'nombre' => 'Anillos de Calamar',
                'descripcion' => 'Aros de calamar fresco congelado, listos para empanizar y freír (rabas).',
                'precio' => 8.75,
                'stock' => 120.00,
                'imagen_url' => 'img/placeholder.jpg',
                'categoria_id' => 2,
                'unidad_compra_id' => 3,
                'unidad_venta_id' => 3,
                'factor_conversion' => 1.0000
            ],
            [
                'nombre' => 'Pargo Rojo Entero',
                'descripcion' => 'Pargo escamado y desviscerado. Ideal para asar entero al carbón.',
                'precio' => 9.50,
                'stock' => 45.00,
                'imagen_url' => 'img/placeholder.jpg',
                'categoria_id' => 1,
                'unidad_compra_id' => 3,
                'unidad_venta_id' => 3,
                'factor_conversion' => 1.0000
            ],
            [
                'nombre' => 'Mejillones Media Concha',
                'descripcion' => 'Mejillones importados, pre-cocidos en su media concha. Especial para paellas.',
                'precio' => 11.20,
                'stock' => 60.00,
                'imagen_url' => 'img/placeholder.jpg',
                'categoria_id' => 2,
                'unidad_compra_id' => 3,
                'unidad_venta_id' => 3,
                'factor_conversion' => 1.0000
            ],
            [
                'nombre' => 'Dorado (Mahi Mahi)',
                'descripcion' => 'Porciones selectas de pescado dorado. Pescado fresco de temporada.',
                'precio' => 7.80,
                'stock' => 150.00,
                'imagen_url' => 'img/placeholder.jpg',
                'categoria_id' => 1,
                'unidad_compra_id' => 1,
                'unidad_venta_id' => 2,
                'factor_conversion' => 2204.62
            ]
        ];

        foreach ($productosExtra as $prod) {
            Producto::updateOrCreate(
                ['nombre' => $prod['nombre']],
                $prod
            );
        }
    }
}
