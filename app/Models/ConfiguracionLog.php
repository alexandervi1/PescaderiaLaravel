<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ConfiguracionLog extends Model
{
    protected $table = 'configuracion_log';
    public $timestamps = false;

    protected $fillable = ['usuario_id', 'clave', 'valor_anterior', 'valor_nuevo', 'created_at'];

    protected $casts = [
        'created_at' => 'datetime',
    ];

    public function usuario()
    {
        return $this->belongsTo(Usuario::class, 'usuario_id', 'usuario_id');
    }
}
