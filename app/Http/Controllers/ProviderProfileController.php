<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdateProfileRequest;
use App\Models\ProviderProfile;
use App\Models\Service;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ProviderProfileController extends Controller
{
    // PUBLIC page: anyone (even a guest) can see a provider's profile.
    // F10 (ratings) and F17 (verified badge) will add their data to this page later.
    public function show(ProviderProfile $providerProfile)
    {
        $providerProfile->load([
            'user',
            'services' => fn ($query) => $query->available(),
            'certifications',
            'portfolioItems',
        ]);

        return view('providers.show', ['profile' => $providerProfile]);
    }

    // "My Profile" page. Only logged-in providers reach this (see routes).
    public function edit(Request $request)
    {
        // Create the profile the first time the provider opens this page.
        $profile = ProviderProfile::firstOrCreate(['user_id' => $request->user()->id]);
        $profile->load(['services', 'certifications', 'portfolioItems']);

        return view('provider.profile.edit', [
            'profile' => $profile,
            'score' => $profile->completeness(),
            'missing' => $profile->missingItems(),
            'allServices' => Service::available()->orderBy('name')->get(),
            // Looks like [service_id => price] for the services this provider already offers.
            'myPrices' => $profile->services->pluck('pivot.price', 'id'),
            'maxPortfolioItems' => ProviderProfile::MAX_PORTFOLIO_ITEMS,
        ]);
    }

    // Save the "Basic information" form: bio, years of experience, photo.
    public function update(UpdateProfileRequest $request)
    {
        $profile = ProviderProfile::firstOrCreate(['user_id' => $request->user()->id]);

        $profile->bio = $request->input('bio');
        $profile->years_experience = $request->input('years_experience');

        // A new photo replaces the old one, so delete the old file first.
        if ($request->hasFile('photo')) {
            if ($profile->photo_path) {
                Storage::disk('public')->delete($profile->photo_path);
            }
            $profile->photo_path = $request->file('photo')->store('profile-photos', 'public');
        }

        $profile->save();

        return redirect(route('provider.profile.edit') . '#basic')->with('success', 'Basic information saved.');
    }
}
