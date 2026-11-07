<?php

namespace App\Policies;

use App\Models\ServiceRequest;
use App\Models\User;
use Illuminate\Auth\Access\Response;

/**
 * Who may see and change a service request (F4). Laravel finds this policy
 * automatically because it's named after the ServiceRequest model.
 */
class ServiceRequestPolicy
{
    public function view(User $user, ServiceRequest $serviceRequest): bool
    {
        return $this->owns($user, $serviceRequest);
    }

    public function update(User $user, ServiceRequest $serviceRequest): Response
    {
        if (! $this->owns($user, $serviceRequest)) {
            return Response::deny('This is not your request.');
        }

        return $serviceRequest->isEditable()
            ? Response::allow()
            : Response::deny('This request can no longer be edited because a provider has already quoted on it, or it is closed.');
    }

    public function cancel(User $user, ServiceRequest $serviceRequest): Response
    {
        if (! $this->owns($user, $serviceRequest)) {
            return Response::deny('This is not your request.');
        }

        return $serviceRequest->canBeCancelled()
            ? Response::allow()
            : Response::deny('Only open or quoted requests can be cancelled.');
    }

    private function owns(User $user, ServiceRequest $serviceRequest): bool
    {
        return $serviceRequest->customer_id === $user->id;
    }
}
