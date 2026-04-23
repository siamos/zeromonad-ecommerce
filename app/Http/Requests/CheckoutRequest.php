<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CheckoutRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'payment_method' => 'required|string',
            'billing_address' => 'required|array',
            'billing_address.name' => 'required|string|max:255',
            'billing_address.email' => 'required|email|max:255',
            'billing_address.phone' => 'required|string|max:50',
            'billing_address.line1' => 'required|string|max:255',
            'billing_address.city' => 'required|string|max:100',
            'billing_address.zip' => 'required|string|max:20',
            'shipping_address' => 'nullable|array',
            'use_points' => 'nullable|integer|min:0',
        ];
    }
}
