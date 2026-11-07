<?php

namespace App\Http\Requests\ServiceRequests;

use App\Models\Service;
use Closure;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Validates a customer posting a new service request (F4).
 */
class StoreServiceRequestRequest extends FormRequest
{
    public function authorize(): bool
    {
        return (bool) $this->user()?->isCustomer();
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            ...static::detailRules(),
            'photos' => ['nullable', 'array', 'max:'.config('thikthak.requests.max_photos')],
            'photos.*' => static::photoRules(),
        ];
    }

    /**
     * Rules shared by posting and editing a request.
     *
     * @return array<string, mixed>
     */
    public static function detailRules(): array
    {
        $maxDays = config('thikthak.requests.max_deadline_days');

        return [
            'service_id' => [
                'required', 'integer',
                // Only services customers can see (the service and its category are active).
                function (string $attribute, mixed $value, Closure $fail) {
                    if (! Service::query()->available()->whereKey($value)->exists()) {
                        $fail('Choose a service from the catalog.');
                    }
                },
            ],
            'title' => ['required', 'string', 'min:5', 'max:120'],
            'description' => ['required', 'string', 'min:20', 'max:2000'],
            'budget_min' => ['required', 'numeric', 'gt:0', 'max:10000000'],
            'budget_max' => ['required', 'numeric', 'gte:budget_min', 'max:10000000'],
            'deadline' => [
                'required', 'date',
                'after_or_equal:'.today()->addDay()->toDateString(),
                'before_or_equal:'.today()->addDays($maxDays)->toDateString(),
            ],
            'area_name' => ['required', Rule::in(array_keys(config('thikthak.areas')))],
        ];
    }

    /**
     * @return array<int, string>
     */
    public static function photoRules(): array
    {
        return ['image', 'mimes:jpg,jpeg,png,webp', 'max:'.config('thikthak.requests.max_photo_kb')];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return static::sharedMessages();
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return static::sharedAttributes();
    }

    /**
     * @return array<string, string>
     */
    public static function sharedMessages(): array
    {
        $maxDays = config('thikthak.requests.max_deadline_days');

        return [
            'budget_min.gt' => 'The minimum budget must be more than ৳0.',
            'budget_max.gte' => 'The maximum budget must be at least the minimum budget.',
            'deadline.after_or_equal' => 'The deadline must be tomorrow or later.',
            'deadline.before_or_equal' => "The deadline can be at most {$maxDays} days from today.",
            'photos.max' => 'You can attach up to :max photos.',
            'photos.*.image' => 'Each photo must be an image.',
            'photos.*.mimes' => 'Photos must be JPG, PNG or WEBP.',
            'photos.*.max' => 'Each photo must be 2 MB or smaller.',
            'area_name.in' => 'Choose an area from the list.',
        ];
    }

    /**
     * @return array<string, string>
     */
    public static function sharedAttributes(): array
    {
        return [
            'service_id' => 'service',
            'budget_min' => 'minimum budget',
            'budget_max' => 'maximum budget',
            'area_name' => 'area',
        ];
    }
}
