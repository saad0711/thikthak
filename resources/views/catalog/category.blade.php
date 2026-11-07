@extends('layouts.app')

@section('title', $category->name)

@section('content')
    <nav class="text-sm text-stone-500">
        <a href="{{ route('home') }}" class="hover:text-stone-800">Home</a> /
        <span class="text-stone-800">{{ $category->name }}</span>
    </nav>

    <div class="mt-3 flex items-center gap-3">
        <span class="text-4xl" aria-hidden="true">{{ $category->icon }}</span>
        <div>
            <h1 class="page-title">{{ $category->name }}</h1>
            @if ($category->description)
                <p class="mt-1 text-sm text-stone-600">{{ $category->description }}</p>
            @endif
        </div>
    </div>

    @if ($services->isEmpty())
        <div class="card mt-6 text-center text-sm text-stone-600">There are no services in this category yet.</div>
    @else
        <div class="mt-6 grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
            @foreach ($services as $service)
                @include('catalog._service-card', ['service' => $service->setRelation('category', $category)])
            @endforeach
        </div>
    @endif
@endsection
