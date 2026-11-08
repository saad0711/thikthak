<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

// Checks the "Service area" form: area name, map pin and radius.
class UpdateServiceAreaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'area_name' => ['required', 'string', 'max:100'],
            'latitude' => ['required', 'numeric', 'between:-90,90'],
            'longitude' => ['required', 'numeric', 'between:-180,180'],
            'radius_km' => ['required', 'integer', 'between:1,50'],
        ];
    }

    public function messages(): array
    {
        return [
            'latitude.required' => 'Click on the map to place your pin.',
            'longitude.required' => 'Click on the map to place your pin.',
        ];
    }
}
