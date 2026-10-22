<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// App móvil del administrador: cada inicio de sesión genera un token (se guarda su hash SHA-256)
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('empleado_token', function (Blueprint $t) {
            $t->increments('Id_token');
            $t->integer('Id_empleado');
            $t->char('Token', 64)->unique();
            $t->string('Dispositivo', 255)->nullable();
            $t->dateTime('Creado_en');
            $t->dateTime('Ultimo_uso');
            $t->foreign('Id_empleado', 'fk_empleado_token')->references('Id_empleado')->on('empleado')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('empleado_token');
    }
};
