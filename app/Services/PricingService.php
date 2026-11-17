<?php

namespace App\Services;

use App\Models\Configuracion;

/**
 * Única autoridad para desglosar importes: subtotal, IVA y total.
 */
class PricingService
{
    /**
     * @return array{subtotal: float, iva: float, total: float, iva_pct: float}
     */
    public function desglosar(float $subtotal): array
    {
        $subtotal = round($subtotal, 2);
        $ivaPct = Configuracion::obtenerIva();
        $iva = round($subtotal * $ivaPct / 100, 2);

        return [
            'subtotal' => $subtotal,
            'iva' => $iva,
            'total' => round($subtotal + $iva, 2),
            'iva_pct' => $ivaPct,
        ];
    }
}
