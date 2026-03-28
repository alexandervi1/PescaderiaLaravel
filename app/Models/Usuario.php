<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class Usuario extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $table = 'usuario';
    protected $primaryKey = 'usuario_id';

    protected $fillable = [
        'nombre_usuario',
        'nombre_completo',
        'identificacion',
        'password',
        'rol_id',
        'direccion',
        'correo',
        'telefono',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    // ── Relaciones ──

    public function rol()
    {
        return $this->belongsTo(Rol::class, 'rol_id', 'rol_id');
    }

    public function carrito()
    {
        return $this->hasMany(Carrito::class, 'usuario_id', 'usuario_id');
    }

    public function pedidos()
    {
        return $this->hasMany(Pedido::class, 'usuario_id', 'usuario_id');
    }

    // ── Helpers de rol ──

    public function esAdmin(): bool
    {
        return $this->rol_id === 1;
    }

    public function esCliente(): bool
    {
        return $this->rol_id === 2;
    }

    public function esVisitante(): bool
    {
        return $this->rol_id === 3;
    }

    /**
     * Columna usada para autenticación (username en vez de email).
     */
    public function getAuthIdentifierName()
    {
        return 'usuario_id';
    }
}
