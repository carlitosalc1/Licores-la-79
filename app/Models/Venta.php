<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\BelongsTo;


class Venta extends Model
{
    protected $fillable = [
        'cliente_id',
        'user_id',
        'fecha_venta',
        'total',
        'metodo_pago',
        'estado',
        'tipo_comprobante'
    ];
      protected $casts = [
        'total' => 'float',
    ];

    public function cliente()
    {
        return $this->belongsTo(Cliente::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function detalleVentas(): HasMany
    {
        return $this->hasMany(DetalleVenta::class);
    }

    public function pagos()
    {
        return $this->hasMany(Pago::class);
    }

        public function factura(): HasOne
    {
        return $this->hasOne(Factura::class);
    }

    public function estaPagada(): bool
    {
        return $this->estado === 'pagada';
    }

    public function estaPendienteDePago(): bool
    {
        return $this->estado === 'pendiente';
    }

    public function estaCancelada(): bool
    {
        return $this->estado === 'cancelada';
    }

    public function esFactura(): bool
    {
        return $this->tipo_comprobante === 'factura';
    }
}
