<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use App\Models\ShippingZone;

class ProcessCheckoutRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $shippingZones = ShippingZone::getActiveWithPrices();
        $allowedPaymentMethods = ['mercadopago', 'transferencia'];

        return [
            'name'          => 'required|string|max:255|min:3',
            'email'         => 'nullable|email|max:255',
            'street_name'   => 'required|string|max:255|min:3',
            'street_number' => 'required|integer|min:1|max:99999',
            'phone'         => 'required|regex:/^(\+?\d{1,3}[-\.\s]?)?(\d{3})?[-\.\s]?\d{3}[-\.\s]?\d{4}$/|min:10|max:20',
            'payment_method' => ['required', Rule::in($allowedPaymentMethods)],
            'shipping_zone' => ['nullable', 'string', Rule::in(array_keys($shippingZones))],
            'lat'           => 'nullable|numeric',
            'lng'           => 'nullable|numeric',
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'El nombre es obligatorio',
            'name.min' => 'El nombre debe tener al menos 3 caracteres',
            'name.max' => 'El nombre no puede exceder 255 caracteres',
            'email.email' => 'El email no es válido',
            'phone.required' => 'El teléfono es obligatorio',
            'phone.regex' => 'El formato del teléfono no es válido',
            'phone.min' => 'El teléfono debe tener al menos 10 dígitos',
            'street_name.min' => 'La calle debe tener al menos 3 caracteres',
            'street_number.max' => 'El número no puede ser mayor a 99999',
            'payment_method.in' => 'Método de pago no válido',
        ];
    }
}
