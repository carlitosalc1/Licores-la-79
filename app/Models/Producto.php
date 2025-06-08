<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Producto extends Model
{
    protected $fillable = [
        'nombre',
        'descripcion',
        'precio_compra',
        'precio_venta',
        'unidad_medida',
        'stock_minimo',
        'stock',
        'categoria_producto_id'
    ];
     protected $casts = [
    'precio_compra' => 'float',
    'precio_venta' => 'float',
    ];

    public function categoriaProducto()
    {
       return $this->belongsTo(CategoriaProducto::class);
    }

    public function detalleVentas()
    {
        return $this->hasMany(DetalleVenta::class);
    }

    public function detalleCompras()
    {
        return $this->hasMany(DetalleCompra::class);
    }

    public function inventario()
    {
        return $this->hasMany(Inventario::class);
    }

    public function getStockActualAttribute()
    {
        return Inventario::getStockActual($this->id);
    }
}
