<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Cliente extends Model
{
    // Propiedad que define los atributos que se pueden asignar de forma masiva
    protected $fillable = [
        'tipo_identificacion',
        'numero_identificacion',
        'nombre',
        'apellido',
        'direccion',
        'telefono',
        'correo',
    ];

    public function ventas()
    {
        return $this->hasMany(Venta::class);
    }

}
