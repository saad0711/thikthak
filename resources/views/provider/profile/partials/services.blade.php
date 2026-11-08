{{-- Services at the provider's own prices. Uses the F1 catalog's price_min/price_max columns. --}}
<section id="services" class="bg-white p-5 rounded shadow">
    <h2 class="text-lg font-semibold mb-1">My services and prices</h2>
    <p class="text-sm text-gray-600 mb-3">Tick the services you offer and type your own price in BDT. Where the catalog has a price range, your price must stay inside it.</p>

    @if ($allServices->isEmpty())
        <p class="text-gray-600">There are no services in the catalog yet. Ask the admin to add some.</p>
    @else
        <form method="POST" action="{{ route('provider.services.update') }}" class="space-y-3">
            @csrf
            @method('PUT')

            @foreach ($allServices as $service)
                <div class="flex flex-wrap items-center gap-3">
                    <input type="checkbox" id="service_{{ $service->id }}" name="service_ids[]" value="{{ $service->id }}"
                           @checked(in_array($service->id, old('service_ids', $myPrices->keys()->all())))>
                    <label for="service_{{ $service->id }}" class="w-44">{{ $service->name }}</label>

                    <input type="number" step="0.01" min="0" name="prices[{{ $service->id }}]"
                           value="{{ old('prices.' . $service->id, $myPrices[$service->id] ?? '') }}"
                           placeholder="Your price" class="border rounded p-1 w-32">

                    <span class="text-sm text-gray-500">
                        @if ($service->price_min !== null || $service->price_max !== null)
                            Allowed: {{ $service->price_min !== null ? number_format($service->price_min, 2) : 'No minimum' }}
                            to {{ $service->price_max !== null ? number_format($service->price_max, 2) : 'No maximum' }} BDT
                        @else
                            Enter your price in BDT.
                        @endif
                    </span>
                </div>
            @endforeach

            <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded">Save services and prices</button>
        </form>
    @endif
</section>
