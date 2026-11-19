<?php

namespace Tests\Feature;

use App\Models\Carrito;
use App\Models\Categoria;
use App\Models\Configuracion;
use App\Models\Producto;
use App\Models\Rol;
use App\Models\UnidadMedida;
use App\Models\Usuario;
use App\Services\OrderService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class OrderStatusTest extends TestCase
{
    use RefreshDatabase;

    private Usuario $admin;

    private Usuario $cliente;

    protected function setUp(): void
    {
        parent::setUp();

        Rol::insert([
            ['rol_id' => 1, 'nombre' => 'Administrador'],
            ['rol_id' => 2, 'nombre' => 'Cliente'],
            ['rol_id' => 3, 'nombre' => 'Visitante'],
        ]);

        UnidadMedida::insert([
            ['unidad_id' => 1, 'nombre' => 'Tonelada', 'abreviatura' => 'Tn'],
            ['unidad_id' => 2, 'nombre' => 'Libra', 'abreviatura' => 'lb'],
            ['unidad_id' => 3, 'nombre' => 'Unidad', 'abreviatura' => 'u'],
        ]);

        Categoria::insert([['categoria_id' => 1, 'nombre' => 'Pescados']]);
        Configuracion::create(['clave' => 'iva_actual', 'valor' => '15']);

        $this->admin = Usuario::create([
            'nombre_usuario' => 'admin1',
            'nombre_completo' => 'Admin Uno',
            'password' => Hash::make('secret123'),
            'rol_id' => 1,
            'direccion' => 'Calle 1',
            'correo' => 'admin1@example.com',
        ]);

        $this->cliente = Usuario::create([
            'nombre_usuario' => 'cliente1',
            'nombre_completo' => 'Cliente Uno',
            'password' => Hash::make('secret123'),
            'rol_id' => 2,
            'direccion' => 'Calle 2',
            'correo' => 'cliente1@example.com',
        ]);
    }

    private function producto(string $nombre, float $precio, float $stock, int $compra, int $venta): Producto
    {
        return Producto::create([
            'nombre' => $nombre,
            'precio' => $precio,
            'stock' => $stock,
            'categoria_id' => 1,
            'unidad_compra_id' => $compra,
            'unidad_venta_id' => $venta,
        ]);
    }

    /**
     * @param array<int, array{0: Producto, 1: int}> $lineas
     */
    private function pedidoCon(array $lineas): \App\Models\Pedido
    {
        foreach ($lineas as [$producto, $cantidad]) {
            Carrito::create([
                'usuario_id' => $this->cliente->usuario_id,
                'producto_id' => $producto->producto_id,
                'cantidad' => $cantidad,
            ]);
        }

        return app(OrderService::class)->processOrder($this->cliente);
    }

    private function cancelar(\App\Models\Pedido $pedido)
    {
        return $this->actingAs($this->admin)
            ->put(route('admin.pedidos.update', $pedido->pedido_id), ['estado' => 'Cancelado']);
    }

    public function test_pendiente_a_cancelado_repone_el_stock_exacto(): void
    {
        $libra = $this->producto('Albacora', 10, 1.0, 1, 2);
        $unidad = $this->producto('Almeja', 5, 100, 3, 3);

        $pedido = $this->pedidoCon([[$libra, 2], [$unidad, 3]]);

        $this->assertLessThan(2204.62, $libra->fresh()->stockVenta);
        $this->assertEquals(97.0, (float) $unidad->fresh()->stock);

        $this->cancelar($pedido)->assertSessionHas('success');

        $this->assertSame('Cancelado', $pedido->fresh()->estado);
        $this->assertEqualsWithDelta(2204.62, $libra->fresh()->stockVenta, 0.01);
        $this->assertEquals(100.0, (float) $unidad->fresh()->stock);
    }

    public function test_enviado_a_cancelado_repone_el_stock(): void
    {
        $producto = $this->producto('Almeja', 5, 100, 3, 3);
        $pedido = $this->pedidoCon([[$producto, 4]]);
        app(OrderService::class)->changeStatus($pedido, 'Enviado');

        $this->cancelar($pedido->fresh())->assertSessionHas('success');

        $this->assertSame('Cancelado', $pedido->fresh()->estado);
        $this->assertEquals(100.0, (float) $producto->fresh()->stock);
    }

    public function test_entregado_a_cancelado_es_rechazado_y_no_toca_el_stock(): void
    {
        $producto = $this->producto('Almeja', 5, 100, 3, 3);
        $pedido = $this->pedidoCon([[$producto, 4]]);
        $service = app(OrderService::class);
        $service->changeStatus($pedido, 'Enviado');
        $service->changeStatus($pedido->fresh(), 'Entregado');

        $this->cancelar($pedido->fresh())->assertSessionHas('error');

        $this->assertSame('Entregado', $pedido->fresh()->estado);
        $this->assertEquals(96.0, (float) $producto->fresh()->stock);
    }

    public function test_cancelado_a_pendiente_es_rechazado(): void
    {
        $producto = $this->producto('Almeja', 5, 100, 3, 3);
        $pedido = $this->pedidoCon([[$producto, 4]]);
        $this->cancelar($pedido);

        $this->actingAs($this->admin)
            ->put(route('admin.pedidos.update', $pedido->pedido_id), ['estado' => 'Pendiente'])
            ->assertSessionHas('error');

        $this->assertSame('Cancelado', $pedido->fresh()->estado);
    }

    public function test_entregado_a_pendiente_es_rechazado(): void
    {
        $producto = $this->producto('Almeja', 5, 100, 3, 3);
        $pedido = $this->pedidoCon([[$producto, 4]]);
        $service = app(OrderService::class);
        $service->changeStatus($pedido, 'Enviado');
        $service->changeStatus($pedido->fresh(), 'Entregado');

        $this->actingAs($this->admin)
            ->put(route('admin.pedidos.update', $pedido->pedido_id), ['estado' => 'Pendiente'])
            ->assertSessionHas('error');

        $this->assertSame('Entregado', $pedido->fresh()->estado);
    }

    public function test_cancelar_dos_veces_no_repone_dos_veces(): void
    {
        $producto = $this->producto('Almeja', 5, 100, 3, 3);
        $pedido = $this->pedidoCon([[$producto, 4]]);

        $this->cancelar($pedido)->assertSessionHas('success');
        $this->cancelar($pedido->fresh())->assertSessionHas('error');

        $this->assertEquals(100.0, (float) $producto->fresh()->stock);
    }

    public function test_cancelar_un_pedido_cuyo_producto_fue_borrado_no_falla(): void
    {
        $producto = $this->producto('Almeja', 5, 100, 3, 3);
        $pedido = $this->pedidoCon([[$producto, 4]]);

        // La FK restrict impide el borrado normal; se fuerza el estado que el código debe tolerar.
        Schema::disableForeignKeyConstraints();
        Producto::whereKey($producto->producto_id)->delete();
        Schema::enableForeignKeyConstraints();

        $cancelado = app(OrderService::class)->cancelOrder($pedido);

        $this->assertSame('Cancelado', $cancelado->estado);
    }

    public function test_un_cliente_no_puede_cambiar_el_estado_de_un_pedido(): void
    {
        $producto = $this->producto('Almeja', 5, 100, 3, 3);
        $pedido = $this->pedidoCon([[$producto, 4]]);

        $this->actingAs($this->cliente)
            ->put(route('admin.pedidos.update', $pedido->pedido_id), ['estado' => 'Cancelado'])
            ->assertRedirect(route('login'));

        $this->assertSame('Pendiente', $pedido->fresh()->estado);
    }
}
