@extends('layouts.app')

@section('title', 'Verify your email')

@section('content')
    <div class="mx-auto max-w-md">
        <div class="card space-y-4">
            <h1 class="page-title">Check your email</h1>
            <p class="text-sm text-stone-600">
                We've sent a verification link to <span class="font-medium text-stone-800">{{ auth()->user()->email }}</span>.
                Click it to finish setting up your account.
            </p>
            @if (app()->isLocal())
                <p class="rounded-lg bg-amber-50 px-3 py-2 text-xs text-amber-800">
                    While developing, emails aren't really sent. Open <code>storage/logs/laravel.log</code> and copy the link from the newest message.
                </p>
            @endif

            <div class="flex flex-wrap items-center gap-3">
                <form method="POST" action="{{ route('verification.send') }}">
                    @csrf
                    <button type="submit" class="btn-primary">Send the link again</button>
                </form>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="btn-secondary">Log out</button>
                </form>
            </div>
        </div>
    </div>
@endsection
