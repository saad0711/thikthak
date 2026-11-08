<?php

namespace App\Policies;

use App\Models\ProviderProfile;
use App\Models\User;

class ProviderProfilePolicy
{
    public function create(User $user): bool
    {
        return $user->isProvider();
    }

    public function update(User $user, ProviderProfile $profile): bool
    {
        return $user->isProvider() && (int) $user->id === (int) $profile->user_id;
    }
}
