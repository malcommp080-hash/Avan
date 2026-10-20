<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Amplía la BD original (database/dav.sql) con lo que piden los requerimientos
 * de la matriz de trazabilidad: detalle real de ventas (RF002/RF012), pagos (RF004),
 * cotizaciones y notificaciones (RF009), imagen de producto (RF003) y bitácora de
 * accesos (RF001/RF014). Se ejecuta DESPUÉS de importar dav.sql.
 */
return new class extends Migration
{
    public function up(): void
    {
        // RF003: imagen del producto para el catálogo
        Schema::table('registroproductos', function (Blueprint $t) {
            $t->string('Imagen', 255)->nullable()->after('Precioneto');
        });

        // RF002: la venta guarda fecha y hora, forma de pago, quién la registró y de dónde viene
        Schema::table('venta', function (Blueprint $t) {
            $t->dateTime('Fecha_hora')->nullable()->after('Fecha');
            $t->string('Forma_pago', 20)->nullable()->after('Total');
            $t->string('Origen', 15)->default('MOSTRADOR')->after('Forma_pago'); // MOSTRADOR | EN_LINEA
            $t->integer('Id_empleado')->nullable()->after('Id_cliente');
            $t->foreign('Id_empleado', 'fk_empleado_venta')->references('Id_empleado')->on('empleado')->nullOnDelete();
        });

        // RF002/RF012: cada renglón del detalle pertenece a una venta
        Schema::table('detalleventa', function (Blueprint $t) {
            $t->integer('Id_venta')->nullable()->after('Id_detallev');
            $t->decimal('Precio_unitario', 10, 2)->default(0)->after('Cantidad');
            $t->decimal('Subtotal', 10, 2)->default(0)->after('Precio_unitario');
            $t->foreign('Id_venta', 'fk_venta_detallev')->references('Id_venta')->on('venta')->cascadeOnDelete();
        });

        // RF004: pagos de ventas en línea, pendientes de aprobación
        Schema::create('pago', function (Blueprint $t) {
            $t->increments('Id_pago');
            $t->string('Folio', 20)->unique();
            $t->integer('Id_venta');
            $t->decimal('Monto', 10, 2);
            $t->string('Metodo', 20);
            $t->string('Estado', 15)->default('PENDIENTE'); // PENDIENTE | APROBADO | RECHAZADO
            $t->string('Referencia', 100)->nullable();
            $t->integer('Validado_por')->nullable();
            $t->dateTime('Validado_en')->nullable();
            $t->dateTime('Creado_en')->useCurrent();
            $t->foreign('Id_venta', 'fk_venta_pago')->references('Id_venta')->on('venta')->cascadeOnDelete();
            $t->foreign('Validado_por', 'fk_empleado_pago')->references('Id_empleado')->on('empleado')->nullOnDelete();
            $t->index('Estado');
        });

        // RF009: cotizaciones generadas desde el carrito
        Schema::create('cotizacion', function (Blueprint $t) {
            $t->increments('Id_cotizacion');
            $t->integer('Id_cliente');
            $t->decimal('Total', 10, 2);
            $t->string('Estado', 15)->default('PENDIENTE'); // PENDIENTE | ATENDIDA
            $t->integer('Atendida_por')->nullable();
            $t->dateTime('Atendida_en')->nullable();
            $t->dateTime('Creado_en')->useCurrent();
            $t->foreign('Id_cliente', 'fk_cliente_cotizacion')->references('Id_cliente')->on('cliente')->cascadeOnDelete();
            $t->foreign('Atendida_por', 'fk_empleado_cotizacion')->references('Id_empleado')->on('empleado')->nullOnDelete();
        });

        Schema::create('detallecotizacion', function (Blueprint $t) {
            $t->increments('Id_detallec');
            $t->unsignedInteger('Id_cotizacion');
            $t->integer('Id_producto');
            $t->integer('Cantidad');
            $t->decimal('Precio_unitario', 10, 2);
            $t->decimal('Subtotal', 10, 2);
            $t->foreign('Id_cotizacion', 'fk_cotizacion_detallec')->references('Id_cotizacion')->on('cotizacion')->cascadeOnDelete();
            $t->foreign('Id_producto', 'fk_producto_detallec')->references('Id_producto')->on('registroproductos');
        });

        // RF009 (y avisos internos): notificaciones para empleados. Id_empleado NULL = para todos.
        Schema::create('notificacion', function (Blueprint $t) {
            $t->increments('Id_notificacion');
            $t->integer('Id_empleado')->nullable();
            $t->string('Tipo', 30);
            $t->string('Mensaje', 255);
            $t->string('Url', 255)->nullable();
            $t->boolean('Leida')->default(false);
            $t->dateTime('Creado_en')->useCurrent();
            $t->foreign('Id_empleado', 'fk_empleado_notificacion')->references('Id_empleado')->on('empleado')->cascadeOnDelete();
            $t->index('Leida');
        });

        // RF001/RF014: bitácora de intentos de acceso (admin y clientes)
        Schema::create('bitacora_acceso', function (Blueprint $t) {
            $t->increments('Id_bitacora');
            $t->string('Tipo', 10);            // ADMIN | CLIENTE
            $t->string('Telefono', 50);
            $t->integer('Id_usuario')->nullable();
            $t->string('Resultado', 20);       // EXITO | FALLIDO | BLOQUEADO | LOGOUT | INACTIVIDAD
            $t->string('Ip', 45)->nullable();
            $t->string('Navegador', 255)->nullable();
            $t->dateTime('Creado_en')->useCurrent();
            $t->index(['Tipo', 'Creado_en']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bitacora_acceso');
        Schema::dropIfExists('notificacion');
        Schema::dropIfExists('detallecotizacion');
        Schema::dropIfExists('cotizacion');
        Schema::dropIfExists('pago');

        Schema::table('detalleventa', function (Blueprint $t) {
            $t->dropForeign('fk_venta_detallev');
            $t->dropColumn(['Id_venta', 'Precio_unitario', 'Subtotal']);
        });
        Schema::table('venta', function (Blueprint $t) {
            $t->dropForeign('fk_empleado_venta');
            $t->dropColumn(['Fecha_hora', 'Forma_pago', 'Origen', 'Id_empleado']);
        });
        Schema::table('registroproductos', function (Blueprint $t) {
            $t->dropColumn('Imagen');
        });
    }
};
