<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class ReviewRequest extends FormRequest
{
    public function authorize(): bool
    {
        return Auth::check();
    }

    public function rules(): array
    {
        return [
            'reviewable_type' => 'nullable|string|in:product,activity,accommodation,vehicle',
            'reviewable_id' => 'nullable|integer|required_with:reviewable_type',
            'product_id' => 'nullable|exists:products,id',
            'rating' => 'required|integer|min:1|max:5',
            'title' => 'nullable|string|max:150',
            'body' => 'required|string|max:2000',
            'images' => 'nullable|array|max:5',
            'images.*' => 'image|max:5120',
        ];
    }
}
