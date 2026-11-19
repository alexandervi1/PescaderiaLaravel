<?php

namespace Tests\Unit;

use App\Models\Pedido;
use PHPUnit\Framework\TestCase;

class PedidoEstadoTest extends TestCase
{
    private function pedido(string $estado): Pedido
    {
        $pedido = new Pedido();
        $pedido->estado = $estado;

        return $pedido;
    }

    public function test_estados_declarados(): void
    {
        $this->assertSame(
            ['Pendiente', 'Enviado', 'Entregado', 'Cancelado'],
            Pedido::ESTADOS
        );
    }

    public function test_pendiente_puede_pasar_a_enviado_o_cancelado(): void
    {
        $pedido = $this->pedido(Pedido::ESTADO_PENDIENTE);

        $this->assertTrue($pedido->puedeCambiarA(Pedido::ESTADO_ENVIADO));
        $this->assertTrue($pedido->puedeCambiarA(Pedido::ESTADO_CANCELADO));
        $this->assertFalse($pedido->puedeCambiarA(Pedido::ESTADO_ENTREGADO));
    }

    public function test_enviado_puede_pasar_a_entregado_o_cancelado(): void
    {
        $pedido = $this->pedido(Pedido::ESTADO_ENVIADO);

        $this->assertTrue($pedido->puedeCambiarA(Pedido::ESTADO_ENTREGADO));
        $this->assertTrue($pedido->puedeCambiarA(Pedido::ESTADO_CANCELADO));
        $this->assertFalse($pedido->puedeCambiarA(Pedido::ESTADO_PENDIENTE));
    }

    public function test_entregado_y_cancelado_son_terminales(): void
    {
        foreach ([Pedido::ESTADO_ENTREGADO, Pedido::ESTADO_CANCELADO] as $estado) {
            $pedido = $this->pedido($estado);

            foreach (Pedido::ESTADOS as $destino) {
                $this->assertSame($destino === $estado, $pedido->puedeCambiarA($destino));
            }
        }
    }
}
