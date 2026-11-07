@extends('layouts.app')

@section('title', 'My requests')

@section('content')
    <div class="flex flex-wrap items-center justify-between gap-4">
        <h1 class="page-title">My requests</h1>
        <a href="{{ route('requests.create') }}" class="btn-primary">Post a request</a>
    </div>

    <nav class="mt-6 flex flex-wrap gap-2 text-sm" aria-label="Filter by status">
        <a href="{{ route('requests.index') }}"
           @class(['rounded-full px-3 py-1', 'bg-teal-700 text-white' => ! $currentStatus, 'bg-white border border-stone-300 text-stone-700 hover:bg-stone-100' => $currentStatus])>All</a>
        @foreach (\App\Enums\ServiceRequestStatus::cases() as $status)
            <a href="{{ route('requests.index', ['status' => $status->value]) }}"
               @class(['rounded-full px-3 py-1', 'bg-teal-700 text-white' => $currentStatus === $status, 'bg-white border border-stone-300 text-stone-700 hover:bg-stone-100' => $currentStatus !== $status])>{{ $status->label() }}</a>
        @endforeach
    </nav>

    <div class="mt-4 space-y-3">
        @forelse ($serviceRequests as $serviceRequest)
            <a href="{{ route('requests.show', $serviceRequest) }}" class="card flex flex-wrap items-center justify-between gap-3 p-4 hover:border-teal-300">
                <span>
                    <span class="block font-medium text-stone-900">{{ $serviceRequest->title }}</span>
                    <span class="block text-xs text-stone-500">
                        {{ $serviceRequest->service->name }} · {{ $serviceRequest->area_name }} ·
                        {{ $serviceRequest->budgetLabel() }} · Needed by {{ $serviceRequest->deadline->format('j M Y') }}
                    </span>
                </span>
                <x-status-badge :status="$serviceRequest->status" />
            </a>
        @empty
            <div class="card text-center text-sm text-stone-600">
                @if ($currentStatus)
                    You have no {{ strtolower($currentStatus->label()) }} requests.
                @else
                    You haven't posted any requests yet. <a href="{{ route('requests.create') }}" class="link">Post your first one</a>.
                @endif
            </div>
        @endforelse
    </div>

    <div class="mt-6">{{ $serviceRequests->links() }}</div>
@endsection
