@extends('layouts.app')

@section('title', $serviceRequest->title)

@section('content')
    <nav class="text-sm text-stone-500">
        <a href="{{ route('requests.index') }}" class="hover:text-stone-800">My requests</a> /
        <span class="text-stone-800">{{ $serviceRequest->title }}</span>
    </nav>

    <div class="mt-3 flex flex-wrap items-start justify-between gap-4">
        <div>
            <h1 class="page-title">{{ $serviceRequest->title }}</h1>
            <p class="mt-1 text-sm text-stone-600">{{ $serviceRequest->service->category->name }} · {{ $serviceRequest->service->name }}</p>
        </div>
        <x-status-badge :status="$serviceRequest->status" class="text-sm" />
    </div>

    <div class="mt-6 grid gap-6 lg:grid-cols-3">
        <div class="space-y-6 lg:col-span-2">
            <section class="card">
                <h2 class="font-semibold">Description</h2>
                <p class="mt-2 whitespace-pre-line text-sm text-stone-700">{{ $serviceRequest->description }}</p>
            </section>

            @if ($serviceRequest->photos->isNotEmpty())
                <section class="card">
                    <h2 class="font-semibold">Photos</h2>
                    <div class="mt-3 grid grid-cols-2 gap-3 sm:grid-cols-3">
                        @foreach ($serviceRequest->photos as $photo)
                            <a href="{{ $photo->url() }}" target="_blank" rel="noopener">
                                <img src="{{ $photo->url() }}" alt="Photo {{ $loop->iteration }} of the job" class="aspect-square w-full rounded-lg object-cover">
                            </a>
                        @endforeach
                    </div>
                </section>
            @endif
        </div>

        <aside class="space-y-6">
            <section class="card">
                <dl class="space-y-3 text-sm">
                    <div>
                        <dt class="text-stone-500">Budget</dt>
                        <dd class="font-medium">{{ $serviceRequest->budgetLabel() }}</dd>
                    </div>
                    <div>
                        <dt class="text-stone-500">Needed by</dt>
                        <dd class="font-medium">{{ $serviceRequest->deadline->format('l, j F Y') }}</dd>
                    </div>
                    <div>
                        <dt class="text-stone-500">Area</dt>
                        <dd class="font-medium">{{ $serviceRequest->area_name }}</dd>
                    </div>
                    <div>
                        <dt class="text-stone-500">Posted</dt>
                        <dd class="font-medium">{{ $serviceRequest->created_at->format('j M Y, g:i a') }}</dd>
                    </div>
                    @if ($serviceRequest->cancelled_at)
                        <div>
                            <dt class="text-stone-500">Cancelled</dt>
                            <dd class="font-medium">{{ $serviceRequest->cancelled_at->format('j M Y, g:i a') }}</dd>
                            @if ($serviceRequest->cancel_reason)
                                <dd class="mt-1 text-stone-600">"{{ $serviceRequest->cancel_reason }}"</dd>
                            @endif
                        </div>
                    @endif
                </dl>
            </section>

            @if ($serviceRequest->isEditable())
                <a href="{{ route('requests.edit', $serviceRequest) }}" class="btn-secondary w-full">Edit request</a>
            @endif

            @if ($serviceRequest->canBeCancelled())
                <form method="POST" action="{{ route('requests.cancel', $serviceRequest) }}" class="card space-y-3"
                      onsubmit="return confirm('Cancel this request? Providers will no longer see it.')">
                    @csrf
                    <h2 class="font-semibold">Cancel this request</h2>
                    <x-textarea label="Reason (optional)" name="cancel_reason" rows="2" maxlength="500" />
                    <button type="submit" class="btn-danger w-full">Cancel request</button>
                </form>
            @endif
        </aside>
    </div>
@endsection
