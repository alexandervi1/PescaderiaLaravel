<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Configuracion extends Model
{
    protected $table = 'configuracion';
    protected $primaryKey = 'clave';
    public $incrementing = false;
    protected $keyType = 'string';
    public $timestamps = false;

    protected $fillable = ['clave', 'valor'];

    /**
     * Obtener el valor de una configuración por su clave.
     */
    public static function obtenerValor(string $clave, $default = null)
    {
        $config = self::find($clave);
        return $config ? $config->valor : $default;
    }

    /**
     * Obtener el IVA actual.
     */
    public static function obtenerIva()
    {
        return (float) self::obtenerValor('iva_actual', 15);
    }
}
