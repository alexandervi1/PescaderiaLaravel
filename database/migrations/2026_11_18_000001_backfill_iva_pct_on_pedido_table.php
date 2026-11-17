<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up()
    {
        // 100.0 fuerza aritmética real: SQLite guarda los decimales enteros como INTEGER
        // y `iva / subtotal` haría división entera.
        DB::table('pedido')
            ->where('subtotal', '>', 0)
            ->update(['iva_pct' => DB::raw('ROUND(iva * 100.0 / subtotal, 2)')]);
    }

    public function down()
    {
        // El dato histórico calculado no se revierte.
    }
};
