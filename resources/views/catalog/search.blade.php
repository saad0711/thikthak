@extends('layouts.app')

@section('title', 'Search services')

@section('content')
    <h1 class="page-title">Services</h1>
    <p class="mt-1 text-sm text-stone-600">Price ranges are a guide. Each provider sets their own price when they quote.</p>

    <div class="mt-6">
        @include('catalog._search-form')
    </div>

    <p class="mt-6 text-sm text-stone-600">
        {{ trans_choice('{0} No services found|{1} 1 service found|[2,*] :count services found', $services->total()) }}
    </p>

    @if ($services->isEmpty())
        <div class="card mt-4 text-center">
            <p class="font-medium">No services found</p>
            <p class="mt-1 text-sm text-stone-600">Try a different word, a wider price range, or <a href="{{ route('catalog.search') }}" class="link">clear the filters</a>.</p>
        </div>
    @else
        <div class="mt-4 grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
            @foreach ($services as $service)
                @include('catalog._service-card', ['service' => $service])
            @endforeach
        </div>

        <div class="mt-6">{{ $services->links() }}</div>
    @endif
@endsection
