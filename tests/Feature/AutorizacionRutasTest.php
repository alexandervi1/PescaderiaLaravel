<?php

namespace Tests\Feature;

use App\Models\Pedido;
use App\Models\Rol;
use App\Models\Usuario;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class AutorizacionRutasTest extends TestCase
{
    use RefreshDatabase;

    private Usuario $cliente;

    private Usuario $otroCliente;

    protected function setUp(): void
    {
        parent::setUp();

        Rol::insert([
            ['rol_id' => 1, 'nombre' => 'Administrador'],
            ['rol_id' => 2, 'nombre' => 'Cliente'],
            ['rol_id' => 3, 'nombre' => 'Visitante'],
        ]);

        $this->cliente = $this->usuario('cliente1');
        $this->otroCliente = $this->usuario('cliente2');
    }

    private function usuario(string $nombre): Usuario
    {
        return Usuario::create([
            'nombre_usuario' => $nombre,
            'nombre_completo' => ucfirst($nombre),
            'password' => Hash::make('secret123'),
            'rol_id' => 2,
            'direccion' => 'Calle 1',
            'correo' => "{$nombre}@example.com",
        ]);
    }

    public function test_toda_ruta_admin_exige_auth_y_admin(): void
    {
        $revisadas = 0;

        foreach (Route::getRoutes() as $route) {
            $nombre = $route->getName();

            if (! $nombre || ! str_starts_with($nombre, 'admin.')) {
                continue;
            }

            $middleware = $route->gatherMiddleware();

            $this->assertContains('auth', $middleware, "La ruta {$nombre} no exige autenticación.");
            $this->assertContains('admin', $middleware, "La ruta {$nombre} no exige rol de administrador.");
            $revisadas++;
        }

        $this->assertGreaterThan(0, $revisadas);
    }

    public function test_las_rutas_de_cliente_exigen_auth_y_cliente(): void
    {
        $revisadas = 0;

        foreach (Route::getRoutes() as $route) {
            $nombre = $route->getName();

            if (! $nombre || ! preg_match('/^(carrito|pedidos)\./', $nombre) || str_starts_with($nombre, 'admin.')) {
                continue;
            }

            $middleware = $route->gatherMiddleware();

            $this->assertContains('auth', $middleware, "La ruta {$nombre} no exige autenticación.");
            $this->assertContains('cliente', $middleware, "La ruta {$nombre} no exige rol de cliente.");
            $revisadas++;
        }

        $this->assertGreaterThan(0, $revisadas);
    }

    public function test_un_cliente_no_puede_ver_el_pedido_de_otro(): void
    {
        $pedido = Pedido::create([
            'usuario_id' => $this->cliente->usuario_id,
            'subtotal' => 10,
            'iva' => 1.5,
            'iva_pct' => 15,
            'total' => 11.5,
            'estado' => 'Pendiente',
        ]);

        $this->actingAs($this->otroCliente)
            ->get(route('pedidos.show', $pedido->pedido_id))
            ->assertNotFound();
    }
}
