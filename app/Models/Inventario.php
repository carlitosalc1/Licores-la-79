<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Inventario extends Model
{
    protected $table = 'inventario';

    protected $fillable = [
        'producto_id',
        'compra_id',
        'venta_id',
        'cantidad_entrada',
        'cantidad_salida',
        'tipo_movimiento',
        'fecha_actualizacion',
    ];

    public function producto()
    {
        return $this->belongsTo(Producto::class);
    }

    public function compra()
    {
        return $this->belongsTo(Compra::class);
    }

    public function venta()
    {
        return $this->belongsTo(Venta::class);
    }

    public static function getStockActual($producto_id)
    {
        return self::where('producto_id', $producto_id)
            ->sum('cantidad_entrada') - self::where('producto_id', $producto_id)->sum('cantidad_salida');
    }
}
