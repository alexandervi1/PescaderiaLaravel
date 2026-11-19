<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Rol;
use App\Models\Configuracion;
use App\Models\UnidadMedida;
use App\Models\Categoria;
use App\Models\Usuario;
use App\Models\Producto;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run()
    {
        // ── 1. Roles ──
        Rol::insert([
            ['rol_id' => 1, 'nombre' => 'Administrador'],
            ['rol_id' => 2, 'nombre' => 'Cliente'],
            ['rol_id' => 3, 'nombre' => 'Visitante'],
        ]);

        // ── 2. Configuración ──
        Configuracion::create(['clave' => 'iva_actual', 'valor' => '15']);
        Configuracion::create(['clave' => 'negocio_nombre', 'valor' => 'COMERCIAL DE MARISCOS DON WALTER']);
        Configuracion::create(['clave' => 'negocio_ruc', 'valor' => '1301234567001']);
        Configuracion::create(['clave' => 'negocio_direccion', 'valor' => 'Barrio Tarqui, Calle 101 y Av. 102, Manta, Ecuador']);
        Configuracion::create(['clave' => 'negocio_telefono', 'valor' => '0995432109']);
        Configuracion::create(['clave' => 'negocio_leyenda', 'valor' => 'CONTRIBUYENTE RÉGIMEN RIMPE - NEGOCIO POPULAR']);

        // ── 3. Unidades de Medida ──
        UnidadMedida::insert([
            ['unidad_id' => 1, 'nombre' => 'Tonelada', 'abreviatura' => 'Tn'],
            ['unidad_id' => 2, 'nombre' => 'Libra', 'abreviatura' => 'lb'],
            ['unidad_id' => 3, 'nombre' => 'Unidad', 'abreviatura' => 'u'],
        ]);

        // ── 4. Categorías ──
        Categoria::insert([
            ['categoria_id' => 1, 'nombre' => 'Pescados'],
            ['categoria_id' => 2, 'nombre' => 'Crustáceos y Moluscos'],
            ['categoria_id' => 3, 'nombre' => 'Pulpos y Calamares'],
        ]);

        // ── 5. Usuario Admin ──
        // En producción se define desde el entorno para no arrancar con credenciales conocidas.
        Usuario::create([
            'nombre_usuario'  => env('INITIAL_ADMIN_USER', 'admin123'),
            'nombre_completo' => 'Maritza Rosero',
            'password'        => Hash::make(env('INITIAL_ADMIN_PASSWORD', 'admin123')),
            'rol_id'          => 1,
            'direccion'       => 'Av. Canónigo Ramos y 11 de Noviembre',
            'correo'          => env('INITIAL_ADMIN_EMAIL', 'maritza@gmail.com'),
            'telefono'        => '0992470053',
        ]);

        // ── 6. Productos ──
        $productos = [
            ['nombre' => 'Albacora Limpia',     'descripcion' => 'Albacora sin vísceras ni piel.',       'precio' => 3.00, 'stock' => 1.0000, 'imagen_url' => 'img/albacoraLimpia.jpg',   'categoria_id' => 1, 'unidad_compra_id' => 1, 'unidad_venta_id' => 2, 'factor_conversion' => 2204.62],
            ['nombre' => 'Albacora Entera',     'descripcion' => 'Pez albacora entero',                 'precio' => 3.00, 'stock' => 1.0000, 'imagen_url' => 'img/albacoraEntera.jpg',   'categoria_id' => 1, 'unidad_compra_id' => 1, 'unidad_venta_id' => 2, 'factor_conversion' => 2204.62],
            ['nombre' => 'Pulpo',               'descripcion' => 'Pulpo fresco en porciones.',           'precio' => 4.00, 'stock' => 1.0000, 'imagen_url' => 'img/pulpo.jpg',             'categoria_id' => 3, 'unidad_compra_id' => 1, 'unidad_venta_id' => 2, 'factor_conversion' => 2204.62],
            ['nombre' => 'Camarón Mar',         'descripcion' => 'Camarón de mar grande.',               'precio' => 4.00, 'stock' => 1.0000, 'imagen_url' => 'img/camaronMar.jpg',       'categoria_id' => 2, 'unidad_compra_id' => 1, 'unidad_venta_id' => 2, 'factor_conversion' => 2204.62],
            ['nombre' => 'Albacora por libra',  'descripcion' => 'Porciones de albacora por libra.',     'precio' => 4.00, 'stock' => 1.0000, 'imagen_url' => 'img/albacoraLibra.png',    'categoria_id' => 1, 'unidad_compra_id' => 1, 'unidad_venta_id' => 2, 'factor_conversion' => 2204.62],
            ['nombre' => 'Albacora negocio',    'descripcion' => 'Precio especial por volumen.',         'precio' => 2.00, 'stock' => 1.0000, 'imagen_url' => 'img/albacoraNegocio.jpg',  'categoria_id' => 1, 'unidad_compra_id' => 1, 'unidad_venta_id' => 2, 'factor_conversion' => 2204.62],
            ['nombre' => 'Calamar',             'descripcion' => 'Calamar fresco por libra.',            'precio' => 4.00, 'stock' => 1.0000, 'imagen_url' => 'img/calamar.jpg',           'categoria_id' => 3, 'unidad_compra_id' => 1, 'unidad_venta_id' => 2, 'factor_conversion' => 2204.62],
            ['nombre' => 'Langostino',          'descripcion' => 'Langostinos grandes de mar.',          'precio' => 4.00, 'stock' => 1.0000, 'imagen_url' => 'img/langostino.jpg',       'categoria_id' => 2, 'unidad_compra_id' => 1, 'unidad_venta_id' => 2, 'factor_conversion' => 2204.62],
            ['nombre' => 'Pez Espada',          'descripcion' => 'Filetes de pescado espada.',           'precio' => 4.00, 'stock' => 1.0000, 'imagen_url' => 'img/espada.jpg',            'categoria_id' => 1, 'unidad_compra_id' => 1, 'unidad_venta_id' => 2, 'factor_conversion' => 2204.62],
            ['nombre' => 'Almeja',              'descripcion' => 'Almejas frescas empacadas.',           'precio' => 4.00, 'stock' => 500.0000, 'imagen_url' => 'img/almejas.jpg',         'categoria_id' => 2, 'unidad_compra_id' => 3, 'unidad_venta_id' => 3, 'factor_conversion' => 1.0000],
            ['nombre' => 'Mejillones',          'descripcion' => 'Mejillones limpios en funda.',         'precio' => 4.00, 'stock' => 1000.0000, 'imagen_url' => 'img/mejillones.jpg',     'categoria_id' => 2, 'unidad_compra_id' => 3, 'unidad_venta_id' => 3, 'factor_conversion' => 1.0000],
            ['nombre' => 'Conchas',             'descripcion' => 'Conchas enteras empacadas (100 unds).','precio' => 4.00, 'stock' => 2000.0000, 'imagen_url' => 'img/conchas.jpg',       'categoria_id' => 2, 'unidad_compra_id' => 3, 'unidad_venta_id' => 3, 'factor_conversion' => 1.0000],
            ['nombre' => 'Pez Rayado',          'descripcion' => 'Pescado rayado fileteado.',            'precio' => 5.00, 'stock' => 1.0000, 'imagen_url' => 'img/rayado.jpg',            'categoria_id' => 1, 'unidad_compra_id' => 1, 'unidad_venta_id' => 2, 'factor_conversion' => 2204.62],
            ['nombre' => 'Pez Cache',           'descripcion' => 'Cache fresco en cortes por libra.',    'precio' => 3.00, 'stock' => 1.0000, 'imagen_url' => 'img/cache.jpg',             'categoria_id' => 1, 'unidad_compra_id' => 1, 'unidad_venta_id' => 2, 'factor_conversion' => 2204.62],
            ['nombre' => 'Tilapia',             'descripcion' => 'Tilapia limpia en porciones.',         'precio' => 3.00, 'stock' => 1.0000, 'imagen_url' => 'img/tilapia.jpg',           'categoria_id' => 1, 'unidad_compra_id' => 1, 'unidad_venta_id' => 2, 'factor_conversion' => 2204.62],
            ['nombre' => 'Picudo',              'descripcion' => 'Picudo fresco fileteado.',             'precio' => 5.00, 'stock' => 1.0000, 'imagen_url' => 'img/picudo.jpg',             'categoria_id' => 1, 'unidad_compra_id' => 1, 'unidad_venta_id' => 2, 'factor_conversion' => 2204.62],
            ['nombre' => 'Camarón Piscina',     'descripcion' => 'Camarón de cultivo.',                  'precio' => 4.00, 'stock' => 1.0000, 'imagen_url' => 'img/camaronPiscina.jpg',   'categoria_id' => 2, 'unidad_compra_id' => 1, 'unidad_venta_id' => 2, 'factor_conversion' => 2204.62],
            ['nombre' => 'Camarón Río',         'descripcion' => 'Camarón silvestre de río.',            'precio' => 6.00, 'stock' => 1.0000, 'imagen_url' => 'img/camaronRio.jpg',       'categoria_id' => 2, 'unidad_compra_id' => 1, 'unidad_venta_id' => 2, 'factor_conversion' => 2204.62],
        ];

        foreach ($productos as $p) {
            Producto::create($p);
        }
    }
}
