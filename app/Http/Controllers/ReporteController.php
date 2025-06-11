<?php

namespace App\Http\Controllers;

use App\Models\Venta;
use App\Models\Producto;
use App\Models\Compra;
use App\Models\Proveedor;
use App\Models\Reporte; // Importar el modelo Reporte para registrar
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use Barryvdh\DomPDF\Facade\Pdf;

class ReporteController extends Controller
{
    /**
     * Muestra el reporte de Ventas por Fecha.
     */
    public function ventasPorFecha(Request $request)
    {
        $filters = $request->only(['fecha_inicio', 'fecha_fin']);

        $query = Venta::query()
            ->with(['cliente', 'detalleVentas.producto']);

        // ***** ESTAS SON LAS LÍNEAS CRÍTICAS QUE DEBEN CAMBIAR *****
        if (isset($filters['fecha_inicio']) && $filters['fecha_inicio']) {
            $query->whereDate('fecha_venta', '>=', $filters['fecha_inicio']);
        }

        if (isset($filters['fecha_fin']) && $filters['fecha_fin']) {
            $query->whereDate('fecha_venta', '<=', $filters['fecha_fin']);
        }
        // **********************************************************

        $ventas = $query->orderBy('fecha_venta', 'desc')->paginate(10);

        $totalVentas = Venta::query();
        if (isset($filters['fecha_inicio']) && $filters['fecha_inicio']) {
            $totalVentas->whereDate('fecha_venta', '>=', $filters['fecha_inicio']);
        }
        if (isset($filters['fecha_fin']) && $filters['fecha_fin']) {
            $totalVentas->whereDate('fecha_venta', '<=', $filters['fecha_fin']);
        }
        $totalVentas = $totalVentas->sum('total');

        // Intenta registrar el reporte, encapsulado en un try-catch para evitar el error de columna si aún no existe
        try {
            if (auth()->check()) {
                Reporte::create([
                    'tipo_reporte' => 'Ventas por Fecha',
                    'parametros_usados' => json_encode($filters),
                    'user_id' => auth()->id(),
                ]);
            }
        } catch (\Illuminate\Database\QueryException $e) {
            // Si la columna no existe, loguea el error y no detengas la aplicación
            Log::error("Error al registrar reporte de Ventas por Fecha (columna tipo_reporte): " . $e->getMessage());
        } catch (\Exception $e) {
            // Otros errores inesperados
            Log::error("Error inesperado al registrar reporte: " . $e->getMessage());
        }


        return Inertia::render('Reportes/VentasPorFecha', [
            'ventas' => $ventas,
            'filtros' => $filters,
            'totalVentas' => $totalVentas,
        ]);
    }


    /**
     * Exporta el reporte de Ventas por Fecha a Excel.
     */
    public function exportarVentasExcel(Request $request)
    {
        $filters = $request->only(['fecha_inicio', 'fecha_fin']);

    $query = \App\Models\Venta::query() // Asegúrate de usar el namespace completo o 'use App\Models\Venta;'
        ->with(['cliente', 'detalleVentas.producto']);

    // **Asegúrate de que estas líneas estén EXACTAMENTE así en tu archivo:**
    if (isset($filters['fecha_inicio']) && $filters['fecha_inicio']) { // Esta es la línea 32 que te da el error
        $query->whereDate('fecha_venta', '>=', $filters['fecha_inicio']);
    }

    if (isset($filters['fecha_fin']) && $filters['fecha_fin']) {
        $query->whereDate('fecha_venta', '<=', $filters['fecha_fin']);
    }

    // Recuperar las ventas paginadas
    $ventas = $query->orderBy('fecha_venta', 'desc')->paginate(10);

    // Calcular el total de ventas filtradas
    $totalVentas = \App\Models\Venta::query();
    if (isset($filters['fecha_inicio']) && $filters['fecha_inicio']) {
        $totalVentas->whereDate('fecha_venta', '>=', $filters['fecha_inicio']);
    }
    if (isset($filters['fecha_fin']) && $filters['fecha_fin']) {
        $totalVentas->whereDate('fecha_venta', '<=', $filters['fecha_fin']);
    }
    $totalVentas = $totalVentas->sum('total'); // Suma la columna 'total' de la tabla de ventas

    // Registra el reporte (si es necesario y tienes la columna 'tipo_reporte' en la BD)
    try {
        if (auth()->check()) {
            \App\Models\Reporte::create([
                'tipo_reporte' => 'Ventas por Fecha',
                'parametros_usados' => json_encode($filters),
                'user_id' => auth()->id(),
            ]);
        }
    } catch (\Exception $e) {
        // Manejar el error de registro de reporte si la columna no existe aún
        // Opcional: Log::error("Error al registrar reporte: " . $e->getMessage());
    }

    return Inertia::render('Reportes/VentasPorFecha', [ // Verifica que 'Reportes' sea plural aquí
        'ventas' => $ventas,
        'filtros' => $filters,
        'totalVentas' => $totalVentas,
    ]);
    }

