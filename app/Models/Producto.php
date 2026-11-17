<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Producto extends Model
{
    protected $table = 'producto';
    protected $primaryKey = 'producto_id';

    protected $fillable = [
        'nombre',
        'descripcion',
        'precio',
        'stock',
        'imagen_url',
        'categoria_id',
        'unidad_compra_id',
        'unidad_venta_id',
        'factor_conversion',
    ];

    protected $casts = [
        'precio' => 'decimal:2',
        'stock' => 'decimal:4',
        'factor_conversion' => 'decimal:4',
    ];

    // ── Relaciones ──

    public function categoria()
    {
        return $this->belongsTo(Categoria::class, 'categoria_id', 'categoria_id');
    }

    public function unidadCompra()
    {
        return $this->belongsTo(UnidadMedida::class, 'unidad_compra_id', 'unidad_id');
    }

    public function unidadVenta()
    {
        return $this->belongsTo(UnidadMedida::class, 'unidad_venta_id', 'unidad_id');
    }

    public function pedidos()
    {
        return $this->belongsToMany(Pedido::class, 'pedidoproducto', 'producto_id', 'pedido_id')
                    ->withPivot('cantidad');
    }

    // ── Helpers ──

    /**
     * Obtener el stock disponible en unidad de venta.
     */
    public function getStockVentaAttribute(): float
    {
        return $this->stock * $this->factor_conversion;
    }

    /**
     * Descontar stock al registrar un pedido.
     *
     * @throws \RuntimeException cuando la cantidad supera la existencia disponible.
     */
    public function descontarStock(float $cantidadVenta): void
    {
        if ($cantidadVenta <= 0) {
            throw new \InvalidArgumentException('La cantidad a descontar debe ser mayor que cero.');
        }

        if ($cantidadVenta > $this->stockVenta + 1e-9) {
            throw new \RuntimeException(
                "Stock insuficiente para {$this->nombre}: disponible {$this->stockVenta}, solicitado {$cantidadVenta}."
            );
        }

        $this->stock -= $this->enUnidadCompra($cantidadVenta);
        $this->save();
    }

    /**
     * Devolver stock al anular un pedido.
     */
    public function reponerStock(float $cantidadVenta): void
    {
        if ($cantidadVenta <= 0) {
            return;
        }

        $this->stock += $this->enUnidadCompra($cantidadVenta);
        $this->save();
    }

    private function enUnidadCompra(float $cantidadVenta): float
    {
        $factor = (float) $this->factor_conversion;

        if ($factor <= 0 || $this->unidad_compra_id === $this->unidad_venta_id) {
            return $cantidadVenta;
        }

        return $cantidadVenta / $factor;
    }

    /**
     * Factor de conversión entre la unidad de compra y la de venta.
     * Devuelve null cuando la combinación no está soportada.
     */
    public static function factorConversion(?int $unidadCompraId, ?int $unidadVentaId): ?float
    {
        if ($unidadCompraId === null || $unidadVentaId === null) {
            return null;
        }

        if ($unidadCompraId === $unidadVentaId) {
            return 1.0;
        }

        $compra = UnidadMedida::find($unidadCompraId);
        $venta = UnidadMedida::find($unidadVentaId);

        if (!$compra || !$venta) {
            return null;
        }

        if ($compra->abreviatura === 'Tn' && $venta->abreviatura === 'lb') {
            return 2204.62;
        }

        return null;
    }

    /**
     * Asignar factor de conversión al crear o al cambiar las unidades.
     */
    protected static function booted()
    {
        static::saving(function (Producto $producto) {
            if (!$producto->exists || $producto->isDirty(['unidad_compra_id', 'unidad_venta_id'])) {
                $factor = self::factorConversion($producto->unidad_compra_id, $producto->unidad_venta_id);
                if ($factor !== null) {
                    $producto->factor_conversion = $factor;
                }
            }
        });
    }
}
