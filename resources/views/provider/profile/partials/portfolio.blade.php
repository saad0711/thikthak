{{-- Portfolio: grid of past-work images and an upload form. --}}
<section id="portfolio" class="bg-white p-5 rounded shadow">
    <h2 class="text-lg font-semibold mb-1">Portfolio</h2>
    <p class="text-sm text-gray-600 mb-3">
        Photos of your past work. You have {{ $profile->portfolioItems->count() }} of {{ $maxPortfolioItems }}.
    </p>

    @if ($profile->portfolioItems->isEmpty())
        <p class="text-gray-600 mb-4">You have not uploaded any portfolio images yet.</p>
    @else
        <div class="grid grid-cols-2 sm:grid-cols-3 gap-4 mb-4">
            @foreach ($profile->portfolioItems as $item)
                <div>
                    <img src="{{ $item->imageUrl() }}" alt="{{ $item->caption ?? 'Portfolio image' }}" class="w-full h-32 object-cover rounded">
                    @if ($item->caption)
                        <p class="text-sm mt-1">{{ $item->caption }}</p>
                    @endif
                    <form method="POST" action="{{ route('provider.portfolio.destroy', $item) }}"
                          onsubmit="return confirm('Delete this image?');">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="text-sm text-red-600 hover:underline">Delete</button>
                    </form>
                </div>
            @endforeach
        </div>
    @endif

    <h3 class="font-medium mb-2">Upload an image</h3>
    <form method="POST" action="{{ route('provider.portfolio.store') }}" enctype="multipart/form-data" class="space-y-3">
        @csrf

        <div>
            <label for="portfolio_image" class="block text-sm font-medium">Image (JPG, PNG or WebP, up to 2 MB)</label>
            <input type="file" id="portfolio_image" name="image" accept=".jpg,.jpeg,.png,.webp">
        </div>
        <div>
            <label for="portfolio_caption" class="block text-sm font-medium">Caption (optional, up to 150 characters)</label>
            <input type="text" id="portfolio_caption" name="caption" maxlength="150" value="{{ old('caption') }}" class="border rounded p-2 w-full">
        </div>

        <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded">Upload image</button>
    </form>
</section>
