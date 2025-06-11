<?php

namespace App\Http\Controllers;

use DateTime;
use App\Models\Producto;
use App\Models\Venta;
use App\Models\Compra;
use App\Models\Reporte; // Asegúrate de que esté importado
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Inertia\Inertia;


class DashboardController extends Controller
{
    public function index()
    {
        // Productos con stock bajo
        $stockBajo = Producto::where('stock', '<', DB::raw('stock_minimo'))
            ->count();

        // Ventas de los últimos 7 días
        $fechaInicioVentas = Carbon::today()->subDays(6)->startOfDay();
        $ventasRecientes = Venta::where('fecha_venta', '>=', $fechaInicioVentas)
            ->select('id', 'cliente_id', 'total', 'fecha_venta')
            ->with('cliente')
            ->orderBy('fecha_venta', 'desc')
            ->take(5)
            ->get()
            ->map(function ($venta) {
                return [
                    'id' => $venta->id,
                    'cliente_nombre' => $venta->cliente ? $venta->cliente->nombre . ' ' . $venta->cliente->apellido : 'N/A', // Asumiendo que cliente tiene nombre y apellido
                    'total' => (float) $venta->total,
                    'fecha_venta' => (new DateTime($venta->fecha_venta))->format('Y-m-d'),
                ];
            });

        $totalVentas = Venta::where('fecha_venta', '>=', $fechaInicioVentas)
            ->sum('total');

        // Ventas diarias para el gráfico
        $ventasDiarias = Venta::where('fecha_venta', '>=', $fechaInicioVentas)
            ->select(DB::raw('DATE(fecha_venta) as fecha'), DB::raw('SUM(total) as total'))
            ->groupBy('fecha')
            ->orderBy('fecha')
            ->get()
            ->mapWithKeys(function ($venta) {
                return [$venta->fecha => (float) $venta->total];
            });

        // Compras de los últimos 7 días
        $fechaInicioCompras = Carbon::today()->subDays(6)->startOfDay();
        $comprasRecientes = Compra::where('fecha_compra', '>=', $fechaInicioCompras)
            ->select('id', 'proveedor_id', 'total', 'fecha_compra')
            ->with('proveedor')
            ->orderBy('fecha_compra', 'desc')
            ->take(5)
            ->get()
            ->map(function ($compra) {
                return [
                    'id' => $compra->id,
                    'proveedor_razon_social' => $compra->proveedor ? $compra->proveedor->nombre : 'N/A', // Asumiendo que proveedor tiene 'nombre'
                    'total' => (float) $compra->total,
                    'fecha_compra' => (new DateTime($compra->fecha_compra))->format('Y-m-d'),
                ];
            });

        $totalCompras = Compra::where('fecha_compra', '>=', $fechaInicioCompras)
            ->sum('total');

        // Reportes del último mes (LA SECCIÓN CORREGIDA)
        $fechaInicioReportes = Carbon::today()->subMonth()->startOfDay();
        $reportesPorTipo = Reporte::where('created_at', '>=', $fechaInicioReportes)
            ->select('tipo_reporte', DB::raw('COUNT(*) as total'))
            ->groupBy('tipo_reporte')
            ->get()
            ->mapWithKeys(function ($reporte) {
                return [$reporte->tipo_reporte => (int) $reporte->total];
            });

        $totalReportes = $reportesPorTipo->sum();

        return Inertia::render('Dashboard', [
            'metrics' => [
                'stock_bajo' => $stockBajo,
                'ventas_recientes' => $ventasRecientes,
                'total_ventas' => (float) $totalVentas,
                'ventas_diarias' => $ventasDiarias,
                'compras_recientes' => $comprasRecientes,
                'total_compras' => (float) $totalCompras,
                'reportes_por_tipo' => $reportesPorTipo,
                'total_reportes' => $totalReportes,
            ],
        ]);
    }
}
