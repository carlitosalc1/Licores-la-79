<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Reporte extends Model
{
    protected $fillable = [
        'user_id',
        'tipo',
        'fecha_inicio',
        'fecha_fin',
        'filtros',
        'descripcion',
        'estado',
    ];

    protected $casts = [
        'filtros' => 'array', // Convierte el campo JSON en un array automáticamente
        'fecha_inicio' => 'date',
        'fecha_fin' => 'date',
    ];

    // Relación con el usuario que generó el reporte
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Método para obtener los datos del reporte según el tipo
    public function getDatos()
    {
        switch ($this->tipo) {
            case 'ventas':
                return Venta::whereBetween('fecha', [$this->fecha_inicio, $this->fecha_fin])
                    ->when($this->filtros['cliente_id'] ?? null, function ($query, $clienteId) {
                        $query->where('cliente_id', $clienteId);
                    })
                    ->with('cliente')
                    ->get();
            case 'compras':
                return Compra::whereBetween('fecha', [$this->fecha_inicio, $this->fecha_fin])
                    ->when($this->filtros['proveedor_id'] ?? null, function ($query, $proveedorId) {
                        $query->where('proveedor_id', $proveedorId);
                    })
                    ->with('proveedor')
                    ->get();
            case 'pedidos':
                return Pedido::whereBetween('fecha', [$this->fecha_inicio, $this->fecha_fin])
                    ->when($this->filtros['mesa_id'] ?? null, function ($query, $mesaId) {
                        $query->where('mesa_id', $mesaId);
                    })
                    ->with('mesa', 'cliente')
                    ->get();
            case 'inventario':
                return Inventario::whereBetween('fecha', [$this->fecha_inicio, $this->fecha_fin])
                    ->when($this->filtros['producto_id'] ?? null, function ($query, $productoId) {
                        $query->where('producto_id', $productoId);
                    })
                    ->with('producto')
                    ->get();
            default:
                return [];
        }
    }
}
