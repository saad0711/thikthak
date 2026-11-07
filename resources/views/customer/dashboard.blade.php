@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')
    <div class="flex flex-wrap items-end justify-between gap-4">
        <div>
            <h1 class="page-title">Hello, {{ auth()->user()->name }}</h1>
            <p class="mt-1 text-sm text-stone-600">Post a job, and providers in your area will send you quotes.</p>
        </div>
        <div class="flex gap-3">
            <a href="{{ route('catalog.search') }}" class="btn-secondary">Browse services</a>
            <a href="{{ route('requests.create') }}" class="btn-primary">Post a request</a>
        </div>
    </div>

    <div class="mt-6 grid grid-cols-2 gap-4 sm:grid-cols-3 lg:grid-cols-6">
        @foreach (\App\Enums\ServiceRequestStatus::cases() as $status)
            <a href="{{ route('requests.index', ['status' => $status->value]) }}" class="card p-4 hover:border-teal-300">
                <x-status-badge :status="$status" />
                <p class="mt-2 text-2xl font-semibold">{{ $counts[$status->value] }}</p>
            </a>
        @endforeach
    </div>

    <div class="card mt-6">
        <div class="flex items-center justify-between">
            <h2 class="text-lg font-semibold">Recent requests</h2>
            <a href="{{ route('requests.index') }}" class="link text-sm">See all</a>
        </div>

        @forelse ($recent as $serviceRequest)
            <a href="{{ route('requests.show', $serviceRequest) }}" class="mt-3 flex flex-wrap items-center justify-between gap-2 rounded-lg border border-stone-200 px-4 py-3 hover:border-teal-300">
                <span>
                    <span class="block font-medium">{{ $serviceRequest->title }}</span>
                    <span class="block text-xs text-stone-500">{{ $serviceRequest->service->name }} · {{ $serviceRequest->area_name }} · Deadline {{ $serviceRequest->deadline->format('j M Y') }}</span>
                </span>
                <x-status-badge :status="$serviceRequest->status" />
            </a>
        @empty
            <p class="mt-3 text-sm text-stone-600">You haven't posted any requests yet. <a href="{{ route('requests.create') }}" class="link">Post your first one</a>.</p>
        @endforelse
    </div>
@endsection
