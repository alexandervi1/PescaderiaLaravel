<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;

class PedidoProducto extends Pivot
{
    protected $table = 'pedidoproducto';
    public $incrementing = false;
    public $timestamps = false;

    protected $fillable = ['pedido_id', 'producto_id', 'cantidad'];

    protected $casts = [
        'cantidad' => 'decimal:2',
    ];

    public function pedido()
    {
        return $this->belongsTo(Pedido::class, 'pedido_id', 'pedido_id');
    }

    public function producto()
    {
        return $this->belongsTo(Producto::class, 'producto_id', 'producto_id');
    }
}
