<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

// Checks the certification form. It is used for both "add" and "edit".
class CertificationRequest extends FormRequest
{
    // When editing, only the owner of this certification may continue.
    // When adding, there is no certification yet, and the route already allows providers only.
    public function authorize(): bool
    {
        $certification = $this->route('certification');

        if ($certification === null) {
            return true;
        }

        return $this->user()->can('update', $certification->providerProfile);
    }

    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:100'],
            'issuer' => ['required', 'string', 'max:100'],
            'year' => ['required', 'integer', 'between:1970,' . now()->year],
            'file' => ['nullable', 'file', 'mimes:jpg,jpeg,png,webp,pdf', 'max:2048'],
        ];
    }
}
