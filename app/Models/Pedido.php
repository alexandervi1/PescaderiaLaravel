<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Pedido extends Model
{
    protected $table = 'pedido';
    protected $primaryKey = 'pedido_id';
    public $timestamps = false;

    protected $fillable = ['usuario_id', 'fecha_pedido', 'subtotal', 'iva', 'iva_pct', 'total', 'numero_comprobante', 'estado'];

    protected $casts = [
        'subtotal' => 'decimal:2',
        'iva' => 'decimal:2',
        'iva_pct' => 'decimal:2',
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
                    ->withPivot(['cantidad', 'precio_unitario', 'subtotal']);
    }
}
