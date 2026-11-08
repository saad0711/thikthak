<?php

namespace App\Http\Controllers;

use App\Http\Requests\PortfolioItemRequest;
use App\Models\PortfolioItem;
use App\Models\ProviderProfile;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;

class PortfolioItemController extends Controller
{
    // Upload a portfolio image.
    public function store(PortfolioItemRequest $request)
    {
        $profile = ProviderProfile::firstOrCreate(['user_id' => $request->user()->id]);

        // Rule: at most 12 images per provider.
        if ($profile->portfolioItems()->count() >= ProviderProfile::MAX_PORTFOLIO_ITEMS) {
            return redirect(route('provider.profile.edit') . '#portfolio')
                ->withErrors(['image' => 'You can upload at most ' . ProviderProfile::MAX_PORTFOLIO_ITEMS . ' portfolio images. Delete one first.']);
        }

        $profile->portfolioItems()->create([
            'image_path' => $request->file('image')->store('portfolio', 'public'),
            'caption' => $request->input('caption'),
        ]);

        return redirect(route('provider.profile.edit') . '#portfolio')->with('success', 'Portfolio image uploaded.');
    }

    // Delete a portfolio image and its file. Only the owner may do this.
    public function destroy(PortfolioItem $portfolioItem)
    {
        Gate::authorize('update', $portfolioItem->providerProfile);

        Storage::disk('public')->delete($portfolioItem->image_path);

        $portfolioItem->delete();

        return redirect(route('provider.profile.edit') . '#portfolio')->with('success', 'Portfolio image deleted.');
    }
}
