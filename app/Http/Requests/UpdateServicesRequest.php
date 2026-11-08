<?php

namespace App\Http\Requests;

use App\Models\Service;
use Illuminate\Foundation\Http\FormRequest;

class UpdateServicesRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isProvider() === true;
    }

    public function rules(): array
    {
        $rules = [
            'service_ids' => ['nullable', 'array'],
            'service_ids.*' => ['required', 'integer', 'distinct', 'exists:services,id'],
            'prices' => ['nullable', 'array'],
        ];

        foreach ($this->tickedServices() as $service) {
            $priceRules = ['required', 'numeric', 'min:0'];
            if ($service->price_min !== null) {
                $priceRules[] = 'min:' . $service->price_min;
            }
            if ($service->price_max !== null) {
                $priceRules[] = 'max:' . $service->price_max;
            }
            $rules['prices.' . $service->id] = $priceRules;
        }

        return $rules;
    }

    public function attributes(): array
    {
        $names = [];
        foreach ($this->tickedServices() as $service) {
            $names['prices.' . $service->id] = 'price for ' . $service->name;
        }
        return $names;
    }

    private function tickedServices()
    {
        $ids = array_filter((array) $this->input('service_ids', []), 'is_scalar');
        return Service::query()->whereIn('id', $ids)->get();
    }
}
