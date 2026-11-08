<?php

namespace App\Http\Requests\ServiceRequests;

use App\Models\ServiceRequest;
use Illuminate\Auth\Access\Response;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

/**
 * Validates a customer editing an Open service request (F4).
 */
class UpdateServiceRequestRequest extends FormRequest
{
    /**
     * Only the owner, and only while the request is still Open (see ServiceRequestPolicy).
     */
    public function authorize(): Response
    {
        return Gate::inspect('update', $this->serviceRequest());
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            ...StoreServiceRequestRequest::detailRules(),
            'photos' => ['nullable', 'array', 'max:'.config('thikthak.requests.max_photos')],
            'photos.*' => StoreServiceRequestRequest::photoRules(),
            'remove_photos' => ['nullable', 'array'],
            'remove_photos.*' => [
                'integer',
                Rule::exists('service_request_photos', 'id')->where('service_request_id', $this->serviceRequest()->id),
            ],
        ];
    }

    /**
     * Photos kept + photos added must not go over the limit.
     *
     * @return array<int, callable>
     */
    public function after(): array
    {
        return [
            function (Validator $validator) {
                $max = config('thikthak.requests.max_photos');
                $kept = $this->serviceRequest()->photos()->count() - count(array_unique($this->input('remove_photos', [])));
                $added = count($this->file('photos', []));

                if ($kept + $added > $max) {
                    $validator->errors()->add('photos', "A request can have at most {$max} photos. Remove some before adding more.");
                }
            },
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return StoreServiceRequestRequest::sharedMessages();
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return StoreServiceRequestRequest::sharedAttributes();
    }

    private function serviceRequest(): ServiceRequest
    {
        return $this->route('serviceRequest');
    }
}
