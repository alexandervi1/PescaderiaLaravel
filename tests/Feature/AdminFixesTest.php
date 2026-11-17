<?php

namespace Tests\Feature;

use App\Models\Carrito;
use App\Models\Categoria;
use App\Models\Configuracion;
use App\Models\Pedido;
use App\Models\PedidoProducto;
use App\Models\Producto;
use App\Models\Rol;
use App\Models\UnidadMedida;
use App\Models\Usuario;
use App\Services\OrderService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AdminFixesTest extends TestCase
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

    private function productoConPedido(int $cantidad = 2): array
    {
        $producto = Producto::create([
            'nombre' => 'Albacora',
            'precio' => 10,
            'stock' => 100,
            'categoria_id' => 1,
            'unidad_compra_id' => 3,
            'unidad_venta_id' => 3,
        ]);

        Carrito::create([
            'usuario_id' => $this->cliente->usuario_id,
            'producto_id' => $producto->producto_id,
            'cantidad' => $cantidad,
        ]);

        $pedido = app(OrderService::class)->processOrder($this->cliente);

        return [$producto->fresh(), $pedido];
    }

    public function test_cancelling_an_order_restores_stock(): void
    {
        [$producto, $pedido] = $this->productoConPedido(2);
        $this->assertEquals(98.0, (float) $producto->stock);

        $this->actingAs($this->admin)
            ->put(route('admin.pedidos.update', $pedido->pedido_id), ['estado' => 'Cancelado'])
            ->assertSessionHas('success');

        $this->assertEquals('Cancelado', $pedido->fresh()->estado);
        $this->assertEquals(100.0, (float) $producto->fresh()->stock);
    }

    public function test_cancelling_twice_does_not_duplicate_the_restock(): void
    {
        [$producto, $pedido] = $this->productoConPedido(2);

        $this->actingAs($this->admin)->put(route('admin.pedidos.update', $pedido->pedido_id), ['estado' => 'Cancelado']);
        $this->actingAs($this->admin)->put(route('admin.pedidos.update', $pedido->pedido_id), ['estado' => 'Cancelado']);

        $this->assertEquals(100.0, (float) $producto->fresh()->stock);
    }

    public function test_cancelled_order_cannot_be_reactivated(): void
    {
        [$producto, $pedido] = $this->productoConPedido(2);

        $this->actingAs($this->admin)->put(route('admin.pedidos.update', $pedido->pedido_id), ['estado' => 'Cancelado']);

        $this->actingAs($this->admin)
            ->put(route('admin.pedidos.update', $pedido->pedido_id), ['estado' => 'Entregado'])
            ->assertSessionHas('error');

        $this->assertEquals('Cancelado', $pedido->fresh()->estado);
    }

    public function test_product_with_order_lines_cannot_be_deleted(): void
    {
        [$producto, $pedido] = $this->productoConPedido(2);

        $this->actingAs($this->admin)
            ->delete(route('admin.productos.destroy', $producto->producto_id))
            ->assertSessionHas('error');

        $this->assertDatabaseHas('producto', ['producto_id' => $producto->producto_id]);
        $this->assertDatabaseHas('pedidoproducto', ['pedido_id' => $pedido->pedido_id]);
    }

    public function test_username_change_logs_the_admin_out(): void
    {
        $this->actingAs($this->admin)
            ->put(route('admin.configuracion.seguridad'), [
                'nombre_completo' => 'Admin Uno',
                'nombre_usuario' => 'admin_renombrado',
            ])
            ->assertRedirect(route('login'));

        $this->assertGuest();
        $this->assertDatabaseHas('usuario', ['usuario_id' => $this->admin->usuario_id, 'nombre_usuario' => 'admin_renombrado']);
    }

    public function test_password_change_sets_rotation_timestamp(): void
    {
        $this->actingAs($this->admin)
            ->put(route('admin.configuracion.seguridad'), [
                'nombre_completo' => 'Admin Uno',
                'nombre_usuario' => 'admin1',
                'password' => 'nuevaClave123',
            ]);

        $this->assertNotNull($this->admin->fresh()->password_changed_at);
    }

    public function test_admin_cannot_edit_another_admin_as_a_client(): void
    {
        $otroAdmin = Usuario::create([
            'nombre_usuario' => 'admin2',
            'nombre_completo' => 'Admin Dos',
            'password' => Hash::make('secret123'),
            'rol_id' => 1,
            'direccion' => 'Calle 3',
            'correo' => 'admin2@example.com',
        ]);

        $this->actingAs($this->admin)
            ->get(route('admin.clientes.edit', $otroAdmin->usuario_id))
            ->assertNotFound();
    }

    public function test_product_with_unsupported_unit_pair_is_rejected(): void
    {
        $this->actingAs($this->admin)
            ->post(route('admin.productos.store'), [
                'nombre' => 'Producto raro',
                'precio' => 5,
                'stock' => 10,
                'categoria_id' => 1,
                'unidad_compra_id' => 1, // Tonelada
                'unidad_venta_id' => 3,  // Unidad: sin conversión definida
            ])
            ->assertSessionHasErrors('unidad_venta_id');

        $this->assertDatabaseMissing('producto', ['nombre' => 'Producto raro']);
    }

    public function test_sales_report_excludes_cancelled_and_previous_years(): void
    {
        $producto = Producto::create([
            'nombre' => 'Albacora',
            'precio' => 10,
            'stock' => 100,
            'categoria_id' => 1,
            'unidad_compra_id' => 3,
            'unidad_venta_id' => 3,
        ]);

        Pedido::create([
            'usuario_id' => $this->cliente->usuario_id,
            'fecha_pedido' => now()->startOfYear()->addMonth(),
            'subtotal' => 100,
            'iva' => 15,
            'total' => 115,
            'estado' => 'Entregado',
        ]);

        Pedido::create([
            'usuario_id' => $this->cliente->usuario_id,
            'fecha_pedido' => now()->startOfYear()->addMonth(),
            'subtotal' => 50,
            'iva' => 7.5,
            'total' => 57.5,
            'estado' => 'Cancelado',
        ]);

        Pedido::create([
            'usuario_id' => $this->cliente->usuario_id,
            'fecha_pedido' => now()->subYear()->startOfYear()->addMonth(),
            'subtotal' => 999,
            'iva' => 149.85,
            'total' => 1148.85,
            'estado' => 'Entregado',
        ]);

        $response = $this->actingAs($this->admin)->get(route('admin.reportes.ventas'));
        $response->assertOk();

        $ventas = collect($response->viewData('ventasMes'));
        $total = $ventas->sum(fn ($fila) => (float) $fila->total_ingresos);

        $this->assertEquals(115.0, $total, 'Solo debe sumar pedidos del año actual no cancelados.');
    }
}