    /**
     * Exporta el reporte de Ventas por Fecha a PDF.
     */
    public function exportarVentasPdf(Request $request)
    {
        $filters = $request->only(['fecha_inicio', 'fecha_fin']);

        $query = Venta::query()->with(['cliente', 'detalleVentas.producto']);

        if (isset($filters['fecha_inicio']) && $filters['fecha_inicio']) {
            $query->whereDate('fecha_venta', '>=', $filters['fecha_inicio']);
        }

        if (isset($filters['fecha_fin']) && $filters['fecha_fin']) {
            $query->whereDate('fecha_venta', '<=', $filters['fecha_fin']);
        }

        $ventas = $query->orderBy('fecha_venta', 'asc')->get();
        $totalVentas = $query->sum('total');

        $data = [
            'ventas' => $ventas,
            'filtros' => $filters,
            'totalVentas' => (float) $totalVentas,
            'fechaGeneracion' => Carbon::now()->format('d/m/Y H:i:s'),
        ];

        $pdf = Pdf::loadView('pdf.reporte_ventas', $data);
        $fileName = 'reporte_ventas_por_fecha_' . Carbon::now()->format('Ymd_His') . '.pdf';

        // Registrar el reporte exportado
        Reporte::create([
            'tipo_reporte' => 'ventas_por_fecha_pdf',
            'parametros_usados' => json_encode($filters),
            'user_id' => auth()->id(),
        ]);

        return $pdf->download($fileName);
    }

    /**
     * Muestra el reporte de Inventario Actual.
     */
    public function inventarioActual(Request $request)
    {
        $filters = $request->only(['stock_bajo', 'nombre']);

        $query = Producto::query()->with('categoriaProducto');

        if (isset($filters['stock_bajo']) && $filters['stock_bajo']) { // Corrección aquí
            $query->whereColumn('stock', '<', 'stock_minimo');
        }

        if (isset($filters['nombre']) && $filters['nombre']) { // Corrección aquí
            $query->where('nombre', 'like', '%' . $filters['nombre'] . '%');
        }

        $productos = $query->orderBy('nombre', 'asc')->paginate(10)->withQueryString();

        // Registrar el reporte generado
        Reporte::create([
            'tipo_reporte' => 'inventario_actual',
            'parametros_usados' => json_encode($filters),
            'user_id' => auth()->id(),
        ]);

        return Inertia::render('Reportes/InventarioActual', [
            'productos' => $productos,
            'filtros' => $filters,
        ]);
    }

