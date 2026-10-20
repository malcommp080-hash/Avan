<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Catálogo de la tienda (RF003, diseño de Figma): cada producto tiene segmento
 * (Caballero/Dama/Niño/Niña/Unisex), color, descripción, precio de mayoreo y
 * stock por talla. registroproductos.Stock se conserva como la suma de las tallas.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('registroproductos', function (Blueprint $t) {
            $t->string('Segmento', 10)->default('UNISEX')->after('Id_categoria');
            $t->string('Color', 30)->nullable()->after('Segmento');
            $t->string('Descripcion', 255)->nullable()->after('Color');
            $t->decimal('Precio_mayoreo', 10, 2)->nullable()->after('Precioneto');
            $t->unsignedSmallInteger('Min_mayoreo')->default(5)->after('Precio_mayoreo');
            $t->boolean('Activo')->default(true)->after('Imagen');
        });

        Schema::create('producto_talla', function (Blueprint $t) {
            $t->increments('Id_pt');
            $t->integer('Id_producto');
            $t->string('Talla', 5);
            $t->integer('Stock')->default(0);
            $t->unique(['Id_producto', 'Talla']);
            $t->foreign('Id_producto', 'fk_producto_talla')->references('Id_producto')->on('registroproductos')->cascadeOnDelete();
        });

        // Los productos que ya existían quedan con talla única y su stock actual
        DB::statement("INSERT INTO producto_talla (Id_producto, Talla, Stock) SELECT Id_producto, 'UNI', Stock FROM registroproductos");

        Schema::table('detalleventa', function (Blueprint $t) {
            $t->string('Talla', 5)->nullable()->after('Id_producto');
        });
        Schema::table('detallecotizacion', function (Blueprint $t) {
            $t->string('Talla', 5)->nullable()->after('Id_producto');
        });
    }

    public function down(): void
    {
        Schema::table('detallecotizacion', fn (Blueprint $t) => $t->dropColumn('Talla'));
        Schema::table('detalleventa', fn (Blueprint $t) => $t->dropColumn('Talla'));
        Schema::dropIfExists('producto_talla');
        Schema::table('registroproductos', function (Blueprint $t) {
            $t->dropColumn(['Segmento', 'Color', 'Descripcion', 'Precio_mayoreo', 'Min_mayoreo', 'Activo']);
        });
    }
};
