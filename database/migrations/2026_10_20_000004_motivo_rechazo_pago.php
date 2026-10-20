<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// RF004: el cliente ve por qué se rechazó su pago
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pago', fn (Blueprint $t) => $t->string('Motivo_rechazo', 255)->nullable()->after('Validado_en'));
    }

    public function down(): void
    {
        Schema::table('pago', fn (Blueprint $t) => $t->dropColumn('Motivo_rechazo'));
    }
};
