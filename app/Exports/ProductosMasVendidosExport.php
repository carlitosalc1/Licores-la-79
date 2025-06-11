<?php

namespace App\Exports;

use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class ProductosMasVendidosExport implements FromCollection, WithHeadings, WithMapping
{
    protected $filtros;

    public function __construct($filtros = [])
    {
        $this->filtros = $filtros;
    }

    /**
    * @return \Illuminate\Support\Collection
    */
    public function collection()
    {
        $query = DB::table('detalle_ventas')
                    ->select('productos.nombre', 'productos.codigo', DB::raw('SUM(detalle_ventas.cantidad) as total_vendido'))
                    ->join('productos', 'detalle_ventas.producto_id', '=', 'productos.id')
                    ->join('ventas', 'detalle_ventas.venta_id', '=', 'ventas.id');

        if (isset($this->filtros['fecha_inicio']) && $this->filtros['fecha_inicio']) {
            $query->where('ventas.fecha_venta', '>=', $this->filtros['fecha_inicio'] . ' 00:00:00');
        }
        if (isset($this->filtros['fecha_fin']) && $this->filtros['fecha_fin']) {
            $query->where('ventas.fecha_venta', '<=', $this->filtros['fecha_fin'] . ' 23:59:59');
        }

        $productos = $query->groupBy('productos.id', 'productos.nombre', 'productos.codigo')
                           ->orderByDesc('total_vendido')
                           ->limit($this->filtros['limite'] ?? 10)
                           ->get();
        return $productos;
    }

    /**
     * @return array
     */
    public function headings(): array
    {
        return [
            'Nombre Producto',
            'Código',
            'Total Vendido',
        ];
    }

    /**
     * @param mixed $producto
     * @return array
     */
    public function map($producto): array
    {
        return [
            $producto->nombre,
            $producto->codigo,
            $producto->total_vendido,
        ];
    }
}
