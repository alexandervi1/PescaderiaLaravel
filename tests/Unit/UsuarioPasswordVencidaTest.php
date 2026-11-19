<?php

namespace Tests\Unit;

use App\Models\Usuario;
use Illuminate\Support\Carbon;
use Tests\TestCase;

/**
 * Carbon 3 (Laravel 11+) devuelve diffInDays() como float y con signo:
 * estas pruebas fijan el comportamiento en el límite del plazo de rotación.
 */
class UsuarioPasswordVencidaTest extends TestCase
{
    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

    private function usuarioConClaveDeHaceDias(float $dias): Usuario
    {
        Carbon::setTestNow('2026-11-19 12:00:00');

        $usuario = new Usuario();
        $usuario->password_changed_at = now()->subMinutes((int) round($dias * 24 * 60));

        return $usuario;
    }

    public function test_89_dias_no_esta_vencida(): void
    {
        $this->assertFalse($this->usuarioConClaveDeHaceDias(89)->passwordVencida(90));
    }

    public function test_89_dias_y_medio_no_esta_vencida(): void
    {
        $this->assertFalse($this->usuarioConClaveDeHaceDias(89.5)->passwordVencida(90));
    }

    public function test_90_dias_esta_vencida(): void
    {
        $this->assertTrue($this->usuarioConClaveDeHaceDias(90)->passwordVencida(90));
    }

    public function test_91_dias_esta_vencida(): void
    {
        $this->assertTrue($this->usuarioConClaveDeHaceDias(91)->passwordVencida(90));
    }

    public function test_sin_fechas_no_esta_vencida(): void
    {
        $this->assertFalse((new Usuario())->passwordVencida(90));
    }
}
