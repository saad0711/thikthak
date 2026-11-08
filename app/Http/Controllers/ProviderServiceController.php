<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdateServicesRequest;
use App\Models\ProviderProfile;

class ProviderServiceController extends Controller
{
    // Save the services the provider offers, each with the provider's own price.
    public function update(UpdateServicesRequest $request)
    {
        $profile = ProviderProfile::firstOrCreate(['user_id' => $request->user()->id]);

        // Build [service_id => ['price' => 1200], ...] from the form.
        $services = [];
        foreach ($request->input('service_ids', []) as $serviceId) {
            $services[$serviceId] = ['price' => $request->input('prices.' . $serviceId)];
        }

        // sync() adds new rows, updates changed prices and removes services that were unticked.
        $profile->services()->sync($services);

        return redirect(route('provider.profile.edit') . '#services')->with('success', 'Services and prices saved.');
    }
}
