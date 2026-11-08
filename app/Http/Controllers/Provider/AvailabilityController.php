<?php

namespace App\Http\Controllers\Provider;

use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateWorkingHoursRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class AvailabilityController extends Controller
{
    /**
     * Show the form for editing working hours and slot length.
     */
    public function edit(): View
    {
        $profile = auth()->user()->providerProfile;
        Gate::authorize('update', $profile);

        $workingHours = $profile->workingHours->keyBy('day_of_week');

        return view('provider.availability.edit', [
            'profile' => $profile,
            'workingHours' => $workingHours,
        ]);
    }

    /**
     * Update working hours and slot length.
     */
    public function update(UpdateWorkingHoursRequest $request): RedirectResponse
    {
        $profile = auth()->user()->providerProfile;
        Gate::authorize('update', $profile);

        $validated = $request->validated();

        // Update slot length
        $profile->update(['slot_minutes' => $validated['slot_minutes']]);

        // Sync working hours
        foreach ($validated['days'] as $dayOfWeek => $day) {
            if (! empty($day['enabled']) && ! empty($day['start_time']) && ! empty($day['end_time'])) {
                $profile->workingHours()->updateOrCreate(
                    ['day_of_week' => (int) $dayOfWeek],
                    [
                        'start_time' => $day['start_time'],
                        'end_time' => $day['end_time'],
                    ]
                );
            } else {
                // Remove working hours for disabled days
                $profile->workingHours()->where('day_of_week', (int) $dayOfWeek)->delete();
            }
        }

        // If no working hours remain and provider is online, force offline
        if (! $profile->hasWorkingHours() && $profile->is_online) {
            $profile->update(['is_online' => false]);
        }

        return redirect()
            ->route('provider.availability.edit')
            ->with('success', 'Working hours updated successfully.');
    }
}
