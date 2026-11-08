<?php

namespace App\Policies;

use App\Models\ProviderProfile;
use App\Models\User;

class AvailabilityPolicy
{
    /**
     * Only the owning provider can view their availability.
     */
    public function view(User $user, ProviderProfile $providerProfile): bool
    {
        return $user->id === $providerProfile->user_id;
    }

    /**
     * Only the owning provider can update their availability.
     */
    public function update(User $user, ProviderProfile $providerProfile): bool
    {
        return $user->id === $providerProfile->user_id;
    }
}
