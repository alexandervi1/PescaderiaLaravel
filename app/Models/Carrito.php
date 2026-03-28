<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Carrito extends Model
{
    protected $table = 'carrito';
    protected $primaryKey = 'carrito_id';
    public $timestamps = false;

    protected $fillable = ['usuario_id', 'producto_id', 'cantidad'];

    public function usuario()
    {
        return $this->belongsTo(Usuario::class, 'usuario_id', 'usuario_id');
    }

    public function producto()
    {
        return $this->belongsTo(Producto::class, 'producto_id', 'producto_id');
    }

    /**
     * Subtotal de este item del carrito.
     */
    public function getSubtotalAttribute(): float
    {
        return $this->cantidad * $this->producto->precio;
    }
}
