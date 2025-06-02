<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Pago extends Model
{
    protected $fillable = [
        'venta_id',
        'compra_id',
        'monto',
        'metodo_pago',
        'fecha_pago',
        'referencia_pago',
    ];
    protected $casts = [
        'monto' => 'float',
    ];

    public function venta()
    {
        return $this->belongsTo(Venta::class);
    }

    public function compra()
    {
        return $this->belongsTo(Compra::class);
    }
}
