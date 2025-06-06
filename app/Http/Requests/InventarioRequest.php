<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class InventarioRequest extends FormRequest
{
    public function rules()
    {
        return [
            'producto_id' => 'required|exists:productos,id',
            'compra_id' => 'nullable|exists:compras,id',
            'venta_id' => 'nullable|exists:ventas,id',
            'cantidad' => 'required|integer|min:0',
            'tipo_movimiento' => 'required|in:entrada,salida,ajuste',
        ];
    }

    public function messages()
    {
        return [
            'producto_id.required' => 'El producto es obligatorio.',
            'producto_id.exists' => 'El producto seleccionado no existe.',
            'compra_id.exists' => 'La compra seleccionada no existe.',
            'venta_id.exists' => 'La venta seleccionada no existe.',
            'cantidad.required' => 'La cantidad es obligatoria.',
            'cantidad.integer' => 'La cantidad debe ser un número entero.',
            'cantidad.min' => 'La cantidad no puede ser negativa.',
            'tipo_movimiento.required' => 'El tipo de movimiento es obligatorio.',
            'tipo_movimiento.in' => 'El tipo de movimiento debe ser entrada, salida o ajuste.',
        ];
    }
}
