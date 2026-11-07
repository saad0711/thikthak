<?php

namespace App\Http\Requests\ServiceRequests;

use Illuminate\Auth\Access\Response;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;

class CancelServiceRequestRequest extends FormRequest
{
    /**
     * Only the owner, and only while the request is Open or Quoted (see ServiceRequestPolicy).
     */
    public function authorize(): Response
    {
        return Gate::inspect('cancel', $this->route('serviceRequest'));
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'cancel_reason' => ['nullable', 'string', 'max:500'],
        ];
    }
}
