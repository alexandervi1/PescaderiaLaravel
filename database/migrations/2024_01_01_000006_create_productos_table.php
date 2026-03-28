<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('producto', function (Blueprint $table) {
            $table->id('producto_id');
            $table->string('nombre', 255);
            $table->text('descripcion')->nullable();
            $table->decimal('precio', 10, 2);
            $table->decimal('stock', 10, 4);
            $table->string('imagen_url', 255)->nullable();
            $table->unsignedBigInteger('categoria_id')->nullable();
            $table->unsignedBigInteger('unidad_compra_id')->nullable();
            $table->unsignedBigInteger('unidad_venta_id')->nullable();
            $table->decimal('factor_conversion', 10, 4)->default(1.0000);
            $table->timestamps();

            $table->foreign('categoria_id')->references('categoria_id')->on('categoria')->onDelete('set null');
            $table->foreign('unidad_compra_id')->references('unidad_id')->on('unidad_medida');
            $table->foreign('unidad_venta_id')->references('unidad_id')->on('unidad_medida');
        });
    }

    public function down()
    {
        Schema::dropIfExists('producto');
    }
};
