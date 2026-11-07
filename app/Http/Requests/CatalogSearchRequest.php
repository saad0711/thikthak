<?php

namespace App\Http\Requests;

use App\Models\Service;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Filters for the public catalog search (F1). If the price range is invalid
 * (min > max), validation fails before any search query runs.
 */
class CatalogSearchRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'q' => ['nullable', 'string', 'max:100'],
            'category' => ['nullable', 'integer', 'exists:service_categories,id'],
            'min_price' => ['nullable', 'numeric', 'min:0'],
            'max_price' => ['nullable', 'numeric', 'min:0', Rule::when($this->filled('min_price'), 'gte:min_price')],
            'sort' => ['nullable', Rule::in(array_keys(Service::SORTS))],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'max_price.gte' => 'The maximum price can\'t be lower than the minimum price.',
        ];
    }

    /**
     * Send people back to the search page itself, with their filters, when the filters are invalid.
     */
    protected function getRedirectUrl(): string
    {
        return route('catalog.search');
    }
}
