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
use App\Http\Controllers\ReporteController; // Asegúrate de que esta importación esté presente

Route::get('/', function () {
    return Inertia::render('Welcome');
})->name('home');


Route::middleware(['auth', 'verified'])->group(function () {

    // Ruta del Dashboard (esta es la correcta y única)
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

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
    Route::get('/ventas/{venta}/factura', [VentaController::class, 'generarFactura'])->name('ventas.factura'); // Ya está correcto

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
    Route::resource('inventarios', InventarioController::class)->except(['show']);
    Route::get('/inventarios/stock/{productId}', [InventarioController::class, 'getStock'])->name('inventarios.stock');

    // Grupo de Rutas para Reportes (este bloque está correcto)
    Route::prefix('reportes')->name('reportes.')->group(function () {
        // Ruta principal para "Reportes" en el menú, que redirige a Ventas por Fecha
        Route::get('/', [ReporteController::class, 'ventasPorFecha'])->name('index'); // Ajusta a la ruta que quieras que se muestre por defecto

        // Rutas para los reportes específicos
        Route::get('/ventas', [ReporteController::class, 'ventasPorFecha'])->name('ventas');
        Route::get('/inventario', [ReporteController::class, 'inventarioActual'])->name('inventario');
        Route::get('/compras', [ReporteController::class, 'comprasPorProveedor'])->name('compras');
        Route::get('/productos-vendidos', [ReporteController::class, 'productosMasVendidos'])->name('productos-vendidos');

        // Rutas de exportación
        Route::get('/ventas/exportar-excel', [ReporteController::class, 'exportarVentasExcel'])->name('ventas.exportarExcel');
        Route::get('/ventas/exportar-pdf', [ReporteController::class, 'exportarVentasPdf'])->name('ventas.exportarPdf');

        Route::get('/inventario/exportar-excel', [ReporteController::class, 'exportarInventarioExcel'])->name('inventario.exportarExcel');
        Route::get('/inventario/exportar-pdf', [ReporteController::class, 'exportarInventarioPdf'])->name('inventario.exportarPdf');

        Route::get('/compras/exportar-excel', [ReporteController::class, 'exportarComprasExcel'])->name('compras.exportarExcel');
        Route::get('/compras/exportar-pdf', [ReporteController::class, 'exportarComprasPdf'])->name('compras.exportarPdf');

        Route::get('/productos-vendidos/exportar-excel', [ReporteController::class, 'exportarProductosVendidosExcel'])->name('productos-vendidos.exportarExcel');
        Route::get('/productos-vendidos/exportar-pdf', [ReporteController::class, 'exportarProductosVendidosPdf'])->name('productos-vendidos.exportarPdf');
        //usuario
        Route::resource('usuarios', UsuarioController::class);
    });

});

require __DIR__.'/settings.php';
require __DIR__.'/auth.php';
