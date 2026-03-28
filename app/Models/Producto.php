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
     */
    public function descontarStock(float $cantidadVenta): void
    {
        if ($this->unidad_compra_id === $this->unidad_venta_id) {
            $this->stock -= $cantidadVenta;
        } else {
            $this->stock -= ($cantidadVenta / $this->factor_conversion);
        }
        $this->save();
    }

    /**
     * Asignar factor de conversión automáticamente.
     */
    protected static function booted()
    {
        static::creating(function (Producto $producto) {
            if ($producto->unidad_compra_id == 1 && $producto->unidad_venta_id == 2) {
                $producto->factor_conversion = 2204.6200;
            } elseif ($producto->unidad_compra_id == 3 && $producto->unidad_venta_id == 3) {
                $producto->factor_conversion = 1.0000;
            }
        });
    }
}
