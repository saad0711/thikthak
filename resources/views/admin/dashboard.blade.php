@extends('layouts.app')

@section('title', 'Admin dashboard')

@section('content')
    <h1 class="page-title">Admin dashboard</h1>

    <div class="mt-6 grid grid-cols-2 gap-4 lg:grid-cols-4">
        <div class="card p-4">
            <p class="text-sm text-stone-500">Customers</p>
            <p class="mt-1 text-2xl font-semibold">{{ $customers }}</p>
        </div>
        <div class="card p-4">
            <p class="text-sm text-stone-500">Providers</p>
            <p class="mt-1 text-2xl font-semibold">{{ $providers }}</p>
        </div>
        <a href="{{ route('admin.categories.index') }}" class="card p-4 hover:border-teal-300">
            <p class="text-sm text-stone-500">Categories</p>
            <p class="mt-1 text-2xl font-semibold">{{ $activeCategories }} <span class="text-sm font-normal text-stone-500">active of {{ $categories }}</span></p>
        </a>
        <a href="{{ route('admin.services.index') }}" class="card p-4 hover:border-teal-300">
            <p class="text-sm text-stone-500">Services</p>
            <p class="mt-1 text-2xl font-semibold">{{ $activeServices }} <span class="text-sm font-normal text-stone-500">active of {{ $services }}</span></p>
        </a>
    </div>

    <div class="card mt-6">
        <h2 class="text-lg font-semibold">Service requests by status</h2>
        <div class="mt-4 grid grid-cols-2 gap-4 sm:grid-cols-3 lg:grid-cols-6">
            @foreach (\App\Enums\ServiceRequestStatus::cases() as $status)
                <div>
                    <x-status-badge :status="$status" />
                    <p class="mt-2 text-2xl font-semibold">{{ $requestCounts[$status->value] }}</p>
                </div>
            @endforeach
        </div>
    </div>

    <div class="mt-6 flex flex-wrap gap-3">
        <a href="{{ route('admin.categories.create') }}" class="btn-primary">Add a category</a>
        <a href="{{ route('admin.services.create') }}" class="btn-secondary">Add a service</a>
    </div>
@endsection
