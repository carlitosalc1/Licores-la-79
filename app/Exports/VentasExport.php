<?php

namespace App\Exports;

use App\Models\Venta;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class VentasExport implements FromCollection, WithHeadings, WithMapping
{
    protected $fechaInicio;
    protected $fechaFin;

    public function __construct($fechaInicio, $fechaFin)
    {
        $this->fechaInicio = $fechaInicio;
        $this->fechaFin = $fechaFin;
    }

    /**
    * @return \Illuminate\Support\Collection
    */
    public function collection()
    {
        $query = Venta::with(['cliente', 'detalleVentas.producto']);

        if ($this->fechaInicio) {
            $query->where('fecha_venta', '>=', $this->fechaInicio . ' 00:00:00');
        }

        if ($this->fechaFin) {
            $query->where('fecha_venta', '<=', $this->fechaFin . ' 23:59:59');
        }

        return $query->orderBy('fecha_venta', 'desc')->get();
    }

    /**
     * @return array
     */
    public function headings(): array
    {
        return [
            'ID Venta',
            'Cliente',
            'Cédula/RUC Cliente',
            'Fecha Venta',
            'Total Venta',
            'Estado',
            'Productos' // Puede ser una columna combinada de productos
        ];
    }

    /**
     * @param mixed $venta
     * @return array
     */
    public function map($venta): array
    {
        $productos = $venta->detalleVentas->map(function($detalle) {
            return $detalle->producto->nombre . ' (' . $detalle->cantidad . 'x' . $detalle->precio_unitario . ')';
        })->implode('; ');

        return [
            $venta->id,
            $venta->cliente ? $venta->cliente->nombre . ' ' . $venta->cliente->apellido : 'Consumidor Final',
            $venta->cliente ? $venta->cliente->cedula_ruc : 'N/A',
            $venta->fecha_venta->format('d/m/Y H:i'),
            $venta->total_venta,
            $venta->estado,
            $productos
        ];
    }
}
