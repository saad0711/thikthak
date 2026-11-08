<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreDayOffRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // Authorization is handled by the policy
    }

    public function rules(): array
    {
        $providerProfileId = $this->user()->providerProfile->id;

        return [
            'date' => [
                'required',
                'date',
                'after_or_equal:today',
                Rule::unique('provider_days_off')->where(function ($query) use ($providerProfileId) {
                    return $query->where('provider_profile_id', $providerProfileId);
                }),
            ],
            'reason' => ['nullable', 'string', 'max:255'],
        ];
    }

    public function messages(): array
    {
        return [
            'date.after_or_equal' => 'The day off must be today or a future date.',
            'date.unique' => 'You have already added this date as a day off.',
        ];
    }
}
