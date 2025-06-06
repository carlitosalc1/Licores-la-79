<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule; // No olvides importar Rule si lo usas

class UpdatePagoRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true; // Considera implementar lógica de autorización real aquí
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'venta_id' => ['nullable','integer','exists:ventas,id','required_without_all:compra_id'],
            'compra_id' => ['nullable','integer','exists:compras,id','required_without_all:venta_id'],
            'monto' => 'required|numeric|min:0', // Ajustado a min:0 como en StorePagoRequest
            'monto_recibido' => 'required|numeric|min:0', // <-- ¡NUEVO CAMPO!
            'cambio' => 'required|numeric', // 'cambio' sin min:0 aquí, validación condicional en withValidator
            'metodo_pago' => ['required', Rule::in(['efectivo', 'tarjeta_credito', 'tarjeta_debito'])],
            'fecha_pago' => 'required|date',
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
            return is_null($input->venta_id) && is_null($input->compra_id);
        });

        // Agrega una validación para asegurar que el monto_recibido no sea menor al monto
        // si el método de pago es efectivo y el monto es positivo
        $validator->after(function ($validator) {
            if ($this->metodo_pago === 'efectivo' && $this->monto > 0 && $this->monto_recibido < $this->monto) {
                $validator->errors()->add('monto_recibido', 'El monto recibido debe ser al menos igual al monto a pagar para pagos en efectivo.');
            }

            // Validación para asegurar que el cambio no sea negativo si es efectivo
            if ($this->metodo_pago === 'efectivo' && $this->cambio < 0) {
                 $validator->errors()->add('cambio', 'El monto recibido es insuficiente. El cambio no puede ser negativo.');
            }
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
            'monto.required' => 'El monto total a pagar es obligatorio.',
            'monto.numeric' => 'El monto total a pagar debe ser un valor numérico.',
            'monto.min' => 'El monto total a pagar debe ser al menos :min.',

            'monto_recibido.required' => 'El monto recibido es obligatorio.',
            'monto_recibido.numeric' => 'El monto recibido debe ser un valor numérico.',
            'monto_recibido.min' => 'El monto recibido debe ser al menos :min.',

            'cambio.required' => 'El cambio es obligatorio.',
            'cambio.numeric' => 'El cambio debe ser un valor numérico.',

            'metodo_pago.required' => 'El método de pago es obligatorio.',
            'fecha_pago.required' => 'La fecha del pago es obligatoria.',
            'fecha_pago.date' => 'La fecha de pago debe ser una fecha válida.',
            'venta_id.exists' => 'La venta seleccionada no es válida.',
            'compra_id.exists' => 'La compra seleccionada no es válida.',
            'venta_id.required_without_all' => 'Debe asociar el pago a una venta o a una compra.',
            'compra_id.required_without_all' => 'Debe asociar el pago a una compra o a una venta.',
            'required_without_all' => 'Debe asociar el pago a una Venta o a una Compra.',
            'referencia_pago.max' => 'La referencia de pago no debe exceder los :max caracteres.',
        ];
    }
}