    /**
     * Exporta el reporte de Inventario Actual a Excel.
     */
    public function exportarInventarioExcel(Request $request)
    {
        $filters = $request->only(['stock_bajo', 'nombre']);

        $query = Producto::query()->with('categoriaProducto');

        if (isset($filters['stock_bajo']) && $filters['stock_bajo']) {
            $query->whereColumn('stock', '<', 'stock_minimo');
        }

        if (isset($filters['nombre']) && $filters['nombre']) {
            $query->where('nombre', 'like', '%' . $filters['nombre'] . '%');
        }

        $productos = $query->orderBy('nombre', 'asc')->get();

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Inventario Actual');

        // Headers
        $headers = ['ID', 'Nombre', 'Código', 'Categoría', 'Stock', 'Stock Mínimo', 'Precio Venta'];
        $sheet->fromArray($headers, NULL, 'A1');

        // Data
        $row = 2;
        foreach ($productos as $producto) {
            $sheet->setCellValue('A' . $row, $producto->id);
            $sheet->setCellValue('B' . $row, $producto->nombre);
            $sheet->setCellValue('C' . $row, $producto->codigo);
            $sheet->setCellValue('D' . $row, $producto->categoriaProducto ? $producto->categoriaProducto->nombre : 'N/A');
            $sheet->setCellValue('E' . $row, $producto->stock);
            $sheet->setCellValue('F' . $row, $producto->stock_minimo);
            $sheet->setCellValue('G' . $row, $producto->precio_venta);
            $row++;
        }

        // Estilos
        $sheet->getStyle('A1:G1')->applyFromArray([
            'font' => ['bold' => true],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
            'borders' => ['bottom' => ['borderStyle' => Border::BORDER_THIN]],
        ]);
        foreach (range('A', 'G') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        $writer = new Xlsx($spreadsheet);
        $fileName = 'reporte_inventario_actual_' . Carbon::now()->format('Ymd_His') . '.xlsx';
        $tempFile = tempnam(sys_get_temp_dir(), $fileName);
        $writer->save($tempFile);

        // Registrar el reporte exportado
        Reporte::create([
            'tipo_reporte' => 'inventario_actual_excel',
            'parametros_usados' => json_encode($filters),
            'user_id' => auth()->id(),
        ]);

        return response()->download($tempFile, $fileName)->deleteFileAfterSend(true);
    }

    /**
     * Exporta el reporte de Inventario Actual a PDF.
     */
    public function exportarInventarioPdf(Request $request)
    {
        $filters = $request->only(['stock_bajo', 'nombre']);

        $query = Producto::query()->with('categoriaProducto');

        if (isset($filters['stock_bajo']) && $filters['stock_bajo']) {
            $query->whereColumn('stock', '<', 'stock_minimo');
        }

        if (isset($filters['nombre']) && $filters['nombre']) {
            $query->where('nombre', 'like', '%' . $filters['nombre'] . '%');
        }

        $productos = $query->orderBy('nombre', 'asc')->get();

        $data = [
            'productos' => $productos,
            'filtros' => $filters,
            'fechaGeneracion' => Carbon::now()->format('d/m/Y H:i:s'),
        ];

        $pdf = Pdf::loadView('pdf.reporte_inventario', $data);
        $fileName = 'reporte_inventario_actual_' . Carbon::now()->format('Ymd_His') . '.pdf';

        // Registrar el reporte exportado
        Reporte::create([
            'tipo_reporte' => 'inventario_actual_pdf',
            'parametros_usados' => json_encode($filters),
            'user_id' => auth()->id(),
        ]);

        return $pdf->download($fileName);
    }

    /**
     * Muestra el reporte de Compras por Proveedor.
     */
    public function comprasPorProveedor(Request $request)
    {
        $filters = $request->only(['proveedor_id', 'fecha_inicio', 'fecha_fin']);

        $query = Compra::query()->with(['proveedor', 'detalleCompras.producto']);

        if (isset($filters['proveedor_id']) && $filters['proveedor_id']) { // Corrección aquí
            $query->where('proveedor_id', $filters['proveedor_id']);
        }
        if (isset($filters['fecha_inicio']) && $filters['fecha_inicio']) { // Corrección aquí
            $query->whereDate('fecha_compra', '>=', $filters['fecha_inicio']);
        }
        if (isset($filters['fecha_fin']) && $filters['fecha_fin']) { // Corrección aquí
            $query->whereDate('fecha_compra', '<=', $filters['fecha_fin']);
        }

        $compras = $query->orderBy('fecha_compra', 'desc')->paginate(10)->withQueryString();
        $totalCompras = $query->sum('total');

        $proveedores = Proveedor::select('id', 'nombre')->orderBy('nombre')->get();

        // Registrar el reporte generado
        Reporte::create([
            'tipo_reporte' => 'compras_por_proveedor',
            'parametros_usados' => json_encode($filters),
            'user_id' => auth()->id(),
        ]);

        return Inertia::render('Reportes/ComprasPorProveedor', [
            'compras' => $compras,
            'filtros' => $filters,
            'totalCompras' => (float) $totalCompras,
            'proveedores' => $proveedores,
        ]);
    }

    /**
     * Exporta el reporte de Compras por Proveedor a Excel.
     */
    public function exportarComprasExcel(Request $request)
    {
        $filters = $request->only(['proveedor_id', 'fecha_inicio', 'fecha_fin']);

        $query = Compra::query()->with(['proveedor', 'detalleCompras.producto']);

        if (isset($filters['proveedor_id']) && $filters['proveedor_id']) {
            $query->where('proveedor_id', $filters['proveedor_id']);
        }
        if (isset($filters['fecha_inicio']) && $filters['fecha_inicio']) {
            $query->whereDate('fecha_compra', '>=', $filters['fecha_inicio']);
        }
        if (isset($filters['fecha_fin']) && $filters['fecha_fin']) {
            $query->whereDate('fecha_compra', '<=', $filters['fecha_fin']);
        }

        $compras = $query->orderBy('fecha_compra', 'asc')->get();

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Compras por Proveedor');

        // Headers
        $headers = ['ID Compra', 'Proveedor', 'Fecha', 'Total', 'Estado', 'Productos'];
        $sheet->fromArray($headers, NULL, 'A1');

        // Data
        $row = 2;
        foreach ($compras as $compra) {
            $productosDetalle = $compra->detalleCompras->map(function ($detalle) {
                return $detalle->producto->nombre . ' (' . $detalle->cantidad . ' x $' . number_format($detalle->precio_unitario, 2) . ')';
            })->implode("\n");

            $sheet->setCellValue('A' . $row, $compra->id);
            $sheet->setCellValue('B' . $row, $compra->proveedor ? $compra->proveedor->nombre : 'N/A');
            $sheet->setCellValue('C' . $row, Carbon::parse($compra->fecha_compra)->format('Y-m-d H:i:s'));
            $sheet->setCellValue('D' . $row, $compra->total);
            $sheet->setCellValue('E' . $row, $compra->estado);
            $sheet->setCellValue('F' . $row, $productosDetalle);
            $row++;
        }

        // Estilos
        $sheet->getStyle('A1:F1')->applyFromArray([
            'font' => ['bold' => true],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
            'borders' => ['bottom' => ['borderStyle' => Border::BORDER_THIN]],
        ]);
        foreach (range('A', 'F') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }
        $sheet->getStyle('F2:F' . ($row - 1))->getAlignment()->setWrapText(true);

        $writer = new Xlsx($spreadsheet);
        $fileName = 'reporte_compras_por_proveedor_' . Carbon::now()->format('Ymd_His') . '.xlsx';
        $tempFile = tempnam(sys_get_temp_dir(), $fileName);
        $writer->save($tempFile);

        // Registrar el reporte exportado
        Reporte::create([
            'tipo_reporte' => 'compras_por_proveedor_excel',
            'parametros_usados' => json_encode($filters),
            'user_id' => auth()->id(),
        ]);

        return response()->download($tempFile, $fileName)->deleteFileAfterSend(true);
    }

    /**
     * Exporta el reporte de Compras por Proveedor a PDF.
     */
    public function exportarComprasPdf(Request $request)
    {
        $filters = $request->only(['proveedor_id', 'fecha_inicio', 'fecha_fin']);

        $query = Compra::query()->with(['proveedor', 'detalleCompras.producto']);

        if (isset($filters['proveedor_id']) && $filters['proveedor_id']) {
            $query->where('proveedor_id', $filters['proveedor_id']);
        }
        if (isset($filters['fecha_inicio']) && $filters['fecha_inicio']) {
            $query->whereDate('fecha_compra', '>=', $filters['fecha_inicio']);
        }
        if (isset($filters['fecha_fin']) && $filters['fecha_fin']) {
            $query->whereDate('fecha_compra', '<=', $filters['fecha_fin']);
        }

        $compras = $query->orderBy('fecha_compra', 'asc')->get();
        $totalCompras = $query->sum('total');

        $data = [
            'compras' => $compras,
            'filtros' => $filters,
            'totalCompras' => (float) $totalCompras,
            'fechaGeneracion' => Carbon::now()->format('d/m/Y H:i:s'),
        ];

        $pdf = Pdf::loadView('pdf.reporte_compras', $data);
        $fileName = 'reporte_compras_por_proveedor_' . Carbon::now()->format('Ymd_His') . '.pdf';

        // Registrar el reporte exportado
        Reporte::create([
            'tipo_reporte' => 'compras_por_proveedor_pdf',
            'parametros_usados' => json_encode($filters),
            'user_id' => auth()->id(),
        ]);

        return $pdf->download($fileName);
    }

    /**
     * Muestra el reporte de Productos Más Vendidos.
     */
    public function productosMasVendidos(Request $request)
    {
        $filters = $request->only(['fecha_inicio', 'fecha_fin', 'limite']);
        $limite = $filters['limite'] ?? 10;

        $query = DB::table('detalle_ventas')
            ->join('productos', 'detalle_ventas.producto_id', '=', 'productos.id')
            ->join('ventas', 'detalle_ventas.venta_id', '=', 'ventas.id')
            ->select(
                'productos.nombre',
                'productos.codigo',
                DB::raw('SUM(detalle_ventas.cantidad) as total_vendido')
            )
            ->groupBy('productos.id', 'productos.nombre', 'productos.codigo')
            ->orderBy('total_vendido', 'desc')
            ->limit($limite);

        if (isset($filters['fecha_inicio']) && $filters['fecha_inicio']) { // Corrección aquí
            $query->whereDate('ventas.fecha_venta', '>=', $filters['fecha_inicio']);
        }

        if (isset($filters['fecha_fin']) && $filters['fecha_fin']) { // Corrección aquí
            $query->whereDate('ventas.fecha_venta', '<=', $filters['fecha_fin']);
        }

        $productosMasVendidos = $query->get();

        // Registrar el reporte generado
        Reporte::create([
            'tipo_reporte' => 'productos_mas_vendidos',
            'parametros_usados' => json_encode($filters),
            'user_id' => auth()->id(),
        ]);

        return Inertia::render('Reportes/ProductosMasVendidos', [
            'productos' => $productosMasVendidos,
            'filtros' => $filters,
        ]);
    }

    /**
     * Exporta el reporte de Productos Más Vendidos a Excel.
     */
    public function exportarProductosVendidosExcel(Request $request)
    {
        $filters = $request->only(['fecha_inicio', 'fecha_fin', 'limite']);
        $limite = $filters['limite'] ?? 10;

        $query = DB::table('detalle_ventas')
            ->join('productos', 'detalle_ventas.producto_id', '=', 'productos.id')
            ->join('ventas', 'detalle_ventas.venta_id', '=', 'ventas.id')
            ->select(
                'productos.nombre',
                'productos.codigo',
                DB::raw('SUM(detalle_ventas.cantidad) as total_vendido')
            )
            ->groupBy('productos.id', 'productos.nombre', 'productos.codigo')
            ->orderBy('total_vendido', 'desc')
            ->limit($limite);

        if (isset($filters['fecha_inicio']) && $filters['fecha_inicio']) {
            $query->whereDate('ventas.fecha_venta', '>=', $filters['fecha_inicio']);
        }

        if (isset($filters['fecha_fin']) && $filters['fecha_fin']) {
            $query->whereDate('ventas.fecha_venta', '<=', $filters['fecha_fin']);
        }

        $productosMasVendidos = $query->get();

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Productos Más Vendidos');

        // Headers
        $headers = ['Nombre Producto', 'Código', 'Cantidad Vendida'];
        $sheet->fromArray($headers, NULL, 'A1');

        // Data
        $row = 2;
        foreach ($productosMasVendidos as $producto) {
            $sheet->setCellValue('A' . $row, $producto->nombre);
            $sheet->setCellValue('B' . $row, $producto->codigo);
            $sheet->setCellValue('C' . $row, $producto->total_vendido);
            $row++;
        }

        // Estilos
        $sheet->getStyle('A1:C1')->applyFromArray([
            'font' => ['bold' => true],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
            'borders' => ['bottom' => ['borderStyle' => Border::BORDER_THIN]],
        ]);
        foreach (range('A', 'C') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        $writer = new Xlsx($spreadsheet);
        $fileName = 'reporte_productos_mas_vendidos_' . Carbon::now()->format('Ymd_His') . '.xlsx';
        $tempFile = tempnam(sys_get_temp_dir(), $fileName);
        $writer->save($tempFile);

        // Registrar el reporte exportado
        Reporte::create([
            'tipo_reporte' => 'productos_mas_vendidos_excel',
            'parametros_usados' => json_encode($filters),
            'user_id' => auth()->id(),
        ]);

        return response()->download($tempFile, $fileName)->deleteFileAfterSend(true);
    }

    /**
     * Exporta el reporte de Productos Más Vendidos a PDF.
     */
    public function exportarProductosVendidosPdf(Request $request)
    {
        $filters = $request->only(['fecha_inicio', 'fecha_fin', 'limite']);
        $limite = $filters['limite'] ?? 10;

        $query = DB::table('detalle_ventas')
            ->join('productos', 'detalle_ventas.producto_id', '=', 'productos.id')
            ->join('ventas', 'detalle_ventas.venta_id', '=', 'ventas.id')
            ->select(
                'productos.nombre',
                'productos.codigo',
                DB::raw('SUM(detalle_ventas.cantidad) as total_vendido')
            )
            ->groupBy('productos.id', 'productos.nombre', 'productos.codigo')
            ->orderBy('total_vendido', 'desc')
            ->limit($limite);

        if (isset($filters['fecha_inicio']) && $filters['fecha_inicio']) {
            $query->whereDate('ventas.fecha_venta', '>=', $filters['fecha_inicio']);
        }

        if (isset($filters['fecha_fin']) && $filters['fecha_fin']) {
            $query->whereDate('ventas.fecha_venta', '<=', $filters['fecha_fin']);
        }

        $productosMasVendidos = $query->get();

        $data = [
            'productos' => $productosMasVendidos,
            'filtros' => $filters,
            'fechaGeneracion' => Carbon::now()->format('d/m/Y H:i:s'),
        ];

        $pdf = Pdf::loadView('pdf.reporte_productos_vendidos', $data);
        $fileName = 'reporte_productos_mas_vendidos_' . Carbon::now()->format('Ymd_His') . '.pdf';

        // Registrar el reporte exportado
        Reporte::create([
            'tipo_reporte' => 'productos_mas_vendidos_pdf',
            'parametros_usados' => json_encode($filters),
            'user_id' => auth()->id(),
        ]);

        return $pdf->download($fileName);
    }
}
