<?php

use App\Http\Controllers\Api;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API del administrador para la app móvil (prefijo /api/admin)
|--------------------------------------------------------------------------
| Responde siempre JSON. Autenticación con "Authorization: Bearer <token>".
*/
Route::prefix('admin')->group(function () {
    Route::post('/login', [Api\AuthController::class, 'login'])->middleware('throttle:20,1');
    Route::post('/recuperar', [Api\AuthController::class, 'recuperar'])->middleware('throttle:5,1');

    Route::middleware('admin.api')->group(function () {
        Route::post('/logout', [Api\AuthController::class, 'logout']);
        Route::get('/yo', [Api\AuthController::class, 'yo']);

        Route::get('/dashboard', [Api\PanelController::class, 'dashboard']);
        Route::get('/reportes', [Api\PanelController::class, 'reportes']);

        Route::get('/notificaciones', [Api\NotificacionController::class, 'index']);
        Route::put('/notificaciones/leer-todas', [Api\NotificacionController::class, 'leerTodas']);
        Route::put('/notificaciones/{notificacion}', [Api\NotificacionController::class, 'leer']);

        // Operatividad
        Route::get('/ventas', [Api\VentaController::class, 'index']);
        Route::get('/ventas/catalogo', [Api\VentaController::class, 'catalogo']);
        Route::post('/ventas', [Api\VentaController::class, 'store']);
        Route::get('/ventas/{venta}', [Api\VentaController::class, 'show']);
        Route::delete('/ventas/{venta}', [Api\VentaController::class, 'destroy']);

        Route::get('/pagos', [Api\PagoController::class, 'index']);
        Route::middleware('admin.api:admin,contador')->group(function () {
            Route::put('/pagos/{pago}/aprobar', [Api\PagoController::class, 'aprobar']);
            Route::put('/pagos/{pago}/rechazar', [Api\PagoController::class, 'rechazar']);
        });

        Route::get('/cotizaciones', [Api\CotizacionController::class, 'index']);
        Route::get('/cotizaciones/{cotizacion}', [Api\CotizacionController::class, 'show']);
        Route::put('/cotizaciones/{cotizacion}/atender', [Api\CotizacionController::class, 'atender']);

        Route::get('/pedidos-clientes', [Api\PedidoClienteController::class, 'index']);
        Route::post('/pedidos-clientes', [Api\PedidoClienteController::class, 'store']);
        Route::delete('/pedidos-clientes/{pedido}', [Api\PedidoClienteController::class, 'destroy']);

        Route::get('/pedidos-proveedores', [Api\PedidoProveedorController::class, 'index']);
        Route::post('/pedidos-proveedores', [Api\PedidoProveedorController::class, 'store']);
        Route::delete('/pedidos-proveedores/{pedido}', [Api\PedidoProveedorController::class, 'destroy']);

        Route::get('/inventario', [Api\InventarioController::class, 'index']);
        Route::put('/inventario/{producto}', [Api\InventarioController::class, 'update']);

        // Catálogos
        Route::get('/clientes', [Api\ClienteController::class, 'index']);
        Route::post('/clientes', [Api\ClienteController::class, 'store']);
        Route::put('/clientes/{cliente}', [Api\ClienteController::class, 'update']);
        Route::delete('/clientes/{cliente}', [Api\ClienteController::class, 'destroy']);

        Route::get('/prendas', [Api\PrendaController::class, 'index']);
        Route::post('/prendas', [Api\PrendaController::class, 'store']);
        Route::put('/prendas/{prenda}', [Api\PrendaController::class, 'update']);
        Route::delete('/prendas/{prenda}', [Api\PrendaController::class, 'destroy']);

        // La imagen va como multipart, por eso la edición también es POST
        Route::get('/productos', [Api\ProductoController::class, 'index']);
        Route::post('/productos', [Api\ProductoController::class, 'store']);
        Route::post('/productos/{producto}', [Api\ProductoController::class, 'update']);
        Route::delete('/productos/{producto}', [Api\ProductoController::class, 'destroy']);

        // Sistema
        Route::get('/respaldo', [Api\RespaldoController::class, 'descargar']);
        Route::get('/reporte-clientes', [\App\Http\Controllers\ReportePdfController::class, 'clientes']);

        Route::get('/proveedores', [Api\ProveedorController::class, 'index']);
        Route::post('/proveedores', [Api\ProveedorController::class, 'store']);
        Route::put('/proveedores/{proveedor}', [Api\ProveedorController::class, 'update']);
        Route::delete('/proveedores/{proveedor}', [Api\ProveedorController::class, 'destroy']);
    });

    // Solo administradores
    Route::middleware('admin.api:admin')->group(function () {
        Route::get('/usuarios', [Api\EmpleadoController::class, 'index']);
        Route::post('/usuarios', [Api\EmpleadoController::class, 'store']);
        Route::put('/usuarios/{usuario}', [Api\EmpleadoController::class, 'update']);
        Route::delete('/usuarios/{usuario}', [Api\EmpleadoController::class, 'destroy']);
        Route::get('/bitacora', [Api\SistemaController::class, 'bitacora']);
        Route::post('/restaurar', [Api\RespaldoController::class, 'restaurar']);
    });
});
