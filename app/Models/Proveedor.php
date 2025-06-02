<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use HasFactory;

class Proveedor extends Model
{
    // Propiedad que define los atributos que se pueden asignar de forma masiva
    protected $fillable = [
        'razon_social',
        'nit',
        'direccion',
        'telefono',
        'correo',
    ];
    public function compras()
    {
        return $this->hasMany(Compra::class);
    }

}
