<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('unidad_medida', function (Blueprint $table) {
            $table->id('unidad_id');
            $table->string('nombre', 50);
            $table->string('abreviatura', 10);
        });
    }

    public function down()
    {
        Schema::dropIfExists('unidad_medida');
    }
};
