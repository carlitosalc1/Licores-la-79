<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Model;


class Factura extends Model
{
    protected $fillable = [
        'venta_id',
        'user_id',
        'numero_factura',
        'fecha_emision',
        'total',
        'metodo_pago',
        'estado'
    ];
    protected $casts = [
        'total' => 'float',
    ];

    protected static function boot()
    {
         parent::boot();

        static::creating(function ($factura) {
        // Evitar duplicado de facturas por venta
        if (Factura::where('venta_id', $factura->venta_id)->exists()) {
            throw new \Exception("Ya existe una factura para esta venta.");
        }

        // Generar número automático
        $lastFactura = Factura::orderBy('id', 'desc')->first();
        $lastId = $lastFactura ? $lastFactura->id : 0;
        $factura->numero_factura = 'FACT-' . str_pad($lastId + 1, 12, '0', STR_PAD_LEFT);
    });
}


    public function venta(): BelongsTo
    {
        return $this->belongsTo(Venta::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function pago()
    {
        return $this->hasOne(Pago::class);
    }

    public function detalleFacturas()
    {
        return $this->hasMany(DetalleFactura::class);
    }

     public function calcularTotalConImpuestos(): float
    {
        return $this->subtotal + $this->impuesto;
    }

    public function esPagada(): bool
    {
        return $this->estado === 'pagada';
    }
}
