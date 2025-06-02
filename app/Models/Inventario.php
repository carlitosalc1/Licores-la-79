<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Inventario extends Model
{
    protected $fillable = [
        'producto_id',
        'cantidad_inicial',
        'cantidad_entrada',
        'cantidad_salida',
        'cantidad_actual',
        'fecha_actualizacion',
        'tipo_movimiento',
    ];

    public function producto()
    {
        return $this->belongsTo(Producto::class);
    }
}
