<?php

namespace Tests\Feature;

use App\Models\Rol;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthThrottleTest extends TestCase
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

    public function test_el_login_limita_los_intentos_por_ip(): void
    {
        for ($i = 1; $i <= 20; $i++) {
            $this->post('/login', [
                'nombre_usuario' => "usuario{$i}",
                'password' => 'incorrecta',
            ]);
        }

        $this->post('/login', [
            'nombre_usuario' => 'usuario21',
            'password' => 'incorrecta',
        ])->assertStatus(429);
    }

    public function test_el_registro_limita_las_altas_por_ip(): void
    {
        for ($i = 1; $i <= 10; $i++) {
            $this->post('/registro', [
                'nombre_completo' => "Persona {$i}",
                'identificacion' => '131234567' . $i,
                'nombre_usuario' => "persona{$i}",
                'password' => 'secret123',
                'password_confirmation' => 'secret123',
                'direccion' => 'Calle 1',
                'correo' => "persona{$i}@example.com",
                'telefono' => '0999999999',
            ]);
        }

        $this->post('/registro', [
            'nombre_completo' => 'Persona 11',
            'identificacion' => '1312345670',
            'nombre_usuario' => 'persona11',
            'password' => 'secret123',
            'password_confirmation' => 'secret123',
            'direccion' => 'Calle 1',
            'correo' => 'persona11@example.com',
            'telefono' => '0999999999',
        ])->assertStatus(429);
    }
}
