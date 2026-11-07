@extends('layouts.app')

@section('title', 'Forgot password')

@section('content')
    <div class="mx-auto max-w-md">
        <h1 class="page-title">Forgot your password?</h1>
        <p class="mt-1 text-sm text-stone-600">Enter your email and we'll send you a link to choose a new one.</p>

        <form method="POST" action="{{ route('password.email') }}" class="card mt-6 space-y-5">
            @csrf

            <x-input label="Email" name="email" type="email" autocomplete="email" required autofocus />

            <button type="submit" class="btn-primary w-full">Send reset link</button>
            <p class="text-center text-sm"><a href="{{ route('login') }}" class="link">Back to log in</a></p>
        </form>
    </div>
@endsection
