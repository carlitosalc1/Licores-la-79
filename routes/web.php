<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ClienteController;
use App\Http\Controllers\ProveedorController;
use App\Http\Controllers\ProductoController;
use App\Http\Controllers\CategoriaProductoController;
use App\Http\Controllers\VentaController;
use App\Http\Controllers\DetalleVentaController;
use App\Http\Controllers\CompraController;
use App\Http\Controllers\DetalleCompraController;
use App\Http\Controllers\RolController;
use App\Http\Controllers\UsuarioController;
use App\Http\Controllers\FacturaController;
use App\Http\Controllers\DetalleFacturaController;
use App\Http\Controllers\PagoController;
use App\Http\Controllers\InventarioController;
use App\Http\Controllers\ReporteController;

Route::get('/', function () {
    return Inertia::render('Welcome');
})->name('home');

Route::get('dashboard', function () {
    return Inertia::render('Dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware(['auth', 'verified'])->group(function () {

    // Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard'); cunado ya tenga el dashboard controller

    // Clientes
    Route::resource('clientes', ClienteController::class);

    // Proveedores
    Route::resource('proveedors', ProveedorController::class);

    // Productos
    Route::resource('productos', ProductoController::class);
    // Categorías de productos
    Route::resource('categoria_productos', CategoriaProductoController::class);

    // Ventas
    Route::resource('ventas', VentaController::class);

    Route::get('/ventas/{venta}/factura', [VentaController::class, 'generarFactura'])->name('ventas.factura');

    // Detalle de Ventas
    Route::resource('detalle_ventas', DetalleVentaController::class);

    // Compras
    Route::resource('compras', CompraController::class);
    // Detalles de Compras
    Route::resource('detalle_compras', DetalleCompraController::class);

    // Roles
    Route::resource('rols', RolController::class);

    // Usuarios
    Route::resource('usuarios', UsuarioController::class);

    // Facturas - Con rutas adicionales si es necesario
    Route::resource('facturas', FacturaController::class);
    Route::post('/facturas/generar-desde-venta/{venta}', [FacturaController::class, 'generarDesdeventa'])
        ->name('facturas.generar-desde-venta');
    // Detalles de Facturas
    Route::resource('detalle_facturas', DetalleFacturaController::class);


    // Pagos
    Route::resource('pagos', PagoController::class);

    // Inventario
    Route::resource('inventarios', InventarioController::class);

    // Reportes
    Route::get('/reportes/ventas', [ReporteController::class, 'ventas'])->name('reportes.ventas');
    Route::get('/reportes/inventario', [ReporteController::class, 'inventario'])->name('reportes.inventario');
    Route::get('/reportes/compras', [ReporteController::class, 'compras'])->name('reportes.compras');
    Route::get('/reportes/facturas', [ReporteController::class, 'facturas'])->name('reportes.facturas');

});

require __DIR__.'/settings.php';
require __DIR__.'/auth.php';
