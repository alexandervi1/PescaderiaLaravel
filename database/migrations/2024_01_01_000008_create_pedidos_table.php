<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('pedido', function (Blueprint $table) {
            $table->id('pedido_id');
            $table->unsignedBigInteger('usuario_id');
            $table->datetime('fecha_pedido')->useCurrent();
            $table->decimal('total', 10, 2)->nullable();

            $table->foreign('usuario_id')->references('usuario_id')->on('usuario')->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('pedido');
    }
};
