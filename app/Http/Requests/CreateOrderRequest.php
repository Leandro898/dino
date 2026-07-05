<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CreateOrderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255|min:3',
            'email' => 'nullable|email|max:255',
            'street_name' => 'required|string|max:255|min:3',
            'street_number' => 'required|integer|min:1|max:99999',
            'phone' => 'required|regex:/^(\+?\d{1,3}[-.\s]?)?\(?\d{3}\)?[-.\s]?\d{3}[-.\s]?\d{4}$/|min:10|max:20',
            'payment_method' => 'required|in:mercadopago,transferencia,efectivo',
            'shipping_zone' => 'nullable|string|max:100',
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'El nombre es obligatorio',
            'name.min' => 'El nombre debe tener al menos 3 caracteres',
            'name.max' => 'El nombre no puede exceder 255 caracteres',
            'email.email' => 'El email no es válido',
            'email.max' => 'El email no puede exceder 255 caracteres',
            'street_name.required' => 'La calle es obligatoria',
            'street_name.min' => 'La calle debe tener al menos 3 caracteres',
            'street_name.max' => 'La calle no puede exceder 255 caracteres',
            'street_number.required' => 'El número es obligatorio',
            'street_number.integer' => 'El número debe ser un entero',
            'street_number.min' => 'El número debe ser mayor a 0',
            'street_number.max' => 'El número no puede ser mayor a 99999',
            'phone.required' => 'El teléfono es obligatorio',
            'phone.regex' => 'El formato del teléfono no es válido',
            'phone.min' => 'El teléfono debe tener al menos 10 dígitos',
            'phone.max' => 'El teléfono no puede exceder 20 caracteres',
            'payment_method.required' => 'El método de pago es obligatorio',
            'payment_method.in' => 'Método de pago no válido',
            'shipping_zone.max' => 'La zona de envío no puede exceder 100 caracteres',
        ];
    }
}
