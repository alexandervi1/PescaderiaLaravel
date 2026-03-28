<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('usuario', function (Blueprint $table) {
            $table->string('identificacion', 13)->nullable()->after('nombre_completo');
        });

        Schema::table('pedido', function (Blueprint $table) {
            $table->string('numero_comprobante', 20)->nullable()->after('total');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('usuario', function (Blueprint $table) {
            $table->dropColumn('identificacion');
        });

        Schema::table('pedido', function (Blueprint $table) {
            $table->dropColumn('numero_comprobante');
        });
    }
};
