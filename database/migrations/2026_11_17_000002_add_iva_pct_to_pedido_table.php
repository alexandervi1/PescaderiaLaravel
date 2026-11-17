<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('pedido', function (Blueprint $table) {
            $table->decimal('iva_pct', 5, 2)->default(0)->after('iva');
        });
    }

    public function down()
    {
        Schema::table('pedido', function (Blueprint $table) {
            $table->dropColumn('iva_pct');
        });
    }
};
