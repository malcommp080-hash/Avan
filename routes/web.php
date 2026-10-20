<?php

use App\Http\Controllers\AdminAuthController;
use App\Http\Controllers\BitacoraController;
use App\Http\Controllers\CarritoController;
use App\Http\Controllers\CotizacionController;
use App\Http\Controllers\ClienteAuthController;
use App\Http\Controllers\ClienteController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\EmpleadoController;
use App\Http\Controllers\InventarioController;
use App\Http\Controllers\NotificacionController;
use App\Http\Controllers\PedidoClienteController;
use App\Http\Controllers\PedidoController;
use App\Http\Controllers\PedidoProveedorController;
use App\Http\Controllers\PrendaController;
use App\Http\Controllers\ProductoController;
use App\Http\Controllers\ProveedorController;
use App\Http\Controllers\ReportePdfController;
use App\Http\Controllers\RespaldoController;
use App\Http\Controllers\TiendaController;
use App\Http\Controllers\ValidacionPagoController;
use App\Http\Controllers\VentaController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Tienda (clientes)
|--------------------------------------------------------------------------
*/
Route::name('tienda.')->group(function () {
    Route::get('/', [TiendaController::class, 'inicio'])->name('inicio');
    Route::get('/categorias', [TiendaController::class, 'categorias'])->name('categorias');

    Route::get('/login', [ClienteAuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [ClienteAuthController::class, 'login'])->middleware('throttle:login')->name('login.post');
    Route::post('/logout', [ClienteAuthController::class, 'logout'])->name('logout');

    Route::get('/crear-cuenta', [ClienteAuthController::class, 'showRegistro'])->name('registro');
    Route::post('/crear-cuenta', [ClienteAuthController::class, 'registro'])->name('registro.post');
    Route::get('/crear-cuenta/exito', [ClienteAuthController::class, 'registroExito'])->name('registro.exito');

    Route::get('/recuperar-password', [ClienteAuthController::class, 'recuperar'])->name('recuperar');
    Route::post('/recuperar-password', [ClienteAuthController::class, 'recuperarBuscar'])->name('recuperar.buscar');
    Route::get('/recuperar-password/nueva', [ClienteAuthController::class, 'recuperarPaso2'])->name('recuperar.paso2');
    Route::post('/recuperar-password/nueva', [ClienteAuthController::class, 'recuperarGuardar'])->name('recuperar.guardar');
    Route::get('/recuperar-password/exito', [ClienteAuthController::class, 'recuperarExito'])->name('recuperar.exito');

    // RF003: catálogo y carrito (se puede llenar el carrito sin sesión)
    Route::get('/catalogo', [TiendaController::class, 'catalogo'])->name('catalogo');
    Route::get('/producto/{producto}', [TiendaController::class, 'producto'])->name('producto');
    Route::get('/carrito', [CarritoController::class, 'index'])->name('carrito');
    Route::post('/carrito', [CarritoController::class, 'agregar'])->name('carrito.agregar');
    Route::patch('/carrito', [CarritoController::class, 'actualizar'])->name('carrito.actualizar');
    Route::delete('/carrito', [CarritoController::class, 'quitar'])->name('carrito.quitar');

    // Pagar y cotizar sí requieren cuenta
    Route::middleware('cliente')->group(function () {
        Route::get('/tienda', fn () => redirect()->route('tienda.inicio'))->name('tienda');
        Route::get('/pedido/confirmar', [PedidoController::class, 'confirmar'])->name('pedido.confirmar');
        Route::post('/pedido', [PedidoController::class, 'crear'])->name('pedido.crear');
        Route::get('/pedido/{venta}/pago', [PedidoController::class, 'pago'])->name('pedido.pago');
        Route::get('/pedido/{venta}/comprobante', [PedidoController::class, 'comprobante'])->name('pedido.comprobante');
        Route::post('/pedido/{venta}/comprobante', [PedidoController::class, 'subirComprobante'])->name('pedido.comprobante.subir');
        Route::get('/pedido/{venta}/finalizado', [PedidoController::class, 'finalizado'])->name('pedido.finalizado');
        Route::delete('/pedido/{venta}', [PedidoController::class, 'cancelar'])->name('pedido.cancelar');
        Route::get('/mis-pedidos', [PedidoController::class, 'index'])->name('pedidos');
        Route::post('/cotizacion', [PedidoController::class, 'cotizar'])->name('cotizar');
        Route::get('/cotizacion/{cotizacion}', [PedidoController::class, 'cotizacion'])->name('cotizacion');
    });
});

/*
|--------------------------------------------------------------------------
| Administrador
|--------------------------------------------------------------------------
*/
Route::prefix('admin')->name('admin.')->group(function () {
    Route::get('/', fn () => redirect()->route('admin.dashboard'));
    Route::get('/login', [AdminAuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AdminAuthController::class, 'login'])->middleware('throttle:login')->name('login.post');
    Route::post('/logout', [AdminAuthController::class, 'logout'])->name('logout');
    Route::get('/recuperar-password', [AdminAuthController::class, 'showRecuperar'])->name('recuperar');
    Route::post('/recuperar-password', [AdminAuthController::class, 'recuperar'])->middleware('throttle:recuperar')->name('recuperar.post');

    Route::middleware('admin')->group(function () {
        Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

        Route::get('notificaciones', [NotificacionController::class, 'index'])->name('notificaciones.index');
        Route::post('notificaciones/leer-todas', [NotificacionController::class, 'leerTodas'])->name('notificaciones.leer-todas');
        Route::get('notificaciones/{notificacion}', [NotificacionController::class, 'abrir'])->name('notificaciones.abrir');

        // Operatividad
        Route::resource('ventas', VentaController::class)->only(['index', 'store', 'show', 'destroy'])->parameters(['ventas' => 'venta']);

        Route::get('pedidos-clientes', [PedidoClienteController::class, 'index'])->name('pedidos-clientes.index');
        Route::post('pedidos-clientes', [PedidoClienteController::class, 'store'])->name('pedidos-clientes.store');
        Route::delete('pedidos-clientes/{pedido}', [PedidoClienteController::class, 'destroy'])->name('pedidos-clientes.destroy');

        Route::get('pedidos-proveedores', [PedidoProveedorController::class, 'index'])->name('pedidos-proveedores.index');
        Route::post('pedidos-proveedores', [PedidoProveedorController::class, 'store'])->name('pedidos-proveedores.store');
        Route::delete('pedidos-proveedores/{pedido}', [PedidoProveedorController::class, 'destroy'])->name('pedidos-proveedores.destroy');

        Route::resource('proveedores', ProveedorController::class)->except(['create', 'show'])->parameters(['proveedores' => 'proveedor']);

        Route::get('validacion-pago', [ValidacionPagoController::class, 'index'])->name('validacion-pago.index');
        Route::put('validacion-pago/{venta}', [ValidacionPagoController::class, 'update'])->name('validacion-pago.update');

        // RF009: cotizaciones de clientes
        Route::get('cotizaciones', [CotizacionController::class, 'index'])->name('cotizaciones.index');
        Route::get('cotizaciones/{cotizacion}', [CotizacionController::class, 'show'])->name('cotizaciones.show');
        Route::put('cotizaciones/{cotizacion}/atender', [CotizacionController::class, 'atender'])->name('cotizaciones.atender');

        // Catálogos
        Route::resource('clientes', ClienteController::class)->except(['create', 'show'])->parameters(['clientes' => 'cliente']);
        Route::resource('prendas', PrendaController::class)->except(['create', 'show'])->parameters(['prendas' => 'prenda']);
        Route::resource('productos', ProductoController::class)->except(['create', 'show'])->parameters(['productos' => 'producto']);

        Route::get('inventario', [InventarioController::class, 'index'])->name('inventario.index');
        Route::put('inventario/{producto}', [InventarioController::class, 'update'])->name('inventario.update');

        // Sistema
        Route::get('respaldar-bd', [RespaldoController::class, 'index'])->name('respaldo.index');
        Route::get('respaldar-bd/descargar', [RespaldoController::class, 'descargar'])->name('respaldo.descargar');
        Route::get('reporte-pdf', [ReportePdfController::class, 'index'])->name('reporte.index');
        Route::get('reporte-pdf/clientes', [ReportePdfController::class, 'clientes'])->name('reporte.clientes');
    });

    // Solo administradores
    Route::middleware('admin:admin')->group(function () {
        Route::resource('usuarios', EmpleadoController::class)->except(['create', 'show'])->parameters(['usuarios' => 'usuario']);
        Route::get('bitacora', [BitacoraController::class, 'index'])->name('bitacora.index');
        Route::get('restaurar-bd', [RespaldoController::class, 'restaurarForm'])->name('restaurar.index');
        Route::post('restaurar-bd', [RespaldoController::class, 'restaurar'])->name('restaurar.store');
    });
});
