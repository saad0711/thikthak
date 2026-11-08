<?php

namespace App\Http\Controllers\Provider;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreDayOffRequest;
use App\Models\DayOff;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class DayOffController extends Controller
{
    /**
     * Display the list of days off.
     */
    public function index(): View
    {
        $profile = auth()->user()->providerProfile;
        Gate::authorize('view', $profile);

        $daysOff = $profile->daysOff()
            ->where('date', '>=', now()->toDateString())
            ->orderBy('date')
            ->get();

        return view('provider.days-off.index', [
            'profile' => $profile,
            'daysOff' => $daysOff,
        ]);
    }

    /**
     * Store a new day off.
     */
    public function store(StoreDayOffRequest $request): RedirectResponse
    {
        $profile = auth()->user()->providerProfile;
        Gate::authorize('update', $profile);

        $profile->daysOff()->create($request->validated());

        return redirect()
            ->route('provider.days-off.index')
            ->with('success', 'Day off added successfully.');
    }

    /**
     * Remove a day off.
     */
    public function destroy(DayOff $dayOff): RedirectResponse
    {
        $profile = auth()->user()->providerProfile;
        Gate::authorize('update', $profile);

        // Ensure the day off belongs to this provider
        if ($dayOff->provider_profile_id !== $profile->id) {
            abort(403);
        }

        $dayOff->delete();

        return redirect()
            ->route('provider.days-off.index')
            ->with('success', 'Day off removed.');
    }
}
