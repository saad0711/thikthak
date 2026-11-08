<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

// Checks the "Basic information" form: bio, years of experience, profile photo.
class UpdateProfileRequest extends FormRequest
{
    // The route already allows providers only (can:create middleware), so this is true.
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'bio' => ['nullable', 'string', 'max:1000'],
            'years_experience' => ['nullable', 'integer', 'between:0,60'],
            // max is in kilobytes: 2048 KB = 2 MB.
            'photo' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        ];
    }
}
