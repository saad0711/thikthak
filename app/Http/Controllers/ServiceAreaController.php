<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdateServiceAreaRequest;
use App\Models\ProviderProfile;

class ServiceAreaController extends Controller
{
    // Save the service area: area name, map pin (latitude, longitude) and radius in km.
    public function update(UpdateServiceAreaRequest $request)
    {
        $profile = ProviderProfile::firstOrCreate(['user_id' => $request->user()->id]);

        $profile->update($request->validated());

        return redirect(route('provider.profile.edit') . '#area')->with('success', 'Service area saved.');
    }
}
