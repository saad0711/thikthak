@extends('layouts.app')

@section('title', 'Account settings')

@section('content')
    <h1 class="page-title">Account settings</h1>
    <p class="mt-1 text-sm text-stone-600">{{ $user->email }} · {{ $user->role->label() }}</p>

    <div class="mt-6 grid gap-6 lg:grid-cols-3">
        {{-- Basic info and photo --}}
        <form method="POST" action="{{ route('account.update') }}" enctype="multipart/form-data" class="card space-y-5 lg:col-span-2">
            @csrf
            @method('PUT')
            <h2 class="text-lg font-semibold">Your details</h2>

            <div class="flex items-center gap-4">
                @if ($user->avatarUrl())
                    <img src="{{ $user->avatarUrl() }}" alt="Your profile photo" class="size-16 rounded-full object-cover">
                @else
                    <span class="flex size-16 items-center justify-center rounded-full bg-teal-100 text-lg font-semibold text-teal-800">{{ $user->initials() }}</span>
                @endif
                <div class="flex-1">
                    <x-input label="Profile photo" name="avatar" type="file" accept="image/jpeg,image/png,image/webp" hint="JPG, PNG or WEBP, up to 2 MB." />
                </div>
            </div>

            <x-input label="Full name" name="name" :value="$user->name" required />
            <x-input label="Mobile number" name="phone" type="tel" :value="$user->phone" required />
            <x-select label="Area" name="area" :options="array_combine($areas, $areas)" :value="$user->area" placeholder="Not set" />
            <x-input label="Address" name="address" :value="$user->address" placeholder="House, road, block" />

            <button type="submit" class="btn-primary">Save details</button>
        </form>

        <div class="space-y-6">
            {{-- Password --}}
            <form method="POST" action="{{ route('account.password') }}" class="card space-y-5">
                @csrf
                @method('PUT')
                <h2 class="text-lg font-semibold">Change password</h2>

                <x-input label="Current password" name="current_password" type="password" bag="password" autocomplete="current-password" required />
                <x-input label="New password" name="password" type="password" bag="password" autocomplete="new-password" hint="At least 8 characters." required />
                <x-input label="Confirm new password" name="password_confirmation" type="password" bag="password" autocomplete="new-password" required />

                <button type="submit" class="btn-primary">Change password</button>
            </form>

            {{-- Deactivate (not for admins) --}}
            @unless ($user->isAdmin())
                <form method="POST" action="{{ route('account.deactivate') }}" class="card space-y-4 border-rose-200">
                    @csrf
                    <h2 class="text-lg font-semibold text-rose-700">Deactivate account</h2>
                    <p class="text-sm text-stone-600">You'll be logged out and won't be able to log in again. Enter your password to confirm.</p>

                    <x-input label="Password" name="password" id="deactivate_password" type="password" bag="deactivate" autocomplete="current-password" required />

                    <button type="submit" class="btn-danger">Deactivate my account</button>
                </form>
            @endunless
        </div>
    </div>
@endsection
