<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UnidadMedida extends Model
{
    protected $table = 'unidad_medida';
    protected $primaryKey = 'unidad_id';
    public $timestamps = false;

    protected $fillable = ['nombre', 'abreviatura'];

    public function productosCompra()
    {
        return $this->hasMany(Producto::class, 'unidad_compra_id', 'unidad_id');
    }

    public function productosVenta()
    {
        return $this->hasMany(Producto::class, 'unidad_venta_id', 'unidad_id');
    }
}
