<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('configuracion', function (Blueprint $table) {
            $table->string('clave', 50)->primary();
            $table->string('valor', 255);
        });
    }

    public function down()
    {
        Schema::dropIfExists('configuracion');
    }
};
