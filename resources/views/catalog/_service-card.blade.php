{{-- One service in the catalog (F1), with a shortcut into posting a request (F4). --}}
<article class="card flex flex-col p-5">
    <p class="text-xs font-medium uppercase tracking-wide text-teal-700">{{ $service->category->name }}</p>
    <h3 class="mt-1 font-semibold text-stone-900">{{ $service->name }}</h3>
    @if ($service->description)
        <p class="mt-2 line-clamp-3 text-sm text-stone-600">{{ $service->description }}</p>
    @endif
    <div class="mt-auto flex items-center justify-between gap-3 pt-4">
        <span class="text-sm font-semibold text-stone-800">{{ $service->priceRangeLabel() }}</span>
        @guest
            <a href="{{ route('login') }}" class="link text-sm">Log in to request</a>
        @else
            @if (auth()->user()->isCustomer())
                <a href="{{ route('requests.create', ['service' => $service->id]) }}" class="btn-secondary px-3 py-1.5">Request this</a>
            @endif
        @endguest
    </div>
</article>
