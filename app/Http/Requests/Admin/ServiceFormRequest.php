<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Validates creating and editing a catalog service (F1).
 */
class ServiceFormRequest extends FormRequest
{
    public function authorize(): bool
    {
        return (bool) $this->user()?->isAdmin();
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'name' => trim((string) $this->input('name')),
            'is_active' => $this->boolean('is_active'),
            'sort_order' => $this->filled('sort_order') ? $this->input('sort_order') : 0,
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'service_category_id' => ['required', 'integer', 'exists:service_categories,id'],
            'name' => [
                'required', 'string', 'max:80',
                Rule::unique('services', 'name')
                    ->where('service_category_id', $this->input('service_category_id'))
                    ->ignore($this->route('service')),
            ],
            'description' => ['nullable', 'string', 'max:1000'],
            'price_min' => ['required', 'numeric', 'min:0', 'max:10000000'],
            'price_max' => ['required', 'numeric', 'gte:price_min', 'max:10000000'],
            'sort_order' => ['integer', 'min:0', 'max:999'],
            'is_active' => ['boolean'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'name.unique' => 'This category already has a service with this name.',
            'price_max.gte' => 'The maximum price must be at least the minimum price.',
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'service_category_id' => 'category',
            'price_min' => 'minimum price',
            'price_max' => 'maximum price',
        ];
    }
}
