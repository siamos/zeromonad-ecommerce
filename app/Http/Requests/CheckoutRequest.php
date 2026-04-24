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
            'shipping_address.name' => 'nullable|string|max:255',
            'shipping_address.email' => 'nullable|email|max:255',
            'shipping_address.phone' => 'nullable|string|max:50',
            'shipping_address.line1' => 'nullable|string|max:255',
            'shipping_address.city' => 'nullable|string|max:100',
            'shipping_address.zip' => 'nullable|string|max:20',
            'use_points' => 'nullable|integer|min:0',
        ];
    }
}
