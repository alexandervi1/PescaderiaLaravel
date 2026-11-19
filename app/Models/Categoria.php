<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Categoria extends Model
{
    protected $table = 'categoria';
    protected $primaryKey = 'categoria_id';
    public $timestamps = false;

    protected $fillable = ['nombre'];

    public function productos(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Producto::class, 'categoria_id', 'categoria_id');
    }

    /**
     * Obtener slug para URLs amigables.
     */
    public function getSlugAttribute(): string
    {
        return strtolower(str_replace([' ', 'á', 'é', 'í', 'ó', 'ú'], ['-', 'a', 'e', 'i', 'o', 'u'], $this->nombre));
    }
}
