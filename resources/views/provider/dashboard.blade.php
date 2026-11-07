@extends('layouts.app')

@section('title', 'Provider dashboard')

@section('content')
    <h1 class="page-title">Hello, {{ $user->name }}</h1>
    <p class="mt-1 text-sm text-stone-600">Provider since {{ $user->created_at->format('F Y') }}.</p>

    <div class="mt-6 grid gap-4 md:grid-cols-3">
        <div class="card">
            <h2 class="font-semibold">Profile &amp; portfolio</h2>
            <p class="mt-1 text-sm text-stone-600">Your services, prices, service area, certifications and past work.</p>
            <p class="mt-3 text-xs font-medium uppercase tracking-wide text-amber-700">Coming soon</p>
        </div>
        <div class="card">
            <h2 class="font-semibold">Availability</h2>
            <p class="mt-1 text-sm text-stone-600">Working hours, days off and going online for instant jobs.</p>
            <p class="mt-3 text-xs font-medium uppercase tracking-wide text-amber-700">Coming soon</p>
        </div>
        <div class="card">
            <h2 class="font-semibold">Verification</h2>
            <p class="mt-1 text-sm text-stone-600">Upload your NID to get the verified badge customers look for.</p>
            <p class="mt-3 text-xs font-medium uppercase tracking-wide text-amber-700">Coming soon</p>
        </div>
    </div>

    <div class="card mt-6">
        <h2 class="font-semibold">Meanwhile</h2>
        <p class="mt-1 text-sm text-stone-600">
            Keep your <a href="{{ route('account.edit') }}" class="link">account details</a> up to date and
            <a href="{{ route('catalog.search') }}" class="link">look through the service catalog</a> to see what customers can request.
        </p>
    </div>
@endsection
