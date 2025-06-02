<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Compra extends Model
{
    protected $fillable = [
        'proveedor_id',
        'user_id',
        'fecha',
        'total',
        'estado'
    ];
    protected $casts = [
    'total' => 'float',
    ];

    public function proveedor()
    {
        return $this->belongsTo(Proveedor::class);
    }

    public function pagos()
    {
        return $this->hasMany(Pago::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function detalleCompras()
    {
        return $this->hasMany(DetalleCompra::class);
    }
}
