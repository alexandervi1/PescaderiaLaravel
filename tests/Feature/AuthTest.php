<?php

namespace Tests\Feature;

use App\Models\Rol;
use App\Models\Usuario;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Rol::insert([
            ['rol_id' => 1, 'nombre' => 'Administrador'],
            ['rol_id' => 2, 'nombre' => 'Cliente'],
            ['rol_id' => 3, 'nombre' => 'Visitante'],
        ]);
    }

    private function cliente(array $overrides = []): Usuario
    {
        return Usuario::create(array_merge([
            'nombre_usuario' => 'cliente1',
            'nombre_completo' => 'Cliente Uno',
            'identificacion' => '1312345678',
            'password' => Hash::make('secret123'),
            'rol_id' => 2,
            'direccion' => 'Calle 1',
            'correo' => 'cliente1@example.com',
        ], $overrides));
    }

    public function test_registration_creates_a_client(): void
    {
        $this->post('/registro', [
            'nombre_completo' => 'Nuevo Cliente',
            'identificacion' => '1312345678',
            'nombre_usuario' => 'nuevo',
            'password' => 'secret123',
            'password_confirmation' => 'secret123',
            'direccion' => 'Calle 9',
            'correo' => 'nuevo@example.com',
            'telefono' => '0999999999',
        ])->assertRedirect(route('login'));

        $this->assertDatabaseHas('usuario', [
            'nombre_usuario' => 'nuevo',
            'identificacion' => '1312345678',
            'rol_id' => 2,
        ]);

        $this->assertNotNull(
            Usuario::where('nombre_usuario', 'nuevo')->first()->password_changed_at,
            'El registro debe marcar la contraseña como recién establecida.'
        );
    }

    public function test_login_accepts_the_email(): void
    {
        $cliente = $this->cliente();

        $this->post('/login', [
            'nombre_usuario' => 'cliente1@example.com',
            'password' => 'secret123',
        ])->assertRedirect(route('home'));

        $this->assertAuthenticatedAs($cliente);
    }

    public function test_login_is_throttled_after_five_failures(): void
    {
        $this->cliente();

        for ($i = 0; $i < 5; $i++) {
            $this->post('/login', [
                'nombre_usuario' => 'cliente1',
                'password' => 'incorrecta',
            ]);
        }

        $this->post('/login', [
            'nombre_usuario' => 'cliente1',
            'password' => 'secret123',
        ])->assertSessionHasErrors('nombre_usuario');

        $this->assertGuest();
    }
}
