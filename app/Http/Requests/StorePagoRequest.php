<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StorePagoRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        // Establece en true si quieres permitir que cualquiera haga esta petición,
        // o implementa tu lógica de autorización aquí (ej. verificar si el usuario está autenticado/es administrador)
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'venta_id' => 'nullable|exists:ventas,id',
            'compra_id' => 'nullable|exists:compras,id',
            'monto' => 'required|numeric|min:0.01',
            'metodo_pago' => 'required|string|max:50',
            'fecha_pago' => 'required|date', // Laravel validará el formato de datetime-local
            'referencia_pago' => 'nullable|string|max:255',
        ];
    }

    /**
     * Configure the validator instance.
     * Opcional: Añade validación condicional si es necesario
     */
    public function withValidator($validator)
    {
        $validator->sometimes(['venta_id', 'compra_id'], 'required_without_all:venta_id,compra_id', function ($input) {
            // Requiere al menos uno de los dos si ambos son nulos
            return is_null($input->venta_id) && is_null($input->compra_id);
        });
    }

    /**
     * Get the error messages for the defined validation rules.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'monto.required' => 'El monto del pago es obligatorio.',
            'monto.numeric' => 'El monto debe ser un valor numérico.',
            'monto.min' => 'El monto debe ser al menos :min.',
            'metodo_pago.required' => 'El método de pago es obligatorio.',
            'fecha_pago.required' => 'La fecha del pago es obligatoria.',
            'fecha_pago.date' => 'La fecha de pago debe ser una fecha válida.',
            'venta_id.exists' => 'La venta seleccionada no es válida.',
            'compra_id.exists' => 'La compra seleccionada no es válida.',
            'required_without_all' => 'Debe asociar el pago a una Venta o a una Compra.',
        ];
    }
}
