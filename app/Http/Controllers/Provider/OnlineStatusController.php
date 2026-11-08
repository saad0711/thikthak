<?php

namespace App\Http\Controllers\Provider;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;

class OnlineStatusController extends Controller
{
    /**
     * Toggle the provider's online/offline status.
     */
    public function toggle(): RedirectResponse
    {
        $profile = auth()->user()->providerProfile;
        Gate::authorize('update', $profile);

        if ($profile->is_online) {
            // Always allow going offline
            $profile->update(['is_online' => false]);

            return back()->with('success', 'You are now offline.');
        }

        // Going online requires canGoOnline() AND working hours
        if (! $profile->canGoOnline()) {
            return back()->with('error', 'You need at least 60% profile completeness to go online. Please complete your profile first.');
        }

        if (! $profile->hasWorkingHours()) {
            return back()->with('error', 'You need to set working hours for at least one day before going online.');
        }

        $profile->update(['is_online' => true]);

        return back()->with('success', 'You are now online!');
    }
}
