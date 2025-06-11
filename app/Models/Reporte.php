<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Reporte extends Model
{
    protected $fillable = [
        'tipo_reporte',
        'parametros_usados',
        'user_id',
    ];


    public function user()
    {
        return $this->belongsTo(User::class);
    }

}
