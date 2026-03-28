<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Pedido extends Model
{
    protected $table = 'pedido';
    protected $primaryKey = 'pedido_id';
    public $timestamps = false;

    protected $fillable = ['usuario_id', 'fecha_pedido', 'total', 'numero_comprobante', 'estado'];

    protected $casts = [
        'total' => 'decimal:2',
        'fecha_pedido' => 'datetime',
    ];

    public function usuario()
    {
        return $this->belongsTo(Usuario::class, 'usuario_id', 'usuario_id');
    }

    public function productos()
    {
        return $this->belongsToMany(Producto::class, 'pedidoproducto', 'pedido_id', 'producto_id')
                    ->withPivot('cantidad');
    }

    /**
     * Calcular y actualizar el total del pedido.
     */
    public function calcularTotal(): void
    {
        $this->total = $this->productos->sum(function ($producto) {
            return $producto->pivot->cantidad * $producto->precio;
        });
        $this->save();
    }
}
