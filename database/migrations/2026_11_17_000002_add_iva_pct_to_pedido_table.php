<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('pedido', function (Blueprint $table) {
            $table->decimal('iva_pct', 5, 2)->default(0)->after('iva');
        });

        // Rellena los pedidos existentes con la tasa que realmente se aplicó.
        DB::table('pedido')
            ->where('subtotal', '>', 0)
            ->update(['iva_pct' => DB::raw('ROUND(iva / subtotal * 100, 2)')]);
    }

    public function down()
    {
        Schema::table('pedido', function (Blueprint $table) {
            $table->dropColumn('iva_pct');
        });
    }
};
