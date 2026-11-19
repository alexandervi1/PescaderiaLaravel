<?php

namespace Tests\Feature;

use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class SeederProduccionTest extends TestCase
{
    use RefreshDatabase;

    private const CLAVES = [
        'INITIAL_ADMIN_USER',
        'INITIAL_ADMIN_PASSWORD',
        'INITIAL_ADMIN_EMAIL',
    ];

    /** @var array<string, string|false> */
    private array $originales = [];

    protected function setUp(): void
    {
        parent::setUp();

        foreach (self::CLAVES as $clave) {
            $this->originales[$clave] = getenv($clave);
        }

        foreach (self::CLAVES as $clave) {
            putenv("{$clave}=");
        }
    }

    protected function tearDown(): void
    {
        foreach ($this->originales as $clave => $valor) {
            $valor === false ? putenv($clave) : putenv("{$clave}={$valor}");
        }

        parent::tearDown();
    }

    private function enProduccion(): void
    {
        $this->app['env'] = 'production';
    }

    private function sembrar(): void
    {
        $seeder = new DatabaseSeeder();
        $seeder->setContainer($this->app);
        $seeder->__invoke();
    }

    public function test_produccion_sin_credenciales_lanza_excepcion(): void
    {
        $this->enProduccion();

        $this->expectException(\RuntimeException::class);

        $this->sembrar();
    }

    public function test_produccion_con_password_por_defecto_lanza_excepcion(): void
    {
        $this->enProduccion();
        putenv('INITIAL_ADMIN_USER=admin');
        putenv('INITIAL_ADMIN_PASSWORD=admin123');
        putenv('INITIAL_ADMIN_EMAIL=admin@example.com');

        $this->expectException(\RuntimeException::class);

        $this->sembrar();
    }

    public function test_testing_sin_variables_crea_el_admin_por_defecto(): void
    {
        $this->sembrar();

        $admin = \App\Models\Usuario::where('rol_id', 1)->first();

        $this->assertNotNull($admin);
        $this->assertSame('admin123', $admin->nombre_usuario);
        $this->assertTrue(Hash::check('admin123', $admin->password));
    }
}
