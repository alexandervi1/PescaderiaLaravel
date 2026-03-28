<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('pedidoproducto', function (Blueprint $table) {
            $table->unsignedBigInteger('pedido_id');
            $table->unsignedBigInteger('producto_id');
            $table->decimal('cantidad', 10, 2);

            $table->primary(['pedido_id', 'producto_id']);
            $table->foreign('pedido_id')->references('pedido_id')->on('pedido')->onDelete('cascade');
            $table->foreign('producto_id')->references('producto_id')->on('producto')->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('pedidoproducto');
    }
};
