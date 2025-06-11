<?php

namespace App\Exports;

use App\Models\Producto;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class InventarioExport implements FromCollection, WithHeadings, WithMapping
{
    protected $stockBajo;
    protected $nombre;

    public function __construct($stockBajo = null, $nombre = null)
    {
        $this->stockBajo = $stockBajo;
        $this->nombre = $nombre;
    }

    /**
    * @return \Illuminate\Support\Collection
    */
    public function collection()
    {
        $query = Producto::with('categoriaProducto');

        if ($this->stockBajo) {
            $query->whereColumn('stock', '<=', 'stock_minimo');
        }
        if ($this->nombre) {
            $query->where('nombre', 'like', '%' . $this->nombre . '%');
        }

        return $query->orderBy('nombre')->get();
    }

    /**
     * @return array
     */
    public function headings(): array
    {
        return [
            'ID Producto',
            'Nombre',
            'Código',
            'Categoría',
            'Descripción',
            'Precio Compra',
            'Precio Venta',
            'Stock Actual',
            'Stock Mínimo',
        ];
    }

    /**
     * @param mixed $producto
     * @return array
     */
    public function map($producto): array
    {
        return [
            $producto->id,
            $producto->nombre,
            $producto->codigo,
            $producto->categoriaProducto->nombre ?? 'N/A',
            $producto->descripcion,
            $producto->precio_compra,
            $producto->precio_venta,
            $producto->stock,
            $producto->stock_minimo,
        ];
    }
}
