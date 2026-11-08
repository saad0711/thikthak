@extends('layouts.app')

@section('title', $profile->user->name . ' - ThikThak')

@section('content')
<div class="max-w-3xl mx-auto py-8 px-4 space-y-6">

    {{-- Header: photo, name, experience, service area --}}
    <div class="bg-white p-5 rounded shadow flex flex-wrap items-center gap-5">
        @if ($profile->photo_path)
            <img src="{{ $profile->photoUrl() }}" alt="Photo of {{ $profile->user->name }}" class="w-28 h-28 rounded-full object-cover">
        @else
            <div class="w-28 h-28 rounded-full bg-gray-200 flex items-center justify-center text-3xl text-gray-500">
                {{ mb_substr($profile->user->name, 0, 1) }}
            </div>
        @endif

        <div>
            <h1 class="text-2xl font-bold">{{ $profile->user->name }}</h1>

            @if ($profile->years_experience !== null)
                <p class="text-gray-700">{{ $profile->years_experience }} years of experience</p>
            @endif

            {{-- Only the area name and radius are public. The exact map pin stays private. --}}
            @if ($profile->hasServiceArea())
                <p class="text-gray-700">Works in {{ $profile->area_name }}, within {{ $profile->radius_km }} km</p>
            @endif

            {{-- F10 will show the average rating here. F17 will show the "Verified" badge here. --}}
        </div>
    </div>

    {{-- About --}}
    @if ($profile->bio)
        <section class="bg-white p-5 rounded shadow">
            <h2 class="text-lg font-semibold mb-2">About</h2>
            <p class="whitespace-pre-line">{{ $profile->bio }}</p>
        </section>
    @endif

    {{-- Services with the provider's own prices --}}
    <section class="bg-white p-5 rounded shadow">
        <h2 class="text-lg font-semibold mb-2">Services and prices</h2>
        @if ($profile->services->isEmpty())
            <p class="text-gray-600">No services listed yet.</p>
        @else
            <ul class="divide-y">
                @foreach ($profile->services as $service)
                    <li class="py-2 flex justify-between">
                        <span>{{ $service->name }}</span>
                        <span class="font-medium">{{ number_format($service->pivot->price, 2) }} BDT</span>
                    </li>
                @endforeach
            </ul>
        @endif
    </section>

    {{-- Certifications (title, issuer, year only. The files stay private.) --}}
    <section class="bg-white p-5 rounded shadow">
        <h2 class="text-lg font-semibold mb-2">Certifications</h2>
        @if ($profile->certifications->isEmpty())
            <p class="text-gray-600">No certifications listed yet.</p>
        @else
            <ul class="divide-y">
                @foreach ($profile->certifications as $certification)
                    <li class="py-2">
                        <p class="font-medium">{{ $certification->title }}</p>
                        <p class="text-sm text-gray-600">{{ $certification->issuer }}, {{ $certification->year }}</p>
                    </li>
                @endforeach
            </ul>
        @endif
    </section>

    {{-- Portfolio --}}
    <section class="bg-white p-5 rounded shadow">
        <h2 class="text-lg font-semibold mb-2">Portfolio</h2>
        @if ($profile->portfolioItems->isEmpty())
            <p class="text-gray-600">No portfolio images yet.</p>
        @else
            <div class="grid grid-cols-2 sm:grid-cols-3 gap-4">
                @foreach ($profile->portfolioItems as $item)
                    <div>
                        <img src="{{ $item->imageUrl() }}" alt="{{ $item->caption ?? 'Portfolio image' }}" class="w-full h-32 object-cover rounded">
                        @if ($item->caption)
                            <p class="text-sm mt-1">{{ $item->caption }}</p>
                        @endif
                    </div>
                @endforeach
            </div>
        @endif
    </section>

</div>
@endsection
