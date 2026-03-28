<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('usuario', function (Blueprint $table) {
            $table->id('usuario_id');
            $table->string('nombre_usuario', 255)->unique();
            $table->string('nombre_completo', 255);
            $table->string('password', 255); // Laravel uses 'password' convention
            $table->unsignedBigInteger('rol_id')->nullable();
            $table->string('direccion', 100)->default('');
            $table->string('correo', 100)->default('');
            $table->string('telefono', 10)->nullable();
            $table->rememberToken();
            $table->timestamps();

            $table->foreign('rol_id')->references('rol_id')->on('rol')->onDelete('set null');
        });
    }

    public function down()
    {
        Schema::dropIfExists('usuario');
    }
};
