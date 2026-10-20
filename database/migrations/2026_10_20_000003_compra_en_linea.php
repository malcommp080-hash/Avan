<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/** RF003: datos de entrega del pedido en línea y ticket de pago que sube el cliente. */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('venta', function (Blueprint $t) {
            $t->string('Direccion_entrega', 100)->nullable()->after('Origen');
            $t->string('CP_entrega', 5)->nullable()->after('Direccion_entrega');
        });
        Schema::table('pago', function (Blueprint $t) {
            $t->string('Comprobante', 255)->nullable()->after('Referencia');
            $t->dateTime('Comprobante_en')->nullable()->after('Comprobante');
        });
    }

    public function down(): void
    {
        Schema::table('pago', fn (Blueprint $t) => $t->dropColumn(['Comprobante', 'Comprobante_en']));
        Schema::table('venta', fn (Blueprint $t) => $t->dropColumn(['Direccion_entrega', 'CP_entrega']));
    }
};
