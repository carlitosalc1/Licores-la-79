<?php

namespace App\Exports;

use App\Models\Compra;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class ComprasExport implements FromCollection, WithHeadings, WithMapping
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
        $query = Compra::with(['proveedor', 'detalleCompras.producto']);

        if (isset($this->filtros['proveedor_id']) && $this->filtros['proveedor_id']) {
            $query->where('proveedor_id', $this->filtros['proveedor_id']);
        }
        if (isset($this->filtros['fecha_inicio']) && $this->filtros['fecha_inicio']) {
            $query->where('fecha_compra', '>=', $this->filtros['fecha_inicio'] . ' 00:00:00');
        }
        if (isset($this->filtros['fecha_fin']) && $this->filtros['fecha_fin']) {
            $query->where('fecha_compra', '<=', $this->filtros['fecha_fin'] . ' 23:59:59');
        }

        return $query->orderBy('fecha_compra', 'desc')->get();
    }

    /**
     * @return array
     */
    public function headings(): array
    {
        return [
            'ID Compra',
            'Proveedor',
            'Fecha Compra',
            'Total Compra',
            'Estado',
            'Productos Comprados',
        ];
    }

    /**
     * @param mixed $compra
     * @return array
     */
    public function map($compra): array
    {
        $productos = $compra->detalleCompras->map(function($detalle) {
            return $detalle->producto->nombre . ' (' . $detalle->cantidad . 'x' . $detalle->precio_unitario . ')';
        })->implode('; ');

        return [
            $compra->id,
            $compra->proveedor->nombre ?? 'N/A',
            $compra->fecha_compra->format('d/m/Y H:i'),
            $compra->total_compra,
            $compra->estado,
            $productos
        ];
    }
}
