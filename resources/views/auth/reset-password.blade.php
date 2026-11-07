@extends('layouts.app')

@section('title', 'Choose a new password')

@section('content')
    <div class="mx-auto max-w-md">
        <h1 class="page-title">Choose a new password</h1>

        <form method="POST" action="{{ route('password.store') }}" class="card mt-6 space-y-5">
            @csrf
            <input type="hidden" name="token" value="{{ $token }}">

            <x-input label="Email" name="email" type="email" :value="$email" autocomplete="email" required />
            <x-input label="New password" name="password" type="password" autocomplete="new-password" hint="At least 8 characters." required />
            <x-input label="Confirm new password" name="password_confirmation" type="password" autocomplete="new-password" required />

            <button type="submit" class="btn-primary w-full">Reset password</button>
        </form>
    </div>
@endsection
