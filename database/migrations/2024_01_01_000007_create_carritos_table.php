<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('carrito', function (Blueprint $table) {
            $table->id('carrito_id');
            $table->unsignedBigInteger('usuario_id');
            $table->unsignedBigInteger('producto_id');
            $table->integer('cantidad')->default(1);
            $table->timestamp('fecha_agregado')->useCurrent();

            $table->unique(['usuario_id', 'producto_id']);
            $table->foreign('usuario_id')->references('usuario_id')->on('usuario')->onDelete('cascade');
            $table->foreign('producto_id')->references('producto_id')->on('producto')->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('carrito');
    }
};
