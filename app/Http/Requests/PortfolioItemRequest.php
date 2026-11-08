<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

// Checks the "upload portfolio image" form.
class PortfolioItemRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'image' => ['required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'caption' => ['nullable', 'string', 'max:150'],
        ];
    }
}
